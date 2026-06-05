# HITO-INTERCALAR-002 — Plan de implementación

**Estado:** 🟡 **EN VALIDACIÓN** — Ola A ✅ y Ola B ✅ ejecutadas; Ola C ⏸ diferida
**Fecha:** 2026-06-05
**Antecedente:** ADR-010 (borrador), HITO-INTERCALAR-001 cerrado (commit `9954a8c`)
**Auditor:** Senior Code & Architecture Quality Auditor
**Solicitud de validación:** `_auditoria/HITO-INTERCALAR-002/solicitud-validacion-ola-a-b.md`

---

## Estado de las olas

| Ola | Puntos | Commits | Estado |
|---|---|---|---|
| **Ola A — Auditoría residual** | 4/4 | `5b4fd05` | ✅ Cerrada y pusheada |
| **Ola B — HITO-004 Purchases** | 14/14 | `8ddbcdd` | ✅ Cerrada y pusheada |
| **Ola C — HITO-004 Finance** | 0/3 | — | ⏸ Diferida para validación previa |

**Tests:** 54/54 verde (SaleTest 18 + InventoryTest 4 + AuthTest 6 + CompraTest 13 + ProveedorTest 6 + CompraTest 7 nuevos)

---

## Contexto

- HITO-003 cerrado ✅
- 10/25 hallazgos remediados (3 🔴 + 7 🟠)
- ISSUE-001 cerrado (corrección documental aplicada)
- ADR-009 archivado, ADR-010 borrador

Este documento **marca los puntos concretos** que el arquitecto debe ejecutar en orden. Cada punto tiene un ID, archivos, criterios de done y referencia al ADR-010.

---

## 🎯 Puntos a implementar

### OLA A — Auditoría residual (~1 sprint)

#### 📌 Punto A-08: `SalePolicy::confirmar` + permiso `confirmar-ventas`

**Severidad:** 🟠 Alto
**Archivos:**
- `backend/database/seeders/RoleAndPermissionSeeder.php` — agregar permiso `confirmar-ventas`
- `backend/app/Modules/Sales/Policies/SalePolicy.php` — nuevo método `confirmar`
- `backend/app/Modules/Sales/Http/Controllers/SaleController.php` — cambiar `authorize('update')` por `authorize('confirmar')` en línea 53
- `backend/tests/Feature/SaleTest.php` — 2 tests (vendedor 403, admin 200)

**Asignación de permiso (seeder):**
- `confirmar-ventas` → Admin, Super-Admin, Jefe-Ventas
- ❌ Vendedor NO debe tener `confirmar-ventas` (solo `editar-ventas`)

**Criterio de done:**
- Vendedor intenta `POST /api/sales/{id}/confirmar` → 403
- Admin confirma → 200 + estado `confirmada` + stock descontado

**Referencia:** ADR-010 Ola A sección A-08 (líneas 64-70)

---

#### 📌 Punto A-11: `authorize('viewAny')` en `index()`

**Severidad:** 🟠 Alto
**Archivos:**
- `backend/app/Modules/Sales/Http/Controllers/SaleController.php` línea 25-31 — agregar `$this->authorize('viewAny', Sale::class)` antes de `$sales = $this->saleService->paginate(...)`
- `backend/app/Modules/Customers/Http/Controllers/CustomerController.php` — replicar
- `backend/app/Modules/Products/Http/Controllers/ProductController.php` — replicar
- `backend/app/Modules/Inventory/Http/Controllers/InventoryController.php` — replicar
- `backend/tests/Feature/SaleTest.php` — 1 test (usuario sin `ver-ventas` → 403)

**Criterio de done:**
- Usuario sin permiso `ver-*` accede a `GET /api/sales` → 403 (no 200 con array vacío)
- Defense in depth: middleware + policy

**Referencia:** ADR-010 Ola A sección A-11 (líneas 72-74)

---

#### 📌 Punto A-02: Decisión multi-almacén

