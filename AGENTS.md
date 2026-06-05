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

**Hito 003 cerrado + Fase 0 auditoría cerrada + Hito 004 Purchases (Ola A+B) en validación** — Sales + Inventory + **Purchases** completo (backend) + 11 fixes auditoría (A-01 a A-11) + 14 fixes Ola B (HITO-004 Purchases: Proveedor + Compra con IGV fuente única).
Módulos funcionales: Auth, Catalog, Customers, Products, **Sales, Inventory, Purchases**.
Portal Cliente: catálogo público + login + dashboard + **pedidos (carrito + checkout + historial)** con navegación responsive.

**Próximo:** Hito 004 Finance (Ola C) ⏸ diferido para validación previa del auditor sobre Ola A+B.

### Arquitectura backend

```
app/Modules/{Auth,Catalog,Customers,Products,Sales,Inventory,Purchases}/
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
│   ├── api/             ← Axios client + interceptors + endpoints (authApi, catalogApi, customersApi, productsApi, salesApi, inventoryApi)
│   ├── hooks/           ← useAuth (Zustand), useCart (Zustand persist) — Hito 003
│   ├── types/           ← 233 → 320+ líneas, interfaces snake_case
│   ├── components/      ← ProtectedRoute, LoginPage, NotFoundPage
│   └── theme.ts         ← MUI theme Admin
├── Admin/               ← SPA Admin ERP (desktop-primary)
│   ├── layouts/         ← AdminLayout (Drawer + AppBar)
│   ├── pages/           ← Dashboard + Customers + Products + Sales (lista + formulario) — Hito 003
│   └── components/      ← (vacíos por ahora)
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
| `npm run test` | Vitest (6 tests: useAuth + ProtectedRoute) |
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

### Hito 005 — Logistics + Loyalty
- Rutas, zonas, transportistas
- Programa de canje y premios
- Frontend Admin Purchases completo
- Inventory Admin completo (stock + kardex)

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
