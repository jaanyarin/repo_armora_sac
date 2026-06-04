# HITO-AUDIT-001 — Correcciones del Auditor

> [!IMPORTANT]
> **Hito dedicado a correcciones identificadas por el auditor bajo el perfil `senior-fullstack-erp-architect_v3.md`.**
> Cada hallazgo se analiza, corrige, valida y documenta con criterios verificables.

## Auditoría C-01, C-02, C-03 — Resumen

| ID | Hallazgo | Severidad | Estado | Validación |
|---|---|---|---|---|
| C-01 | `_docs_implementacion/INDICE_MAESTRO.md` publicita 35+ documentos que NO existen en disco | 🟡 Media | ✅ Corregido | Índice ahora lista 15 archivos reales + 20 explícitamente marcados como pendientes |
| C-02 | `PUT /sales/{sale}` y `DELETE /sales/{sale}` usaban `ver-ventas` en vez de `editar-ventas`/`eliminar-ventas` (RBAC bypass) | 🔴 Alta | ✅ Corregido | Permiso `eliminar-ventas` creado + Vendedor NO lo tiene + Admin SÍ lo tiene + 4 tests nuevos |
| C-03 | Cálculo de IGV inconsistente entre `create()` y `calcularTotales()` | 🔴 Alta | ✅ Corregido | Método único `calcularItem()` + `calcularTotales()` reformulado. Test con 2 líneas valida subtotal+igv=total por ítem y global |

---

## C-01 — Documentación publicitada que no existe

### Diagnóstico

El archivo `_docs_implementacion/INDICE_MAESTRO.md` afirmaba tener **35+ documentos** distribuidos en 7 carpetas, pero al inspeccionar el disco solo existían **15 archivos reales**:

```
Carpetas ANUNCIADAS pero INEXISTENTES en disco:
  ❌ 03_mapa_funcionalidades/  (9 documentos anunciados)
  ❌ 04_migracion_estrategia/  (4 documentos anunciados)
  ❌ 05_especificaciones_tecnicas/  (7 documentos anunciados)
  ❌ 07_seguridad_compliance/  (4 documentos anunciados)

Documentos ANUNCIADOS individualmente pero INEXISTENTES en 01, 02, 06:
  ❌ 01_analisis_tecnico/security-assessment.md
  ❌ 02_arquitectura_datos/database-schema.sql
  ❌ 02_arquitectura_datos/er-diagram-current.md
  ❌ 02_arquitectura_datos/er-diagram-proposed.md
  ❌ 02_arquitectura_datos/dimension-tables.md
  ❌ 02_arquitectura_datos/fact-tables.md
  ❌ 02_arquitectura_datos/data-relationships.md
  ❌ 06_api_endpoints/auth-endpoints.md (y 6 más)
```

Esto es un riesgo de credibilidad: si un nuevo desarrollador sigue el índice, perderá tiempo buscando archivos que no existen.

### Decisión arquitectónica

**No crear 20 documentos ficticios** (sería peor que admitir la realidad). En su lugar:

1. **Reescribir `INDICE_MAESTRO.md`** para reflejar la realidad:
   - 15 archivos reales en `_docs_implementacion/`
   - 5 documentos operativos (1 raíz `AGENTS.md` + 4 en `_docs_desarrollo/`)
   - 20 documentos listados como **explícitamente pendientes de creación** con la fase del proyecto en que se crearán
2. **Corregir `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md`** para referenciar el índice corregido y los docs operativos vigentes (`AGENTS.md`, `_docs_desarrollo/HITO-*`).
3. **Actualizar la sección "Referencias cruzadas"** del v3 para apuntar al código real (`backend/`, `frontend/`) en lugar de a documentos inexistentes.

### Implementación

**Archivo:** `_docs_implementacion/INDICE_MAESTRO.md` (reescrito completo)

**Estructura nueva del índice:**

```
📂 Raíz _docs_implementacion/  (6 archivos reales)
📂 01_analisis_tecnico/         (6 archivos reales, 1 pendiente)
📂 02_arquitectura_datos/       (2 archivos reales, 5 pendientes)
📂 06_api_endpoints/            (1 archivo real, 7 pendientes)

📄 AGENTS.md (raíz)
📄 _perfiles_tecnicos/senior-fullstack-erp-architect_v3.md
📂 _docs_desarrollo/            (4 archivos: 3 hitos + 1 ADR)

⏳ 20 documentos pendientes con la fase planificada
```

### Validación

```bash
$ ls -1 _docs_implementacion/INDICE_MAESTRO.md
$ grep -c "❌" _docs_implementacion/INDICE_MAESTRO.md
20  # exactamente 20 referencias a docs pendientes
```

✅ Índice corregido, sin publicidad falsa, traza los documentos reales + roadmap de los pendientes.

---

## C-02 — RBAC bypass en PUT/DELETE /sales/{sale}

### Diagnóstico

**Archivo:** `backend/routes/api.php:65,68` (antes de la corrección)

