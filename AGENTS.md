# ARMORA SAC — AGENTS.md

## Stack real

| Capa | Tecnología | Docs arquitecto |
|---|---|---|
| Backend | Laravel 13 + PHP 8.3 | Laravel 12 (desfase documental, no técnico) |
| Frontend Admin | React 19 + TypeScript 6 + MUI 7 + React Router 7 | MUI 6 (desfase documental) |
| Estado cliente | Zustand 5 | Context API / Zustand |
| Estado servidor | TanStack Query 5 | TanStack Query |
| Forms | React Hook Form + Zod | React Hook Form + Zod |
| BD | PostgreSQL 16 + Redis 7 | PostgreSQL 16 + Redis 7 |
| Auth | Sanctum + Spatie Permission RBAC | Sanctum + Spatie RBAC |
| API | REST + Laravel API Resources | REST + API Resources |
| Migraciones | Laravel Migrations nativas + SQL crudo embebido | Laravel Migrations |
| Testing | PHPUnit (16 tests) | PHPUnit / Pest |
| Infra dev | Docker Compose (Postgres :5434, Redis :6379) | Docker + Laravel Sail |

## Estado actual del proyecto

**Hito 003 cerrado + Fase 0 auditoría cerrada + Hito 004 Purchases (Ola A+B) cerrado + Hito 004a Company Settings cerrado + Hito 007 Personal cerrado + iteración feedback Hito 007 aplicada + Gestión Personal + Reportes Personal + Fixes UX edición (estado legible, persistencia permisos/listas/almacenes, select all por grupo) + Hito 005 frontend PurchaseFormPage implementado** — Sales + Inventory + **Purchases** completo (backend + frontend "Crear Compra") + **Company** (backend + frontend) + **Personal** (backend + frontend CRUD completo + iteración ficha + reportes + 4 fixes UX edición) + 11 fixes auditoría (A-01 a A-11) + 14 fixes Ola B + 4 fixes Personal + 3 migraciones + 6 cambios de feedback + 5 tests reportes + 2 tests resource IDs.
Módulos funcionales: Auth, Catalog, Customers, Products, **Sales, Inventory, Purchases, Company, Personal**.
Portal Cliente: catálogo público + login + dashboard + **pedidos (carrito + checkout + historial)** con navegación responsive.

**Próximo:** Hito 005 PurchaseListPage (DataGrid) + vista detalle + Hito 005 Logistics (Mapa de Rutas) ⏸.

### Arquitectura backend

```
app/Modules/{Auth,Catalog,Customers,Products,Sales,Inventory,Purchases,Company}/
├── Http/
│   ├── Controllers/     ← Delgados, delegan en Services
│   ├── Requests/        ← FormRequests con validación + mensajes español
│   └── Resources/       ← API Resources con whenLoaded()
├── Services/            ← Lógica de negocio (no HTTP acoplado)
├── Events/              ← SaleConfirmed (Hito 003), CompraConfirmada (Hito 004 Purchases)
├── Listeners/           ← DescontarStock, AumentarStock (Hito 004 Purchases, no-op)
├── Policies/            ← CustomerPolicy, ProductPolicy, SalePolicy, ProveedorPolicy, CompraPolicy, InventoryPolicy
└── Models/              ← Eloquent + ULIDs + SoftDeletes + LogsActivity
```

