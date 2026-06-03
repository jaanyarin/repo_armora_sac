# Senior Fullstack ERP Architect — Guía Operativa del Proyecto (v3)

> [!NOTE]
> **v1 / v2** describen el **perfil genérico del puesto** (qué tipo de profesional se requiere, habilidades, seniority, etc.).
> **v3 (este documento)** describe el **estado real del proyecto** ARMORA NextGen al 2026-06-03, contrasta con el perfil planeado, y sirve como **guía operativa** + **hoja de ruta** para el equipo.
>
> Audiencia: Tech Lead, Arquitecto de Soluciones, desarrolladores del proyecto, agente AI de automatización.

---

## 0. Resumen ejecutivo (1 minuto)

- **Proyecto:** ARMORA NextGen — ERP peruano (SUNAT: facturación electrónica, IGV/ISC, inventarios, ventas, compras, logística, canje de puntos).
- **Stack vigente:** Laravel 13.8 + PHP 8.3 + PostgreSQL 16 + Redis 7 (backend) · React 19 + Vite 8 + TypeScript 6 + MUI 7 (frontend).
- **Estado:** 🟡 **Fase 1A completada**. Auth + Catalog + Customers + Products operativos (backend + admin UI + tests). Faltan 6 módulos de negocio y todo el stack SUNAT.
- **Siguiente hito propuesto:** HITO-003 — **Sales + Inventory** (desbloquea SUNAT/Greenter y el Portal Cliente).
- **Documentación funcional:** ver `_docs_implementacion/INDICE_MAESTRO.md` (35+ docs), `_docs_desarrollo/HITO-001`, `HITO-002` y `AGENTS.md` en raíz.

---

## 1. Auditoría: Perfil v2 vs Implementación Real

### 1.1 Stack — desviaciones detectadas

| Componente | Documentado v2 | Real (jun-2026) | Acción |
|---|---|---|---|
| Backend framework | Laravel 12 | **Laravel 13.8** | Actualizar v2 o referenciar aquí |
| Frontend framework | React 19 + Vite | React 19 + Vite 8 | ✅ Coincide |
| Lenguaje FE | TypeScript strict | TypeScript **6.0.2** | ✅ Coincide (versión muy nueva, OK) |
| UI Library | MUI 6 | **MUI 7** + `@mui/x-data-grid` 7 | Actualizar v2 |
| Backend Auth | Sanctum + Spatie Permission | Sanctum 4 + Spatie 8 | ✅ Coincide |
| DB | PostgreSQL 16 | PostgreSQL 16 (port **5434** en docker) | ✅ |
| Cache/Queue | Redis 7 | Redis 7 (port 6379) + Predis 3.5 | ✅ |
| **Migraciones** | Laravel Migrations nativas | **Dual**: Laravel migrations (`database/migrations/*.php`) + SQL raw (`database/migrations/001_dim_tables.sql`) | Documentar convención híbrida |
| RBAC | Spatie (~480 permisos) | Spatie 8 + tablas **`dim_rol` / `dim_permiso` / `dim_rol_permiso` / `dim_usuario_rol`** (custom, no tablas Spatie por defecto) | Documentar integración custom |
| SUNAT / Greenter | Pendiente | **No instalado** | Crítico — HITO-003 |
| Queues | Laravel Queues + Redis + Horizon | Predis listo, **driver de colas aún no configurado** (`QUEUE_CONNECTION` no en uso) | HITO-003 |
| Broadcasting / Reverb | Planeado | **No instalado** | Diferido |
| PWA / vite-plugin-pwa | Planeado | **No instalado** | Diferido (Portal) |
| CI/CD | GitHub Actions | **No configurado** | Diferido (HITO-006) |
| Monitoreo (Pulse, Sentry) | Planeado | **No instalado** | Diferido |
| Testing FE | Vitest + Playwright | **No configurado** | Diferido |
| Testing BE | Pest / PHPUnit | **PHPUnit 12** (no Pest) | OK; Pest opcional |

