# Matriz de Riesgos — ARMORA NextGen

**Documento vivo.** Cada hallazgo identificado en gate reviews se registra aquí con severidad, estado, y plan de remediación.  
**Última actualización:** 2026-06-06 (validación Ola A + B HITO-INTERCALAR-002)

---

## Resumen de estado

| Severidad | Abiertos | Cerrados | Total |
|---|---|---|---|
| 🔴 Crítico | 0 | 3 | 3 |
| 🟠 Alto | 0 | 11 | 11 |
| 🟡 Medio | 8 | 0 | 8 |
| 🟢 Bajo | 3 | 0 | 3 |
| **Total** | **11** | **14** | **25** |

---

## 🔴 Críticos

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| C-01 | 003 | Documentación publicitada (35+ docs) no existe en disco. Los directorios `05_especificaciones_tecnicas/` y `07_seguridad_compliance/` están vacíos. | `_docs_implementacion/` | Eliminar referencias falsas del índice o crear los documentos. Verificar que cada archivo listado en `INDICE_MAESTRO.md` exista. | Arquitecto | ✅ Cerrado — Corregido commit `5ba310b`. INDICE_MAESTRO.md reescrito: 15 reales + 20 pendientes. v3 perfil referencias corregidas. |
| C-02 | 003 | Permisos RBAC incorrectos: `PUT /sales/{sale}` y `DELETE /sales/{sale}` requieren `permission:ver-ventas` en vez de `editar-ventas` y `eliminar-ventas` respectivamente. | `backend/routes/api.php:65,68` | Corregir los middleware: `PUT → editar-ventas`, `DELETE → eliminar-ventas`. | Arquitecto | ✅ Cerrado — Corregido commit `5ba310b`. PUT ahora usa `editar-ventas`, DELETE usa `eliminar-ventas`. Nuevo permiso en seeder. +4 tests. |
| C-03 | 003 | Cálculo de IGV inconsistente: `SaleService::create()` calcula IGV como `lineTotal * 0.18` (incorrecto, ~16.27%) mientras `calcularTotales()` usa `lineTotal / 1.18 * 0.18` (correcto). Los valores guardados difieren según el path. | `backend/app/Modules/Sales/Services/SaleService.php:60,174` | Unificar cálculo: sacar a método privado único `calcularLineaIgv(cantidad, precioUnitario)` que use la fórmula correcta `gravada = total / 1.18; igv = gravada * 0.18`. | Arquitecto | ✅ Cerrado — Corregido commit `5ba310b`. Método único `calcularLineaIgv()`. Migración correctiva. Frontend `igvCalculator.ts`. +1 test. 15/15 tests Sales+Inventory pasan. |

---

