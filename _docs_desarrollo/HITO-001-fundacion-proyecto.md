# Log de Desarrollo — ARMORA NextGen

**Proyecto:** Re-implementación de ARMORA bajo Arquitectura Laravel 12 + React 19  
**Fecha de inicio:** Junio 2026  
**Repositorio:** `armora-sac`  
**Arquitecto:** Senior Fullstack ERP Architect  
**Estado general:** 🟡 En Desarrollo (Fase 0 — Fundaciones)

---

## 📋 Hito 001: Fundación del Proyecto

**Fecha:** 2026-06-03  
**Estado:** ✅ Completo

### Alcance

Configuración inicial del proyecto frontend y definición del esquema de base de datos dimensional.

### Entregables

#### Frontend (React 19 + TypeScript + Vite)

| Componente | Archivo | Estado |
|---|---|---|
| Proyecto Vite + React + TypeScript | `frontend/` | ✅ |
| Dependencias instaladas (MUI, TanStack Query, React Router, Axios, Zod, Zustand) | `package.json` | ✅ |
| Tipos TypeScript compartidos (moneda, unidad_medida, pais, ubigeo, rol, permiso, etc.) | `src/shared/types/index.ts` | ✅ |
| API Client con Axios + interceptors | `src/shared/api/client.ts` | ✅ |
| Endpoints de API catalogados | `src/shared/api/endpoints.ts` | ✅ |
| Store de autenticación (Zustand) | `src/shared/hooks/useAuth.ts` | ✅ |
| Tema MUI para Admin ERP | `src/shared/theme.ts` | ✅ |
| Componente ProtectedRoute | `src/shared/components/ProtectedRoute.tsx` | ✅ |
| Página de Login | `src/shared/components/LoginPage.tsx` | ✅ |
| Página 404 | `src/shared/components/NotFoundPage.tsx` | ✅ |
| Layout Admin (Drawer + AppBar + navegación) | `src/Admin/layouts/AdminLayout.tsx` | ✅ |
| Página Dashboard (cards de KPIs) | `src/Admin/pages/DashboardPage.tsx` | ✅ |
| Layout Portal (AppBar + Footer responsive) | `src/Portal/layouts/PortalLayout.tsx` | ✅ |
| Router principal con 7 rutas implementadas | `src/App.tsx` | ✅ |
| Estructura Admin (layouts, pages, components) | `src/Admin/` | ✅ |
| Estructura Portal (layouts, pages, components) | `src/Portal/` | ✅ |

#### Base de Datos (PostgreSQL 16)

| Componente | Archivo | Estado |
|---|---|---|
| Migración completa de tablas dimensionales (24 tablas) | `database/migrations/001_dim_tables.sql` | ✅ |
| Seed data oficial SUNAT (moneda, unidad_medida, pais, igv, isc, etc.) | Incluido en migración | ✅ |
| Seed data de negocio (roles, permisos, tipos de cliente, etc.) | Incluido en migración | ✅ |
| Seed data geográfica (departamentos, provincias, ubigeo Lima) | Incluido en migración | ✅ |

### Tablas Creadas (24 tablas dimensionales)

#### Nivel 0 (sin dependencias) — 7 tablas

| Tabla | Registros | Fuente |
|---|---|---|
| `dim_moneda` | 2 | SUNAT Cat. 02 (ISO 4217) |
| `dim_unidad_medida` | 49 | SUNAT Cat. 03 (UN/ECE rec 20) |
| `dim_pais` | 22 | SUNAT Cat. 04 (ISO 3166-1) |
| `dim_dia_semana` | 7 | — |
| `dim_rol` | 11 | Diseño RBAC (Spatie compatible) |
| `dim_permiso` | 23 | Diseño RBAC (Spatie compatible) |
| `dim_documento_simbolo` | 9 | SUNAT Cat. 01 |

#### Nivel 1 (con dependencias) — 17 tablas

| Tabla | Dependencia | Registros | Fuente |
|---|---|---|---|
| `dim_departamento` | `dim_pais` | 25 | SUNAT Cat. 13 / INEI |
| `dim_provincia` | `dim_departamento` | 45 (de 196) | SUNAT Cat. 13 / INEI |
| `dim_ubigeo` | `dim_pais + dim_departamento + dim_provincia` | 36 (de 1874) | SUNAT Cat. 13 / INEI |
| `dim_tipo_afeccion_igv` | — | 19 | SUNAT Cat. 07 |
| `dim_tipo_calculo_isc` | — | 3 | SUNAT Cat. 08 |
| `dim_nota_credito_tipo` | — | 10 | SUNAT Cat. 09 |
| `dim_segmento_sunat` | — | 7 | Negocio |
| `dim_tipo_cliente` | — | 6 | Negocio |
| `dim_familia_sunat` | — | 10 | SUNAT |
| `dim_clase_sunat` | `dim_familia_sunat` | 8 | SUNAT |
| `dim_producto_clase` | — | 7 | Negocio |
| `dim_producto_subclase` | `dim_producto_clase` | 10 | Negocio |
| `dim_tipo_venta` | — | 6 | Negocio |
| `dim_tipo_compra` | — | 6 | Negocio |
| `dim_documento_tipo` | `dim_documento_simbolo` | 9 | SUNAT Cat. 01 |
| `dim_tipo_cambio` | `dim_moneda` (x2) | 4 | — |
| `dim_lista_precios` | `dim_moneda` | 4 | Negocio |

### Decisiones Técnicas (ADRs)

#### ADR-001: Prefijo `dim_` para tablas dimensionales

- **Contexto:** Se requiere diferenciar tablas dimensionales de tablas transaccionales.
- **Decisión:** Todas las tablas de catálogos maestros llevan prefijo `dim_`. Las tablas transaccionales (venta, compra, etc.) se crearán sin prefijo en sprints posteriores.
- **Consecuencia:** Compatible con Spatie Permission configurando `table_names` en `config/permission.php`.

#### ADR-002: Spatie Laravel Permission para RBAC

- **Contexto:** El sistema requiere ~480+ permisos distribuidos en roles.
- **Decisión:** `dim_rol` y `dim_permiso` siguen la estructura de Spatie (`name`, `guard_name`), con columnas adicionales en español (`descripcion`, `modulo`).
- **Consecuencia:** Migrar las tablas con Spatie configurado para usar nombres personalizados.

#### ADR-003: Dual-Frontend Architecture

- **Contexto:** El sistema legacy ARMORA solo tiene interfaz de admin interno; se requiere portal cliente/proveedor.
- **Decisión:** Dos SPA en el mismo bundle (Admin + Portal) con layouts, temas y rutas diferenciados, compartiendo tipos, hooks y API client.
- **Consecuencia:** Código compartido en `src/shared/`, código específico en `src/Admin/` y `src/Portal/`.

### Pendientes para Siguiente Hito

| Tarea | Prioridad | Dependencia |
|---|---|---|
| Completar seed de provincias (196 total) y ubigeos (1874 total) | 🟡 Media | — |
| Crear backend Laravel con migrations, models y controllers | 🔴 Alta | Hito 001 |
| Implementar autenticación JWT (Sanctum) + login real | 🔴 Alta | Backend Laravel |
| Módulo de Clientes (CRUD + validación DNI/RUC) | 🔴 Alta | Backend Laravel |
| Módulo de Productos (catálogo + IGV/ISC) | 🔴 Alta | Backend Laravel |
| Pruebas E2E con Playwright | 🟢 Baja | Frontend completo |

---

*Documento generado: 2026-06-03*  
*Próxima revisión: Al completar Hito 002*