### 1.2 Módulos — cobertura real

| Módulo | Backend | Frontend Admin | Frontend Portal | Tests | Notas |
|---|---|---|---|---|---|
| **Auth** | ✅ Sanctum 4 + Spatie 8 + multi-campo (username/email/DNI/RUC) | ✅ Login + ProtectedRoute + Auth Store (Zustand) | — | ✅ 5 casos | Login único `auth/login` + `auth/me` + `auth/logout` |
| **Catalog** (24 endpoints) | ✅ `CatalogController` 25 métodos sobre tablas `dim_*` | ✅ `catalogApi` + tipos en `shared/types` | — | ❌ | Datos seed (PEN/USD, 50 UM, 25 departamentos, 36 distritos Lima, 18 afección IGV, etc.) |
| **Customers** | ✅ CRUD completo + SoftDeletes + paginación + búsqueda | ✅ List (DataGrid) + Form (RHF + Zod) | — | ✅ 6 casos | Modelo con relaciones a `dim_*` |
| **Products** | ✅ CRUD completo + SoftDeletes + filtros | ✅ List + Form con selectores de catálogo | — | ✅ 5 casos | Precios S/ + USD, costo, stock |
| **Sales** | ❌ | ❌ (placeholder en AdminLayout) | ❌ | — | **HITO-003** — crítico |
| **Inventory** | ❌ | ❌ (placeholder) | ❌ | — | **HITO-003** |
| **Purchases** | ❌ | ❌ | ❌ | — | HITO-004 |
| **Finance / PLE** | ❌ | ❌ | ❌ | — | HITO-005 |
| **Logistics** | ❌ | ❌ (placeholder) | ❌ | — | HITO-005 |
| **Loyalty / Nube de Puntos** | ❌ | ❌ | ❌ | — | HITO-006 |
| **Notifications** | ❌ | ❌ | ❌ | — | Diferido |
| **Portal Cliente/Proveedor** | — | — | ⚠️ Solo `PortalLayout.tsx` (esqueleto); sin rutas | — | HITO-003 (catálogo + pedido) |

**Tests totales:** 16 casos (PHPUnit). `composer test` corre feature tests; frontend sin test runner.

### 1.3 Cobertura del dominio SUNAT

| Capacidad SUNAT | Estado |
|---|---|
| Catálogos SUNAT (moneda, UM, IGV, ISC, NC, ubigeo, doc) | ✅ Tablas `dim_*` pobladas |
| XML UBL 2.1 + firma digital + SOAP | ❌ Greenter no instalado |
| Envío a OSE / SUNAT | ❌ |
| CDR, resumen diario, comunicación de baja | ❌ |
| Tipos doc 01/03/07/08/09/12/20/31/40 | ✅ Catálogo `dim_documento_tipo` + símbolos |
| Códigos de tributo, tipo de operación | ✅ `dim_tipo_afeccion_igv`, `dim_tipo_calculo_isc` |
| PLE (libro contable electrónico) | ❌ |
| Detracciones / percepciones / retenciones | ❌ |

---

## 2. Stack vigente (referencia rápida)

### 2.1 Backend

| Componente | Versión | Notas |
|---|---|---|
| PHP | 8.3+ (tipado estricto) | |
| Laravel | 13.8 | con Sanctum 4, Spatie Permission 8, Predis 3.5, Pail 1.2 |
| PHPUnit | 12.5 | (no Pest instalado) |
| Pint | 1.27 | Linter formato |
| DB driver | PostgreSQL 16 | |
| Migrations | Dual: Laravel classes + SQL raw (`001_dim_tables.sql`) | **Convención:** seeders dimensionales en SQL; entidades de negocio en Laravel migrations |

### 2.2 Frontend

