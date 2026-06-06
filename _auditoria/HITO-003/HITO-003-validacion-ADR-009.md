# VALIDACIÓN ADR-009 — Auditoría Fase 0

**Fecha:** 2026-06-05
**Auditor:** Senior Code & Architecture Quality Auditor
**Commit auditado:** `d7e5fdd fix(audit-002): cerrar Fase 0 ADR-009 con A-01/A-04/A-05/A-07 + colaterales A-03/A-09/A-10`
**Veredicto global:** 🟡 **APROBADO PARCIAL** — Fase 0 ✅ | Fases 1-4 ❌

---

## 1. Resumen ejecutivo

| Métrica | Valor |
|---|---|
| Fixes upfront (Fase 0) | 4/4 ✅ |
| Fixes colaterales | 3/3 ✅ (A-03, A-09, A-10) |
| Fixes pendientes | 4/11 ❌ (A-02, A-06, A-08, A-11) |
| Módulos nuevos | 0/2 ❌ (Purchases, Finance) |
| Tests Sales+Inventory+Auth | 27/27 ✅ |
| Tests frontend (Vitest) | 6/6 ✅ |
| Lint | 0 errores ✅ |
| Compilación | OK ✅ |

**Conclusión:** El arquitecto cerró **Fase 0 + 3 colaterales** (= 7/11 hallazgos) pero NO ejecutó Fases 1-4 (HITO-004 Purchases + Finance + cleanup). El ADR-009 estaba compuesto por 5 fases; solo se completó la primera.

---

## 2. Verificación detallada por hallazgo (cerrados)

### ✅ A-01 — Secuencia PostgreSQL para `generateCode`
- `database/migrations/2026_06_05_010000_create_sales_codigo_seq.php:17-22` crea `sales_codigo_seq`
- `setval` inicial desde `MAX(codigo)` existente
- `SaleService.php:233` usa `nextval('sales_codigo_seq')` atómico
- Test `test_generateCode_uses_atomic_sequence` valida consecutividad ✅

### ✅ A-04 — Transaccionalidad inline
- `SaleService.php:140-160` `confirmar()` invoca `descontarPorVenta()` inline
- `InventoryService.php:76-80` detecta `transactionLevel() > 0` para no anidar
- `AppServiceProvider.php:30-34` NO auto-registra listener (comentario explícito)
- Test `test_listener_failure_rolls_back_sale_confirmation` valida rollback ✅

### ✅ A-05 — Rate limiting en login
- `AppServiceProvider.php:37-44` named limiter `login` (5/min key=login|ip)
- `routes/api.php:11` aplica `throttle:login`
- `bootstrap/app.php:24` `throttleApi()` global (60/min defense in depth)
- Test `test_login_is_rate_limited_after_5_attempts` valida 429 ✅

### ✅ A-07 — SoftDeletes en `exists`
- `StoreSaleRequest.php:19,31` y `UpdateSaleRequest.php:28` con `Rule::exists(...)->whereNull('deleted_at')`
- Tests `test_sale_with_soft_deleted_*_is_rejected` validan 422 ✅

### ✅ A-03 (colateral) — `update` rechaza confirmada
- `SaleService.php:87-91` lanza `ValidationException` con mensaje ES
- Test `test_update_sale_confirmada_is_rejected` ✅

### ✅ A-09 (colateral) — `anular` resetea saldo
- `SaleService.php:174-177` setea `saldo_pendiente: 0` ✅

### ✅ A-10 (colateral) — `update` recalcula saldo
- `SaleService.php:120-124` recalcula `saldo_pendiente = total` ✅

---

## 3. Hallazgos NO remediados

### ❌ A-02 — `dim_almacen` nunca se usa
- `InventoryService.php:29` sigue con `whereNull('almacen_id')`
- Decisión arquitectónica pendiente: multi-almacén real o eliminar `almacen_id`

### ❌ A-06 — DNI/RUC sin validación de formato
- `LoginRequest` no aplica regex DNI (8) / RUC (11)
- Riesgo bajo (rate limit cubre) pero datos inválidos posibles

