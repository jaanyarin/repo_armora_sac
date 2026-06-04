# Hallazgos de Auditoría — HITO 003 (Sales + Inventory)

**Fecha:** 2026-06-04  
**Total:** 25 hallazgos (3 🔴 + 11 🟠 + 8 🟡 + 3 🟢)

---

## 🔴 Críticos (deben remediarse para cerrar el HITO)

### C-01 — Documentación publicitada no existe en disco

El `INDICE_MAESTRO.md` publicita 35+ documentos. Solo existen 16 archivos reales. Los directorios `05_especificaciones_tecnicas/` y `07_seguridad_compliance/` están vacíos.

- **Archivo:** `_docs_implementacion/INDICE_MAESTRO.md`
- **Remediación:** Eliminar las referencias falsas o crear los documentos. No publicitar lo que no existe.
- **Verificación:** `Get-ChildItem -Recurse _docs_implementacion | Measure-Object` debe coincidir con el conteo del índice.

### C-02 — Permisos RBAC incorrectos en rutas de ventas

| Ruta | Permiso actual | Permiso correcto |
|---|---|---|
| `PUT /api/sales/{sale}` | `ver-ventas` | `editar-ventas` |
| `DELETE /api/sales/{sale}` | `ver-ventas` | `eliminar-ventas` |

Esto significa que cualquier usuario con `ver-ventas` (permiso de solo lectura) puede modificar y eliminar ventas.

- **Archivo:** `backend/routes/api.php:65,68`
- **Remediación:** Cambiar los middleware de ruta.
- **Verificación:** Probar `PUT /api/sales/{id}` con token de usuario con solo `ver-ventas` → debe responder 403.

### C-03 — Cálculo de IGV inconsistente entre paths

`SaleService::create()` línea 60: `$lineIgv = round($lineTotal * 0.18, 2)`  
`SaleService::calcularTotales()` línea 174: `$lineSubtotal = round($lineTotal / 1.18, 2)`

La primera fórmula es incorrecta (da ~16.27% en vez de 18%), la segunda es correcta. Los valores guardados difieren según el path de código.

- **Archivo:** `backend/app/Modules/Sales/Services/SaleService.php:60,174`
- **Remediación:** Ver ADR-A001 para la solución detallada.
- **Verificación:** Crear una venta con `{"items": [{"cantidad": 1, "precio_unitario": 118}]}` → debe dar `subtotal=100, igv=18, total=118`.

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