**Severidad:** 🟠 Alto
**Archivos:**
- `backend/app/Modules/Inventory/Services/InventoryService.php` — refactor para que `descontarPorVenta(Sale $sale, ?int $almacenId = null)` reciba almacén opcional
- `backend/app/Modules/Sales/Services/SaleService.php` — pasar almacén (default: primer almacén activo)
- `backend/database/migrations/2026_06_06_010000_seed_default_almacen.php` — sembrar 1 almacén por defecto
- `backend/database/seeders/DimAlmacenSeeder.php` (nuevo) — seed mínimo
- `backend/tests/Feature/InventoryTest.php` — test con 2 almacenes

**Decisión arquitectónica:** Multi-almacén mínimo viable
- 1 almacén por defecto sembrado
- `almacen_id` se usa en `stock_actual` y `inventory_movimientos`
- Kardex multi-almacén completo → HITO-005 (Logistics)

**Criterio de done:**
- Test crea 2 almacenes, transfiere stock entre ellos, verifica saldos independientes
- `almacen_id` ya no es siempre `null` en `inventory_movimientos`

**Referencia:** ADR-010 Ola A sección A-02 (líneas 45-51)

---

#### 📌 Punto A-06: DNI/RUC regex en `LoginRequest`

**Severidad:** 🟠 Alto
**Archivos:**
- `backend/app/Modules/Auth/Http/Requests/LoginRequest.php` — añadir validación condicional por longitud
- `backend/tests/Feature/AuthTest.php` — 2 tests (DNI no-8-dígitos → 422, RUC no-11-dígitos → 422)

**Lógica sugerida:**
```php
public function rules(): array
{
    $rules = ['password' => ['required', 'string']];
    $login = $this->input('login', '');
    
    if (is_numeric($login)) {
        match (strlen($login)) {
            8  => $rules['login'] = ['required', 'string', 'regex:/^\d{8}$/'],
            11 => $rules['login'] = ['required', 'string', 'regex:/^\d{11}$/'],
            default => $rules['login'] = ['required', 'string'],
        };
    } else {
        $rules['login'] = ['required', 'string', 'max:100'];
    }
    return $rules;
}
```

**Criterio de done:**
- `POST /api/auth/login { login: '1234', password: 'x' }` → 422
- `POST /api/auth/login { login: '12345678', password: 'admin123' }` → 200 (DNI válido)
- `POST /api/auth/login { login: 'admin', password: 'x' }` → 200/401 (username sin restricción)

**Referencia:** ADR-010 Ola A sección A-06 (líneas 53-62)

---

### OLA B — HITO-004 Purchases (~2 sprints)

#### 📌 Punto B-MIG: Migraciones Purchases

**Archivos (3 migraciones):**
- `backend/database/migrations/2026_06_06_020000_create_purchases_proveedores.php`
  - `purchases_proveedores`: ULID PK, SoftDeletes, `numero_documento`, `tipo_documento_id` (FK a `dim_documento_tipo`), `nombre_completo`, `direccion`, `telefono`, `email`, `contacto_nombre`, `activo`
  - UNIQUE INDEX `(tipo_documento_id, numero_documento) WHERE deleted_at IS NULL`
- `backend/database/migrations/2026_06_06_030000_create_purchases_compras.php`
  - `purchases_compras`: ULID PK, SoftDeletes, FK `proveedor_id` (a `purchases_proveedores`), FK `usuario_id`, `documento_tipo_id`, `serie`, `numero`, `codigo` (con secuencia `purchases_codigo_seq` reutilizando patrón A-01), `fecha_emision`, `fecha_vencimiento`, `moneda_id`, `subtotal`, `igv`, `total`, `saldo_pendiente`, `estado`, `observaciones`, `almacen_id` (FK a `dim_almacen`, opcional)
- `backend/database/migrations/2026_06_06_040000_create_purchases_compra_items.php`
  - `purchases_compra_items`: ULID PK, FK `compra_id`, FK `producto_id`, FK `unidad_medida_id`, `numero_linea`, `cantidad`, `precio_unitario`, `descuento_linea`, `subtotal`, `igv`, `total`, `observaciones`

**Secuencia paralela:** `purchases_codigo_seq` (mismo patrón que `sales_codigo_seq`)