Principios aplicados (alineados con perfil arquitecto):
- **Service Layer**: toda lógica de negocio en Services, nunca en Controllers ✅
- **Constructor injection** con propiedades readonly tipadas ✅
- **Form Requests** para validación con mensajes en español ✅
- **API Resources** con `whenLoaded()` para relaciones ✅
- **Soft Deletes** en Customer, Product, Sale, CreditNote ✅
- **Catálogos SUNAT** completos (24 tablas dim_*, 458 líneas SQL) ✅
- **RBAC granular**: 39 permisos, 11 roles, middleware `permission:*` por ruta CRUD ✅
- **IGV fuente única** backend: `SaleService::calcularLineaIgv()` (importado por `CompraService`) ✅
- **Login multi-campo**: username, email, DNI o RUC (ADR-004) ✅
- **Validación DNI/RUC**: regex 8/11 dígitos en `LoginRequest` (A-06) ✅
- **Multi-almacén mínimo viable**: 1 almacén por defecto, `almacen_id` real en stock y movimientos (A-02) ✅
- **Paginación server-side** en Customers, Products, Sales, **Compras, Proveedores** (DataGrid MUI + ResourceCollection) ✅
- **Event-Driven**: `SaleConfirmed` → `DescontarStock` (Hito 003) ✅
- **Policies de Laravel**: `SalePolicy`, `CustomerPolicy`, `ProductPolicy` (Hito 003) ✅
- **ULID** en PKs transaccionales: `sales_*`, `inventory_movimientos` (Hito 003) ✅
- **Prefijos de tabla** por dominio: `sales_`, `inventory_` (Hito 003) ✅
- **spatie/laravel-activitylog**: instalado, 3 migraciones ejecutadas (Hito 003) ✅
- **Storage imágenes empresa**: `Storage::disk('public')` → `storage/app/public/empresa/` (Hito 004a) ✅
- **Bloqueo ventas/compras**: flag cross-module en EmpresaConfig + checks en SaleService/CompraService (Hito 004a) ✅
- **Extender `users` con columnas nullable** para Personal (Hito 007) ✅
- **`Storage::disk('public')` para fotos de personal** en `storage/app/public/personal/{userId}/` (Hito 007) ✅
- **`$guard_name = 'web'` en modelo User** para que `syncRoles` use el guard correcto (fix Hito 007 reabre fix Hito 003) ✅
- **Date casts** para `password_changed_at` y `fecha_nacimiento` (fix Hito 007) ✅
- **useForm/useQuery tipados con genéricos** en `env.d.ts` (mejora Hito 007 que también arregla 2 errores pre-existentes) ✅
- **CRUD Personal completo backend** + frontend "Crear Personal" wizard 6 pasos (Hito 007) ✅
- **Iteración feedback Personal**: step 1 sin `nombre_completo` (computado) + eye toggle passwords; step 2 con `Autocomplete` `dim_documento_identidad` (DNI/CE/Pasaporte/RUC) + único `numero_documento`; step 3 roles **single-select** (radio list); `dim_almacen` sembrado con "Almacén Principal" ✅
- **`dim_documento_identidad`** nueva tabla catálogo (reemplaza uso incorrecto de `dim_documento_tipo` comprobantes para identidad) con `codigo`, `nombre`, `longitud`, `regex` PCRE-delimitado (DNI 8d / CE alphanumeric 12c / PAS 20c / RUC 11d) ✅
- **Regex dinámica + maxLength** en `numero_documento` según `documento_identidad_id` seleccionado (helper `resolveRegex/resolveMaxLength` en FormRequests) ✅
- **`roles.max:1`** validación server-side que rechaza 2+ roles en payload + UI single-select con radio buttons (Hito 007 iter) ✅
- **`seed_default_almacen`** con `insertOrIgnore` idempotente (soporta `RefreshDatabase` en tests + no rompe InventoryTest) ✅

Principios PENDIENTES (hoja de ruta del perfil arquitecto):
- Repository Pattern (opcional, para consultas complejas) ❌
- Multi-tenancy (`empresa_id`) ❌
- Job asíncrono `SendInvoiceToSunat` con Redis Queue ❌
- Greenter 5.x + envío SOAP a SUNAT ❌
- WebPush / Laravel Broadcasting ❌

### Arquitectura frontend

