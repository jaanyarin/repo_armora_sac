# ARMORA - Documentación de Análisis y Re-Implementación

**Fecha:** Junio 2026  
**Proyecto:** Modernización de ARMORA SAC (armorasac.com/app)  
**Status:** ✅ Análisis Completado | 🔄 Validación Pendiente | ⏳ Implementación  

---

## 🚀 ¿POR DÓNDE EMPIEZO?

### ⏱️ Lectura Rápida (5 minutos)
1. Lee este README
2. Abre: `01_analisis_tecnico/RESUMEN_EJECUTIVO.md`
3. Luego: `01_analisis_tecnico/CHECKLIST_STAKEHOLDERS.md` (firma requerida)

### ⏱️ Lectura Completa (1-2 horas)  
Consulta `RESUMEN_DOCUMENTACION_GENERADA.md` para ruta por rol (PM, Arquitecto, Dev, etc.)

### ⏱️ Acción Inmediata
Distribuir a stakeholders: `01_analisis_tecnico/CHECKLIST_STAKEHOLDERS.md` (ESTA SEMANA)

---

## 📋 Estructura de Documentación

Este directorio contiene **documentación técnica exhaustiva** para la modernización de ARMORA:
- ✅ **Análisis Profundo** del sistema actual (jQuery + Java + PostgreSQL)
- ✅ **Arquitectura Propuesta** (Laravel 12 + React 19 + PostgreSQL 16)
- ✅ **Especificación Completa** de módulos, APIs, datos
- ✅ **Plan de Migración** (Strangler Fig, 4-6 meses, sin downtime)
- 🔄 **Validaciones Pendientes** (8 decisiones críticas)

### Carpetas Principales

#### 1. `01_analisis_tecnico/`
Análisis técnico profundo del sistema actual (ARMORA legacy) comparado con el análisis anterior.
- **frontend-analysis.md** - Análisis detallado del frontend (jQuery, Semantic UI)
- **backend-analysis.md** - Análisis detallado del backend (Java, Servlets)
- **database-analysis.md** - Inferencia de modelo de datos y estructura
- **api-analysis.md** - Mapeo completo de los 60+ endpoints REST
- **comparison-with-previous.md** - Comparación y validación con análisis previo
- **security-assessment.md** - Evaluación detallada de seguridad

#### 2. `02_arquitectura_datos/`
Modelo de datos actual y propuesto.
- **er-diagram-current.md** - Diagrama ER del sistema actual (inferido)
- **er-diagram-proposed.md** - Diagrama ER del sistema propuesto (Laravel/PostgreSQL)
- **dimension-tables.md** - Tablas de dimensión (catálogos maestros)
- **fact-tables.md** - Tablas de hechos (transaccionales)
- **data-relationships.md** - Relaciones entre tablas y constraints
- **database-schema.sql** - DDL propuesto para PostgreSQL

#### 3. `03_mapa_funcionalidades/`
Mapeo detallado de funcionalidades del sistema.
- **modulos-overview.md** - Vista general de módulos (Auth, Sales, Inventory, etc.)
- **auth-module.md** - Módulo de Autenticación y Autorización
- **sales-module.md** - Módulo de Ventas y Documentos
- **inventory-module.md** - Módulo de Inventario y Almacenes
- **customers-module.md** - Módulo de Clientes
- **products-module.md** - Módulo de Productos
- **finance-module.md** - Módulo de Finanzas y SUNAT
- **logistics-module.md** - Módulo de Logística y Rutas
- **loyalty-module.md** - Módulo de Canje y Premios
- **features-matrix.md** - Matriz de características (actual vs propuesto)

#### 4. `04_migracion_estrategia/`
Estrategia y plan de migración.
- **migration-approach.md** - Estrategia Strangler Fig para migración sin downtime
- **phasing-plan.md** - Fases de migración y timeline
- **data-migration-plan.md** - Plan de migración de datos
- **legacy-to-modern-mapping.md** - Mapeo de endpoints legacy → nuevos

#### 5. `05_especificaciones_tecnicas/`
Especificaciones técnicas del nuevo sistema.
- **stack-tecnico.md** - Stack completo: PHP 8.3, Laravel 12, React 19, PostgreSQL 16
- **architecture-decisions.md** - ADRs (Architecture Decision Records)
- **design-patterns.md** - Patrones de diseño (Service Layer, Repository, Events)
- **api-specification.md** - Especificación de API REST
- **frontend-architecture.md** - Arquitectura del frontend (Admin + Portal)
- **authentication-authorization.md** - Sistema de autenticación y RBAC

#### 6. `06_api_endpoints/`
Documentación detallada de endpoints.
- **auth-endpoints.md** - Autenticación (login, logout, refresh)
- **customer-endpoints.md** - Clientes (CRUD, búsqueda)
- **product-endpoints.md** - Productos (catálogo, precios)
- **sales-endpoints.md** - Ventas (crear, consultar, estado)
- **inventory-endpoints.md** - Inventario (stock, movimientos)
- **finance-endpoints.md** - Finanzas (SUNAT, libros contables)
- **dashboard-endpoints.md** - Dashboards y reportes