**Criterio de done:**
- `php artisan migrate` ejecuta las 3 migraciones sin error
- `php artisan migrate:rollback` revierte correctamente

---

#### 📌 Punto B-MOD: Modelos Purchases

**Archivos (3 modelos):**
- `backend/app/Modules/Purchases/Models/Proveedor.php` — HasUlids, SoftDeletes, LogsActivity, relaciones
- `backend/app/Modules/Purchases/Models/Compra.php` — HasUlids, SoftDeletes, LogsActivity, relaciones: `items()`, `proveedor()`, `usuario()`, `almacen()`, `documentoTipo()`, `moneda()`
- `backend/app/Modules/Purchases/Models/CompraItem.php` — HasUlids, relaciones: `compra()`, `producto()`, `unidadMedida()`

**Castes:** `'cantidad' => 'decimal:2'`, `'precio_unitario' => 'decimal:2'`, etc. (alineado con Sale)

**Criterio de done:**
- `Compra::find($id)->items` funciona
- `Compra::find($id)->proveedor->nombre_completo` funciona

---

#### 📌 Punto B-SVC: Services Purchases (IGV importado)

**Archivos (2 services):**
- `backend/app/Modules/Purchases/Services/ProveedorService.php` — CRUD + búsqueda + paginación (mismo patrón que `CustomerService`)
- `backend/app/Modules/Purchases/Services/CompraService.php` — **importa** `\App\Modules\Sales\Services\SaleService::calcularLineaIgv()` (no duplica fórmula)
  - `paginate(array $filters): LengthAwarePaginator`
  - `findById(string $id): ?Compra`
  - `create(array $data): Compra` — DB::transaction + generar código
  - `update(Compra $compra, array $data): Compra` — bloquea confirmadas (A-03 replicado)
  - `confirmar(Compra $compra): Compra` — transacción inline + `aumentarPorCompra` (A-04 patrón)
  - `anular(Compra $compra): Compra` — `saldo_pendiente = 0` (A-09 replicado)
  - `delete(Compra $compra): void`

**Criterio de done:**
- `CompraService` no contiene fórmulas IGV propias; importa de `SaleService`
- `confirmar` ejecuta `aumentarPorCompra` inline dentro de transacción

---

#### 📌 Punto B-EVT: Eventos y Listeners Purchases

**Archivos (2):**
- `backend/app/Modules/Purchases/Events/CompraConfirmada.php` — lleva `$compra`
- `backend/app/Modules/Purchases/Listeners/AumentarStock.php` — invoca `InventoryService::aumentarPorCompra($compra)` (a crear o reutilizar `aplicarMovimientoVenta` con signo +1)

**⚠️ Decisión:** Crear método público `aumentarPorCompra(Compra $compra)` en `InventoryService` o generalizar `aplicarMovimientoVenta` para aceptar signo (+1/-1).

**Patrón de invocación:** Igual que `DescontarStock` ya corregido en A-04: **invocación inline** desde `CompraService::confirmar()`, NO auto-registro de listener.

**Criterio de done:**
- `CompraService::confirmar` invoca `InventoryService::aumentarPorCompra` dentro de la misma `DB::transaction`
- Si listener falla → rollback completo (compra queda en borrador, stock sin cambio)

---

#### 📌 Punto B-POL: Policies Purchases

**Archivos (2 policies):**
- `backend/app/Modules/Purchases/Policies/ProveedorPolicy.php` — viewAny, view, create, update, delete
- `backend/app/Modules/Purchases/Policies/CompraPolicy.php` — viewAny, view, create, update, **`confirmar`**, delete, anular
  - Método `confirmar` verifica `$user->can('confirmar-compras')` (permiso nuevo en seeder)
- `backend/app/Providers/AppServiceProvider.php` — registrar 2 nuevas policies con `Gate::policy()`

**Permisos nuevos en seeder:**
- `ver-compras`, `crear-compras`, `editar-compras`, `anular-compras`, `eliminar-compras`, `confirmar-compras`
- `ver-proveedores`, `crear-proveedores`, `editar-proveedores`, `eliminar-proveedores`