```
frontend/src/
├── shared/              ← Código compartido Admin + Portal
│   ├── api/             ← Axios client + interceptors + endpoints (authApi, catalogApi, customersApi, productsApi, salesApi, inventoryApi, empresaApi)
│   ├── hooks/           ← useAuth (Zustand), useCart (Zustand persist) — Hito 003
│   ├── types/           ← 233 → 320+ líneas, interfaces snake_case
│   ├── components/      ← ProtectedRoute, LoginPage, NotFoundPage
│   └── theme.ts         ← MUI theme Admin
├── Admin/               ← SPA Admin ERP (desktop-primary)
│   ├── layouts/         ← AdminLayout (Drawer + AppBar)
│   ├── pages/           ← Dashboard + Customers + Products + Sales (lista + formulario) — Hito 003
│   └── components/      ← Sidebar (acordeón: 1 sección abierta a la vez + auto-open por ruta activa + búsqueda con filtro en vivo)
└── Portal/              ← SPA Portal Cliente (mobile-first)
    ├── layouts/         ← PortalLayout (AppBar + BottomNav + Footer)
    ├── pages/           ← ProductCatalog + PortalDashboard + PortalLogin + OrderCreate + OrderHistory — Hito 003
    └── components/      ← (vacíos por ahora)
```

Principios aplicados:
- **Dual-Frontend**: Admin y Portal en el mismo bundle, código compartido en `shared/` ✅
- **Lazy loading** de rutas Admin y Portal ✅
- **Proxy Vite** `/api` → `http://localhost:8005` ✅
- **Interceptor 401** → logout automático en client.ts ✅
- **Cart persistente** en localStorage con Zustand `persist` middleware (Hito 003) ✅
- **IGV calculation client-side** (18% sobre subtotal gravada, idéntico al backend) (Hito 003) ✅
- **Server-side DataGrid** en Ventas con paginación, búsqueda y filtros (Hito 003) ✅

Brechas vs perfil arquitecto:
- Inventario Admin: solo placeholder ("Próximamente") ⚠️
- WebPush / Laravel Broadcasting no configurado ❌

## Comandos clave

### Frontend (`frontend/`)
| Comando | Descripción |
|---|---|
| `npm run dev` | Servidor Vite en puerto **5175** |
| `npm run build` | `tsc -b && vite build` |
| `npm run lint` | ESLint |
| `npm run test` | Vitest (9 tests: useAuth + ProtectedRoute + Sidebar) |
| `npm run test:watch` | Vitest en modo watch |
| `npm run test:e2e` | Playwright E2E (8 tests: portal + auth + navegación) |

### Backend (`backend/`)
| Comando | Descripción |
|---|---|
| `composer dev` | artisan serve (:8005) + queue + pail + Vite concurrente |
| `composer test` | Crea BD `armora_erp_test` + `php artisan config:clear` + PHPUnit |
| `composer setup` | Instalación inicial (composer install, .env, key:generate, migrate, npm build) |
| `php artisan migrate` | Ejecuta migraciones |
| `php artisan serve` | Servidor dev puerto 8000 (usar `--port=8005`) |
| `php artisan db:seed` | Siembra roles (11), permisos (28), usuarios admin/vendedor |

### Auditoría (`_auditoria/`)
| Comando | Descripción |
|---|---|
| `.\_auditoria\auditar.ps1` | Layer 1: chequeos mecánicos (build + lint + tests FE/BE) |
| `.\_auditoria\auditar.ps1 -Quick` | Solo build + lint, sin tests |
| `.\_auditoria\auditar.ps1 -Frontend` | Solo frontend (build + lint + test) |
| `.\_auditoria\auditar.ps1 -Backend` | Solo backend (test) |

## Base de datos

PostgreSQL 16, esquema dimensional (catálogos SUNAT compatibles).

**Migraciones activas** (9 archivos en `backend/database/migrations/`):
- `0001_01_01_*` → users, cache, jobs (Schema Builder)
- `2026_06_03_061919_*` → Spatie permissions + Sanctum tokens (Schema Builder)
- `2026_06_03_062016_create_dim_tables.php` → 24 tablas `dim_*` con datos SUNAT (raw SQL embebido)
- `2026_06_03_070000_*` → add dni/ruc a users (Schema Builder)
- `2026_06_03_071000_*` → customers (Schema Builder)
- `2026_06_03_072000_*` → products (Schema Builder)

