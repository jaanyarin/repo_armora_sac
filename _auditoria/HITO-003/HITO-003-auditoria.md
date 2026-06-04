# Gate Review — HITO 003: Sales + Inventory

**Re-auditoría.**  
**Fecha:** 2026-06-04 (revisión post-corrección)  
**HITO entregado:** Sales + Inventory (backend completo + frontend parcial)  
**Estado del HITO:** 🟡 **APROBADO CON OBSERVACIONES** — 3 críticos corregidos. 11 altos pendientes.

---

## Semáforo

| Área | Estado | Hallazgos |
|---|---|---|
| Arquitectura y patrones | 🟡 ⚠️ | A-02 (almacén sin uso), A-03 (update permite confirmadas), A-08 (Policy incompleta) |
| Seguridad y RBAC | 🟡 ⚠️ | A-05 (sin rate limit), A-06 (DNI sin validación), C-02 (✅ corregido) |
| SUNAT / IGV | 🟢 ✅ | C-03 (✅ corregido + migración correctiva + test) |
| Tests | 🟡 ⚠️ | 15/15 Sales+Inventory ✅ | 26/35 totales (fallos preexistentes en CustomerTest) |
| Consistencia documental | 🟢 ✅ | C-01 (✅ corregido — índice honesto) |
| Frontend | 🟡 ⚠️ | 6/6 Vitest ✅. IGV calculator compartido. Pendiente auditoría extensa. |
| BD / Migraciones | 🟡 ⚠️ | A-02 (almacén), M-03 (unique constraint). Migración correctiva IGV aplicada. |

---

## Resumen de hallazgos

| Severidad | Cantidad | Estado |
|---|---|---|
| 🔴 Crítico | 3 → 0 | ✅ **3 corregidos** (verificado en commit `5ba310b` + tests 15/15) |
| 🟠 Alto | 11 | ❌ Pendientes de corrección (deben remediarse antes de HITO 004) |
| 🟡 Medio | 8 | ⏳ Documentados, remediación según capacidad |
| 🟢 Bajo | 3 | ⏳ Oportunidad de mejora |

**Total: 22 hallazgos abiertos (de 25 originales)**

---

## Correcciones verificadas

| ID | Hallazgo | Verificación | Resultado |
|---|---|---|---|
| C-01 | Docs fantasma | `INDICE_MAESTRO.md` reescrito (15 reales + 20 pendientes). Perfil v3 corregido. | ✅ |
| C-02 | RBAC bypass | `routes/api.php`: PUT→`editar-ventas`, DELETE→`eliminar-ventas`. Nuevo permiso seeder. +4 tests. | ✅ |
| C-03 | IGV inconsistente | `calcularLineaIgv()` fuente única. Backend + frontend. Migración correctiva. +1 test. 15/15 pasan. | ✅ |
| Bonus | 500→401 en API | `bootstrap/app.php`: `redirectGuestsTo` JSON | ✅ |

---

## Pendientes antes de HITO 004

- A-01: Race condition en `generateCode()` — `withTrashed()->count()+1` no atómico
- A-02: `dim_almacen` creada pero nunca usada (siempre `whereNull('almacen_id')`)
- A-03: `update` permite modificar ítems de venta confirmada
- A-04: Listener `DescontarStock` abre transacción separada
- A-05: `AuthService::login` sin rate limiting
- A-06: DNI/RUC sin validación de formato en login
- A-07: `exists:customers,id` ignora SoftDeletes
- A-08: `SalePolicy` sin método `confirmar` dedicado
- A-09: `anular` no actualiza `saldo_pendiente`
- A-10: `update` no recalcula `saldo_pendiente`
- A-11: `index()` no llama a `authorize('viewAny')`

---

## Veredicto

> **HITO 003 se aprueba condicionalmente.**  
> Los 3 hallazgos críticos (C-01, C-02, C-03) han sido corregidos y verificados en código y tests. Quedan 22 hallazgos abiertos (11 altos, 8 medios, 3 bajos) que deben remediarse antes del cierre de HITO 004. El arquitecto ha documentado las correcciones en `_docs_desarrollo/HITO-AUDIT-001-correcciones-auditor.md`.

---

*Re-auditoría — 2026-06-04*
