# Evidencia de Auditoría — HITO 003 (Sales + Inventory)

> Este documento contiene las citas textuales del código y la documentación que respaldan cada hallazgo de la auditoría.  
> Formato: `file_path:line_number` → cita textual.

---

## C-01: Documentación publicitada no existe

**Fuente:** `_docs_implementacion\INDICE_MAESTRO.md:4-6`

```
Total de Documentos:              35+
```

**Verificación:** `Get-ChildItem -Recurse _docs_implementacion -File` → 16 archivos

```
C:\repos\repo_armora_sac\_docs_implementacion\COMIENZA_AQUI.md
C:\repos\repo_armora_sac\_docs_implementacion\INDICE_DOCUMENTACION.md
C:\repos\repo_armora_sac\_docs_implementacion\INDICE_MAESTRO.md
C:\repos\repo_armora_sac\_docs_implementacion\README.md
C:\repos\repo_armora_sac\_docs_implementacion\REORGANIZACION_MENU_SIDEBAR.md
C:\repos\repo_armora_sac\_docs_implementacion\RESUMEN_DOCUMENTACION_GENERADA.md
C:\repos\repo_armora_sac\_docs_implementacion\01_analisis_tecnico\backend-analysis.md
C:\repos\repo_armora_sac\_docs_implementacion\01_analisis_tecnico\CHECKLIST_STAKEHOLDERS.md
C:\repos\repo_armora_sac\_docs_implementacion\01_analisis_tecnico\comparison-with-previous.md
C:\repos\repo_armora_sac\_docs_implementacion\01_analisis_tecnico\frontend-analysis.md
C:\repos\repo_armora_sac\_docs_implementacion\01_analisis_tecnico\RESUMEN_EJECUTIVO.md
C:\repos\repo_armora_sac\_docs_implementacion\01_analisis_tecnico\VALIDACIONES_PENDIENTES.md
C:\repos\repo_armora_sac\_docs_implementacion\02_arquitectura_datos\database-analysis.md
C:\repos\repo_armora_sac\_docs_implementacion\02_arquitectura_datos\tabla-creation-dependency-order.md
C:\repos\repo_armora_sac\_docs_implementacion\06_api_endpoints\ENDPOINTS_COMPLETE_MAPPING.md
```

**Documentos publicitados que NO existen:**
- `05_especificaciones_tecnicas/architecture-decisions.md`
- `05_especificaciones_tecnicas/backend-architecture.md`
- `05_especificaciones_tecnicas/frontend-architecture.md`
- `05_especificaciones_tecnicas/design-patterns.md`
- `05_especificaciones_tecnicas/authentication-authorization.md`
- `05_especificaciones_tecnicas/api-specification.md`
- `07_seguridad_compliance/sunat-compliance.md`
- `07_seguridad_compliance/security-improvements.md`
- `07_seguridad_compliance/data-protection.md`
- `07_seguridad_compliance/penetration-testing-checklist.md`
- `02_arquitectura_datos/er-diagram-current.md`
- `02_arquitectura_datos/er-diagram-proposed.md`
- `02_arquitectura_datos/dimension-tables.md`
- `02_arquitectura_datos/fact-tables.md`
- `02_arquitectura_datos/data-relationships.md`
- `02_arquitectura_datos/database-schema.sql`
- `03_mapa_funcionalidades/` (9 archivos)
- `06_api_endpoints/auth-endpoints.md`
- `06_api_endpoints/customer-endpoints.md`
- `06_api_endpoints/product-endpoints.md`
- `06_api_endpoints/sales-endpoints.md`
- `06_api_endpoints/inventory-endpoints.md`
- `06_api_endpoints/finance-endpoints.md`
- `06_api_endpoints/dashboard-endpoints.md`

---

## C-02: Permisos RBAC incorrectos

**Fuente:** `backend\routes\api.php:65-68`

```php
Route::put('{sale}', [SaleController::class, 'update'])->middleware('permission:ver-ventas');
Route::delete('{sale}', [SaleController::class, 'destroy'])->middleware('permission:ver-ventas');
```

**Permisos definidos en seeder:**
- `ver-ventas` = permiso de solo lectura
- `editar-ventas` = permiso de escritura
- `eliminar-ventas` = permiso de eliminación

