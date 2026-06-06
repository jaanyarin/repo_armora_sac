# ADR-010 — Plan de implementación HITO-004 (Purchases + Finance) y remediación residual

**Estado:** 🟡 Borrador (pendiente de aprobación del arquitecto)
**Fecha:** 2026-06-05
**Contexto:** ADR-009 archivado con Fase 0 cerrada. Resto de hallazgos 🟠 + módulos nuevos se planifican aquí.
**Antecedente:** [`archivados/ADR-009-fase-0-cerrada.md`](./archivados/ADR-009-fase-0-cerrada.md)

---

## Contexto

HITO-003 cerrado (commit `a73080e`) + 2 rondas de remediación (commits `5ba310b` y `d7e5fdd`):

- ✅ 3 hallazgos 🔴 críticos corregidos
- ✅ 7 hallazgos 🟠 altos corregidos (A-01, A-03, A-04, A-05, A-07, A-09, A-10)
- ❌ 4 hallazgos 🟠 altos pendientes: A-02, A-06, A-08, A-11
- ❌ HITO-004 Purchases: 0% ejecutado
- ❌ HITO-004 Finance: 0% ejecutado
- ⏳ 8 hallazgos 🟡 medios + 3 🟢 bajos sin tratar

El ADR-009 planteaba la estrategia de remediación intercalada; la **Fase 0** se ejecutó. Las **Fases 1-4** no se ejecutaron y se trasladan a este nuevo ADR.

---

## Decisión

Reagrupar el trabajo pendiente en **3 olas secuenciales**:

| Ola | Contenido | Esfuerzo | Prerrequisito |
|---|---|---|---|
| **Ola A** | 4 hallazgos 🟠 residuales (A-02, A-06, A-08, A-11) | ~1 sprint | Ninguno |
| **Ola B** | HITO-004 Purchases (backend + frontend + tests) | ~2 sprints | Ola A completada |
| **Ola C** | HITO-004 Finance (asientos + PLE 14.1/8.1) | ~2 sprints | Ola B completada |

### Por qué esta estructura

1. **Ola A upfront** — cierra la deuda técnica antes de empezar Purchases. Evita replicar bugs (especialmente A-08 Policy y A-11 `viewAny` que también aplican a Purchases).
2. **Ola B antes que C** — Purchases es prerequisito de Finance: PLE 8.1 (Registro de Compras) necesita la tabla `purchases_compras`. Finance sin Purchases no puede validar el flujo contable completo.
3. **C-03 fix de IGV** ya está en `frontend/src/shared/utils/igvCalculator.ts` y `SaleService::calcularLineaIgv()`. Se **importa directamente** en `PurchaseService` (no se duplica la fórmula — alinea con ADR-A001).

---

## Ola A: Hallazgos residuales

### A-02 — `dim_almacen` decisión multi-almacén
- **Decisión:** Implementar multi-almacén mínimo viable (Purchases ingresa a almacén destino configurable).
- **Cambios:**
  - `InventoryService::descontarPorVenta` recibe `almacen_id` como parámetro opcional (default: primer almacén activo).
  - `PurchaseService::confirmar` invoca `aumentarPorCompra` con `almacen_id` del payload.
  - `almacen_id` deja de ser siempre `null`; se mantiene en la tabla pero ahora se usa.
- **Criterio de done:** Test que crea 2 almacenes, transfiere stock entre ellos manualmente, y verifica consistencia.

### A-06 — DNI/RUC regex en `LoginRequest`
- **Cambios:** `app/Modules/Auth/Http/Requests/LoginRequest.php` añade regla condicional:
  ```php
  match (strlen($this->input('login'))) {
      8 => ['regex:/^\d{8}$/', 'in:dni,username,email,ruc'],
      11 => ['regex:/^\d{11}$/'],
      default => [],
  };
  ```
- **Criterio de done:** Test: login con `'1234'` (no 8 dígitos) → 422.

### A-08 — `SalePolicy::confirmar` + permiso `confirmar-ventas`
- **Cambios:**
  - Nuevo permiso `confirmar-ventas` en seeder (solo Admin, Super-Admin, Jefe-Ventas).
  - `SalePolicy::confirmar` nuevo método que verifica `$user->can('confirmar-ventas')`.
  - `SaleController::confirmar` cambia `authorize('update')` → `authorize('confirmar')`.
  - **Misma estructura se replica en `PurchasePolicy::confirmar`** (Ola B).