**Testing**: BD `armora_erp_test` creada automáticamente por `composer test`.

## ADRs activos

| ADR | Decisión | Estado |
|---|---|---|
| ADR-001 | Prefijo `dim_` para tablas dimensionales | ✅ Aplicado |
| ADR-002 | Spatie Laravel Permission para RBAC | ✅ Aplicado |
| ADR-003 | Dual-Frontend Architecture (Admin + Portal) | ✅ Aplicado |
| ADR-004 | Login multi-campo (username/email/DNI/RUC) | ✅ Aplicado |
| ADR-005 | Modelos Eloquent para dim_*, CatalogController con DB::table() | 🟡 Transición gradual |
| ADR-006 | Autoincrement (SERIAL) vs ULID | ✅ Aplicado en `sales_*`, `inventory_movimientos`, `purchases_*` |
| ADR-007 | API REST standalone (sin Inertia) | ✅ Decisión tomada |
| ADR-008 | No hardcodear credenciales en tests — `User::factory() + actingAs()` | ✅ Aplicado |
| ADR-009 | Remediación intercalada Hito 003→004 en 4 fases (Fase 0 cerrada) | ✅ Archivado, sucesor ADR-010 |
| ADR-010 | Hito 004 Purchases + Finance (3 olas) | 🟡 Borrador, Ola A+B ejecutadas, Ola C ⏸ |

## Deuda técnica conocida

| Item | Impacto | Plan |
|---|---|---|
| CatalogController usa `DB::table()` no Eloquent | Inconsistencia arquitectónica | Migrar a Eloquent cuando Catalog tenga Service Layer |
| ~~Sin Policies de Laravel~~ | ~~RBAC solo por middleware, sin lógica por modelo~~ | ✅ Creadas en Hito 003-004 (SalePolicy, CustomerPolicy, ProductPolicy, InventoryPolicy, ProveedorPolicy, CompraPolicy) |
| ~~Sin eventos entre módulos~~ | ~~Customers/Products no emiten eventos~~ | ✅ SaleConfirmed (Hito 003), CompraConfirmada (Hito 004 Purchases) |
| ~~Sin ULID en PKs~~ | ~~Exponen volumen de registros~~ | ✅ Aplicado en `sales_*`, `inventory_movimientos`, `purchases_*` |
| ~~Sin audit trail~~ | ~~Sin trazabilidad de cambios en entidades~~ | ✅ `spatie/laravel-activitylog` instalado, usado en 7 modelos |
| ~~Credenciales hardcodeadas en SaleTest/InventoryTest~~ | ~~Riesgo de seguridad, viola DRY~~ | ~~Corregido en Hito 003 (ADR-008)~~ |
| Frontend Admin de Purchases faltante | Brecha UX (backend OK) | Hito 005 sprint backlog |
| Inventory Admin placeholder (stock + kardex) | Brecha UX | Hito 005 sprint backlog |
| Module Company sin ULID/SoftDeletes (tabla singleton) | Inconsistencia arquitectónica vs módulos transaccionales | Aceptado por diseño (singleton no requiere trazabilidad por fila) |
| Bloqueo ventas/compras sin middleware de validación request-time | Dependencia de lógica en Services (SaleService/CompraService chequean EmpresaConfig manualmente) | OK si no escala; migrar a middleware si hay >3 módulos bloqueables |
| Imágenes empresa: primer uso de Storage::disk('public') | Requiere `php artisan storage:link` manual | Docs en setup;

## Hoja de ruta (del perfil Senior Fullstack ERP Architect v2)

### Hito 003 — Sales + Inventory ✅ Implementado (parcial, sin SUNAT ni Finance)

