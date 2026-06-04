# Hallazgos de Auditoría — HITO 003 (Sales + Inventory)

**Fecha:** 2026-06-04 (re-auditoría)  
**Total:** 25 hallazgos (3 🔴 cerrados + 11 🟠 + 8 🟡 + 3 🟢 = **22 abiertos**)

---

## ✅ Críticos — CORREGIDOS (commit `5ba310b`)

### C-01 — Documentación publicitada no existe en disco — ✅ CORREGIDO

**Corrección verificada:**
- `INDICE_MAESTRO.md` reescrito — lista 15 docs reales + 20 pendientes con fase planificada
- Perfil v3 referencias actualizadas a docs reales
- Nuevo doc `HITO-AUDIT-001-correcciones-auditor.md` como bitácora

### C-02 — Permisos RBAC incorrectos en rutas de ventas — ✅ CORREGIDO

**Corrección verificada:**
- `routes/api.php`: PUT→`editar-ventas`, DELETE→`eliminar-ventas`
- Seeder: nuevo permiso `eliminar-ventas` (solo Admin/Super-Admin)
- Seeder idempotente (`firstOrCreate` + `syncPermissions`)
- +4 tests en SaleTest (vendedor update OK, vendedor delete 403, admin delete OK, logistica update 403)

### C-03 — Cálculo de IGV inconsistente entre paths — ✅ CORREGIDO

**Corrección verificada:**
- Método único `calcularLineaIgv()` en `SaleService.php`
- `create()` y `update()` usan `calcularItem()` → `calcularLineaIgv()`
- Migración correctiva `2026_06_04_170000_recalcular_igv_formula_unica.php`
- Frontend: `shared/utils/igvCalculator.ts` con misma fórmula
- +1 test: `test_igv_calculation_is_consistent` (4 propiedades validadas)
- 15/15 tests Sales+Inventory pasan (37 assertions)

---

## 🟠 Altos (deben remediarse antes de HITO 004)

| ID | Hallazgo | Archivo | Remedio |
|---|---|---|---|
| A-01 | Race condition en `generateCode()`: `withTrashed()->count() + 1` no es atómico | `SaleService.php:185` | Usar secuencia PostgreSQL o UUID |
| A-02 | `dim_almacen` creada pero `InventoryService` siempre usa `whereNull('almacen_id')` | `InventoryService.php:19,70` | Decidir: quitar o implementar multi-almacén |
| A-03 | `update` permite modificar ítems de venta confirmada | `SaleService.php:89` | Agregar `confirmada` al guard |
| A-04 | Listener `DescontarStock` abre transacción separada; si falla, venta queda confirmada sin stock | `DescontarStock.php:18` vs `SaleService.php:140-144` | Transacción global que cubra venta + stock |
| A-05 | `AuthService::login` sin rate limiting | `AuthService.php:22-26` | RateLimiter con 5 intentos/min, lockout 15 min |
| A-06 | DNI/RUC sin validación de formato en login | `AuthService.php:17-18` | Regex en LoginRequest |
| A-07 | `exists:customers,id` ignora SoftDeletes | `StoreSaleRequest.php:18,29` | `->whereNull('deleted_at')` en la regla |
| A-08 | `SalePolicy` sin método `confirmar` dedicado (usa `update` para confirmar) | `SaleController.php:53` | Agregar `confirmar()` en Policy |
| A-09 | `anular` no actualiza `saldo_pendiente` a 0 | `SaleService.php:147-162` | Poner `saldo_pendiente = 0` al anular |
| A-10 | `update` no recalcula `saldo_pendiente` al cambiar total | `SaleService.php:86-132` | Recalcular después de update |
| A-11 | `index()` no llama a `authorize('viewAny')` | `SaleController.php:25-31` | Agregar `$this->authorize('viewAny', Sale::class)` |

---

## 🟡 Medios

| ID | Hallazgo | Archivo | Remedio |
|---|---|---|---|
| M-01 | `currentAccessToken()->delete()` falla sin token | `AuthService.php:42` | Validar existencia antes de eliminar |
| M-02 | `LoginRequest` no valida largo de DNI/RUC | `LoginRequest.php` | Agregar regex |
| M-03 | `customer.numero_documento` sin unique compuesto | Migración customers | UNIQUE(tipo_documento_id, numero_documento) |
| M-04 | `LogsActivity` sin contexto adicional | Modelos | Configurar `$logAttributes` |
| M-05 | Falta Scribe/Scramble para docs API | proyecto | Instalar y configurar |
| M-06 | Tipos monetarios como float en vez de decimal | `SaleService.php` | Usar `brick/money` |
| M-07 | `update` permite cambiar `cliente_id` post-emisión | `SaleService.php:123` | Proteger campo si estado ≥ confirmada |
| M-08 | `confirmar` no valida stock antes de cambiar estado | `SaleService.php:134-145` | Validar stock en confirmar() |

---

## 🟢 Bajos

| ID | Hallazgo | Archivo | Remedio |
|---|---|---|---|
| B-01 | DELETE hace soft delete, no hay purge endpoint | `SaleService.php:166` | Agregar `/force` si se necesita |
| B-02 | `customer_id` no valida `activo` | `StoreSaleRequest.php:18` | Agregar `where('activo', true)` |
| B-03 | Relación `cliente()` sin `withTrashed()` | Modelo Sale | Usar `->withTrashed()` |

---

*Hallazgos HITO 003 — Versión 1.0 — 2026-06-04*
