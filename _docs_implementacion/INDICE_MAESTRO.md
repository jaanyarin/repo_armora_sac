# Índice Maestro de Documentación - ARMORA Modernización

> [!IMPORTANT]
> **Estado real al 2026-06-04:** Este índice fue corregido tras la auditoría **C-01** (el documento original publicitaba 35+ archivos pero solo existían 15).
>
> **Total de documentos presentes en disco:** 15 archivos en `_docs_implementacion/` + 5 documentos operativos (1 raíz + 4 en `_docs_desarrollo/`).
>
> **Documentos faltantes (marcados como [NO IMPLEMENTADO]):** ver sección "Pendientes de creación" al final.

**Documento Maestro:** Índice central de toda la documentación generada  
**Total de Documentos en disco:** 20 (15 en `_docs_implementacion/` + 1 AGENTS.md + 4 en `_docs_desarrollo/`)  
**Fecha:** Junio 2026  
**Estado:** 🟡 COMPLETO (15 docs reales) + pendientes (20 docs por crear)

---

## 🚀 ACCESO RÁPIDO POR NECESIDAD

### "Necesito un overview (5 minutos)"
```
📄 RESUMEN_EJECUTIVO.md  (raíz de _docs_implementacion/01_analisis_tecnico/)
```

### "Necesito entender la arquitectura vigente (1 hora)"
```
📄 AGENTS.md  (raíz del repo)
📄 _perfiles_tecnicos/senior-fullstack-erp-architect_v3.md
📄 _docs_desarrollo/HITO-001-fundacion-proyecto.md
📄 _docs_desarrollo/HITO-002-customers-products.md
📄 _docs_desarrollo/HITO-003-sales-inventory.md
```

### "Necesito conocer el estado y roadmap"
```
📄 AGENTS.md  (raíz del repo) — hoja de ruta y métricas
📄 _perfiles_tecnicos/senior-fullstack-erp-architect_v3.md — guía operativa del proyecto
```

### "Necesito conocer los endpoints API actuales"
```
📄 06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md
```

---

## 📚 ÍNDICE REAL POR CARPETA (15 archivos en disco)

### 📂 Raíz `_docs_implementacion/`

| Archivo | Propósito | Audiencia | Tiempo |
|---|---|---|---|
| **README.md** | Guía de inicio rápido | Todos | 5 min |
| **COMIENZA_AQUI.md** | Punto de entrada al proyecto | Todos | 5 min |
| **INDICE_DOCUMENTACION.md** | Índice por rol y tema | Todos | 10 min |
| **INDICE_MAESTRO.md** | Este archivo | Todos | 5 min |
| **REORGANIZACION_MENU_SIDEBAR.md** | Reorganización UI del sidebar Admin | Frontend | 5 min |
| **RESUMEN_DOCUMENTACION_GENERADA.md** | Estadísticas y resumen histórico | Todos | 10 min |

---

### 📂 `01_analisis_tecnico/` - Análisis Profundo (6 documentos)

| Archivo | Descripción | Para Quién | Tamaño | Prioridad |
|---|---|---|---|---|
| **RESUMEN_EJECUTIVO.md** | Overview 1-página con ROI, timeline, presupuesto | PM, C-Level | 5 KB | 🔴 P0 |
| **CHECKLIST_STAKEHOLDERS.md** | Validación pre-implementación (firmas) | Stakeholders | 8 KB | 🔴 P0 |
| **VALIDACIONES_PENDIENTES.md** | 8 decisiones críticas con checklist | CTO, PM | 10 KB | 🔴 P0 |
| **frontend-analysis.md** | jQuery 3.4.1, Semantic UI, vulnerabilidades | Dev Frontend, Arch | 12 KB | 🟡 P1 |
| **backend-analysis.md** | Java, 60 endpoints, auth, seguridad | Dev Backend, Arch | 15 KB | 🟡 P1 |
| **comparison-with-previous.md** | Validación (90% confirmado, 10% nuevo) | Tech Lead, Arch | 12 KB | 🟢 P2 |

**Subtotal:** 6 documentos (de 8 anunciados originalmente, 2 no creados)

---

### 📂 `02_arquitectura_datos/` - Modelo de Datos (2 documentos)

| Archivo | Descripción | Para Quién |
|---|---|---|
| **database-analysis.md** | Análisis completo (volumen, índices, optimization) | DBA, Arch |
| **tabla-creation-dependency-order.md** | Orden de creación de tablas por dependencias FK | DBA |

**Subtotal:** 2 documentos (de 7 anunciados, 5 no creados)

---

### 📂 `06_api_endpoints/` - Especificación de APIs (1 documento)

| Archivo | Descripción |
|---|---|
| **ENDPOINTS_COMPLETE_MAPPING.md** | 60+ endpoints actuales mapeados (referencia legacy) |