**Backend (completo)**:
- ✅ 6 migraciones con prefijo (`sales_ventas`, `sales_venta_items`, `sales_notas_credito`, `inventory_stock`, `inventory_movimientos`, `dim_almacen`)
- ✅ ULID en todas las PKs transaccionales
- ✅ Models con `HasUlids`, `SoftDeletes`, `LogsActivity`
- ✅ `SaleService` (crear, actualizar, confirmar, anular con IGV 18% — fórmula fuente única en `calcularLineaIgv`)
- ✅ `InventoryService` (descontar/reingresar stock con `lockForUpdate` y `almacen_id`)
- ✅ `CreditNoteService` (emitir NC)
- ✅ `SaleConfirmed` event + `DescontarStock` listener
- ✅ 3 Policies: `SalePolicy`, `CustomerPolicy`, `ProductPolicy`
- ✅ 8 rutas sales + 3 rutas inventory con `permission:*` middleware
- ✅ 8 permisos sales + 3 permisos inventory; total 14 con A-08 confirmar-ventas
- ✅ 4 tests Feature (`SaleTest` 18, `InventoryTest` 4) con factories + actingAs (ADR-008)

**Frontend (parcial)**:
- ✅ `SaleListPage` Admin con DataGrid + filtros + confirmar/anular
- ✅ `SaleFormPage` Admin con ítems dinámicos + cálculo IGV
- ✅ `OrderCreatePage` Portal con carrito + checkout
- ✅ `OrderHistoryPage` Portal con tabs + detalle
- ✅ Cart store Zustand con persist en localStorage
- ⏳ `InventoryPage` Admin (solo placeholder, falta stock list + kardex)

### Hito 004 — Purchases ✅ Backend implementado · Finance ⏸ Pendiente

**Purchases (Ola B, completo)**:
- ✅ 3 migraciones con prefijo (`purchases_proveedores`, `purchases_compras` con `purchases_codigo_seq`, `purchases_compra_items`)
- ✅ ULID en todas las PKs + SoftDeletes
- ✅ 3 modelos: `Proveedor`, `Compra`, `CompraItem` con `HasUlids`, `SoftDeletes`, `LogsActivity`
- ✅ 2 services: `ProveedorService` (CRUD), `CompraService` (CRUD, importa `SaleService::calcularLineaIgv`, `aumentarStock` inline)
- ✅ 2 policies: `ProveedorPolicy`, `CompraPolicy` con `confirmar()` y `anular()`
- ✅ 2 controllers delgados con `viewAny` en `index`
- ✅ 4 FormRequests con `exists:dim_*` (sin `deleted_at`) y `exists:purchases_*` (con `deleted_at`)
- ✅ 3 API Resources con `whenLoaded()`
- ✅ 12 rutas REST con `permission:*` middleware
- ✅ `CompraConfirmada` event + `AumentarStock` listener (no-op, lógica inline)
- ✅ 11 nuevos permisos Purchases; rol `Comprador` ampliado
- ✅ 2 tests Feature (`CompraTest` 13/13, `ProveedorTest` 6/6) con factories + actingAs (ADR-008)
- ✅ Fix SUNAT: IGV = `total - gravada` (post-rounding) en `calcularLineaIgv`
- ✅ `App\Models\Catalog\Almacen.php` (modelo Eloquent para `dim_almacen`)

**Pendiente Purchases (Frontend)**:
- ❌ `PurchaseListPage` Admin con DataGrid
- ❌ `PurchaseFormPage` Admin con ítems dinámicos
- ❌ Portal Proveedor (vista de sus órdenes)

**Finance (Ola C, ⏸ pendiente de validación)**:
- ⏸ 3 migraciones (`finance_cuentas_contables`, `finance_asientos`, `finance_asiento_lineas`)
- ⏸ Plan contable básico SUNAT (40 cuentas)
- ⏸ `FinanceService` con `generarAsientoPorVenta/Compra`, `validarCuadratura`, `exportarPLE 14.1/8.1`
- ⏸ Tests ~8