- **Criterio de done:** Test: Vendedor intenta confirmar → 403; Admin confirma → 200.

### A-11 — `authorize('viewAny')` en `index()`
- **Cambios:** 4 controllers (Sales, Purchases futuro, Customers, Products) llaman `$this->authorize('viewAny', Model::class)` en `index()`.
- **Criterio de done:** Test con usuario sin permiso `ver-*` → 403 (no 200 con datos vacíos).

---

## Ola B: HITO-004 Purchases

### Backend — Migraciones

| Migración | Tabla | Comentario |
|---|---|---|
| `2026_06_06_010000_create_purchases_proveedores.php` | `purchases_proveedores` | ULID PK, SoftDeletes, `numero_documento UNIQUE(tipo_documento, deleted_at IS NULL)` |
| `2026_06_06_020000_create_purchases_compras.php` | `purchases_compras` | ULID PK, SoftDeletes, FK a `customers` (proveedor) |
| `2026_06_06_030000_create_purchases_compra_items.php` | `purchases_compra_items` | ULID PK, FK a `purchases_compras` |

### Backend — Modelos

- `Modules/Purchases/Models/Proveedor.php` — extends Model, HasUlids, SoftDeletes, LogsActivity
- `Modules/Purchases/Models/Compra.php` — relación `items()`, `proveedor()`, `usuario()`
- `Modules/Purchases/Models/CompraItem.php`

### Backend — Services

- `Modules/Purchases/Services/ProveedorService.php` — CRUD + búsqueda + paginación
- `Modules/Purchases/Services/CompraService.php` — **importa `SaleService::calcularLineaIgv()`** (no duplica fórmula)
  - `create(array $data): Compra`
  - `update(Compra $compra, array $data): Compra`
  - `confirmar(Compra $compra): Compra` — transacción inline + `AfectarStock` listener
  - `anular(Compra $compra): Compra` — `saldo_pendiente = 0`

### Backend — Events & Listeners

- `Events/CompraConfirmada.php`
- `Listeners/AfectarStock.php` — mismo patrón que `DescontarStock`, pero aumenta stock

### Backend — Policies

- `Policies/ProveedorPolicy.php` — viewAny, view, create, update, delete
- `Policies/CompraPolicy.php` — viewAny, view, create, update, `confirmar`, delete, anular
  - **`confirmar` usa permiso `confirmar-compras`** (nuevo en seeder)

### Backend — Controllers

- `Http/Controllers/ProveedorController.php` — index, show, store, update, destroy
- `Http/Controllers/CompraController.php` — index, show, store, update, confirmar, anular, destroy
  - **Todos los `index()` llaman `$this->authorize('viewAny', Model::class)`** (remediación A-11 replicada)

### Backend — FormRequests

- `Http/Requests/StoreProveedorRequest.php` — `Rule::exists` con SoftDeletes
- `Http/Requests/UpdateProveedorRequest.php`
- `Http/Requests/StoreCompraRequest.php` — items con productos SoftDeletes, almacén opcional
- `Http/Requests/UpdateCompraRequest.php`

### Backend — Resources

- `Http/Resources/ProveedorResource.php`
- `Http/Resources/CompraResource.php` + `CompraItemResource.php`

### Backend — Tests (mínimo 10)

| Test | Cubre |
|---|---|
| `test_list_compras` | index + paginación |
| `test_create_compra_with_borrador` | create + cálculo IGV importado |
| `test_create_compra_requires_proveedor` | validación |
| `test_create_compra_requires_items` | validación |
| `test_confirmar_compra_aumenta_stock` | transacción + listener |
| `test_anular_compra_disminuye_stock` | revertir |
| `test_compra_with_soft_deleted_proveedor_is_rejected` | A-07 replicado |
| `test_vendedor_cannot_confirmar_compra` | A-08 replicado |
| `test_vendedor_cannot_delete_compra` | RBAC |
| `test_compra_borrador_puede_ser_editada` | A-03 replicado |
| `test_compra_confirmada_no_puede_ser_editada` | A-03 replicado |
| `test_igv_calculation_uses_sales_formula` | no se duplica fórmula |

### Frontend — Admin (paridad con Sales)

- `frontend/src/Admin/pages/Purchases/ProveedorListPage.tsx` — DataGrid + RHF
- `frontend/src/Admin/pages/Purchases/ProveedorFormPage.tsx`
- `frontend/src/Admin/pages/Purchases/CompraListPage.tsx`
- `frontend/src/Admin/pages/Purchases/CompraFormPage.tsx` — usa `igvCalculator.ts`