**Criterio de done:**
- Vendedor intenta confirmar compra → 403
- Admin/Super-Admin/Jefe-Almacén confirma → 200

---

#### 📌 Punto B-CTL: Controllers Purchases

**Archivos (2 controllers):**
- `backend/app/Modules/Purchases/Http/Controllers/ProveedorController.php` — index, show, store, update, destroy
  - **A-11 replicado:** `index()` llama `$this->authorize('viewAny', Proveedor::class)`
- `backend/app/Modules/Purchases/Http/Controllers/CompraController.php` — index, show, store, update, confirmar, anular, destroy
  - **A-11 replicado:** `index()` llama `$this->authorize('viewAny', Compra::class)`
  - **`confirmar` usa `authorize('confirmar', $compra)`** (A-08 replicado)

**Criterio de done:**
- Rutas: `GET/POST/PUT/DELETE /api/purchases/compras` + `POST /api/purchases/compras/{id}/confirmar` + `POST /api/purchases/compras/{id}/anular`
- Rutas: `GET/POST/PUT/DELETE /api/purchases/proveedores`

---

#### 📌 Punto B-FR: FormRequests Purchases

**Archivos (4):**
- `backend/app/Modules/Purchases/Http/Requests/StoreProveedorRequest.php` — `Rule::exists` con SoftDeletes (A-07 replicado)
- `backend/app/Modules/Purchases/Http/Requests/UpdateProveedorRequest.php`
- `backend/app/Modules/Purchases/Http/Requests/StoreCompraRequest.php` — items con `Rule::exists('products', 'id')->whereNull('deleted_at')`, almacén opcional
- `backend/app/Modules/Purchases/Http/Requests/UpdateCompraRequest.php`

**Criterio de done:**
- Proveedor soft-deleteado en POST → 422 con `errors.proveedor_id`
- Producto soft-deleteado en ítem → 422 con `errors.items.X.producto_id`

---

#### 📌 Punto B-RES: Resources Purchases

**Archivos (3):**
- `backend/app/Modules/Purchases/Http/Resources/ProveedorResource.php` — con `whenLoaded()` para relaciones
- `backend/app/Modules/Purchases/Http/Resources/CompraResource.php` — con `whenLoaded()` para `items`, `proveedor`, `usuario`, `almacen`
- `backend/app/Modules/Purchases/Http/Resources/CompraItemResource.php` — con `whenLoaded()` para `producto`, `unidadMedida`

---

#### 📌 Punto B-RT: Rutas Purchases

**Archivo:** `backend/routes/api.php`

```php
Route::prefix('purchases')->group(function () {
    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->middleware('permission:ver-proveedores');
        Route::get('{proveedor}', [ProveedorController::class, 'show'])->middleware('permission:ver-proveedores');
        Route::post('/', [ProveedorController::class, 'store'])->middleware('permission:crear-proveedores');
        Route::put('{proveedor}', [ProveedorController::class, 'update'])->middleware('permission:editar-proveedores');
        Route::delete('{proveedor}', [ProveedorController::class, 'destroy'])->middleware('permission:eliminar-proveedores');
    });
    Route::prefix('compras')->group(function () {
        Route::get('/', [CompraController::class, 'index'])->middleware('permission:ver-compras');
        Route::get('{compra}', [CompraController::class, 'show'])->middleware('permission:ver-compras');
        Route::post('/', [CompraController::class, 'store'])->middleware('permission:crear-compras');
        Route::put('{compra}', [CompraController::class, 'update'])->middleware('permission:editar-compras');
        Route::post('{compra}/confirmar', [CompraController::class, 'confirmar'])->middleware('permission:confirmar-compras');
        Route::post('{compra}/anular', [CompraController::class, 'anular'])->middleware('permission:anular-compras');
        Route::delete('{compra}', [CompraController::class, 'destroy'])->middleware('permission:eliminar-compras');
    });
});
```

**Criterio de done:**
- 12 endpoints REST funcionan con permisos correctos
- PUT/DELETE con permisos granulares (NO bypass — lección aprendida C-02)

---

