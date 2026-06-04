# ADR-009 — Estrategia de remediación intercalada para hallazgos A-01..A-11 durante HITO-004

**Estado:** Aprobado  
**Fecha:** 2026-06-04  
**Contexto:** HITO-003 cerrado 🟡 condicional. 3 críticos corregidos, 11 hallazgos 🟠 pendientes.  
**Decisión:** Opción A — Intercalar 4 fixes upfront (~4h) + 7 fixes durante HITO-004.

---

## Contexto

HITO-003 (Sales + Inventory) aprobado condicionalmente por el auditor tras verificarse las correcciones C-01, C-02, C-03 en commit `5ba310b`.

Quedan 11 hallazgos 🟠 que deben remediarse antes del cierre de HITO-004:

| ID | Hallazgo | Impacto |
|---|---|---|
| A-01 | `generateCode()` no atómico — race condition | Códigos duplicados en producción |
| A-02 | `dim_almacen` creada pero nunca usada | Inconsistencia de diseño |
| A-03 | `update` permite modificar ítems de venta confirmada | Violación de integridad |
| A-04 | `DescontarStock` abre transacción separada | Venta confirmada sin stock descontado |
| A-05 | `AuthService::login` sin rate limiting | Fuerza bruta ilimitada |
| A-06 | DNI/RUC sin validación de formato | Datos inválidos en BD |
| A-07 | `exists:customers,id` ignora SoftDeletes | FK a registros eliminados |
| A-08 | `SalePolicy` sin método `confirmar` dedicado | Semántica incorrecta |
| A-09 | `anular` no actualiza `saldo_pendiente` | Saldo inconsistente |
| A-10 | `update` no recalcula `saldo_pendiente` | Saldo inconsistente |
| A-11 | `index()` no llama a `authorize('viewAny')` | Bypass de policy |

---

## Decisión

### Opción elegida: Intercalar (Opción A)

| Fase | Contenido | Esfuerzo |
|---|---|---|
| **Fase 0** | A-01, A-04, A-05, A-07 upfront | ~4h |
| **Fase 1** | HITO-004 Purchases base (60%) | Sprint 1 |
| **Fase 2** | A-02, A-03, A-08, A-09, A-10, A-11 durante HITO-004 | ~3h |
| **Fase 3** | HITO-004 Finance (40%) | Sprint 2 |
| **Fase 4** | A-06 + M-04 + B-01..B-03 cleanup residual | Baja prioridad |

### Alternativas descartadas

| Opción | Razón de descarte |
|---|---|
| **Opción B (upfront total)** | Sprint dedicado solo a refactor sin features visibles; riesgo de regresión sin detector |
| **Opción C (HITO-004 primero)** | Mismo bug se replica en 2 módulos; migración correctiva más costosa |

---

## Consecuencias

### Positivas
- Cada fix se valida con código real nuevo (no regresión ciega)
- El patrón correcto se propaga a Purchases desde día 1
- Reduce migración correctiva futura
- Auditor puede validar fixes en contexto

### Negativas
- Estimación total: 2-3 sprints
- HITO-004 se entrega en olas, no de una vez

---

## Plan de ejecución

### Fase 0: Upfront fixes (prerrequisito HITO-004)

| # | Fix | Archivos | Criterio de done |
|---|---|---|---|
| 0.1 | A-01: Secuencia PostgreSQL para `generateCode` | `SaleService.php`, migración `create_sales_codigo_seq` | 2 requests concurrentes → códigos distintos |
| 0.2 | A-04: Event/transaction boundary listener | `SaleService.php`, `DescontarStock.php` | Si listener falla, venta no queda confirmada |
| 0.3 | A-05: Rate limiting en login | `bootstrap/app.php` (throttle:5,1) | 6to login en 1 min → 429 |
| 0.4 | A-07: SoftDeletes en reglas `exists` | `StoreSaleRequest.php`, `UpdateSaleRequest.php` | Cliente soft-deleteado → 422 |

### Fase 1: HITO-004 Purchases base

| Task | Descripción |
|---|---|
| Migraciones `purchases_compras` + `purchases_proveedores` (ULID + SoftDeletes) | Crear tablas |
| `SupplierService` + `PurchaseService` | Importar `calcularLineaIgv` de Sales (no duplicar) |
| `PurchaseConfirmed → AfectarStock` listener | Reutilizar fix A-04 |
| `StorePurchaseRequest` con SoftDeletes | Reutilizar fix A-07 |
| Tests Feature Purchases (10 tests) | Cobertura mínima |

### Fase 2: Fixes durante HITO-004

