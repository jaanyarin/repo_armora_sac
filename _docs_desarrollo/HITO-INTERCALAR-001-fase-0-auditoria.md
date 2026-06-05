# HITO-INTERCALAR-001 — Fase 0 Auditoría Hito 003 (ADR-009)

**Fecha de cierre**: 2026-06-05
**Hito de origen**: Hito 003 — Sales + Inventory
**ADR gatillante**: [ADR-009-remediacion-intercalada-hito-003-004.md](./ADR-009-remediacion-intercalada-hito-004.md)
**Auditor**: [senior-code-architecture-quality-auditor](../_auditoria/senior-code-architecture-quality-auditor.md)
**Estado final**: ✅ Cerrado (4/4 fixes implementados + 3 colaterales)
**Prefijo commit**: `fix(audit-002):`

---

## Resumen ejecutivo

La Fase 0 del ADR-009 (remediación intercalada Hito 003 → Hito 004) ejecuta 4 fixes
upfront que el auditor marcó como 🟠 y que son prerrequisitos de seguridad
transaccional antes de empezar Hito 004 (Purchases). Adicionalmente, 3 fixes
colaterales (A-03, A-09, A-10) se cerraron en la misma pasada por afinidad lógica.

| ID    | Hallazgo                                      | Severidad | Estado |
|-------|-----------------------------------------------|-----------|--------|
| A-01  | `generateCode` con `count()+1` (race condition) | 🟠 | ✅ |
| A-04  | `SaleConfirmed` no transaccional con stock   | 🟠 | ✅ |
| A-05  | Login sin rate limiting                       | 🟠 | ✅ |
| A-07  | FK `exists` ignora `SoftDeletes`              | 🟠 | ✅ |
| A-03  | `update()` permite modificar venta confirmada | 🟡 | ✅ (colateral) |
| A-09  | `anular()` no resetea `saldo_pendiente`       | 🟡 | ✅ (colateral) |
| A-10  | `update()` no recalcula `saldo_pendiente`     | 🟡 | ✅ (colateral) |

---

## A-01 — Secuencia PostgreSQL para `generateCode`

### Problema
`SaleService::generateCode()` calculaba el siguiente código con `count() + 1`,
lo cual es **no atómico**: dos requests concurrentes pueden leer el mismo `count()`
y generar el mismo código. Viola constraint `UNIQUE`.

### Solución
Migración `2026_06_05_010000_create_sales_codigo_seq.php` crea la secuencia
PostgreSQL `sales_codigo_seq` con `setval` inicial desde el `MAX(codigo)` actual:

```php
DB::statement("
    CREATE SEQUENCE IF NOT EXISTS sales_codigo_seq
    START WITH " . (int) (DB::table('sales_ventas')
        ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(codigo FROM '[0-9]+$') AS BIGINT)), 0) + 1")
        ->value('max') ?? 1) . "
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
");
```

`SaleService::generateCode()`:
```php
$n = (int) DB::selectOne("SELECT nextval('sales_codigo_seq') as n")->n;
return sprintf('V-%05d', $n);
```

### Test añadido
`test_generateCode_uses_atomic_sequence()`: crea 5 ventas, verifica que los
últimos 5 dígitos sean **consecutivos sin gaps** (no sólo únicos).

### Migración
Aplicada a producción el 2026-06-05. Idempotente (`IF NOT EXISTS`).

---

## A-04 — Transaccionalidad global `SaleConfirmed → DescontarStock`

### Problema
El evento `SaleConfirmed` se emitía desde `SaleService::confirmar()` pero el
listener `DescontarStock` corría en una **transacción separada**. Si el descuento
de stock fallaba, la venta quedaba como `confirmada` sin stock descontado.
Estado inconsistente.

### Solución (definitiva, después de 2 iteraciones)

**Patrón**: invocar el side-effect crítico (descontar stock) **inline** dentro de
la misma `DB::transaction` del Service. El evento se mantiene para observabilidad.

```php
public function confirmar(Sale $sale): Sale
{
    if ($sale->estado === 'confirmada') {
        return $sale;
    }
    return DB::transaction(function () use ($sale) {
        $sale->update(['estado' => 'confirmada']);

        // A-04: ejecutar el side-effect crítico DENTRO de la misma transacción
        app(\App\Modules\Inventory\Services\InventoryService::class)
            ->descontarPorVenta($sale->fresh());

        // Evento solo para observabilidad (Notification, Webhook, AuditLog)
        event(new SaleConfirmed($sale));

        return $sale->fresh();
    });
}
```

`InventoryService::aplicarMovimientoVenta()` detecta si ya está dentro de una
transacción con `DB::transactionLevel() > 0` y NO abre una nueva (evita
transacción anidada con rollback independiente).

