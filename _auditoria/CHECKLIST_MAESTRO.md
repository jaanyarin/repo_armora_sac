# Checklist Maestro de Gates — ARMORA NextGen

**Documento vivo.** Por cada HITO, se evalúa cada gate aplicable y se marca como ✅ (pasa), ⚠️ (pasa con observaciones), ❌ (no pasa).  
**Última actualización:** 2026-06-04

---

## Leyenda

| Símbolo | Significado |
|---|---|
| ✅ | Pasa el gate sin observaciones |
| ⚠️ | Pasa con observaciones (ver documento de hallazgos) |
| ❌ | No pasa (uno o más hallazgos críticos/altos en esta categoría) |
| ➖ | No aplica al HITO actual |
| ❓ | No evaluado aún |

---

## Resumen por HITO

| Gate | HITO-001 | HITO-002 | HITO-003 | HITO-004 | HITO-005 | HITO-006 |
|---|---|---|---|---|---|---|
| G-ARQ | ❓ | ❓ | ⚠️ | ➖ | ➖ | ➖ |
| G-RBAC | ❓ | ❓ | ❌ | ➖ | ➖ | ➖ |
| G-FORM | ❓ | ❓ | ⚠️ | ➖ | ➖ | ➖ |
| G-EVT | ➖ | ➖ | ❌ | ➖ | ➖ | ➖ |
| G-TX | ➖ | ➖ | ❌ | ➖ | ➖ | ➖ |
| G-TEST | ❓ | ❓ | ⚠️ | ➖ | ➖ | ➖ |
| G-DOC | ❓ | ❓ | ❌ | ➖ | ➖ | ➖ |
| G-API | ❓ | ❓ | ⚠️ | ➖ | ➖ | ➖ |
| G-SUNAT | ➖ | ➖ | ❌ | ➖ | ➖ | ➖ |
| G-OWASP | ❓ | ❓ | ❌ | ➖ | ➖ | ➖ |
| G-LOGS | ➖ | ➖ | ⚠️ | ➖ | ➖ | ➖ |
| G-FE | ❓ | ❓ | ❓ | ➖ | ➖ | ➖ |
| G-TS | ❓ | ❓ | ❓ | ➖ | ➖ | ➖ |
| G-PERF | ❓ | ❓ | ⚠️ | ➖ | ➖ | ➖ |
| G-MIG | ❓ | ❓ | ⚠️ | ➖ | ➖ | ➖ |
| G-DEVOPS | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ |
| G-MULTI | ➖ | ➖ | ➖ | ➖ | ➖ | ➖ |

---

## Criterios detallados por gate

### G-ARQ — Arquitectura (Service Layer)

- [ ] Controllers ≤ 10 líneas por método (solo validación, delegación, respuesta)
- [ ] Services con constructor injection `private readonly`
- [ ] Sin lógica de negocio en Controllers (excepción: request->validated())
- [ ] Events/Listeners separados por módulo
- [ ] Policies registradas en `AppServiceProvider::boot()`

### G-RBAC — RBAC

- [ ] Cada endpoint con `auth:sanctum` en el grupo
- [ ] Cada endpoint con `permission:*` middleware específico
- [ ] Cada acción sobre modelo con `$this->authorize()` en el Controller
- [ ] Policy con métodos para cada acción de negocio (create, view, update, delete, anular, confirmar, etc.)
- [ ] Permisos en `RoleAndPermissionSeeder` para todos los roles
- [ ] Permisos con naming kebab-case consistente

### G-FORM — Form Requests

- [ ] Toda entrada de datos con `FormRequest` (sin `$request->validate()` inline)
- [ ] `authorize()` delegando en Policy o `$user->can()`
- [ ] `rules()` con validaciones completas (requeridos, tipos, existencias, formato)
- [ ] `messages()` en español
- [ ] Uso de `$request->validated()` en Controller (no `$request->all()`)
- [ ] Reglas `exists` que respetan SoftDeletes donde aplique (`->whereNull('deleted_at')`)

### G-EVT — Event-Driven

- [ ] Eventos disparados DENTRO de la transacción que les da coherencia
- [ ] Listeners no abren transacciones separadas (todo en la misma transacción del service)
- [ ] Si el listener falla, la operación completa se revierte
- [ ] Eventos nombrados en pasado (`SaleConfirmed`, `ProductCreated`)
- [ ] Listeners atrapan `\Throwable` y loguean

### G-TX — Transacciones

