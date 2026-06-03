# ARMORA SAC — AGENTS.md

## Repo structure

Monorepo with two packages:
- `frontend/` — React 19 + Vite 8 + TypeScript 6 + MUI 7 + React Router 7 + TanStack Query 5 + Zustand 5 + Zod + React Hook Form
- `backend/` — Laravel 13 + PHP 8.3 + PostgreSQL 16 + Redis 7

Infra: `docker-compose.yml` (PostgreSQL port 5434, Redis port 6379). DB name `armora_erp`, user `armora`.

## Key commands

### Frontend (`frontend/`)
| Command | Description |
|---|---|
| `npm run dev` | Vite dev server on port **5175** |
| `npm run build` | `tsc -b && vite build` |
| `npm run lint` | ESLint |

### Backend (`backend/`)
| Command | Description |
|---|---|
| `composer dev` | Runs artisan serve (port 8005), queue worker, pail logs, Vite concurrently |
| `composer test` | Clears config then runs PHPUnit |
| `composer setup` | Fresh project setup (composer install, .env, key:generate, migrate, npm build) |
| `php artisan migrate` | Run DB migrations |
| `php artisan serve` | Dev server |

The backend `composer.json` has `"dev"` and `"test"` scripts (not npm scripts).

## Architecture

### Backend
- **Module-based** under `app/Modules/`. Each module: `Http/Controllers/`, `Http/Requests/`, `Http/Resources/`, `Services/`, `Models/`.
- Active modules: Auth, Catalog, Customers, Products.
- Routes in `routes/api.php` under `auth:sanctum` middleware.
- Token auth via Sanctum (Bearer token in `Authorization` header).
- RBAC via Spatie Laravel Permission (`dim_rol`, `dim_permiso` tables).
- Database: raw SQL migrations in `database/migrations/` (not Laravel's standard migration classes).

### Frontend
- Entry: `src/main.tsx` → `src/App.tsx`.
- Router: `/login`, `/admin/*` (protected by `ProtectedRoute`), `/*` catch-all.
- Admin layout with lazy-loaded modules: Customers, Products (Sales/Inventory/Logistics are placeholder).
- Auth state via Zustand store (`useAuthStore`), token in `localStorage('auth_token')`.
- API client via Axios (`shared/api/client.ts`), base URL from `VITE_API_BASE_URL` env var or `/api` default.
- Dev proxy: `/api` → `http://localhost:8005` (vite.config.ts).
- Types in `shared/types/index.ts`.

### Database
- PostgreSQL 16 with dimensional schema (SUNAT-compatible catalogs).
- `dim_*` tables: moneda, unidad_medida, pais, rol, permiso, departamento, provincia, ubigeo, etc.
- Tables created via raw SQL in `database/migrations/001_dim_tables.sql`.

## Testing

- **Backend only**: PHPUnit via `composer test`.
- **Frontend**: No test framework detected; no test runner configured.

## Linting/Formatting

- **Frontend**: ESLint (`npm run lint`).
- **Backend**: Laravel Pint (`vendor/bin/pint`).
- TypeScript: strict mode via `tsc -b`.

## Conventions

- Backend uses constructor injection (typed readonly properties).
- Frontend uses `@/` path alias for `src/`.
- API endpoints are snake_case (`tipo-afeccion-igv`, `unidades-medida`).
- Frontend types mirror backend resources (snake_case fields, `id` as `number`).
- All UI text is in Spanish (ERP for Peruvian market).
- Styles through MUI `sx` prop or `styled`, no CSS modules.
