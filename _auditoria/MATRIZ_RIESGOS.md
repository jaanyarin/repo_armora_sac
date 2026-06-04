# Matriz de Riesgos — ARMORA NextGen

**Documento vivo.** Cada hallazgo identificado en gate reviews se registra aquí con severidad, estado, y plan de remediación.  
**Última actualización:** 2026-06-04

---

## Resumen de estado

| Severidad | Abiertos | Cerrados | Total |
|---|---|---|---|
| 🔴 Crítico | 3 | 0 | 3 |
| 🟠 Alto | 11 | 0 | 11 |
| 🟡 Medio | 8 | 0 | 8 |
| 🟢 Bajo | 3 | 0 | 3 |
| **Total** | **25** | **0** | **25** |

---

## 🔴 Críticos

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| C-01 | 003 | Documentación publicitada (35+ docs) no existe en disco. Los directorios `05_especificaciones_tecnicas/` y `07_seguridad_compliance/` están vacíos. | `_docs_implementacion/` | Eliminar referencias falsas del índice o crear los documentos. Verificar que cada archivo listado en `INDICE_MAESTRO.md` exista. | Arquitecto | 🔴 Abierto |
| C-02 | 003 | Permisos RBAC incorrectos: `PUT /sales/{sale}` y `DELETE /sales/{sale}` requieren `permission:ver-ventas` en vez de `editar-ventas` y `eliminar-ventas` respectivamente. | `backend/routes/api.php:65,68` | Corregir los middleware: `PUT → editar-ventas`, `DELETE → eliminar-ventas`. | Arquitecto | 🔴 Abierto |
| C-03 | 003 | Cálculo de IGV inconsistente: `SaleService::create()` calcula IGV como `lineTotal * 0.18` (incorrecto, ~16.27%) mientras `calcularTotales()` usa `lineTotal / 1.18 * 0.18` (correcto). Los valores guardados difieren según el path. | `backend/app/Modules/Sales/Services/SaleService.php:60,174` | Unificar cálculo: sacar a método privado único `calcularLineaIgv(cantidad, precioUnitario)` que use la fórmula correcta `gravada = total / 1.18; igv = gravada * 0.18`. | Arquitecto | 🔴 Abierto |

---

## 🟠 Altos

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| A-01 | 003 | Race condition en `generateCode()`: `withTrashed()->count() + 1` no es atómico. | `SaleService.php:185` | Usar `DB::raw('SELECT nextval(...)')` o secuencia PostgreSQL o UUID para código. | Arquitecto | 🟠 Abierto |
| A-02 | 003 | `dim_almacen` se crea en migración pero `InventoryService` siempre usa `whereNull('almacen_id')`. | `InventoryService.php:19,70` | Decidir: quitar `almacen_id` hasta que haya lógica multi-almacén real o implementar multi-almacén completo. | Arquitecto | 🟠 Abierto |
| A-03 | 003 | `SaleService.update` permite modificar ítems de venta confirmada (solo bloquea anulada/pagada). | `SaleService.php:89` | Agregar `confirmada` al guard: `if (in_array($sale->estado, ['anulada', 'pagada', 'confirmada']))`. | Arquitecto | 🟠 Abierto |
| A-04 | 003 | Listener `DescontarStock` abre su propia transacción; si falla, la venta queda confirmada sin descuento de stock. | `DescontarStock.php:18` vs `SaleService.php:140-144` | Mover el `event(new SaleConfirmed(...))` fuera de la transacción y hacer que el listener ejecute dentro de la misma transacción del service, o usar transacción global que abarque venta + stock. | Arquitecto | 🟠 Abierto |
| A-05 | 003 | `AuthService::login` sin rate limiting. Fuerza bruta ilimitada. | `AuthService.php:22-26` | Agregar `RateLimiter` de Laravel en el controller o en `LoginRequest`, con 5 intentos/minuto y lockout de 15 min (OWASP API2:2023). | Arquitecto | 🟠 Abierto |
| A-06 | 003 | DNI/RUC sin validación de formato: `orWhere('dni', $login)` acepta cualquier string. | `AuthService.php:17-18` | Agregar expresión regular en `LoginRequest`: DNI = `/^\d{8}$/`, RUC = `/^\d{11}$/`. | Arquitecto | 🟠 Abierto |
| A-07 | 003 | `exists:customers,id` y `exists:products,id` en StoreSaleRequest no validan SoftDeletes (clientes/productos eliminados son válidos). | `StoreSaleRequest.php:18,29` | Reemplazar `exists:customers,id` por `exists:customers,id,deleted_at,NULL` (igual para products). | Arquitecto | 🟠 Abierto |
| A-08 | 003 | `SalePolicy` mezcla permisos: método `update` usado para `confirmar` en vez de tener método `confirmar` dedicado. | `SaleController.php:53` + `SalePolicy.php:25-31` | Agregar método `confirmar(User $user, Sale $sale): bool` en `SalePolicy` y llamar `authorize('confirmar', $sale)` desde el controller. | Arquitecto | 🟠 Abierto |
| A-09 | 003 | `anular` no actualiza `saldo_pendiente` a 0 cuando la venta tiene pagos parciales. | `SaleService.php:147-162` | En `anular`, si `saldo_pendiente > 0`, ponerlo a 0. | Arquitecto | 🟠 Abierto |
| A-10 | 003 | `update` no recalcula `saldo_pendiente` cuando cambia el total. | `SaleService.php:86-132` | Recalcular `saldo_pendiente` como `total - sum(pagos)` cuando exista módulo de pagos. Por ahora setear `saldo_pendiente = total - descuentos`. | Arquitecto | 🟠 Abierto |
| A-11 | 003 | `SaleController::index` no llama a `authorize('viewAny', Sale::class)`. | `SaleController.php:25-31` | Agregar `$this->authorize('viewAny', Sale::class)` al inicio de `index()`. | Arquitecto | 🟠 Abierto |