**Subtotal:** 1 documento (de 8 anunciados, 7 no creados)

---

## 📚 DOCUMENTACIÓN OPERATIVA (no está en _docs_implementacion/)

### Raíz del repositorio

| Archivo | Propósito | Estado |
|---|---|---|
| **AGENTS.md** | Guía operativa + comandos + estado del proyecto | ✅ Actualizado al Hito 003 |
| **_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md** | Guía operativa del proyecto (3 versiones: v1 genérico, v2 refinado, v3 estado real) | ✅ Corregido en auditoría C-01 |

### `_docs_desarrollo/`

| Archivo | Propósito | Estado |
|---|---|---|
| **HITO-001-fundacion-proyecto.md** | Log del Hito 001 (fundación) | ✅ Existe |
| **HITO-002-customers-products.md** | Log del Hito 002 (CRUD básico) | ✅ Existe |
| **HITO-003-sales-inventory.md** | Log del Hito 003 (Sales + Inventory + Portal) | ✅ Creado en Hito 003 |
| **ADR-008-no-hardcode-credenciales-tests.md** | ADR-008: convención de tests sin credenciales hardcoded | ✅ Creado en Hito 003 |

---

## 🔍 BÚSQUEDA POR PROBLEMA

### "¿Cómo migro del sistema actual?"
1. `01_analisis_tecnico/backend-analysis.md`
2. `06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md`
3. `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md` → sección roadmap

### "¿Cuáles son los endpoints actuales?"
1. `06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md`

### "¿Cuál es el estado del proyecto?"
1. `AGENTS.md` (raíz)
2. `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md`
3. `_docs_desarrollo/HITO-003-sales-inventory.md`

### "¿Cómo levantarlo en dev?"
1. `AGENTS.md` → sección "Comandos clave"
2. `docker-compose.yml` (raíz)
3. `.env` y `.env.example` (backend)

### "¿Cómo es la arquitectura backend?"
1. `AGENTS.md` → sección "Arquitectura backend"
2. `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md` → sección 3

### "¿Cómo es la arquitectura frontend?"
1. `AGENTS.md` → sección "Arquitectura frontend"
2. Frontend modules en `frontend/src/`

---

## 📊 ESTADÍSTICAS REALES

```
Documentos en _docs_implementacion/:    15 archivos reales
Documentos operativos en raíz:         2 (AGENTS.md + v3 del perfil)
Documentos en _docs_desarrollo/:        4 (3 hitos + 1 ADR)
─────────────────────────────────────────────────────────
TOTAL EN DISCO:                        21 documentos

Anunciados originalmente:              35+ documentos
Pendientes de creación:                 20 documentos (ver abajo)
```

---

## ⏳ PENDIENTES DE CREACIÓN (auditoría C-01)

Documentos anunciados en el índice original que **NO existen** en disco:

### `01_analisis_tecnico/`
- ❌ `security-assessment.md` (P1)

### `02_arquitectura_datos/`
- ❌ `database-schema.sql`
- ❌ `er-diagram-current.md`
- ❌ `er-diagram-proposed.md`
- ❌ `dimension-tables.md`
- ❌ `fact-tables.md`
- ❌ `data-relationships.md`

### `03_mapa_funcionalidades/` (carpeta completa no existe)
- ❌ `modulos-overview.md`
- ❌ `features-matrix.md`
- ❌ `auth-module.md`, `customers-module.md`, `products-module.md`
- ❌ `sales-module.md`, `purchases-module.md`, `inventory-module.md`
- ❌ `finance-module.md`, `logistics-module.md`, `loyalty-module.md`

### `04_migracion_estrategia/` (carpeta completa no existe)
- ❌ `migration-approach.md`
- ❌ `phasing-plan.md`
- ❌ `data-migration-plan.md`
- ❌ `legacy-to-modern-mapping.md`

### `05_especificaciones_tecnicas/` (carpeta completa no existe)
- ❌ `stack-tecnico.md`
- ❌ `architecture-decisions.md`
- ❌ `design-patterns.md`
- ❌ `frontend-architecture.md`
- ❌ `backend-architecture.md`
- ❌ `authentication-authorization.md`
- ❌ `api-specification.md`

### `06_api_endpoints/`
- ❌ `auth-endpoints.md`, `customer-endpoints.md`, `product-endpoints.md`
- ❌ `sales-endpoints.md`, `inventory-endpoints.md`, `finance-endpoints.md`
- ❌ `dashboard-endpoints.md`

### `07_seguridad_compliance/` (carpeta completa no existe)
- ❌ `security-improvements.md`
- ❌ `penetration-testing-checklist.md`
- ❌ `sunat-compliance.md`
- ❌ `data-protection.md`

**Decisión:** Estos 20 documentos NO se crearán hasta que sean necesarios (en hitos futuros como Hito 005 Finance, Hito 006 Cross-cutting). El índice ahora refleja la realidad del proyecto.