## 🟠 Altos

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| A-01 | 003 | Race condition en `generateCode()`: `withTrashed()->count() + 1` no es atómico. | `SaleService.php:185` | Usar `DB::raw('SELECT nextval(...)')` o secuencia PostgreSQL o UUID para código. | Arquitecto | ✅ Cerrado — Secuencia `sales_codigo_seq` (commit `d7e5fdd`). Test consecutividad OK. |
| A-02 | 003 | `dim_almacen` se crea en migración pero `InventoryService` siempre usa `whereNull('almacen_id')`. | `InventoryService.php:19,70` | Decidir: quitar `almacen_id` hasta que haya lógica multi-almacén real o implementar multi-almacén completo. | Arquitecto | ✅ Cerrado — Multi-almacén MVP en commit `5b4fd05` (Ola A). `InventoryService::resolverAlmacenId()` con cadena de fallback (explícito→principal→primero→crear). `App\Models\Catalog\Almacen.php` modelo Eloquent. Compras usan `almacen_id`. Transferencias entre almacenes → HITO-005 (D-02). |
| A-03 | 003 | `SaleService.update` permite modificar ítems de venta confirmada (solo bloquea anulada/pagada). | `SaleService.php:89` | Agregar `confirmada` al guard: `if (in_array($sale->estado, ['anulada', 'pagada', 'confirmada']))`. | Arquitecto | ✅ Cerrado — Colateral en commit `d7e5fdd` |
| A-04 | 003 | Listener `DescontarStock` abre su propia transacción; si falla, la venta queda confirmada sin descuento de stock. | `DescontarStock.php:18` vs `SaleService.php:140-144` | Mover el `event(new SaleConfirmed(...))` fuera de la transacción y hacer que el listener ejecute dentro de la misma transacción del service, o usar transacción global que abarque venta + stock. | Arquitecto | ✅ Cerrado — Patrón inline en `confirmar()`/`update()` (commit `d7e5fdd`). `AppServiceProvider` no auto-registra listener. |
| A-05 | 003 | `AuthService::login` sin rate limiting. Fuerza bruta ilimitada. | `AuthService.php:22-26` | Agregar `RateLimiter` de Laravel en el controller o en `LoginRequest`, con 5 intentos/minuto y lockout de 15 min (OWASP API2:2023). | Arquitecto | ✅ Cerrado — `throttle:login` (5/min) + `throttleApi()` (60/min) (commit `d7e5fdd`) |
| A-06 | 003 | DNI/RUC sin validación de formato: `orWhere('dni', $login)` acepta cualquier string. | `AuthService.php:17-18` | Agregar expresión regular en `LoginRequest`: DNI = `/^\d{8}$/`, RUC = `/^\d{11}$/`. | Arquitecto | ✅ Cerrado — `LoginRequest.php:14-32` (commit `5b4fd05`, Ola A). Valida 8 dígitos → DNI, 11 → RUC, otros numéricos → rechaza. +3 tests AuthTest. |
| A-07 | 003 | `exists:customers,id` y `exists:products,id` en StoreSaleRequest no validan SoftDeletes (clientes/productos eliminados son válidos). | `StoreSaleRequest.php:18,29` | Reemplazar `exists:customers,id` por `exists:customers,id,deleted_at,NULL` (igual para products). | Arquitecto | ✅ Cerrado — `Rule::exists(...)->whereNull('deleted_at')` (commit `d7e5fdd`) |
| A-08 | 003 | `SalePolicy` mezcla permisos: método `update` usado para `confirmar` en vez de tener método `confirmar` dedicado. | `SaleController.php:53` + `SalePolicy.php:25-31` | Agregar método `confirmar(User $user, Sale $sale): bool` en `SalePolicy` y llamar `authorize('confirmar', $sale)` desde el controller. | Arquitecto | ✅ Cerrado — `SalePolicy::confirmar:56-58` (commit `5b4fd05`, Ola A). Permiso `confirmar-ventas` agregado al seeder. `CompraPolicy::confirmar:36-38` replica patrón (Ola B). **D-01**: Vendedor SÍ tiene acceso (decisión producto pendiente de validación stakeholder). |
| A-09 | 003 | `anular` no actualiza `saldo_pendiente` a 0 cuando la venta tiene pagos parciales. | `SaleService.php:147-162` | En `anular`, si `saldo_pendiente > 0`, ponerlo a 0. | Arquitecto | ✅ Cerrado — Colateral en commit `d7e5fdd` |
| A-10 | 003 | `update` no recalcula `saldo_pendiente` cuando cambia el total. | `SaleService.php:86-132` | Recalcular `saldo_pendiente` como `total - sum(pagos)` cuando exista módulo de pagos. Por ahora setear `saldo_pendiente = total - descuentos`. | Arquitecto | ✅ Cerrado — Colateral en commit `d7e5fdd` |
| A-11 | 003 | `SaleController::index` no llama a `authorize('viewAny', Sale::class)`. | `SaleController.php:25-31` | Agregar `$this->authorize('viewAny', Sale::class)` al inicio de `index()`. | Arquitecto | ✅ Cerrado — `SaleController:27` + `ProveedorController:24` + `CompraController:24` (commits `5b4fd05` + `8ddbcdd`). `InventoryPolicy` + 3 Gates resource-less en `AppServiceProvider:34-37` para módulos sin modelo "single". |

---

## 🟡 Medios

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| M-01 | 003 | `currentAccessToken()->delete()` falla si no hay token (comandos/jobs). | `AuthService.php:42` | Validar `if ($user->currentAccessToken())` antes de eliminar. | Arquitecto | 🟡 Abierto |
| M-02 | 003 | `LoginRequest` no valida largo de DNI (8) ni RUC (11). | `LoginRequest.php` | Agregar validación por regex después de determinar el tipo de documento por la longitud del input. | Arquitecto | ✅ Cerrado por A-06 (Ola A) — Resuelto en `LoginRequest:14-32`. |
| M-03 | 003 | `customer.numero_documento` sin UNIQUE constraint compuesto por `tipo_documento`. | Migración customers | Agregar índice único compuesto `(tipo_documento_id, numero_documento)`. | Arquitecto | 🟡 Abierto |
| M-04 | 003 | `LogsActivity` instalado pero sin contexto adicional (IP, user agent, motivo). | Modelos con `LogsActivity` | Configurar `$logAttributes` para incluir contexto de request en acciones críticas. | Arquitecto | 🟡 Abierto |
| M-05 | 003 | Falta autogeneración de documentación API (Scribe/Scramble). | Perfil v3 sección 6 | Instalar `scramble` y documentar endpoints. | Arquitecto | 🟡 Abierto |
| M-06 | 003 | Tipos monetarios como `float` en PHP en vez de `decimal` o `brick/math`. | `SaleService.php` líneas de cálculo | Usar `brick/money` o `decimal` para precisión fiscal. | Arquitecto | 🟡 Abierto |
| M-07 | 003 | `update` no protege cambios de `cliente_id` cuando la venta ya fue emitida. | `SaleService.php:123` | Si estado es confirmada o pagada, no permitir cambiar `cliente_id`. | Arquitecto | 🟡 Abierto |
| M-08 | 003 | `confirmar` no verifica stock disponible antes de cambiar estado. | `SaleService.php:134-145` | Agregar validación de stock en `confirmar()` antes de disparar el evento. | Arquitecto | 🟡 Abierto |
| **D-06** | **004** | **`Proveedor::generateCode()` no es atómico** (con concurrencia extrema podría colisionar). | `ProveedorService.php:55-59` | Migrar a `nextval('purchases_proveedor_codigo_seq')` consistente con `purchases_codigo_seq`. | Arquitecto | 🟡 **Nuevo (Ola B)** — Diferido a HITO-005 (volumen bajo, no bloqueante). |