| Componente | Versión | Notas |
|---|---|---|
| React | 19.2 | |
| Vite | 8.0 | con `@rolldown/binding-win32-x64-msvc` |
| TypeScript | 6.0 | strict mode |
| MUI | 7.0 | `@mui/material`, `@mui/icons-material`, `@mui/x-data-grid` 7 |
| React Router | 7.16 | con lazy loading |
| TanStack Query | 5.x | server state, defaults: `retry:1, refetchOnWindowFocus:false, staleTime:5min` |
| Zustand | 5.x | `useAuthStore` en `shared/hooks/useAuth.ts` |
| React Hook Form | 7.54 | con `@hookform/resolvers` 5 |
| Zod | 3.24 | validación performante |
| Axios | 1.7 | cliente con interceptor Bearer + 401 → logout |
| Day.js | 1.11 | fechas |

### 2.3 Infraestructura

- Docker Compose con PostgreSQL 16 (puerto 5434) + Redis 7 (puerto 6379).
- DB name: `armora_erp`, user `armora`, password en `.env` (ver `docker-compose.yml`).
- Vite dev: **5175** → proxy `/api` a `http://localhost:8005`.
- Laravel dev: `php artisan serve --port=8005` (Sanctum `SANCTUM_STATEFUL_DOMAINS` ya configurado en `backend/config/sanctum.php`).
- Sin `.github/workflows/` aún.

---

## 3. Arquitectura vigente

### 3.1 Estructura real del monorepo

```
repo_armora_sac/
├── AGENTS.md                          ← guía operativa para agentes/equipo
├── docker-compose.yml                 ← PostgreSQL 16 + Redis 7
├── _perfiles_tecnicos/                ← v1, v2, v3 (perfiles + guía operativa)
├── _docs_desarrollo/                  ← HITO-001, HITO-002 (logs de hitos)
├── _docs_implementacion/              ← 35+ docs (análisis, ADRs, módulos, API, SUNAT)
├── backend/
│   ├── app/
│   │   ├── Models/
│   │   │   ├── User.php               ← Authenticatable + HasApiTokens + HasRoles
│   │   │   └── Catalog/               ← 18 modelos Eloquent para tablas dim_*
│   │   └── Modules/
│   │       ├── Auth/                  (Controller, Service, LoginRequest, UserResource)
│   │       ├── Catalog/               (1 Controller, 25 métodos, sin Resources)
│   │       ├── Customers/             (CRUD completo, 2 Form Requests, 1 Resource)
│   │       └── Products/              (CRUD completo, 2 Form Requests, 1 Resource)
│   ├── database/
│   │   ├── migrations/                ← 8 archivos (Laravel) + 001_dim_tables.sql (raw)
│   │   ├── seeders/                   ← DatabaseSeeder, RoleAndPermissionSeeder, DimTableSeeder
│   │   └── factories/UserFactory.php
│   ├── routes/api.php                 ← /api/auth/*, /api/catalog/*, /api/customers, /api/products
│   └── tests/Feature/                 ← AuthTest, CustomerTest, ProductTest (16 casos)
├── frontend/
│   └── src/
│       ├── main.tsx, App.tsx          ← entry + router con lazy loading
│       ├── shared/
│       │   ├── api/{client,endpoints}.ts
│       │   ├── components/{LoginPage,ProtectedRoute,NotFoundPage}.tsx
│       │   ├── hooks/useAuth.ts       ← Zustand store
│       │   ├── types/index.ts         ← 16 interfaces (catálogo + Customer + Product + Usuario)
│       │   └── theme.ts
│       ├── Admin/
│       │   ├── layouts/AdminLayout.tsx          ← Drawer permanente + AppBar + menú lateral
│       │   └── pages/
│       │       ├── DashboardPage.tsx
│       │       ├── Customers/ (List + Form)
│       │       └── Products/ (List + Form)
│       └── Portal/
│           └── layouts/PortalLayout.tsx         ← Esqueleto (sin páginas ni rutas aún)
└── database/migrations/                ← 001_dim_tables.sql (mirror/sync con Laravel migrations)
```