---

## ✅ CHECKLIST DE LECTURA

### Para Project Manager
```
[ ] AGENTS.md (10 min)
[ ] _perfiles_tecnicos/senior-fullstack-erp-architect_v3.md (15 min)
[ ] 01_analisis_tecnico/RESUMEN_EJECUTIVO.md (5 min)
[ ] _docs_desarrollo/HITO-003-sales-inventory.md (10 min)
Total: 40 minutos
```

### Para Solutions Architect
```
[ ] AGENTS.md (10 min)
[ ] _perfiles_tecnicos/senior-fullstack-erp-architect_v3.md (15 min)
[ ] 01_analisis_tecnico/backend-analysis.md (15 min)
[ ] 06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md (15 min)
[ ] _docs_desarrollo/HITO-001/002/003 (20 min)
Total: 75 minutos
```

### Para Backend Developer
```
[ ] AGENTS.md (10 min)
[ ] 01_analisis_tecnico/backend-analysis.md (15 min)
[ ] 06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md (15 min)
[ ] _docs_desarrollo/HITO-003-sales-inventory.md (10 min)
Total: 50 minutos
```

### Para Frontend Developer
```
[ ] AGENTS.md (10 min)
[ ] 01_analisis_tecnico/frontend-analysis.md (15 min)
[ ] frontend/src/ (explorar código)
Total: 30 minutos
```

### Para DBA
```
[ ] 02_arquitectura_datos/database-analysis.md (20 min)
[ ] 02_arquitectura_datos/tabla-creation-dependency-order.md (10 min)
[ ] backend/database/migrations/ (explorar)
Total: 40 minutos
```

---

## 🔎 BÚSQUEDA POR TECNOLOGÍA

### **Laravel + PHP**
- `AGENTS.md` (raíz)
- `backend/` (código fuente directo)

### **React + TypeScript**
- `AGENTS.md` (raíz)
- `frontend/src/` (código fuente directo)

### **PostgreSQL**
- `02_arquitectura_datos/database-analysis.md`
- `backend/database/migrations/` (DDL real ejecutado)

### **REST API**
- `06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md`
- `backend/routes/api.php` (rutas reales)

### **SUNAT Compliance**
- **Pendiente de creación** (no hay docs; no hay código; se implementa en Hito 003+)

### **Seguridad**
- **Pendiente de creación** (no hay docs específicos)

---

## 📅 FLUJO RECOMENDADO

### Día 1: Onboarding
1. Leer `AGENTS.md` (raíz)
2. Explorar `frontend/src/` y `backend/app/`
3. Revisar `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md`

### Semana 1: Contexto
1. Leer `_docs_desarrollo/HITO-001/002/003`
2. Revisar `01_analisis_tecnico/RESUMEN_EJECUTIVO.md`
3. Ejecutar `docker compose up` y probar `npm run dev`

### Semana 2+: Implementación
1. Seguir roadmap en `AGENTS.md` → "Hoja de ruta"
2. Para cada módulo, revisar el código en `backend/app/Modules/<Name>/`
3. Para cada UI, revisar el código en `frontend/src/Admin/pages/<Name>/`

---

## ❓ PREGUNTAS FRECUENTES

| Pregunta | Respuesta |
|---|---|
| ¿Por dónde empiezo? | `AGENTS.md` (raíz) |
| ¿Cuál es el stack? | `_perfiles_tecnicos/senior-fullstack-erp-architect_v3.md` sección 2 |
| ¿Cómo levanto el proyecto? | `AGENTS.md` → "Comandos clave" |
| ¿Qué módulos hay? | `AGENTS.md` → "Arquitectura backend" |
| ¿Cuál es el roadmap? | `AGENTS.md` → "Hoja de ruta" + v3 del perfil sección 5 |
| ¿Cómo es la API? | `06_api_endpoints/ENDPOINTS_COMPLETE_MAPPING.md` + `backend/routes/api.php` |
| ¿Cómo es la BD? | `02_arquitectura_datos/database-analysis.md` + migraciones reales en `backend/database/migrations/` |
| ¿Cómo es la arquitectura? | `AGENTS.md` → "Arquitectura backend" + "Arquitectura frontend" |
| ¿Cómo se valida? | `01_analisis_tecnico/VALIDACIONES_PENDIENTES.md` |

---

## 📝 HISTORIAL DE CAMBIOS

| Fecha | Cambio | Razón |
|---|---|---|
| 2026-06-04 | Corrección masiva del índice (35+ → 15 reales) | **Auditoría C-01**: el índice publicitaba documentos que no existían en disco |
| 2026-06-04 | Referencias actualizadas a AGENTS.md y v3 del perfil | Realinear el índice con la documentación operativa vigente |

---

*Índice Maestro - Última actualización: 2026-06-04 (post-auditoría C-01)*