### Frontend — Endpoints API

- `frontend/src/shared/api/purchasesApi.ts` — proveedores + compras
- Tipos en `frontend/src/shared/types/index.ts` (Proveedor, Compra, CompraItem)

---

## Ola C: HITO-004 Finance

### Backend — Migraciones

| Migración | Tabla |
|---|---|
| `2026_06_07_010000_create_finance_asientos.php` | `finance_asientos` |
| `2026_06_07_020000_create_finance_asiento_lineas.php` | `finance_asiento_lineas` |
| `2026_06_07_030000_create_finance_cuentas_contables.php` | `finance_cuentas_contables` (plan contable) |

### Backend — Models

- `Modules/Finance/Models/AsientoContable.php`
- `Modules/Finance/Models/AsientoLinea.php`
- `Modules/Finance/Models/CuentaContable.php`

### Backend — Services

- `Modules/Finance/Services/FinanceService.php`
  - `generarAsientoPorVenta(Sale $sale): AsientoContable`
  - `generarAsientoPorCompra(Compra $compra): AsientoContable`
  - `validarCuadratura(AsientoContable $asiento): bool` (debe: débitos = créditos)
  - `exportarPLE(AsientoContable $asiento, string $tipo): string` (14.1 ventas, 8.1 compras)

### Backend — PLE (libro contable electrónico)

- **PLE 14.1** (Registro de Ventas) — genera TXT con formato SUNAT desde `sales_ventas`
- **PLE 8.1** (Registro de Compras) — genera TXT con formato SUNAT desde `purchases_compras`
- **Diferido:** envío SOAP a SUNAT (HITO-006 + Greenter 5.x)

### Backend — Tests (mínimo 8)

- Cuadratura débito = crédito
- Asiento de venta con IGV desglosado
- Asiento de compra con IGV desglosado
- Exportación PLE 14.1 (snapshot)
- Exportación PLE 8.1 (snapshot)
- Plan contable sembrado con cuentas SUNAT mínimas

---

## Criterios de aceptación

### Por ola
- **Ola A:** 4 fixes + 4 tests + 0 regresiones
- **Ola B:** 12 tests Purchases pasan; importa `calcularLineaIgv` (no duplica)
- **Ola C:** PLE 14.1/8.1 validados con caso real; asientos cuadran

### Globales
- `composer test` → 0 fallos (35+ → 47+ tests al cerrar Ola B)
- `npm run test && npm run lint` → 0 errores
- **Auditoría:** sin regresiones en fixes previos (A-01..A-11)
- **Git:** commits con prefijo `feat(hito-004):` (features) y `fix(audit-003):` (remediaciones)

---

## Riesgos

| Riesgo | Mitigación |
|---|---|
| Ola A retrasa inicio de Ola B | Ola A es ~1 sprint; si se atrasa, B puede iniciar con solo A-08 + A-11 (los más críticos para Purchases) |
| A-02 multi-almacén se complica | MVP: 1 almacén por defecto + selector opcional. Kardex multi-almacén completo en HITO-005 |
| PLE 14.1/8.1 cambia formato SUNAT | Implementar con datos de prueba 2025; validar con contador antes de producción |
| Greenter no instalado | HITO-004 emite XML/PLE; envío SOAP a SUNAT queda para HITO-006 |

---

## Métricas de seguimiento

| Métrica | Meta | Frecuencia |
|---|---|---|
| Hallazgos 🟠 cerrados al cerrar Ola A | 11/11 | Por sprint |
| Módulos comprados (P/F) al cerrar Ola C | 2/2 | Por sprint |
| Tests Sales+Inventory+Purchases+Finance | 100% pasan | Por commit |
| Cobertura services críticos | ≥ 80% | Por ola |

---

## Alternativas descartadas

| Opción | Razón de descarte |
|---|---|
| Empezar Purchases sin cerrar Ola A | Replicaría A-08, A-11 en Purchases; refactor posterior más caro |
| Hacer Finance antes que Purchases | PLE 8.1 depende de `purchases_compras`; sin compras no se puede probar el flujo |
| Matar A-02 (eliminar `dim_almacen`) | Pérdida de capability; cuando entre logística multi-almacén habría que re-crear |

---

## Historial

| Fecha | Cambio |
|---|---|
| 2026-06-05 | Creación ADR-010 (tras archivar ADR-009) |

---

*Documento generado tras validación del ADR-009 y planificación de remediación residual.*