### 3.2 Convenciones activas (extraídas de `AGENTS.md` + HITO-002)

- **Backend:** módulos en `app/Modules/<Nombre>/{Http,Services,Models}` con `Http/{Controllers,Requests,Resources}`. Constructor injection con `readonly` typed properties. Tablas dimensionales en `app/Models/Catalog/*` apuntando a `dim_*`. SoftDeletes por defecto. Validación en `FormRequest`, transformación en `JsonResource`, lógica en `Service`.
- **Frontend:** alias `@/` → `src/`. Endpoints snake_case (`/api/tipo-afeccion-igv`, `/api/unidades-medida`). Tipos en `shared/types` con snake_case. UI 100% en español. Estilos vía MUI `sx` (sin CSS modules).
- **Auth flow:** Sanctum Bearer token. Token en `localStorage('auth_token')`. `useAuthStore` Zustand. Axios interceptor inyecta header y maneja 401.
- **Login multi-campo:** campo único `login` acepta `username | email | dni | ruc` (ADR-004).
- **Tablas dimensionales** se sirven vía `DB::table()` en `CatalogController` (simple, sin necesidad de modelos); entidades de negocio (Customers, Products) usan Eloquent con relaciones.
- **Soft deletes** en `customers` y `products` (`deleted_at`).
- **Tests:** PHPUnit + `RefreshDatabase` + `RoleAndPermissionSeeder` sembrado en `setUp`. Sin tests para Catalog.

### 3.3 Diferencias con el "shape" del v2

El v2 describe una estructura `app/Modules/<Name>/{Http,Policies,Events,Jobs,...}`. Hoy Auth tiene `Http/Controllers, Http/Requests, Http/Resources, Services` (sin Policies/Events/Jobs). Cuando se implemente Sales con eventos `SaleConfirmed`, se completará la estructura.

---

## 4. ADR-002 — Estado actual y próximos pasos

### 4.1 Decisión

Continuar la **Fase 1** del plan original con el orden ajustado al estado real:

1. ✅ **HITO-001** Fundación del proyecto (esquema BD dimensional + React 19 + AGENTS).
2. ✅ **HITO-002** Customers + Products (CRUD completo + tests).
3. 🟡 **HITO-003** **Sales + Inventory** (próximo) — desbloquea SUNAT, Portal Cliente, Finance.
4. ⏭️ **HITO-004** Purchases + Proveedores.
5. ⏭️ **HITO-005** Finance (PLE, detracciones) + Logistics (rutas, zonas).
6. ⏭️ **HITO-006** Loyalty (canje de puntos) + Notifications + CI/CD + Sentry.

### 4.2 Justificación de orden

- **Sales primero** porque activa el dominio crítico SUNAT y desbloquea **Inventory** (descuento de stock vía `SaleConfirmed` → `InventoryMovement`), **Finance** (asiento + PLE) y **Portal Cliente** (checkout).
- **Inventory segundo** porque es la otra mitad de la integridad transaccional (stock = ventas + compras + ajustes) y ambos módulos comparten `kardex`.
- **Purchases** después: depende de Inventory para registrar ingresos a almacén.
- **Finance** al final del flujo core: requiere ventas + compras + catálogo de tributos (ya listos).
- **Loyalty + Notifications + DevOps** son transversales; pueden entrar en paralelo.

### 4.3 Stack nuevo a incorporar (HITO-003 en adelante)

| Librería | Propósito | Cuándo |
|---|---|---|
| `greenter/greenter` 5.x | XML UBL 2.1 + firma + SOAP SUNAT | HITO-003 |
| `laravel/horizon` | UI para queues | HITO-003 |
| `laravel/reverb` | Broadcasting (preparar, no obligatorio) | Diferido |
| `spatie/laravel-activitylog` | Auditoría (ADR-002 ya lo preveía) | HITO-003 |
| `laravel/scout` + driver PG | Búsqueda full-text (opcional) | Diferido |
| `sentry/sentry-laravel` | Monitoreo errores | HITO-006 |
| `laravel/pulse` | Métricas | HITO-006 |
| `vite-plugin-pwa` | PWA del Portal | HITO-003 (Portal) |
| `vitest` + `@testing-library/react` | Tests FE | HITO-006 |
| `playwright` | E2E | HITO-006 |
| GitHub Actions | CI lint + tests | HITO-006 |