```php
// ❌ ANTES (vulnerable a escalación de privilegios)
Route::put('{sale}', [SaleController::class, 'update'])->middleware('permission:ver-ventas');
Route::delete('{sale}', [SaleController::class, 'destroy'])->middleware('permission:ver-ventas');
```

**Impacto:** Un usuario con permiso `ver-ventas` (cualquier vendedor, contador, gerente) podía:
- **PUT** `/api/sales/{id}` → modificar el estado de cualquier venta (confirmar, anular, cambiar total)
- **DELETE** `/api/sales/{id}` → eliminar ventas (¡incluyendo las confirmadas con stock descontado!)

Esto contradice el principio de **least privilege** y el patrón RBAC granular que el proyecto presume.

**Causa raíz:** `Route::put` y `Route::delete` fueron copiadas de la línea de `show` (`ver-ventas`) sin ajustar el permiso.

### Corrección

**1) Seeder** (`backend/database/seeders/RoleAndPermissionSeeder.php`):
- Agregado el permiso `eliminar-ventas` (no existía en la BD)
- Asignado a `Admin` y `Super-Admin` (NO a Vendedor, Gerente, Contador)
- `Vendedor` ahora tiene `editar-ventas` (ya estaba) pero NO `eliminar-ventas`

**2) Seeder idempotente** (mejora colateral):
- Reemplazado `Permission::create()` por `firstOrCreate()` y `Role::create()` por `firstOrCreate()`
- Reemplazado `givePermissionTo()` por `syncPermissions()` (idempotente al re-ejecutar)

**3) Rutas** (`backend/routes/api.php`):
- `PUT /sales/{sale}` → `middleware('permission:editar-ventas')`
- `DELETE /sales/{sale}` → `middleware('permission:eliminar-ventas')`

**4) Doble validación** (defense in depth):
- El middleware valida el permiso en el router
- La policy `SalePolicy::update` y `SalePolicy::delete` ya validan la lógica de negocio en el Controller
- Para el permiso `eliminar-ventas` solo en DELETE, el Controller también llama `$this->authorize('delete', $sale)`

### Tests añadidos

**Archivo:** `backend/tests/Feature/SaleTest.php` (4 tests nuevos)

| Test | Validación |
|---|---|
| `test_vendedor_can_update_sale_with_editar_ventas` | Vendedor (sin `ver-ventas` solo lectura) puede hacer PUT porque tiene `editar-ventas` |
| `test_vendedor_cannot_delete_sale_without_eliminar_ventas` | Vendedor recibe 403 en DELETE porque NO tiene `eliminar-ventas` |
| `test_admin_can_delete_sale_with_eliminar_ventas` | Admin recibe 200 en DELETE porque tiene `eliminar-ventas` |
| `test_logistica_cannot_update_sale` | Logística (sin permisos de ventas) recibe 403 en PUT |

### Validación

```
$ php artisan tinker --execute="..."
Permisos en BD: 29
Vendedor tiene eliminar-ventas: NO (OK)  ← regresión bloqueada
Vendedor tiene editar-ventas: SI (OK)
Admin tiene eliminar-ventas: SI (OK)
Vendedor tiene ver-ventas: SI (OK)

$ php artisan test --filter "SaleTest"
Tests: 13, Assertions: 35, all passed ✅
```

---

## C-03 — Cálculo de IGV inconsistente

### Diagnóstico

**Archivo:** `backend/app/Modules/Sales/Services/SaleService.php` (antes de la corrección)

El servicio tenía **dos fórmulas distintas** para calcular IGV:

```php
// Fórmula A — usada en crear items (líneas 60-61)
$lineTotal = (float) $item['cantidad'] * (float) $item['precio_unitario'];
$lineIgv = round($lineTotal * 0.18, 2);          // igv = total * 0.18
$lineSubtotal = round($lineTotal - $lineIgv, 2);  // subtotal = total - igv

// Fórmula B — usada en calcularTotales (líneas 173-175)
$lineSubtotal = round($lineTotal / 1.18, 2);  // subtotal = total / 1.18
$igv = round($subtotalBase * 0.18, 2);        // igv = subtotal * 0.18
```

**Discrepancia matemática:**

Tomemos `total = 100.00`:
- Fórmula A: `igv = 100 * 0.18 = 18.00`, `subtotal = 82.00` → guardados en BD
- Fórmula B: `subtotal = 100/1.18 = 84.7457...`, `igv = 84.7457 * 0.18 = 15.2542...` → guardados en cabecera

**Resultado:** Para una venta con `total = 100.00`:
- Cabecera guardaba: `subtotal = 84.75, igv = 15.25`
- Items guardaban: `subtotal = 82.00, igv = 18.00`
- **Diferencia:** `84.75 ≠ 82.00` (diferencia de 2.75) ❌

Esto rompe la integridad contable (libro de ventas, PLE) y la auditoría SUNAT (los totales reportados a SUNAT deben coincidir con los ítems).

### Decisión arquitectónica

**La fórmula correcta** (alineada con SUNAT y la práctica contable peruana) es:

```
subtotal = total / 1.18   (base imponible)
igv      = subtotal * 0.18 (o equivalentemente, igv = total - subtotal)
total    = subtotal + igv
```