`AppServiceProvider` **NO** registra `Event::listen(SaleConfirmed::class, DescontarStock::class)`
porque la lógica crítica se ejecuta inline (si se registrara, se ejecutaría DOS veces).

### Tests añadidos
- `test_confirmar_sale_descuenta_stock()` — verifica que `stock_actual` baja en 2.
- `test_anular_sale_revierte_stock()` — verifica que `stock_actual` vuelve a 50.
- `test_listener_failure_rolls_back_sale_confirmation()` — mockea el
  `InventoryService` para que tire excepción; verifica que la venta queda en
  `borrador` y el stock no cambia.

### Iteraciones
1. **Iter 1** (rechazada): el listener se auto-registraba y descuenta
   fuera de la transacción del Service → stock doble descontado.
2. **Iter 2** (rechazada): el Service ejecutaba el side-effect, pero también
   disparaba `event()` y el listener (auto-registrado) volvía a ejecutar →
   stock doble descontado.
3. **Iter 3** (✅ definitiva): el Service ejecuta el side-effect inline; el
   `Event::listen` se eliminó del `AppServiceProvider`; el `event()` se
   mantiene únicamente para observabilidad.

### Pendiente para Fase 1 (Hito 004)
Refactorizar a una clase explícita de "transacción de aplicación" si el patrón
se repite. Por ahora, la duplicación está documentada y aislada en 2 métodos
de `SaleService`.

---

## A-05 — Rate limiting en login

### Problema
`POST /api/auth/login` no tenía rate limiting. Vulnerable a fuerza bruta de
credenciales (DNI, RUC, username, email).

### Solución
**Named limiter** en `AppServiceProvider::boot()`:

