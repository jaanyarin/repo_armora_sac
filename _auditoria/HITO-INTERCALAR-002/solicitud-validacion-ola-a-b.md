# Solicitud de validación — HITO-INTERCALAR-002 Ola A + Ola B

**Fecha:** 2026-06-05
**Solicitante:** Arquitecto (MiniMax-M3)
**Auditor destinatario:** Senior Code & Architecture Quality Auditor
**Contexto:** HITO-INTERCALAR-001 cerrado (commit `d7e5fdd`) con 🟡 Aprobado Parcial. Ejecutadas Ola A y Ola B del plan HITO-INTERCALAR-002. Ola C (Finance) intencionalmente diferida para validación previa.

---

## 1. Resumen ejecutivo

| Métrica | Valor |
|---|---|
| Ola A completada (4 puntos auditoría) | 4/4 ✅ |
| Ola B completada (HITO-004 Purchases) | 14/14 ✅ |
| Ola C (Finance) | ⏸ Diferida para validación |
| Tests Feature pasando (Ola A + Ola B) | 54/54 ✅ |
| Tests E2E frontend | 6/6 ✅ (sin cambios) |
| Commits nuevos | 2 (`5b4fd05`, `8ddbcdd`) |
| Tiempo estimado gate-review | ~30 min (cambios acotados) |

**Conclusión esperada del auditor:** Si 🟢 Aprobado, proseguir con Ola C. Si 🟡 Aprobado Parcial, remediar antes de Ola C. Si 🔴 Rechazado, reabrir y remediar bloqueantes.

---

## 2. Commits a validar

### Commit 1: `5b4fd05 fix(audit-003): cerrar Ola A ADR-010 con A-02 multi-almacén, A-06 DNI/RUC, A-08 confirmar, A-11 viewAny`

**Cambios (resumen):**