### Hito 004a — Company Settings ✅ Backend + Frontend implementado

**Company (Configuración de Empresa)**:
- ✅ 1 migración (`config_empresa`, tabla singleton sin ULID/SoftDeletes por diseño)
- ✅ Modelo `EmpresaConfig` con casts boolean/date
- ✅ Service `EmpresaService` con CRUD + subida/reset imágenes (`Storage::disk('public')`)
- ✅ Controller thin con upload de imágenes vía multipart
- ✅ `UpdateEmpresaRequest` con validación y mensajes en español
- ✅ `EmpresaResource` con URLs de imágenes via `url("storage/...")`
- ✅ `EmpresaPolicy` + 2 permisos: `ver-configuracion`, `configurar-empresa`
- ✅ 5 rutas REST (`GET`, `PUT`, `POST imagen/{tipo}`, `DELETE imagen/{tipo}`, `POST actualizar-decimales`)
- ✅ CompanySettingsPage Admin con 4 tabs (Empresa, Parámetros, Imágenes, Bloqueo)
- ✅ Catálogos anidados (país→departamento→provincia→distrito) vía catalogApi + Autocomplete MUI con búsqueda en vivo
- ✅ Validación contextual por sección: `__seccion=empresa` activa `required` en RZ, RUC, Email, Celular, Departamento, Provincia, Distrito, Dirección; resto de tabs sigue `nullable` para PATCH parcial
- ✅ Bloqueo ventas/compras: flag en BD + check en SaleService::create() y CompraService::create()
- ✅ `POST actualizar-decimales` recalcula ROUND() en products y stock
- ✅ CompanyTest con 9 casos (GET, PUT, decimales, flags, policy, validación sección empresa, regex RUC, PATCH parcial parámetros)
- ⏳ `php artisan storage:link` requerido para imágenes

### Hito 007 — Personal ✅ Backend + Frontend "Crear" implementados

**Personal (Gestión de Usuarios Internos)**:
- ✅ 4 migraciones (1 extiende `users` con 19 columnas nullable + 7 FK + softDeletes + índices + 3 pivotes: `personal_listas_precios`, `personal_almacenes`, `personal_permisos`)
- ✅ 2 modelos catálogo nuevos: `Sexo`, `EstadoCivil` (extiende `dim_sexo`/`dim_estado_civil` con `$timestamps=false`)
- ✅ Modelo `Personal extends User` con `SoftDeletes` + `LogsActivity` + 10 relaciones
- ✅ `PersonalService` con 9 métodos (paginate, findById, create, update, delete, uploadPhoto, resetPhoto, getPhotoPath, getRolesDisponibles, getPermisosAgrupados) + helper privado `generateCode` (PER-XXXXX)
- ✅ `PersonalPolicy` con `delete()` que impide auto-eliminarse
- ✅ 2 FormRequests con regex DNI/RUC, `confirmed` password, `withValidator` que rechaza DNI+RUC simultáneos
- ✅ `PersonalResource` con `whenLoaded` para todas las relaciones + `foto_url`
- ✅ `PersonalController` con 10 endpoints REST
- ✅ 4 nuevos permisos Spatie (ver/crear/editar/eliminar-personal); Admin y Super-Admin actualizados
- ✅ 10 rutas REST con middleware `auth:sanctum + permission:*`
- ✅ 1 endpoint catálogo nuevo: `GET /api/catalog/almacenes`
- ✅ PersonalTest con **22/22 casos passing** (CRUD, validaciones, upload foto, reset foto, RBAC, no auto-delete)
- ✅ Fix 4 issues críticos: `data` wrap en Resources, `$fillable` incompleto, date casts faltantes, env.d.ts type augmentation