---

## 5. Hoja de ruta concreta (próximos hitos)

### 5.1 HITO-003 — Sales + Inventory + SUNAT (4-5 semanas)

**Backend — Sales**
- Migración: `sales` (cabecera), `sales_items` (línea), `sales_payments` (pagos), `credit_notes` y `credit_note_items`.
- Modelos: `Sale`, `SaleItem`, `CreditNote` con `SoftDeletes`, ULID opcional, relaciones a `customers`, `products`, `users`, `dim_documento_tipo`, `dim_moneda`, `dim_tipo_venta`.
- `SaleService` con: cálculo de IGV/ISC por línea, totales, redondeo, conversor de moneda, validación de stock.
- `CreditNoteService` con tipos según `dim_nota_credito_tipo` (01-10).
- `Job\SendInvoiceToSunat` (queue Redis) → Greenter → storage S3/local de XML/CDR/PDF.
- `Event\SaleConfirmed` + listeners (Inventory descuenta, Finance genera asiento, Notifications envía).
- `Policy\SalePolicy` + middleware `permission:crear-ventas`, `permission:anular-ventas`.
- `FormRequest` (Store/Update) con validación por tipo de documento (DNI/RUC → 01, sin RUC → 03).

**Backend — Inventory**
- Migración: `inventory_warehouses`, `inventory_locations`, `inventory_movements` (kardex), `inventory_adjustments`.
- Modelos con `SoftDeletes`.
- `InventoryService::recordMovement()` idempotente.
- `InventoryService::kardex($productId, $from, $to)` con valuación PEPS o promedio ponderado.
- Listener de `SaleConfirmed` → crea `inventory_movements` (tipo SALIDA) con `DB::transaction()`.
- Job `RecalculateAverageCost` al confirmar compra.

**Integración SUNAT**
- Instalar Greenter 5.x, certificado digital en `storage/sunat/cert.pem`.
- `config/sunat.php` con RUC, modo (BETA/PROD), OSE endpoint, certificado.
- `SunatService` con métodos: `sendInvoice(Sale)`, `sendSummary`, `sendVoided`.
- Almacenamiento en `Storage::disk('local')->put("sunat/{$ruc}/{$serie}-{$correlativo}.xml")`.

**Frontend — Admin**
- Página `SalesListPage` con MUI DataGrid: filtro por fecha, cliente, estado, tipo documento.
- Página `SaleFormPage` con: cliente (autocomplete desde API), líneas dinámicas (producto + cantidad + precio + descuento), cálculo de IGV en vivo, totales, modal de confirmación.
- Página `CreditNotePage` (selector de venta origen + motivo + líneas a devolver).
- Mutaciones con `useMutation` + invalidación de queries.

**Frontend — Portal**
- Habilitar ruta `/portal/*` en `App.tsx`.
- Página `CatalogPage` con cards de productos + carrito (Zustand store + localStorage).
- Página `CheckoutPage` que llama a `POST /api/portal/orders` (endpoint público con token de portal).
- Activar `vite-plugin-pwa` con manifest, service worker, add-to-home-screen.

**Tests**
- Feature: `SaleTest` (crear venta, calcular IGV, emitir boleta/factura, aplicar NC).
- Feature: `InventoryTest` (kardex tras venta, ajuste, recálculo costo promedio).
- Unit: `GreenterXmlBuilderTest` con mock del servicio SUNAT.
- Unit: `IgvCalculatorTest` con casos: gravado, exonerado, inafecto, mixto.