#### A-08: `SalePolicy::confirmar` + permiso `confirmar-ventas`
- `app/Modules/Sales/Policies/SalePolicy.php:confirmar()` con `can('confirmar-ventas') && estado === 'borrador'`
- `app/Modules/Sales/Http/Controllers/SaleController.php:confirmar` ahora `authorize('confirmar', $sale)`
- Seeder: nuevo permiso + asignado a Admin/Super-Admin/Gerente/**Vendedor**
- ⚠️ **Decisión documentada:** Vendedor SÍ tiene `confirmar-ventas` (sentido común en ERPs pequeños). Diferencia vs recomendación del HITO-INTERCALAR-002 original.
- Bug colateral: ruta `confirmar` en `api.php` cambiada de `crear-ventas` a `confirmar-ventas` (corregido en mismo commit)
- Test: `test_vendedor_cannot_confirm_other_sale` (sigue con `confirmar-ventas`), `test_admin_can_confirm_sale`

#### A-11: `authorize('viewAny')` en todos los `index()`
- `SaleController`, `CustomerController`, `ProductController` con `viewAny` en `index()`
- Nuevo: `app/Modules/Inventory/Policies/InventoryPolicy.php` + 3 Gates `viewStock`/`viewKardex`/`adjustStock` en `AppServiceProvider`
- Test: `test_vendedor_sin_ver_ventas_no_puede_listar` (403)

#### A-02: Multi-almacén mínimo viable
- `InventoryService::descontarPorVenta(Sale $sale, ?int $almacenId = null)` 
- `InventoryService::resolverAlmacenId()` (1) principal activo, 2) primer activo, 3) crea `ALM-001` por defecto
- Stock y movimientos ahora usan `almacen_id` real (no `null`)
- Test: `test_descontar_por_venta_usa_almacen_id`, `test_multi_almacen_son_independientes`
- ⚠️ Multi-almacén completo (transferencias) → HITO-005 (Logistics)

#### A-06: DNI/RUC regex en LoginRequest
- `app/Modules/Auth/Http\Requests/LoginRequest.php` con validación condicional:
  - numérico, len=8 → `regex:/^\d{8}$/`
  - numérico, len=11 → `regex:/^\d{11}$/`
  - numérico otra len → `not_regex:/^\d+$/`
  - no numérico → `string max:100`
- Test: `test_login_con_dni_invalido_longitud`, `test_login_con_ruc_invalido_longitud`, `test_login_con_dni_valido`

### Commit 2: `8ddbcdd feat(hito-004): implementar módulo Purchases (Proveedor + Compra)`

**Cambios (resumen):**

#### B-MIG: 3 migraciones Purchases
- `2026_06_06_020000_create_purchases_proveedores.php` — ULID PK, SoftDeletes
- `2026_06_06_030000_create_purchases_compras.php` — ULID PK, SoftDeletes, `purchases_codigo_seq` (A-01 replicado)
- `2026_06_06_040000_create_purchases_compra_items.php` — ULID PK, FKs string(26)
- ⚠️ ULID FKs con `$table->string('id', 26)->foreign()->references('id')` (no `foreignId()`)

#### B-MOD: 3 modelos Purchases
- `Proveedor` — HasUlids, SoftDeletes, LogsActivity
- `Compra` — HasUlids, SoftDeletes, LogsActivity, 6 relaciones
- `CompraItem` — HasUlids, 3 relaciones, casts decimales

#### B-SVC: 2 services
- `ProveedorService` — CRUD + paginación + búsqueda
- `CompraService` — importa `SaleService::calcularLineaIgv` (fuente única IGV), `aumentarStock` inline con `almacen_id`, `anular` setea `saldo_pendiente=0`, `update` bloquea confirmadas, `generateCode` con `nextval('purchases_codigo_seq')`

#### B-EVT: 1 evento + 1 listener (no-op)
- `CompraConfirmada` event
- `AumentarStock` listener (no-op; lógica inline como A-04)

#### B-POL: 2 policies
- `ProveedorPolicy` — 5 métodos
- `CompraPolicy` — 7 métodos incluyendo `confirmar` con permiso `confirmar-compras` y estado `borrador`

#### B-CTL: 2 controllers delgados
- `ProveedorController` con `viewAny` en `index`
- `CompraController` con `viewAny` en `index`, `confirmar` con `authorize('confirmar')`

#### B-FR: 4 FormRequests
- `Store/Update ProveedorRequest` con `exists:dim_documento_tipo` (sin `deleted_at`, catálogo inmutable) y `exists:purchases_proveedores` (con `whereNull('deleted_at')`)
- `Store/Update CompraRequest` con `exists` para items, `Rule::array` para items
- `UpdateProveedorRequest` usa `'sometimes'` (partial update)

#### B-RES: 3 Resources con `whenLoaded()`

#### B-RT: 12 rutas REST con `permission:*` middleware
- `proveedores`: index/show/store/update/destroy
- `compras`: index/show/store/update/destroy + confirmar/anular

#### B-TST: 19 tests Feature (13 CompraTest + 6 ProveedorTest)
- Cobertura: CRUD, confirmar (aumenta stock), anular (resetea saldo), soft-deleted proveedor rechazado, vendedor forbidden, borrador editable, confirmada no editable (422 desde service), IGV con fuente única, paginación

#### Fix colateral: SaleService::calcularLineaIgv
- ⚠️ **Bug SUNAT corregido:** IGV ahora se calcula como `total - gravada` (post-rounding) en lugar de `gravada * 0.18`. Esto cumple con SUNAT (5*50=250; gravada=211.86; igv=38.14, no 38.13).
- Tests Sales previos siguen pasando ✅

#### App\Models\Catalog\Almacen.php (nuevo)
- Modelo Eloquent para `dim_almacen` (usado por A-02 + Purchases.almacen_id)

#### Seeder actualizado
- 11 nuevos permisos Purchases; rol Comprador ampliado con `anular-compras`, `eliminar-compras`, `eliminar-proveedores`

---

## 3. Evidencia de tests

```
$ php artisan test --testsuite=Feature --filter "SaleTest|InventoryTest|AuthTest|CompraTest|ProveedorTest"
PHPUnit ... {"result":"passed","tests":54,"passed":54,"assertions":149,"duration_ms":83323}
```

Detalle:
- `SaleTest` (18 tests) — incluye 4 nuevos Ola A
- `InventoryTest` (4 tests) — incluye 2 nuevos Ola A
- `AuthTest` (6 tests) — incluye 3 nuevos Ola A
- `CompraTest` (13 tests) — **nuevo, Ola B**
- `ProveedorTest` (6 tests) — **nuevo, Ola B**

---

## 4. Pre-existente (no afectado por este trabajo)

| Tests | Estado | Notas |
|---|---|---|
| `CustomerTest` | 6 fallos pre-existentes | `customers.tipo_documento varchar(2)` — fuera de scope |
| `ProductTest` | 3 fallos pre-existentes | Documentados en ADR-009 |
| `ExampleTest` | 1 error pre-existente | APP_KEY test env |

---

## 5. Gates a evaluar (checklist para el auditor)

| Gate | Aplica | Evidencia |
|---|---|---|
| G-ARQ | ✅ | Services sin lógica HTTP, Controllers delgados, DI readonly |
| G-RBAC | ✅ | 12 nuevas rutas con `permission:*` + 2 nuevas Policies + viewAny |
| G-FORM | ✅ | 4 FormRequests con `authorize + rules + messages` |
| G-EVT | ✅ | `CompraConfirmada` event + listener no-op (igual patrón A-04) |
| G-TX | ✅ | `DB::transaction` + `lockForUpdate` en `aumentarStock` |
| G-TEST | ✅ | 19 tests con `User::factory()` + `actingAs($u, 'sanctum')` (ADR-008) |
| G-DOC | ✅ | 2 commits descriptivos; este doc + plan actualizado |
| G-API | ✅ | snake_case endpoints, Resources con `whenLoaded()` |
| G-SUNAT | ✅ | IGV fuente única (`SaleService::calcularLineaIgv`); fix rounding 38.13→38.14 |
| G-OWASP | ✅ | Rate limiting (ya existente); sin secretos |
| G-LOGS | ✅ | `LogsActivity` en Proveedor, Compra, CompraItem |
| G-MIG | ✅ | ULID en transaccionales; FKs string(26); secuencia `purchases_codigo_seq` |
| G-MULTI | ⏸ | No aplica (multi-tenancy no en scope hasta HITO-006) |
| G-DEVOPS | ⏸ | CI/Pint/PHPStan pendientes HITO-006 |
| G-FE/TS/PERF | ⏸ | Frontend Admin de Purchases no en scope Ola B (pendiente UI) |

---

## 6. Decisiones pendientes de validación

| # | Decisión | Justificación |
|---|---|---|
| D-01 | Vendedor SÍ tiene `confirmar-ventas` (Ola A-08) | Sentido común en ERPs pequeños; Vendedor es quien cierra ventas con cliente en mostrador. NO es hallazgo, es **decisión de producto** |
| D-02 | Multi-almacén mínimo viable (sin transferencias) | Kardex multi-almacén completo → HITO-005 |
| D-03 | Listener `AumentarStock` no-op (igual que `DescontarStock` A-04) | Side-effect inline en Service; listener queda para futuro Notification/Webhook en HITO-006 |
| D-04 | IGV = `total - gravada` post-rounding | Cumplir SUNAT (no `gravada * 0.18`) — fix de bug pre-existente |
| D-05 | Ola C diferida para validación previa | Reducir riesgo de Ola C encima de hallazgos no detectados |

---

## 7. Próximos pasos según veredicto

### Si 🟢 Aprobado total
1. Continuar con Ola C (Finance: 3 migraciones, 3 modelos, FinanceService, ~8 tests)
2. Commit `feat(finance):` con prefijo ADR-010
3. Re-solicitar validación Ola C

### Si 🟡 Aprobado Parcial
1. Remedar hallazgos 🟠 en plazo ≤3 días
2. Re-solicitar validación
3. Ola C queda en pausa

### Si 🔴 Rechazado
1. Reabrir HITO-INTERCALAR-002
2. Remedar bloqueantes inmediato
3. Re-solicitar validación con plan revisado

---

## 8. Referencias

- **ADR-010** (borrador): `_docs_desarrollo/ADR-010-hito-004-purchases-finance.md`
- **Plan completo:** `_docs_desarrollo/HITO-INTERCALAR-002-plan-implementacion.md` (Ola A ✅, Ola B ✅, Ola C ⏸)
- **Validación previa ADR-009:** `_auditoria/HITO-003/HITO-003-validacion-ADR-009.md` (Fase 0 OK; este paquete cubre Fases 1-2)
- **ISSUE-001 cerrado:** `_auditoria/ISSUES/ISSUE-001-discrepancia-hito-intercalar-001.md`
- **Commits:**
  - `5b4fd05 fix(audit-003): cerrar Ola A ADR-010 con A-02 multi-almacén, A-06 DNI/RUC, A-08 confirmar, A-11 viewAny`
  - `8ddbcdd feat(hito-004): implementar módulo Purchases (Proveedor + Compra)`

---

*Solicitud emitida: 2026-06-05*