### ❌ A-08 — `SalePolicy` sin método `confirmar` dedicado
- `SaleController.php:53` sigue con `authorize('update', $sale)`
- `SalePolicy` no tiene método `confirmar`
- Vendedor con `editar-ventas` puede confirmar (debería ser permiso separado)

### ❌ A-11 — `index` no llama a `authorize('viewAny')`
- `SaleController.php:25-31` sin policy check
- Middleware cubre, pero rompe "defense in depth"

---

## 4. Módulos NO implementados

### ❌ Purchases (Fase 1)
`glob backend/app/Modules/Purchases/**/*.php` → No files found

No existe: `PurchaseController`, `PurchaseService`, `StorePurchaseRequest`, `PurchaseConfirmed`, `PurchasePolicy`, `PurchaseTest`, migraciones `purchases_*`.

### ❌ Finance (Fase 3)
`glob backend/app/Modules/Finance/**/*.php` → No files found

No existe: `FinanceService`, `VentaContabilizada`, migraciones `finance_asientos`, PLE 14.1/8.1.

---

## 5. Discrepancia en el documento de cierre

`HITO-INTERCALAR-001-fase-0-auditoria.md:295-307` lista "Pendientes para Fase 1" con **ítems incorrectos** que no corresponden al ADR-009:

| Línea | Dice el doc | Realidad ADR-009 |
|---|---|---|
| 297 | "A-02: Frontend OrderHistoryPage skeleton" | A-02 = `dim_almacen` sin uso |
| 298 | "A-06: Inventory Admin UI" | A-06 = DNI/RUC regex |
| 299 | "A-08: Plantilla XML UBL 2.1" | A-08 = `SalePolicy::confirmar` |
| 300 | "A-11: Queue Redis + worker" | A-11 = `authorize('viewAny')` |

**Diagnóstico:** Copy-paste de roadmap general, no del ADR-009.

---

## 6. Recomendación sobre el archivo `ADR-009-remediacion-intercalada-hito-003-004.md`

**El ADR-009 no puede darse por cerrado completo** (solo Fase 0 ejecutada).

**Opciones:**

| Opción | Descripción | Trade-offs |
|---|---|---|
| **A (Recomendada)** | Archivar en `_docs_desarrollo/archivados/ADR-009-fase-0-cerrada.md` + crear **ADR-010** con Fases 1-4 | Trazabilidad clara; separación de concerns |
| **B** | Marcar ADR-009 como `[PARCIAL]` en título; sin mover | Menos disrupción; archivar al cerrar todo |
| **C** | Tratar Fase 0 como cierre total; crear ADR-010 independiente para Purchases | Más simple; pierde visión de remediación intercalada |

**Recomendación:** Opción A. Razones:
1. El archivo declara 5 fases explícitas — etiquetarlo cerrado a medias genera confusión
2. La remediación intercalada era la **estrategia** del ADR-009; fragmentar es contraproducente
3. ADR-010 puede citar el ADR-009 archivado como antecedente

---

## 7. Veredicto final

| Componente | Estado |
|---|---|
| HITO-003 backend | ✅ Cerrado |
| HITO-003 frontend | ✅ Cerrado |
| C-01, C-02, C-03 | ✅ Cerrados (commit `5ba310b`) |
| A-01, A-03, A-04, A-05, A-07, A-09, A-10 | ✅ Cerrados (commit `d7e5fdd`) |
| A-02, A-06, A-08, A-11 | ❌ Abiertos |
| HITO-004 Purchases | ❌ No iniciado |
| HITO-004 Finance | ❌ No iniciado |
| 8 hallazgos 🟡 medios + 3 🟢 bajos | ⏳ Sin tratar |

**Próximos pasos para el arquitecto:**

1. Decidir el destino del archivo ADR-009 (recomendado: Opción A)
2. Corregir `HITO-INTERCALAR-001-fase-0-auditoria.md` (ítems de pendientes son incorrectos)
3. Planificar Fases 1-4 (o ADR-010) — sin Purchases no se pueden emitir comprobantes de compra
4. Cerrar los 4 hallazgos 🟠 restantes antes de iniciar HITO-004 (A-02, A-06, A-08, A-11)

---

*Validación cerrada: 2026-06-05*