**Frontend (parcial)**:
- ✅ `personalApi` en `endpoints.ts` con 8 métodos
- ✅ 5 tipos nuevos en `shared/types/index.ts` (Personal, PersonalPayload, Sexo, EstadoCivil, PermisoAgrupado)
- ✅ `PersonalFormPage.tsx` con MUI Stepper de 6 pasos + RHF + Zod (Datos Personales → Identidad → Contacto y Ubicación → Permisos y Accesos → Fotografía → Confirmación)
- ✅ 2 rutas en `App.tsx` (nuevo, editar)
- ✅ `deriveTitle` actualizado en `AdminLayout`

**Iteración feedback (post-revisión de ficha)**:
- ✅ Step 1 sin campo `nombre_completo` (se computa server-side como `apellido_paterno apellido_materno nombres`); `password`/`password_confirmation` con `IconButton` `Visibility`/`VisibilityOff` para mostrar/ocultar
- ✅ Step 2: `documento_identidad_id` ahora es `Autocomplete` contra `dim_documento_identidad` (DNI/CE/Pasaporte/RUC); campo único `numero_documento` con regex y `maxLength` dinámicas (helper `resolveRegex/resolveMaxLength` en FormRequests); `dni`/`ruc` eliminados
- ✅ Step 2: `estado_civil_id` ahora es `Autocomplete` contra `dim_estado_civil` (catálogo SUNAT)
- ✅ Step 3: roles **single-select** con radio buttons (`RadioButtonChecked`/`RadioButtonUnchecked`) + validación `roles.max:1` server-side (test #23 `test_create_personal_allows_only_one_role`)
- ✅ Nueva tabla `dim_documento_identidad` (migración `2026_06_07_010000`): DNI/Carné de Extranjería/Pasaporte/RUC con regex PCRE-delimitado (`/^\d{8}$/`, etc.)
- ✅ Seed `dim_almacen` con "Almacén Principal" (migración `2026_06_07_010001` con `insertOrIgnore` idempotente)
- ✅ Migración `2026_06_07_010002` elimina `dni`/`ruc`/`documento_tipo_id` de `users` y agrega `documento_identidad_id` FK a `dim_documento_identidad`
- ✅ `User::factory()` y `AuthService::login` actualizados (login multi-campo usa `numero_documento` en vez de `dni`/`ruc`)
- ✅ PersonalTest pasa **23/23** (75 assertions); suite completa no pre-existente: 67/67 passing (Personal 23 + Inventory 4 + Sales 18 + Purchases 19 + Company 3)
- ✅ `PersonalListPage` (DataGrid) implementado (2026-06-07)
- ✅ `Reportes Personal` implementado (2 cards: Personal Activo + Ficha Personal, HTML printable con `window.print()`) (2026-06-07)
- ✅ **PurchaseFormPage frontend** implementado con UX/UI mejorada: bloqueo proactivo de empresa, autocomplete server-side con debounce, snapshot de costo promedio, sticky bottom bar con totales, 2 CTAs (borrador/confirmar), validación inline, snackbar feedback. Bundle: 16.24 kB / 5.89 kB gzip. Doc: `_docs_desarrollo/HITO-005-purchases-frontend.md` (2026-06-07)

### Hito 005 — Logistics + Loyalty
- Rutas, zonas, transportistas
- Programa de canje y premios
- Frontend Admin Purchases (Form ✅, List ⏳, Detalle ⏳)
- Inventory Admin completo (stock + kardex)
- PersonalListPage (DataGrid) + Gestión Personal completa + **Reportes Personal** (2 cards: Personal Activo + Ficha Personal) ✅
- **PurchaseFormPage** ✅: bloquea ventas/compras si flag activo, autocomplete proveedores (debounce 350 ms), snapshot de `costo_promedio`, sticky bottom bar con IGV 18% en vivo, 2 CTAs (Guardar borrador / Crear y confirmar). Doc `_docs_desarrollo/HITO-005-purchases-frontend.md`

### Hito 006 — Cross-cutting
- CI/CD (GitHub Actions)
- PHPStan level máximo
- Laravel Pulse + Sentry
- Monitoreo (Prometheus + Grafana)
- Tests E2E (Playwright)
- Greenter 5.x + SUNAT + Job async

## Convenciones

- Backend: inyección por constructor (readonly properties tipadas)
- Frontend: alias `@/` para `src/`
- Endpoints API en snake_case (`tipo-afeccion-igv`, `unidades-medida`)
- Tipos TypeScript reflejan Resources del backend (snake_case, `id: number`)
- UI en español (ERP para mercado peruano)
- Estilos: prop `sx` de MUI o `styled` (sin CSS modules)
- NUNCA editar `vendor/` ni `node_modules/`

### Testing (PHPUnit / Feature)

- **Crear usuarios con `User::factory()`** + estado específico (e.g. `User::factory()->admin()->create()`), NUNCA `User::create([...atributos literales...])`.
- **Autenticar con `$user->createToken('test')->plainTextToken`** y `withToken($token)`, NUNCA llamar a `/api/auth/login` desde tests (evita hardcodear passwords, no ejercita lógica de login real, y desacopla el test de la API de auth).
- Para tests de validación/anulación que requieren otro rol, instanciar el segundo usuario en `setUp()` y exponer un helper por rol (`asVendedor()`, `asAdmin()`, `asJefeAlmacen()`).
- **Excepción**: tests E2E de Sanctum (que ejercitan la API real de login) pueden usar `actingAs()`, pero no credenciales literales en el código — usar `User::factory()` + `actingAs($user, 'web')` con `Sanctum::actingAs($user)`.
- Aplicar `RefreshDatabase` o `DatabaseTransactions` para aislar estado entre tests.

## Auditoría Automática (Arquitecto ↔ Auditor)

El proyecto cuenta con **2 agentes independientes** que trabajan coordinados:

| Agente | Perfil | Labor |
|---|---|---|
| **Arquitecto** | `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md` | Implementar features, escribir código |
| **Auditor** | `_auditoria/senior-code-architecture-quality-auditor.md` | Validar calidad contra 17 gates |

### Flujo de trabajo

```
Arquitecto implementa → Layer 1 (script) → ¿pasa? → Layer 2 (auditor IA) → ¿sin críticos? → ✅
                         ↑                                     │
                         └───────── remediar ←─────────────────┘
```

### Layer 1 — Chequeos Mecánicos (script)

Ejecutar **inmediatamente después de implementar**:

```powershell
.\_auditoria\auditar.ps1          # build + lint + tests FE/BE
.\_auditoria\auditar.ps1 -Quick   # solo build + lint (rápido)
```

El script produce `_auditoria/auditar-resultado.json`.

### Layer 2 — Gate Review (agente auditor)

Si Layer 1 pasa, el agente arquitecto **invoca al agente auditor** mediante el mecanismo de subagente (Task tool). El auditor:
1. Inspecciona los archivos modificados
2. Evalúa cada gate aplicable (G-ARQ, G-RBAC, G-FORM, G-API, G-TS, G-FE, etc.)
3. Produce un reporte con hallazgos clasificados por severidad (🔴/🟠/🟡/🟢)

### Ciclo de remediación

- **🔴 Crítico** → arquitecto corrige INMEDIATAMENTE, reinicia desde Layer 1
- **🟠 Alto** → arquitecto corrige antes del siguiente HITO
- **🟡 Medio / 🟢 Bajo** → se documentan en `_auditoria/MATRIZ_RIESGOS.md`

### Documentos de referencia

| Documento | Propósito |
|---|---|
| `_auditoria/PROTOCOLO_AUTOMATICO.md` | Protocolo detallado de interacción arquitecto↔auditor |
| `_auditoria/auditar.ps1` | Script Layer 1 (chequeos mecánicos) |
| `_auditoria/CHECKLIST_MAESTRO.md` | 17 gates con criterios detallados |
| `_auditoria/MATRIZ_RIESGOS.md` | Deuda técnica diferida acumulada |