#### 📌 Punto B-TST: Tests Purchases (12 tests mínimos)

**Archivo:** `backend/tests/Feature/CompraTest.php`

| # | Test | Cubre |
|---|---|---|
| 1 | `test_list_compras` | index + paginación |
| 2 | `test_create_compra_with_borrador` | create + cálculo IGV importado |
| 3 | `test_create_compra_requires_proveedor` | validación |
| 4 | `test_create_compra_requires_at_least_one_item` | validación |
| 5 | `test_confirmar_compra_aumenta_stock` | transacción + listener |
| 6 | `test_anular_compra_disminuye_stock` | revertir |
| 7 | `test_compra_with_soft_deleted_proveedor_is_rejected` | A-07 replicado |
| 8 | `test_vendedor_cannot_confirmar_compra` | A-08 replicado |
| 9 | `test_vendedor_cannot_delete_compra` | RBAC |
| 10 | `test_compra_borrador_puede_ser_editada` | A-03 replicado |
| 11 | `test_compra_confirmada_no_puede_ser_editada` | A-03 replicado |
| 12 | `test_igv_calculation_uses_sales_formula` | importa `calcularLineaIgv`, no duplica |

**Archivo:** `backend/tests/Feature/ProveedorTest.php` — 6 tests (paridad con `CustomerTest`)

**Criterio de done:**
- `composer test --filter "CompraTest|ProveedorTest"` → 18/18 ✅
- `composer test` total → 0 regresiones (35+18 = 53+ tests)

---

#### 📌 Punto B-FE: Frontend Purchases (paridad con Sales)

**Archivos (5):**
- `frontend/src/shared/api/purchasesApi.ts` — endpoints proveedores + compras
- `frontend/src/shared/types/index.ts` — interfaces `Proveedor`, `Compra`, `CompraItem`
- `frontend/src/Admin/pages/Purchases/ProveedorListPage.tsx` — DataGrid + filtros
- `frontend/src/Admin/pages/Purchases/ProveedorFormPage.tsx` — RHF + Zod
- `frontend/src/Admin/pages/Purchases/CompraListPage.tsx` — DataGrid
- `frontend/src/Admin/pages/Purchases/CompraFormPage.tsx` — ítems dinámicos + cálculo IGV (usa `igvCalculator.ts`)

**Criterio de done:**
- Frontend puede crear, listar, confirmar compras con el mismo flujo UX que Sales

---

### OLA C — HITO-004 Finance (~2 sprints)

#### 📌 Punto C-MIG: Migraciones Finance

**Archivos (3):**
- `backend/database/migrations/2026_06_07_010000_create_finance_cuentas_contables.php`
  - `finance_cuentas_contables`: id, `codigo` (UNIQUE, formato SUNAT `XXXXX`), `nombre`, `tipo` (activo/pasivo/patrimonio/ingreso/gasto), `nivel`, `activo`
  - Seed con plan contable básico (40 cuentas SUNAT)
- `backend/database/migrations/2026_06_07_020000_create_finance_asientos.php`
  - `finance_asientos`: ULID PK, `fecha`, `glosa`, `total_debe`, `total_haber`, `origen_tipo` (sale/purchase/manual), `origen_id`, `usuario_id`, SoftDeletes
- `backend/database/migrations/2026_06_07_030000_create_finance_asiento_lineas.php`
  - `finance_asiento_lineas`: id, FK `asiento_id`, FK `cuenta_contable_id`, `debe`, `haber`, `orden`

---

#### 📌 Punto C-MOD: Modelos Finance

**Archivos (3):**
- `backend/app/Modules/Finance/Models/CuentaContable.php`
- `backend/app/Modules/Finance/Models/AsientoContable.php` — relación `lineas()`
- `backend/app/Modules/Finance/Models/AsientoLinea.php` — relación `cuentaContable()`

---

#### 📌 Punto C-SVC: Service Finance (asientos + PLE)

**Archivo:** `backend/app/Modules/Finance/Services/FinanceService.php`