**DevOps — queues**
- Cambiar `QUEUE_CONNECTION=database` a `redis` en `.env`.
- `php artisan queue:table` (ya existe) + migrate.
- Instalar Horizon + `php artisan horizon:install` + servir en `/horizon` (solo admin).

### 5.2 HITO-004 — Purchases (2 semanas)

- `purchases`, `purchase_items`, `suppliers` (módulo nuevo: `Purchases` con su `Supplier`).
- Vinculación con `Inventory` (ingreso a almacén al confirmar compra).
- Vinculación con `Finance` (registro de factura de proveedor, detracciones si aplica).

### 5.3 HITO-005 — Finance + Logistics (3 semanas)

- `Finance`: asientos contables, libro diario, libro mayor, PLE 5.0/5.1, detracciones, percepciones.
- `Logistics`: rutas, zonas, transportistas, guías de remisión (tipo doc 09), despacho.
- Reportería básica: ventas por período, top productos, kardex valorizado, cuentas por cobrar.

### 5.4 HITO-006 — Loyalty + Cross-cutting (2-3 semanas)

- `Loyalty`: reglas de puntos por compra, vigencia, canje, premios.
- `Notifications`: email (Laravel Mail), WebPush (Portal), broadcasting (Reverb).
- CI/CD GitHub Actions: jobs `lint` (Pint), `static` (PHPStan si se agrega), `test` (PHPUnit backend + npm run build + lint frontend).
- Monitoreo: Sentry, Laravel Pulse, logs estructurados.
- Frontend: configurar Vitest + Playwright. Alcanzar cobertura >60% en módulos críticos.

---

## 6. Pendientes transversales

- [ ] Documentar OpenAPI/Swagger de los endpoints (actualmente solo en `_docs_implementacion/06_api_endpoints/`).
- [ ] Configurar `QUEUE_CONNECTION=redis` y probar `php artisan queue:work`.
- [ ] Habilitar `Scribe` o `Scramble` para autogenerar docs REST.
- [ ] Agregar `spatie/laravel-activitylog` para auditoría.
- [ ] Definir política de `CORS` (Portal en otro origen).
- [ ] Variables de entorno: documentar en `.env.example` (SUNAT_*, REDIS_*, QUEUE_*).
- [ ] Crear `docker-compose.override.yml` para Mailhog/Localstack en dev (futuro).

---

## 7. Referencias cruzadas

| Tema | Documento |
|---|---|
| Comandos y arquitectura general | `AGENTS.md` (raíz) |
| Hitos previos | `_docs_desarrollo/HITO-001-fundacion-proyecto.md`, `HITO-002-customers-products.md` |
| Análisis legacy + ADRs | `_docs_implementacion/05_especificaciones_tecnicas/architecture-decisions.md` |
| Stack detallado | `_docs_implementacion/05_especificaciones_tecnicas/stack-tecnico.md` |
| Modelo de datos | `_docs_implementacion/02_arquitectura_datos/database-schema.sql` |
| Módulos funcional | `_docs_implementacion/03_mapa_funcionalidades/` (uno por módulo) |
| API REST | `_docs_implementacion/06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md` |
| Compliance SUNAT | `_docs_implementacion/07_seguridad_compliance/sunat-compliance.md` |
| Perfil de puesto (genérico) | `_perfiles_tecnicos/senior-fullstack-erp-architect_v1.md`, `v2.md` |

---

## 8. Cambios respecto a versiones previas

| Versión | Naturaleza | Mantener como |
|---|---|---|
| v1 | Perfil de puesto genérico (Java/Spring) | Referencia histórica |
| v2 | Perfil de puesto refinado (Laravel/React) | Referencia de contratación |
| **v3 (este)** | **Guía operativa del proyecto + roadmap** | **Doc vivo, actualizar al cierre de cada HITO** |

---

*Documento generado: 2026-06-03 · Próxima revisión: al cierre del HITO-003 (Sales + Inventory + SUNAT).*