---

## 🟡 Medios

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| M-01 | 003 | `currentAccessToken()->delete()` falla si no hay token (comandos/jobs). | `AuthService.php:42` | Validar `if ($user->currentAccessToken())` antes de eliminar. | Arquitecto | 🟡 Abierto |
| M-02 | 003 | `LoginRequest` no valida largo de DNI (8) ni RUC (11). | `LoginRequest.php` | Agregar validación por regex después de determinar el tipo de documento por la longitud del input. | Arquitecto | 🟡 Abierto |
| M-03 | 003 | `customer.numero_documento` sin UNIQUE constraint compuesto por `tipo_documento`. | Migración customers | Agregar índice único compuesto `(tipo_documento_id, numero_documento)`. | Arquitecto | 🟡 Abierto |
| M-04 | 003 | `LogsActivity` instalado pero sin contexto adicional (IP, user agent, motivo). | Modelos con `LogsActivity` | Configurar `$logAttributes` para incluir contexto de request en acciones críticas. | Arquitecto | 🟡 Abierto |
| M-05 | 003 | Falta autogeneración de documentación API (Scribe/Scramble). | Perfil v3 sección 6 | Instalar `scramble` y documentar endpoints. | Arquitecto | 🟡 Abierto |
| M-06 | 003 | Tipos monetarios como `float` en PHP en vez de `decimal` o `brick/math`. | `SaleService.php` líneas de cálculo | Usar `brick/money` o `decimal` para precisión fiscal. | Arquitecto | 🟡 Abierto |
| M-07 | 003 | `update` no protege cambios de `cliente_id` cuando la venta ya fue emitida. | `SaleService.php:123` | Si estado es confirmada o pagada, no permitir cambiar `cliente_id`. | Arquitecto | 🟡 Abierto |
| M-08 | 003 | `confirmar` no verifica stock disponible antes de cambiar estado. | `SaleService.php:134-145` | Agregar validación de stock en `confirmar()` antes de disparar el evento. | Arquitecto | 🟡 Abierto |

---

## 🟢 Bajos

| ID | HITO | Hallazgo | Archivo | Remedio propuesto | Responsable | Estado |
|---|---|---|---|---|---|---|
| B-01 | 003 | DELETE HTTP hace soft delete; no se distingue papelera vs eliminación. | `SaleService.php:166` | Si se necesita purge, agregar endpoint `DELETE /sales/{sale}/force` con permiso específico. | Arquitecto | 🟢 Abierto |
| B-02 | 003 | `customer_id` no se valida contra `customers.activo`. | `StoreSaleRequest.php:18` | Agregar condición `where('activo', true)` a la regla exists. | Arquitecto | 🟢 Abierto |
| B-03 | 003 | Si un cliente se soft-deletea, las ventas existentes pierden nombre legible (no hay `withTrashed` en la relación). | Modelo Sale relación `cliente()` | Usar `->withTrashed()` en la relación para que las ventas existentes sigan mostrando datos del cliente. | Arquitecto | 🟢 Abierto |

---

## Historial de cambios

| Fecha | Acción | Detalle |
|---|---|---|
| 2026-06-04 | Creación inicial | 25 hallazgos registrados de HITO 003 (3C + 11A + 8M + 3B) |