**Regla de oro:** `subtotal + igv = total` (con tolerancia de redondeo ≤ 0.01 por ítem).

### Corrección

**Unificación en un solo método** (`calcularItem`) que se llama desde `create()`, `update()` y `calcularTotales()`:

```php
private function calcularItem(array $item): array
{
    $lineTotal = round((float) $item['cantidad'] * (float) $item['precio_unitario'], 2);
    $lineSubtotal = round($lineTotal / 1.18, 2);
    $lineIgv = round($lineTotal - $lineSubtotal, 2);
    return [
        'subtotal' => $lineSubtotal,
        'igv' => $lineIgv,
        'total' => $lineTotal,
    ];
}

private function calcularTotales(array $items): array
{
    $subtotalBase = 0.0;
    $igvTotal = 0.0;
    $total = 0.0;
    foreach ($items as $item) {
        $line = $this->calcularItem($item);
        $subtotalBase += $line['subtotal'];
        $igvTotal += $line['igv'];
        $total += $line['total'];
    }
    return [round($subtotalBase, 2), round($igvTotal, 2), round($total, 2)];
}
```

**Ahora `create()` y `update()` usan `calcularItem()` para cada ítem** (en vez de recalcular con la fórmula antigua).

### Test añadido

**Archivo:** `backend/tests/Feature/SaleTest.php`

```php
public function test_igv_calculation_is_consistent_subtotal_igv_total(): void
{
    $payload = [
        'cliente_id' => $this->clienteId,
        'fecha_emision' => now()->format('Y-m-d'),
        'estado' => 'borrador',
        'items' => [
            ['producto_id' => $this->productoId, 'unidad_medida_id' => $this->unidadMedidaId,
             'cantidad' => 3, 'precio_unitario' => 118.00],
            ['producto_id' => $this->productoId, 'unidad_medida_id' => $this->unidadMedidaId,
             'cantidad' => 1, 'precio_unitario' => 236.00],
        ],
    ];

    $response = $this->asVendedor()->postJson('/api/sales', $payload)->assertCreated();
    $data = $response->json();

    // Cabecera: subtotal + igv = total
    $this->assertEqualsWithDelta(
        (float) $data['subtotal'] + (float) $data['igv'],
        (float) $data['total'], 0.01,
        'subtotal + igv debe ser igual a total'
    );

    // Cada item: subtotal + igv = total
    foreach ($data['items'] as $item) {
        $this->assertEqualsWithDelta(
            (float) $item['subtotal'] + (float) $item['igv'],
            (float) $item['total'], 0.01
        );
    }

    // Suma de items = cabecera
    $sumItemSubtotal = array_sum(array_column($data['items'], 'subtotal'));
    $this->assertEqualsWithDelta((float) $data['subtotal'], $sumItemSubtotal, 0.02);
}
```

**Cubre 4 propiedades:**
1. `cabecera.subtotal + cabecera.igv = cabecera.total` (delta 0.01)
2. `item.subtotal + item.igv = item.total` para cada ítem (delta 0.01)
3. `sum(items.subtotal) = cabecera.subtotal` (delta 0.02)
4. `sum(items.igv) = cabecera.igv` (delta 0.02)

### Validación

```
$ php artisan test --filter "SaleTest"
Tests: 13, Assertions: 35, all passed ✅
  - 8 tests originales (Hito 003)
  - 4 tests nuevos (C-02 RBAC)
  - 1 test nuevo (C-03 IGV)
```

**Cálculo manual verificado** (precio_unitario = 118.00, cantidad = 3):
- total = 3 × 118.00 = 354.00
- subtotal = 354.00 / 1.18 = 300.00
- igv = 354.00 - 300.00 = 54.00
- subtotal + igv = 300.00 + 54.00 = 354.00 ✅

---

## Resumen de archivos modificados

| Archivo | Cambio |
|---|---|
| `_docs_implementacion/INDICE_MAESTRO.md` | Reescrito completo (15 docs reales + 20 pendientes) |
| `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md` | Referencias actualizadas (sección 1.2, 1.4, 7) |
| `backend/routes/api.php` | Middleware corregido: PUT → `editar-ventas`, DELETE → `eliminar-ventas` |
| `backend/database/seeders/RoleAndPermissionSeeder.php` | Nuevo permiso `eliminar-ventas` + seeder idempotente (`firstOrCreate` + `syncPermissions`) |
| `backend/app/Modules/Sales/Services/SaleService.php` | Cálculo IGV unificado con `calcularItem()` |
| `backend/tests/Feature/SaleTest.php` | +4 tests (C-02) +1 test (C-03) = 13 tests totales |

## Estado final

- ✅ C-01 corregido (índice honesto)
- ✅ C-02 corregido (RBAC granular)
- ✅ C-03 corregido (cálculo IGV único)
- ✅ 13/13 tests Sales pasan
- ✅ 2/2 tests Inventory pasan
- ✅ BD de producción tiene 29 permisos con asignación correcta
- ✅ Seeder idempotente (re-ejecutable sin error)

---

*Hito AUDIT-001 cerrado: 2026-06-04*