---

## 🟢 Bajos

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| B-01 | 003 | DELETE HTTP hace soft delete; no se distingue papelera vs eliminación. | `SaleService.php:166` | Si se necesita purge, agregar endpoint `DELETE /sales/{sale}/force` con permiso específico. | Arquitecto | 🟢 Abierto |
| B-02 | 003 | `customer_id` no se valida contra `customers.activo`. | `StoreSaleRequest.php:18` | Agregar condición `where('activo', true)` a la regla exists. | Arquitecto | 🟢 Abierto |
| B-03 | 003 | Si un cliente se soft-deletea, las ventas existentes pierden nombre legible (no hay `withTrashed` en la relación). | Modelo Sale relación `cliente()` | Usar `->withTrashed()` en la relación para que las ventas existentes sigan mostrando datos del cliente. | Arquitecto | 🟢 Abierto |
| **D-07** | **004** | **FormRequests no leídos en detalle en esta auditoría** (existe validación parcial con `exists:dim_*` y `exists:purchases_*`). | `StoreCompraRequest.php`, `UpdateCompraRequest.php`, `StoreProveedorRequest.php`, `UpdateProveedorRequest.php` | Lectura completa y validación cruzada en próxima pasada (Ola C). | Auditor | 🟢 **Nuevo (Ola B)** — Diferido, no bloqueante. |

---

## Historial de cambios

| Fecha | Acción | Detalle |
|---|---|---|
| 2026-06-04 | Creación inicial | 25 hallazgos registrados de HITO 003 (3C + 11A + 8M + 3B) |
| 2026-06-04 | Re-auditoría — Cierre C-01, C-02, C-03 | Verificados en commit `5ba310b`. 15/15 tests Sales+Inventory ✅. INDICE_MAESTRO honesto. RBAC granular corregido. IGV unificado. HITO 003 → 🟡 Aprobado condicional. 22 hallazgos abiertos restantes. |
| 2026-06-05 | Cierre parcial ADR-009 (Fase 0 + 3 colaterales) | Commit `d7e5fdd` corrige A-01, A-04, A-05, A-07 + A-03, A-09, A-10. 27/27 tests ✅. **Fases 1-4 NO ejecutadas**: Purchases no existe, Finance no existe. Pendientes: A-02, A-06, A-08, A-11. |
| 2026-06-05 | ISSUE-001 cerrado — corrección documental | Commit `9954a8c` corrige `HITO-INTERCALAR-001` sección "Pendientes Fase 1" con IDs reales del ADR-009. Sección ahora coherente con hallazgos A-02/A-06/A-08/A-11. ✅ |
| 2026-06-06 | Cierre Ola A (commit `5b4fd05`) — HITO-INTERCALAR-002 | A-02, A-06, A-08, A-11 ✅ cerrados. M-02 cerrado colateralmente. `LoginRequest` con regex DNI/RUC. `SalePolicy::confirmar` creado. Multi-almacén MVP. `authorize('viewAny')` en todos los `index()`. 33 tests OK. |
| 2026-06-06 | Cierre Ola B (commit `8ddbcdd`) — HITO-INTERCALAR-002 | Módulo Purchases ✅ implementado: 3 modelos, 2 services, 2 controllers, 2 policies, 4 requests, 3 resources, 1 event, 1 listener, 12 rutas, 11 permisos, 3 migraciones. IGV fuente única respetada (reusa `SaleService::calcularLineaIgv`). 21 tests nuevos (13 Compra + 6 Proveedor). +2 hallazgos nuevos (D-06 🟡, D-07 🟢). |
| 2026-06-06 | **HITO-INTERCALAR-002 Ola A+B — 🟢 APROBADO** | 54/54 tests pasan (149 aserciones, 82.6s). 11/11 hallazgos 🟠 altos cerrados. **Ola C (Finance) AUTORIZADA** para inicio inmediato. Reporte: `_auditoria/HITO-INTERCALAR-002/validacion-ola-a-b.md`. |