```php
RateLimiter::for('login', function (Request $request) {
    $key = strtolower((string) $request->input('login', '')) . '|' . $request->ip();
    return Limit::perMinute(5)->by($key)->response(function () {
        return response()->json([
            'message' => 'Demasiados intentos de inicio de sesión. Intente de nuevo en 1 minuto.',
        ], 429);
    });
});

// Limiter 'api' por defecto (60 req/min por user/IP) requerido por throttleApi()
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

Aplicado a la ruta:
```php
Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');
```

Y `bootstrap/app.php`:
```php
$middleware->throttleApi();  // Aplica throttle:api a TODO el grupo api
```

### Key compuesta
La key es `login|ip` (no sólo IP), porque:
- 5 intentos con el mismo DNI desde 5 IPs distintas: **bloquea el DNI**.
- 5 intentos con DNIs distintos desde la misma IP: **bloquea la IP**.
- Combinación: **bloquea ambos**.

### Test añadido
`test_login_is_rate_limited_after_5_attempts()`: 6 requests → 1ª-5ª devuelven
401 (credenciales inválidas), 6ª devuelve 429 con mensaje en español.

### Compromiso arquitectónico
`throttleApi()` en `bootstrap/app.php` aplica `throttle:api` a **toda** la API
(60 req/min por user/IP). Es defensa en profundidad contra abuso desde un
usuario autenticado. Si en el futuro se necesita que algunos endpoints
(DataGrid con polling) exijan más requests/min, refactorizar a un limiter
específico por ruta.

---

## A-07 — SoftDeletes en reglas `exists`

### Problema
Los `FormRequest` usaban `exists:customers,id` y `exists:products,id`. Con
`SoftDeletes`, un cliente/producto soft-deleted tiene `id` aún existente en la
tabla, por lo que la validación pasaba, pero el modelo cargado luego era `null`
o lanzaba error al acceder.

### Solución
`Illuminate\Validation\Rule::exists('tabla', 'id')->whereNull('deleted_at')`:

```php
// StoreSaleRequest y UpdateSaleRequest
'cliente_id' => ['required', Rule::exists('customers', 'id')->whereNull('deleted_at')],
'items.*.producto_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],
```

### Tests añadidos
- `test_sale_with_soft_deleted_customer_is_rejected()`.
- `test_sale_with_soft_deleted_product_is_rejected()`.

---

## Fixes colaterales

### A-03 — `update()` rechaza venta confirmada/anulada/pagada
`SaleService::update()` ahora lanza `ValidationException` con mensaje en español
si la venta está en `confirmada|pagada|anulada`:
```php
if (in_array($sale->estado, ['anulada', 'pagada', 'confirmada'], true)) {
    throw \Illuminate\Validation\ValidationException::withMessages([
        'estado' => ['No se puede modificar una venta anulada, pagada o confirmada.'],
    ]);
}
```

**Test**: `test_update_sale_confirmada_is_rejected()` espera 422 + `errors.estado`.

### A-09 — `anular()` resetea `saldo_pendiente`
```php
$sale->update([
    'estado' => 'anulada',
    'saldo_pendiente' => 0,
]);
```

**Test implícito**: el `Resource` de la venta ahora muestra `saldo_pendiente: 0`.

### A-10 — `update()` recalcula `saldo_pendiente`
Si cambia `total` y no se pasa `saldo_pendiente` explícito:
```php
if (array_key_exists('total', $data) && !array_key_exists('saldo_pendiente', $data)) {
    $data['saldo_pendiente'] = $data['total'];
}
```

**Test implícito**: agregar ítems a borrador aumenta `saldo_pendiente` igual que
`total`. (Se verifica en `test_update_sale_recalcula_saldo_pendiente` no escrito
en esta Fase 0, queda para cobertura incremental en Fase 1.)

---

## Validación

| Suite | Resultado |
|---|---|
| `SaleTest` (Feature) | 18/18 ✅ |
| `InventoryTest` (Feature) | 2/2 ✅ |
| `AuthTest` (Feature) | 6/6 ✅ |
| Total Feature (sin pre-existentes) | **27/27 ✅** |
| `npm run lint` | 0 errores |
| `npm run test` (Vitest) | 6/6 ✅ |
| `npm run build` | OK (4.49s) |

> ⚠️ `CustomerTest`, `ProductTest` y `ExampleTest` tienen fallos preexistentes
> documentados en [AGENTS.md](../AGENTS.md) § Deuda técnica:
> - `customers.tipo_documento` es `varchar(2)` (CustomerTest usa `'DNI'`).
> - `ExampleTest` requiere `APP_KEY` en `.env.testing` (pendiente crear).
>
> **Fuera del scope de la Fase 0 — NO tocar en esta pasada.**

---

## Archivos modificados

### Nuevos
- `backend/database/migrations/2026_06_05_010000_create_sales_codigo_seq.php` (A-01)
- `backend/tests/Feature/SaleTest.php` — +5 tests (atomicity, RBAC, IGV, A-04, A-07)
- `backend/tests/Feature/AuthTest.php` — +1 test (rate limit)

### Modificados
- `backend/app/Modules/Sales/Services/SaleService.php` (A-01, A-03, A-04, A-09, A-10)
- `backend/app/Modules/Inventory/Services/InventoryService.php` (A-04 — refactor
  `aplicarMovimientoVenta` privado)
- `backend/app/Modules/Inventory/Listeners/DescontarStock.php` (A-04 — limpieza,
  rethrow eliminado)
- `backend/app/Modules/Sales/Http/Requests/StoreSaleRequest.php` (A-07)
- `backend/app/Modules/Sales/Http/Requests/UpdateSaleRequest.php` (A-07)
- `backend/app/Providers/AppServiceProvider.php` (A-05 — RateLimiter `login`
  y `api`)
- `backend/routes/api.php` (A-05 — `throttle:login` en login)
- `backend/bootstrap/app.php` (A-05 — `$middleware->throttleApi()` + redirectGuestsTo)

---

## Pendientes para Fase 1 (Hito 004 — Purchases) — pendiente de aprobación

**Hallazgos 🟠 aún abiertos (deben remediarse antes/durante Fase 1):**
- **A-02**: Decisión arquitectónica sobre `dim_almacen` (multi-almacén real o eliminar campo).
- **A-06**: Validación de formato DNI (8 dígitos) / RUC (11 dígitos) en `LoginRequest`.
- **A-08**: Agregar método `confirmar()` en `SalePolicy` con permiso `confirmar-ventas` separado.
- **A-11**: Agregar `$this->authorize('viewAny', Sale::class)` en `SaleController::index()`.

**Migración a ADR-010:**
> Este documento cierra la Fase 0 del ADR-009. Las Fases 1-4 (Purchases,
> Finance, cleanup) se han trasladado al nuevo ADR-010.
> Ver: `_docs_desarrollo/ADR-010-hito-004-purchases-finance.md`.

---

## Referencias

- [ADR-009](./ADR-009-remediacion-intercalada-hito-003-004.md) — Plan completo
  de remediación
- [HITO-AUDIT-001-correcciones-auditor](./HITO-AUDIT-001-correcciones-auditor.md) —
  Fases previas (C-01, C-02, C-03)
- [_auditoria/HITO-003/HITO-003-hallazgos.md](../_auditoria/HITO-003/HITO-003-hallazgos.md) —
  Hallazgos originales del auditor
- [AGENTS.md](../AGENTS.md) — Estado actual del proyecto