**Métodos:**
- `generarAsientoPorVenta(Sale $sale): AsientoContable`
  - DÉBITO: 1212 (Cuentas por cobrar) o 1011 (Caja) según estado de pago
  - CRÉDITO: 4011 (Ventas) + 4011-IGV (IGV)
- `generarAsientoPorCompra(Compra $compra): AsientoContable`
  - DÉBITO: 6011 (Compras) + 4011-IGV
  - CRÉDITO: 4212 (Cuentas por pagar)
- `validarCuadratura(AsientoContable $asiento): bool` — `total_debe == total_haber`
- `exportarPLE(AsientoContable $asiento, string $tipo): string` — 14.1 ventas, 8.1 compras

**Criterio de done:**
- Asientos cuadran (`validarCuadratura` retorna `true` para casos válidos)
- PLE 14.1 genera TXT con formato SUNAT para un caso de prueba

---

#### 📌 Punto C-TST: Tests Finance (8 tests mínimos)

**Archivo:** `backend/tests/Feature/FinanceTest.php`

| # | Test | Cubre |
|---|---|---|
| 1 | `test_cuadratura_debito_igual_credito` | Asiento bien formado |
| 2 | `test_asiento_por_venta_desglosa_igv` | IGV separado en línea |
| 3 | `test_asiento_por_compra_desglosa_igv` | IGV separado |
| 4 | `test_ple_14_1_formato_sunat` | Snapshot de TXT generado |
| 5 | `test_ple_8_1_formato_sunat` | Snapshot de TXT generado |
| 6 | `test_plan_contable_sembrado_basico` | Cuentas SUNAT mínimas existen |
| 7 | `test_asiento_invalido_no_cuadra` | Validación retorna false |
| 8 | `test_venta_sin_confirmar_no_genera_asiento` | Lógica de activación |

**Criterio de done:**
- `composer test --filter "FinanceTest"` → 8/8 ✅
- `composer test` total → 0 regresiones (53+8 = 61+ tests)

---

## 📊 Estado de avance

| Ola | Punto | Severidad | Estado |
|---|---|---|---|
| A | A-08 confirmar-ventas | 🟠 | 📌 Marcado |
| A | A-11 viewAny en index | 🟠 | 📌 Marcado |
| A | A-02 multi-almacén | 🟠 | 📌 Marcado |
| A | A-06 DNI/RUC regex | 🟠 | 📌 Marcado |
| B | B-MIG Migraciones | — | 📌 Marcado |
| B | B-MOD Modelos | — | 📌 Marcado |
| B | B-SVC Services | — | 📌 Marcado |
| B | B-EVT Eventos | — | 📌 Marcado |
| B | B-POL Policies | — | 📌 Marcado |
| B | B-CTL Controllers | — | 📌 Marcado |
| B | B-FR FormRequests | — | 📌 Marcado |
| B | B-RES Resources | — | 📌 Marcado |
| B | B-RT Rutas | — | 📌 Marcado |
| B | B-TST Tests | — | 📌 Marcado |
| B | B-FE Frontend | — | 📌 Marcado |
| C | C-MIG Migraciones | — | 📌 Marcado |
| C | C-MOD Modelos | — | 📌 Marcado |
| C | C-SVC Services | — | 📌 Marcado |
| C | C-TST Tests | — | 📌 Marcado |

**Total:** 19 puntos marcados.

---

## 🏷️ Convención de commits

- `fix(audit-003):` — remediaciones Ola A
- `feat(hito-004):` — Purchases (Ola B)
- `feat(finance):` — Finance (Ola C)

---

## ✅ Criterios de cierre

| Hito | Criterio |
|---|---|
| Ola A cerrada | 4 fixes + 4 tests + 0 regresiones; matriz de riesgos: 11/11 🟠 cerrados |
| Ola B cerrada | 18 tests Purchases pasan; importa `calcularLineaIgv`; 53+ tests totales |
| Ola C cerrada | 8 tests Finance pasan; PLE 14.1/8.1 validados; 61+ tests totales |
| Auditoría final | Re-auditoría confirma cierre completo; HITO-004 marcado como cerrado |

---

*Documento activo — actualizado: 2026-06-05*
