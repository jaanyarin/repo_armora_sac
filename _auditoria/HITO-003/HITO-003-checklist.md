# Checklist de Gates — HITO 003 (Sales + Inventory)

**Fecha de re-evaluación:** 2026-06-04  
**Evaluador:** Senior Code & Architecture Quality Auditor  
**Estado:** 🟡 Aprobado con observaciones — 3 críticos corregidos. 11 altos pendientes.

---

## G-ARQ — Arquitectura (Service Layer)

| Criterio | Estado | Evidencia |
|---|---|---|
| Controllers ≤ 10 líneas por método | ✅ | SaleController: 3-8 líneas por método |
| Services con DI readonly | ✅ | Constructor injection con `private readonly` |
| Sin lógica de negocio en Controllers | ✅ | Toda la lógica en SaleService/InventoryService |
| Events/Listeners separados | ✅ | SaleConfirmed → DescontarStock |
| Policies registradas en AppServiceProvider | ✅ | Customer, Product, Sale |
| **Resultado** | ⚠️ | Ver A-08 (Policy sin método confirmar) |

---

## G-RBAC — RBAC

| Criterio | Estado | Evidencia |
|---|---|---|
| Cada endpoint con `auth:sanctum` | ✅ | Grupo completo protegido |
| Cada endpoint con `permission:*` específico | ✅ | `PUT`→`editar-ventas`, `DELETE`→`eliminar-ventas` (C-02 corregido commit `5ba310b`) |
| Cada acción sobre modelo con `$this->authorize()` | ❌ | `index()` no llama a `authorize('viewAny')` — A-11 pendiente |
| Policy con métodos por acción de negocio | ⚠️ | Falta `confirmar()` en SalePolicy — A-08 pendiente |
| Permisos en seeder para todos los roles | ✅ | Vendedor, Admin, Jefe-Almacen. Nuevo `eliminar-ventas` solo Admin/Super-Admin. |
| Permisos en kebab-case | ✅ | `ver-ventas`, `crear-ventas`, etc. |
| **Resultado** | ⚠️ | C-02 corregido ✅. Pendientes A-08 y A-11. |

---

## G-FORM — Form Requests

| Criterio | Estado | Evidencia |
|---|---|---|
| Toda entrada con FormRequest | ✅ | StoreSaleRequest, UpdateSaleRequest |
| `authorize()` delegando | ✅ | `$this->user()->can('crear-ventas')` |
| `rules()` completas | ⚠️ | `exists` sin respetar SoftDeletes |
| `messages()` en español | ✅ | StoreSaleRequest con mensajes ES |
| Uso de `$request->validated()` | ✅ | En store() y update() |
| Reglas `exists` con `whereNull('deleted_at')` | ❌ | Cliente y producto ignoran deleted_at |
| **Resultado** | ⚠️ | A-07 |

---

## G-EVT — Event-Driven

| Criterio | Estado | Evidencia |
|---|---|---|
| Eventos dentro de transacción del service | ✅ | SaleConfirmed dentro de DB::transaction() |
| Listeners no abren transacciones separadas | ❌ | DescontarStock abre su propio DB::transaction |
| Si listener falla, operación se revierte | ❌ | Fallo en listener no revierte venta |
| Eventos en pasado | ✅ | `SaleConfirmed` |
| Listeners atrapan Throwable | ✅ | try/catch + Log::error + rethrow |
| **Resultado** | ❌ | A-04 |

---

## G-TX — Transacciones

| Criterio | Estado | Evidencia |
|---|---|---|
| `DB::transaction` en operaciones multi-tabla | ✅ | SaleService.create/update/confirmar/anular |
| `lockForUpdate` en recursos disputados | ✅ | Stock con lockForUpdate |
| Sin race conditions en generación de códigos | ❌ | generateCode() no atómico |
| Rollback explícito | ✅ | DB::transaction con throw en fallo |
| **Resultado** | ❌ | A-01 |

---

## G-TEST — Tests

| Criterio | Estado | Evidencia |
|---|---|---|
| `User::factory()` (no `User::create()`) | ✅ | ADR-008 aplicado |
| `actingAs($user, 'sanctum')` | ✅ | Helpers por rol |
| Sin credenciales literales | ✅ | ADR-008 |
| Helpers por rol | ✅ | `asVendedor()`, `asAdmin()`, etc. |
| Cubre 200/422/403/404 | ⚠️ | Falta test de 403 en algunos endpoints |
| RefreshDatabase | ✅ | Sí aplica |
| `composer test` → 0 fallos | ✅ | 26/26 tests pasan |
| **Resultado** | ⚠️ | Bueno pero no exhaustivo en autorización |