#### 7. `07_seguridad_compliance/`
Seguridad y compliance.
- **security-improvements.md** - Mejoras de seguridad respecto a legacy
- **sunat-compliance.md** - Requisitos de facturación electrónica SUNAT
- **data-protection.md** - Protección de datos y privacidad
- **penetration-testing-checklist.md** - Checklist de testing de seguridad

---

## 🔄 Comparación con Análisis Anterior

El archivo `01_analisis_tecnico/comparison-with-previous.md` contiene la comparación detallada entre:
- **Análisis anterior** (`_perfiles_tecnicos/analisis-armorasac-app.md`)
- **Análisis profundo actual** (resultado del scraping y análisis técnico)

---

## 🏗️ Arquitectura Propuesta

El sistema será re-implementado bajo el stack definido en `_perfiles_tecnicos/senior-fullstack-erp-architect_v2.md`:

### Backend
- **Lenguaje:** PHP 8.3 (tipado estricto)
- **Framework:** Laravel 12
- **ORM:** Eloquent ORM + Query Builder
- **BD:** PostgreSQL 16
- **Cache/Queues:** Redis 7
- **Autenticación:** Sanctum (JWT) + Spatie Permission (RBAC)

### Frontend (Admin ERP)
- **Framework:** React 19 + Vite
- **Lenguaje:** TypeScript (strict mode)
- **UI Library:** Material UI 6
- **Estado:** TanStack Query + Zustand/Context API
- **Formularios:** React Hook Form + Zod

### Frontend (Portal Cliente/Proveedor)
- **Framework:** React 19 + Vite (mismo bundle que Admin)
- **Enfoque:** Mobile-first, PWA
- **Notificaciones:** Laravel Broadcasting + WebPush

### Infraestructura
- **Servidor:** Nginx + PHP-FPM
- **Contenedores:** Docker + Docker Compose (dev) / Kubernetes (prod)
- **CI/CD:** GitHub Actions
- **Monitoreo:** Laravel Pulse + Sentry

---

## 📝 Próximos Pasos

1. **Análisis Técnico Completo** (01_analisis_tecnico/)
   - Scraping detallado y mapeo de todas las funcionalidades
   - Análisis de seguridad
   - Comparación con análisis anterior

2. **Diseño de Arquitectura de Datos** (02_arquitectura_datos/)
   - Diseño del schema PostgreSQL
   - Tablas de dimensión y hechos
   - Relaciones y constraints

3. **Mapeo de Funcionalidades** (03_mapa_funcionalidades/)
   - Documentación de cada módulo
   - Matriz de características

4. **Plan de Migración** (04_migracion_estrategia/)
   - Estrategia Strangler Fig
   - Timeline y fases

5. **Especificaciones Técnicas** (05_especificaciones_tecnicas/)
   - Decisiones arquitectónicas
   - Patrones de diseño
   - Especificación de API

---

## 🔍 Notas Importantes

### Validaciones Pendientes

Antes de proceder con la implementación, se deben validar los siguientes puntos:

- [ ] **Stack de SUNAT:** Confirmar uso de Greenter 5.x vs LibPHP SUNAT
- [ ] **Integración de Pagos:** Stripe vs MercadoPago vs Izipay (confirmar proveedor actual)
- [ ] **Modelo de Moneda:** Sistema multi-moneda vs mono-moneda (PEN)
- [ ] **Estructura de Empresa:** Soporte para multi-empresa desde el inicio
- [ ] **Detracciones:** Implementación de detracciones (incluidas en módulo Finance)
- [ ] **Retenciones:** Cálculo automático de retenciones (incluido en módulo Finance)
- [ ] **Consulta RUC/DNI:** Integración con API SUNAT para validación

### Supuestos Técnicos

1. **Base de Datos:** Se asume PostgreSQL como BD principal (no confirmado, requiere validación)
2. **Multi-empresa:** Sistema diseñado para soportar múltiples empresas desde el inicio
3. **Roles/Permisos:** Implementación RBAC con Spatie Laravel Permission (480+ permisos)
4. **PWA Portal Cliente:** Portal cliente tendrá capacidad PWA (offline support)
5. **WebSockets:** Notificaciones en tiempo real mediante Laravel Broadcasting

---

## 📚 Referencias

- **Análisis Anterior:** `_perfiles_tecnicos/analisis-armorasac-app.md`
- **Arquitectura Propuesta:** `_perfiles_tecnicos/senior-fullstack-erp-architect_v2.md`
- **Perfil de Scraping:** `_perfiles_tecnicos/senior-web-intelligence-scraping-engineer_v1.md`

---

**Responsable:** Senior Web Intelligence & Scraping Engineer + Senior Fullstack ERP Architect  
**Última Actualización:** Junio 2026  
**Estado:** 🟡 En Progreso
