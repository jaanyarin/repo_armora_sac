# ISSUE para el Arquitecto — Discrepancias en HITO-INTERCALAR-001-fase-0-auditoria.md

**De:** Senior Code & Architecture Quality Auditor
**Para:** Senior Fullstack ERP Architect
**Fecha:** 2026-06-05
**Asunto:** Discrepancias en el documento de cierre de Fase 0 del ADR-009
**Severidad:** 🟡 Media (documental, no funcional)
**Estado:** ✅ **CERRADO** (commit `9954a8c` del 2026-06-05)

---

## Hallazgo

Al validar el commit `d7e5fdd` que cerró la Fase 0 del ADR-009, detecté que la sección **"Pendientes para Fase 1"** del documento `_docs_desarrollo/HITO-INTERCALAR-001-fase-0-auditoria.md:295-307` lista **ítems que NO corresponden al ADR-009**.

## Evidencia

### Texto actual (líneas 295-307)

```markdown
## Pendientes para Fase 1 (Hito 004 — Purchases)

1. **A-02**: Frontend `OrderHistoryPage` con skeleton loaders y error boundary.
2. **A-06**: Inventory Admin UI (stock list + kardex) — placeholder actual.
3. **A-08**: Plantilla XML UBL 2.1 para facturas (preparación SUNAT).
4. **A-11**: Queue Redis + worker supervisord.
5. **Greenter 5.x + envío SOAP a SUNAT** (próximo hito post-004).
6. Refactorizar listener `DescontarStock` para que se ejecute de forma
   **explícita** (no implícita via event) si el patrón se replica en
   `PurchaseConfirmed → AumentarStock`.
7. Multi-tenancy (`empresa_id`) cuando entremos a Hito 005.
```

### Discrepancia con el ADR-009 real

| ID | Dice el doc | Realidad ADR-009 (`_docs_desarrollo/archivados/ADR-009-fase-0-cerrada.md`) |
|---|---|---|
| **A-02** | "Frontend `OrderHistoryPage` skeleton loaders" | A-02 = `dim_almacen` se crea pero `InventoryService` siempre usa `whereNull('almacen_id')` |
| **A-06** | "Inventory Admin UI (stock list + kardex)" | A-06 = DNI/RUC sin validación de formato en `AuthService::login` |
| **A-08** | "Plantilla XML UBL 2.1 para facturas" | A-08 = `SalePolicy` mezcla permisos: usa `update` para `confirmar`; falta método `confirmar` dedicado |
| **A-11** | "Queue Redis + worker supervisord" | A-11 = `SaleController::index` no llama a `authorize('viewAny', Sale::class)` |

## Diagnóstico

Parece un **copy-paste de roadmap general** (probablemente del perfil v3 o de HITO-AUDIT-001) en lugar de referenciar los hallazgos reales del ADR-009. Los IDs A-02, A-06, A-08, A-11 fueron reasignados a features genéricas.

## Impacto

- **Riesgo de confusión para futuros lectores** que busquen "A-02 en el ADR-009" y no encuentren coherencia
- **Riesgo de duplicación de trabajo** si el arquitecto decide abordar "A-02" pensando que es el skeleton del OrderHistoryPage cuando en realidad es el almacén no usado
- **Documentación no confiable** — al igual que C-01 (docs fantasma), esto erosiona la credibilidad del repositorio documental

## Recomendación

Reemplazar las líneas 295-307 del archivo `HITO-INTERCALAR-001-fase-0-auditoria.md` con la lista correcta:

```markdown
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
```

## Acción solicitada

1. ✅ Reemplazar las líneas 295-307 con la versión correcta arriba
2. ✅ Commit con prefijo `docs:` o `fix(audit-002):` (corrección documental post-validación)
3. ⏳ Una vez corregido, el ADR-009 archivado + ADR-010 nuevo + HITO-INTERCALAR-001 corregido conforman el set documental consistente

## Acción tomada por el auditor

- ✅ ADR-009 movido a `_docs_desarrollo/archivados/ADR-009-fase-0-cerrada.md` con etiqueta de archivado
- ✅ ADR-010 creado en `_docs_desarrollo/ADR-010-hito-004-purchases-finance.md` con Fases 1-4
- ✅ `_auditoria/HITO-003/HITO-003-validacion-ADR-009.md` generado con validación completa
- ✅ `_auditoria/MATRIZ_RIESGOS.md` actualizado con cierre de A-01, A-03, A-04, A-05, A-07, A-09, A-10

---

*Issue levantado durante validación post-implementación — 2026-06-05*