**Problema:** `ver-ventas` está asignado a Vendedor, Admin y Super-Admin. Cualquiera de ellos puede modificar y eliminar ventas aunque no tenga `editar-ventas` o `eliminar-ventas`.

---

## C-03: Cálculo IGV inconsistente

**Fuente:** `backend\app\Modules\Sales\Services\SaleService.php:58-62`

```php
$lineTotal = (float) $item['cantidad'] * (float) $item['precio_unitario'];
$lineIgv = round($lineTotal * 0.18, 2);
$lineSubtotal = round($lineTotal - $lineIgv, 2);
```

**VS fuente:** `SaleService.php:169-180`

```php
private function calcularTotales(array $items): array
{
    $subtotalBase = 0;
    foreach ($items as $item) {
        $lineTotal = (float) $item['cantidad'] * (float) $item['precio_unitario'];
        $lineSubtotal = round($lineTotal / 1.18, 2);
        $subtotalBase += $lineSubtotal;
    }
    $igv = round($subtotalBase * 0.18, 2);
    $total = round($subtotalBase + $igv, 2);
    return [$subtotalBase, $igv, $total];
}
```

**Demostración del error:**

```
Input: cantidad=1, precio_unitario=118 (incluye IGV)

Path 1 (create() línea 60-61):
  lineTotal = 1 × 118 = 118
  lineIgv = 118 × 0.18 = 21.24  ← INCORRECTO (esto es 21.24/118 = 18% del total, ignora que 118 ya incluye IGV)
  lineSubtotal = 118 - 21.24 = 96.76

Path 2 (calcularTotales() línea 174):
  lineTotal = 1 × 118 = 118
  lineSubtotal = 118 / 1.18 = 100.00  ← CORRECTO
  igv = 100 × 0.18 = 18.00

Diferencia: subtotal 96.76 vs 100.00, igv 21.24 vs 18.00
```

---

## A-01: Race condition en generateCode()

**Fuente:** `SaleService.php:182-187`

```php
private function generateCode(): string
{
    $prefix = 'V';
    $last = Sale::withTrashed()->count() + 1;
    return $prefix . '-' . date('Y') . '-' . Str::padLeft($last, 5, '0');
}
```

Dos requests concurrentes pueden obtener el mismo `count()` y generar el mismo código.

---

## A-02: almacen_id siempre null

**Fuente:** `InventoryService.php:18-21, 69-72`

```php
$stock = Stock::where('producto_id', $item->producto_id)
    ->whereNull('almacen_id')
    ->lockForUpdate()
    ->first();
```

La migración `2026_06_04_145959_create_dim_almacen_table.php` crea la tabla, pero nunca se usa. Solo hay una fila de stock por producto (`whereNull('almacen_id')`).

---

## A-05: Sin rate limiting en login

**Fuente:** `AuthService.php:22-26`

```php
if (!$user || !Hash::check($password, $user->password)) {
    throw ValidationException::withMessages([
        'login' => ['Credenciales inválidas.'],
    ]);
}
```

No hay llamado a `RateLimiter::hit()` ni verificación de `RateLimiter::tooManyAttempts()`.

---

## A-07: exists ignora SoftDeletes

**Fuente:** `StoreSaleRequest.php:18,29`

```php
'cliente_id' => ['required', 'integer', 'exists:customers,id'],
'items.*.producto_id' => ['required', 'integer', 'exists:products,id'],
```

Si un cliente fue soft-deleteado, `exists:customers,id` lo encuentra como válido porque la regla `exists` no aplica `whereNull('deleted_at')` por defecto. Debería ser:

```php
'exists:customers,id,deleted_at,NULL',
```

---

## A-08: Policy sin método confirmar

**Fuente:** `SaleController.php:53`

```php
public function confirmar(Sale $sale): JsonResponse
{
    $this->authorize('update', $sale);  // ← usa update(), debería ser confirmar()
```

vs `SalePolicy.php` que no tiene método `confirmar()`. Usar `update` para `confirmar` es semánticamente incorrecto porque una cosa es editar datos de la venta y otra es confirmarla (cambio de estado irreversible).

---

*Evidencia HITO 003 — Versión 1.0 — 2026-06-04*