- [ ] `DB::transaction` en toda operación que modifica múltiples tablas
- [ ] `lockForUpdate` en recursos disputados (stock, saldos)
- [ ] Sin race conditions en generación de códigos/secuencias
- [ ] Rollback explícito o excepción si falla una operación intermedia

### G-TEST — Tests

- [ ] Todos los tests Feature usan `User::factory()` (no `User::create()`)
- [ ] Autenticación con `actingAs($user, 'sanctum')` o `Sanctum::actingAs($user)`
- [ ] Sin credenciales literales (passwords, tokens) en código de tests (ADR-008)
- [ ] Helpers por rol (`asVendedor()`, `asAdmin()`)
- [ ] Cubre 200 (éxito), 422 (validación), 403 (no autorizado), 404 (no encontrado)
- [ ] `RefreshDatabase` o `DatabaseTransactions` para aislamiento
- [ ] `composer test` → 0 fallos

### G-DOC — Documentación

- [ ] Cada documento listado en índices existe como archivo real en disco
- [ ] ADRs tienen formato: Contexto → Decisión → Consecuencia
- [ ] HITO tiene log de cierre con métricas
- [ ] `AGENTS.md` actualizado con estado real
- [ ] `_docs_implementacion/` refleja lo que realmente hay

### G-API — API

- [ ] Endpoints en snake-case (`/api/sales`, `/api/nota-credito`)
- [ ] Resources con `whenLoaded()` para relaciones
- [ ] Códigos HTTP semánticos (200, 201, 204, 400, 422, 403, 404)
- [ ] JSON consistente (data wrapper o paginación estándar de Laravel)

### G-SUNAT — SUNAT

- [ ] IGV calculado con fórmula única en todo el código
- [ ] Valores de IGV/ISC no hardcodeados (usar constantes o config)
- [ ] Catálogos SUNAT referenciados por ID, no por nombre
- [ ] XML UBL 2.1 generado con Greenter (cuando aplique)
- [ ] CDR procesado y almacenado

### G-OWASP — OWASP Top 10:2025

- [ ] Rate limiting en login (≤5 intentos/min, lockout 15 min)
- [ ] Tokens con expiración configurada (Sanctum)
- [ ] Sin credenciales en URLs
- [ ] CORS configurado restrictivamente
- [ ] Headers de seguridad (X-Frame-Options, CSP, X-Content-Type-Options)
- [ ] Audit log en acciones críticas (login, anular, NC, cambio de estado)
- [ ] Sin secrets en código ni en repo

### G-LOGS — Activitylog

- [ ] `spatie/laravel-activitylog` activo en modelos transaccionales
- [ ] Contexto adicional en logs (motivo, IP, user agent, referencia)
- [ ] Log en eventos: crear, actualizar, anular, emitir NC

### G-FE — Frontend

- [ ] MUI imports tree-shakeables (`import Button from '@mui/material/Button'`)
- [ ] TanStack Query con key factories
- [ ] Zod schemas sincronizados con validaciones backend
- [ ] Sin `any` en TypeScript
- [ ] `sx` o `styled` (no CSS modules)
- [ ] UI en español

### G-TS — TypeScript

- [ ] Interfaces en snake_case (reflejan Resources backend)
- [ ] Tipos con readonly donde corresponda
- [ ] Sin `as any` ni `@ts-ignore`
- [ ] strict mode en tsconfig

### G-PERF — Performance

- [ ] Eager loading con `with()` en listados
- [ ] Sin N+1 queries detectables
- [ ] Índices en todas las FK
- [ ] Paginación server-side en listados
- [ ] `staleTime` configurado en TanStack Query (≥5 min para catálogos)

### G-MIG — Migraciones

- [ ] ULID en PKs de tablas transaccionales
- [ ] FKs con `->constrained()` (nombres consistentes)
- [ ] SoftDeletes donde aplique (clientes, productos, ventas, NC)
- [ ] Indices en columnas de búsqueda frecuente
- [ ] SQL raw embebido solo para datos semilla (catálogos SUNAT)

### G-DEVOPS (HITO 006+)

- [ ] GitHub Actions: lint + test + build
- [ ] Pint corriendo sin errores
- [ ] PHPStan level max o el definido
- [ ] Sentry/Pulse configurados

### G-MULTI (cuando llegue)

- [ ] `tenant_id` en toda tabla de tenant
- [ ] Global scope aplicado a modelos de tenant
- [ ] Middleware de identificación de tenant
- [ ] Sin cross-tenant data leakage

---

*Checklist Maestro — Versión 1.0 — 2026-06-04*