---

## G-DOC — Documentación

| Criterio | Estado | Evidencia |
|---|---|---|
| Documentos listados existen en disco | ✅ | INDICE_MAESTRO.md reescrito: 15 reales + 20 pendientes (C-01 corregido commit `5ba310b`) |
| ADRs con formato estándar | ✅ | ADR-008 correcto |
| HITO tiene log de cierre | ✅ | HITO-003-sales-inventory.md |
| HITO-AUDIT tiene log de correcciones | ✅ | HITO-AUDIT-001-correcciones-auditor.md |
| AGENTS.md actualizado | ✅ | Refleja estado real |
| **Resultado** | ✅ | C-01 corregido |

---

## G-API — API

| Criterio | Estado | Evidencia |
|---|---|---|
| Endpoints en snake-case | ✅ | `/api/sales`, `/api/nota-credito` |
| Resources con `whenLoaded()` | ✅ | SaleResource, SaleItemResource |
| Códigos HTTP semánticos | ✅ | 201 en store, 200 en show |
| JSON consistente | ⚠️ | `paginate()` devuelve JSON de LengthAwarePaginator |
| **Resultado** | ⚠️ | Sin OpenAPI/Scribe generado |

---

## G-SUNAT — SUNAT

| Criterio | Estado | Evidencia |
|---|---|---|
| IGV fórmula única | ✅ | `calcularLineaIgv()` fuente única en backend + `igvCalculator.ts` en frontend (C-03 corregido commit `5ba310b`) |
| IGV no hardcodeado | ❌ | `0.18` hardcodeado en backend y frontend — M-06 pendiente (usar `brick/money` o constante configurable) |
| Catálogos SUNAT por ID | ✅ | `dim_tipo_afeccion_igv`, `dim_moneda` |
| Migración correctiva aplicada | ✅ | `2026_06_04_170000_recalcular_igv_formula_unica.php` |
| **Resultado** | ⚠️ | C-03 corregido ✅. Pendiente M-06 (hardcodeo de tasa). |

---

## G-OWASP — OWASP

| Criterio | Estado | Evidencia |
|---|---|---|
| Rate limiting en login | ❌ | AuthService sin RateLimiter |
| Tokens con expiración | ❌ | Sanctum por defecto no expira tokens |
| Sin credenciales en URLs | ✅ | Por POST body |
| CORS configurado | ✅ | Sanctum configurado |
| Audit log en acciones críticas | ⚠️ | Solo en modelos, no en acciones |
| **Resultado** | ❌ | A-05 |

---

## G-LOGS — Activitylog

| Criterio | Estado | Evidencia |
|---|---|---|
| Log en modelos transaccionales | ✅ | HasActivityLog en Sale, InventoryMovement |
| Contexto adicional | ❌ | No hay IP, user agent, motivo |
| **Resultado** | ⚠️ | M-04 |

---

## G-PERF — Performance

| Criterio | Estado | Evidencia |
|---|---|---|
| Eager loading con `with()` | ✅ | Sale::with(['cliente', 'items', 'usuario']) |
| Sin N+1 | ✅ | Uso de with() en paginate() y findById() |
| Índices en FKs | ❓ | No revisado en migraciones |
| Paginación server-side | ✅ | LengthAwarePaginator |
| **Resultado** | ⚠️ | Pendiente verificar índices |

---

## G-FE + G-TS — Frontend y TypeScript

No auditado en esta ronda. Pendiente para revisión complementaria.

**Resultado:** ❓

---

## Resumen final (re-auditoría)

| Gates que pasan | Gates que no pasan | Gates con observaciones |
|---|---|---|
| G-ARQ, G-DOC, G-SUNAT | G-EVT, G-TX, G-OWASP | G-RBAC, G-FORM, G-API, G-LOGS, G-PERF, G-TEST |

**Leyenda:** ✅ = corregido desde auditoría inicial  
**Pendientes críticos:** G-EVT (A-04), G-TX (A-01), G-OWASP (A-05)  
**Próximo gate:** HITO-004 (Purchases + Finance)

---

*Checklist HITO 003 — Versión 2.0 (re-auditoría) — 2026-06-04*