| # | Fix | Dónde aplica en Purchases |
|---|---|---|
| 2.1 | A-02: Decisión multi-almacén | Si Purchases afecta 1 almacén → `whereNull('almacen_id')` aceptable; si N → implementar |
| 2.2 | A-03: Update bloquea confirmadas | `PurchaseService::update` replica guard |
| 2.3 | A-08: Policy `confirmar` dedicado | `PurchasePolicy::confirmar()` desde día 1 |
| 2.4 | A-11: `authorize('viewAny')` | `PurchaseController::index` |
| 2.5 | A-09/A-10: `saldo_pendiente` | Purchase tendrá `monto_pagado` + `saldo_pendiente` |

### Fase 3: HITO-004 Finance

| Task | Descripción |
|---|---|
| Migraciones `finance_asientos` + `finance_asiento_lineas` | Tablas contables |
| `FinanceService::generarAsientoContable(Sale|Purchase)` | Doble partida |
| Eventos `VentaContabilizada` + `CompraContabilizada` | Event-driven |
| PLE 14.1 (Registro Ventas) + PLE 8.1 (Registro Compras) | Formato SUNAT |
| Tests Finance | Cobertura |

### Fase 4: Cleanup residual

| # | Fix | Prioridad |
|---|---|---|
| 4.1 | A-06: DNI/RUC regex en LoginRequest | ⏳ Post-HITO-004 |
| 4.2 | M-04: Activitylog con IP/UA | ⏳ Post-HITO-004 |
| 4.3 | B-01..B-03: Cleanups menores | ⏳ Post-HITO-004 |

---

## Arquitectura de archivos

```
backend/
├── app/Modules/
│   ├── Sales/Services/SaleService.php          [M: A-01, A-04]
│   ├── Sales/Listeners/DescontarStock.php      [M: A-04]
│   ├── Purchases/                               ← NUEVO
│   │   ├── Http/Controllers/PurchaseController.php
│   │   ├── Http/Requests/StorePurchaseRequest.php
│   │   ├── Http/Resources/PurchaseResource.php
│   │   ├── Services/PurchaseService.php
│   │   ├── Events/PurchaseConfirmed.php
│   │   ├── Listeners/AfectarStock.php
│   │   ├── Policies/PurchasePolicy.php
│   │   └── Models/Purchase.php, PurchaseItem.php
│   ├── Finance/                                 ← NUEVO
│   │   ├── Http/Controllers/FinanceController.php
│   │   ├── Services/FinanceService.php
│   │   ├── Events/VentaContabilizada.php
│   │   └── Models/AsientoContable.php
│   └── Auth/Http/Requests/LoginRequest.php    [M: A-06]
├── database/migrations/
│   ├── 2026_06_05_010000_create_sales_codigo_seq.php  ← NUEVO
│   ├── 2026_06_05_020000_create_purchases_tables.php  ← NUEVO
│   └── 2026_06_05_030000_create_finance_tables.php    ← NUEVO
└── tests/Feature/
    ├── SaleTest.php                               [+tests A-01, A-04, A-05, A-07]
    ├── PurchaseTest.php                          ← NUEVO
    └── FinanceTest.php                           ← NUEVO
```

---

## Criterios de aceptación

### Por fase
- **Fase 0:** 4 fixes + 4 tests + 0 regresiones en suite actual
- **Fase 1:** 10 tests Purchases pasan; importa `calcularLineaIgv` (no duplica)
- **Fase 2:** Cada fix validado con test específico
- **Fase 3:** PLE 14.1 + 8.1 validados con caso real

### Globales
- `composer test` → 0 fallos (35+ tests)
- `npm run test && npm run lint` → 0 errores
- **Auditoría:** sin regresiones en fixes previos
- **Git:** commits con prefijo `fix(audit-002):`

---

## Riesgos

| Riesgo | Mitigación |
|---|---|
| Regresión en `generateCode` al cambiar a secuencia | Test concurrente con `Process::concurrently()` |
| A-04 con `Event::fake()` no detecta el bug | Test sin `Event::fake()` que verifica rollback |
| HITO-004 > 3 sprints | Gate: si Fase 1+2 > 2 sprints, diferir PLE a HITO-005 |
| Greenter no instalado aún | HITO-004 emite XML; envío SOAP a SUNAT en HITO-006 |

---

## Métricas de seguimiento

| Métrica | Meta | Frecuencia |
|---|---|---|
| Hallazgos 🟠 cerrados | 11/11 al cierre HITO-004 | Por sprint |
| Tests Sales+Inventory+Purchases+Finance | 100% pasan | Por commit |
| Cobertura services críticos | ≥ 80% | Por fase |

---

## Historial

| Fecha | Cambio |
|---|---|
| 2026-06-04 | Creación ADR-009 |

*Documento generado tras re-auditoría HITO-003 y planificación HITO-004.*