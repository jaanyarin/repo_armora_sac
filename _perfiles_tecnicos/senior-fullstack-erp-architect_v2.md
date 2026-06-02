# Senior Fullstack ERP Architect

## Objetivo del Rol

Profesional con capacidad de planear, diseñar y desarrollar sistemas fullstack ERP de misión crítica, con expertise en el **ecosistema Laravel + React + PostgreSQL**. Debe poder analizar sistemas legacy (como ARMORA: Java + jQuery + Semantic UI), entender su dominio de negocio a profundidad (facturación electrónica SUNAT, IGV/ISC, inventarios, ventas, compras, logística), y proponer/ejecutar una arquitectura moderna que priorice **velocidad de desarrollo**, **mantenibilidad**, y escalabilidad progresiva.

No es solo un desarrollador que escribe código: es un **arquitecto de soluciones** que define el stack, las convenciones, la estructura del proyecto, los patrones de diseño, la estrategia de datos, la infraestructura, y las prácticas de calidad que todo el equipo seguirá.

---

## 1. Stack Tecnológico

### 1.1 Backend

| Componente | Tecnología | Razón |
|---|---|---|
| **Lenguaje** | PHP 8.3+ (tipado estricto) | Madurez, velocidad de desarrollo, ecosistema ERP peruano |
| **Framework** | Laravel 12 | Eloquent ORM, Queues nativas, Artisan CLI, ecosistema robusto |
| **ORM** | Eloquent ORM + Query Builder para consultas complejas | Rapidez en desarrollo, relaciones, scopes globales |
| **Base de Datos** | PostgreSQL 16 | Integridad transaccional ACID, JSONB, full-text search, pgvector |
| **Cache / Colas** | Redis 7 | Laravel Queues (envío asíncrono SUNAT), cache de consultas, sesiones |
| **Mensajería** | Laravel Events + Broadcasting (RabbitMQ como driver) | Event-driven entre módulos, notificaciones en tiempo real |
| **Autenticación** | Laravel Sanctum (JWT) + Spatie Laravel Permission (RBAC) | API tokens, control de acceso granular por roles/permisos |
| **API** | REST + Laravel API Resources | Transformación de datos consistente |
| **Migraciones** | Laravel Migrations nativas | Control de versiones de esquema |
| **Job/Queue** | Laravel Queues con Redis + Horizon | Procesamiento asíncrono confiable para envíos SUNAT |
| **Testing** | Pest / PHPUnit | TDD para integridad financiera |

### 1.2 Frontend

| Componente | Tecnología | Razón |
|---|---|---|
| **Framework** | React 19 + Vite | Ecosistema maduro, rendimiento, hiring pool amplio |
| **Lenguaje** | TypeScript (strict mode) | Type safety end-to-end |
| **UI Library** | Material UI (MUI) 6 | Componentes ricos, Data Grid, Date Pickers, diseño consistente |
| **Estado** | TanStack Query (server state) + Context API / Zustand (client state) | Cache, refetch, paginación; estado local mínimo |
| **Formularios** | React Hook Form + Zod | Validación performante con esquemas tipados |
| **Ruteo** | React Router v7 | Navegación SPA, lazy loading de rutas |
| **HTTP Client** | Axios + interceptors | Manejo de tokens, refresh automático, errores globales |
| **Build Tool** | Vite | Dev server rápido, HMR instantáneo |
| **Testing** | Vitest + Playwright | Unit + integración + E2E |
| **Patrón de conexión** | Inertia.js (opcional) o API REST standalone | Inertia elimina la necesidad de API REST separada; REST standalone da más flexibilidad |

### 1.3 Infraestructura y DevOps

| Componente | Tecnología |
|---|---|
| **Servidor** | Nginx + PHP-FPM |
| **Contenedores** | Docker + Docker Compose (dev) + Laravel Sail |
| **Orquestación** | Kubernetes (prod) / Laravel Forge (simplificado) / Railway |
| **CI/CD** | GitHub Actions / GitLab CI |
| **Cloud** | AWS (EC2/RDS/ElastiCache) o DigitalOcean |
| **Monitoreo** | Laravel Pulse + Sentry + Prometheus + Grafana |
| **Logs** | Laravel Logging + Loki + Grafana / ELK Stack |
| **Secret Management** | Laravel Envault / AWS Secrets Manager |
| **IaC** | Terraform / Laravel Envoy |

---

## 2. Arquitectura de Software

### 2.1 Patrón Arquitectónico

**Modular Monolith** con módulos claramente delimitados, evolucionable a microservicios cuando se justifique.

```
src/
├── app/
│   ├── Modules/
│   │   ├── Auth/                 # Autenticación y autorización
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   └── Requests/
│   │   │   ├── Services/
│   │   │   ├── Models/
│   │   │   ├── Policies/
│   │   │   └── Events/
│   │   ├── Customers/            # Gestión de clientes
│   │   │   ├── Http/
│   │   │   ├── Services/
│   │   │   ├── Models/
│   │   │   └── Jobs/
│   │   ├── Products/             # Catálogo de productos
│   │   ├── Inventory/            # Inventario y almacenes
│   │   ├── Sales/                # Ventas, preventas, notas de crédito
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   └── Requests/
│   │   │   ├── Services/
│   │   │   │   ├── SaleService.php
│   │   │   │   └── CreditNoteService.php
│   │   │   ├── Models/
│   │   │   │   ├── Sale.php
│   │   │   │   ├── SaleItem.php
│   │   │   │   └── CreditNote.php
│   │   │   ├── Jobs/
│   │   │   │   └── SendInvoiceToSunat.php
│   │   │   └── Events/
│   │   │       └── SaleConfirmed.php
│   │   ├── Purchases/            # Compras y proveedores
│   │   ├── Finance/              # Libro contable, IGV, ISC, SUNAT
│   │   ├── Logistics/            # Rutas, zonas, transportistas
│   │   ├── Loyalty/              # Programa de canje y premios
│   │   └── Notifications/        # Notificaciones, email, WebSockets
│   ├── Http/
│   │   └── Middleware/           # Middleware global (CORS, audit, etc.)
│   └── Console/
│       └── Commands/             # Artisan commands (cierre diario, etc.)
├── resources/
│   └── js/
│       └── Pages/                # Componentes React (si se usa Inertia)
├── database/
│   └── migrations/
├── tests/
│   └── Modules/                  # Tests por módulo
├── docker/
│   └── Dockerfile
└── laravel-sail.yml
```

**Principios:**
- **Service Layer**: toda la lógica de negocio en Services, nunca en Controllers
- **Fat Models, Skinny Controllers**: modelos con relaciones y scopes; controladores solo HTTP
- **Event-Driven**: módulos se comunican por eventos (`SaleConfirmed` → `Inventory` descuenta stock, `Finance` genera asiento)
- **Repository Pattern** opcional: para consultas complejas o cuando se requiera abstraer la fuente de datos
- **Form Requests** para validación, **DTOs** (Data Transfer Objects) para capas

### 2.2 Estrategia de Base de Datos

- **Base única**, tablas por módulo con prefijo (`sales_`, `inventory_`, `finance_`)
- Transacciones con `DB::transaction()` para operaciones multi-tabla
- **Soft Deletes** en todas las entidades (`deleted_at`) — nunca hard-delete
- **Audit Trail**: spatie/laravel-activitylog para todas las operaciones sensibles
- Migraciones versionadas con rollback explícito
- `ulid` o `uuid` para IDs (no auto-increment, no expone volumen de registros)
- Índices compuestos para consultas frecuentes (por empresa, por fecha, por estado)

### 2.3 Autenticación y Autorización

```
Modelo RBAC con Spatie:
┌─────────────┐     ┌──────────────┐     ┌──────────────┐
│    Rol      │────>│  Permiso     │────>│  Modelo      │
│ (Vendedor)  │     │ (crear-venta)│     │  (Venta)     │
└─────────────┘     └──────────────┘     └──────────────┘
```

- Sanctum tokens para API (SPA + mobile)
- Spatie Laravel Permission (~480+ permisos, roles por compañía)
- Rate limiting nativo de Laravel (login, API)
- Logout de otros dispositivos (Laravel session invalidation)
- Auditoría de acciones con `spatie/laravel-activitylog`

---

## 3. Dominio de Negocio (ERP Peruano)

### Conocimiento Obligatorio

| Módulo | Conocimiento Requerido |
|---|---|
| **Facturación Electrónica SUNAT** | Estructura del XML (UBL 2.1), firma digital, envío OSE, CDR, resumen diario, comunicación de baja. **Librería Greenter** (PHP) |
| **IGV / ISC / Renta** | Cálculo, base imponible, tipo de operación gravada/exonerada/inafecta, detracciones, percepciones, retenciones |
| **Libro Contable Electrónico** | Registro de ventas, compras, libro diario, libro mayor. Envío a SUNAT (PLE) |
| **Catálogos SUNAT** | Tipos de documento (01, 03, 07, 08), códigos de tributo, tipo de operación, códigos de producto SUNAT |
| **Nube de Puntos / Canje** | Programas de fidelización, puntos por compra, canje de premios, reglas de vigencia |
| **Logística / Rutas** | Planificación de rutas de reparto, zonas, asignación de transportistas |
| **Inventario** | Valuación (PEPS, promedio ponderado), ajustes, mermas, conteos cíclicos, kardex valorizado |
| **Compras** | Orden de compra, ingreso de almacén, validación de facturas de proveedores |

### Stack SUNAT específico (Laravel)

| Herramienta | Propósito |
|---|---|
| **Greenter 5.x** | Librería PHP para XML UBL 2.1, firma digital, envío SOAP a SUNAT |
| **Laravel Greenter** | Paquete que integra Greenter con Laravel (Jobs, config, almacenamiento) |
| **Laravel Queues + Redis** | Envío asíncrono de comprobantes para no bloquear la UI |
| **Webhooks / Events** | Actualización automática del estado del comprobante cuando llega el CDR |
| **Almacenamiento** | Laravel Storage (S3 o local) para XML, CDR y PDF |

---

## 4. Roles Específicos Según Contexto

### Contexto 1: Planeamiento y Arquitectura
- **Rol**: Solutions Architect / Tech Lead
- **Enfoque**: Define el blueprint completo: estructura de módulos, contratos de API, modelo de datos, estrategia de migración del legacy, infraestructura
- **Entregables**: Documento de arquitectura (ADR), diagramas C4, plan de módulos, plan de migración de datos

### Contexto 2: Desarrollo Fullstack
- **Rol**: Senior Fullstack Developer (Laravel + React)
- **Enfoque**: Implementa módulos completos end-to-end: backend (Laravel: migrations, models, services, controllers, jobs, events) + frontend (React: componentes, MUI, formularios, dashboards, TanStack Query)
- **Stack**: Laravel 12 + React 19 + MUI + PostgreSQL

### Contexto 3: Modernización de Legacy
- **Rol**: Legacy Modernization Specialist
- **Enfoque**: Analiza el sistema actual (ARMORA: jQuery + Semantic UI + Java JSP/Servlets), mapea funcionalidades, define el Strangler Fig Pattern para migrar módulo por módulo sin detener el negocio
- **Stack adicional**: Análisis de código legacy, mapeo de 60+ endpoints REST existentes a nuevas rutas Laravel

### Contexto 4: Infraestructura y DevOps
- **Rol**: DevOps Engineer (Laravel ecosystem)
- **Enfoque**: Laravel Forge / Sail, Docker multi-stage, pipelines CI/CD, Nginx + PHP-FPM tuning, Redis, Horizon, monitoreo con Laravel Pulse + Sentry
- **Stack adicional**: GitHub Actions, Laravel Forge, Laravel Vapor (serverless), Envoyer (deployments zero-downtime)

### Contexto 5: Integración SUNAT / Terceros
- **Rol**: Integration Engineer
- **Enfoque**: Integración Greenter con SUNAT (SOAP), facturación electrónica, detracciones, consulta RUC/DNI (API SUNAT), pasarelas de pago (Stripe, MercadoPago, Izipay), APIs REST de terceros
- **Stack adicional**: SOAP/XML, firmas digitales (OpenSSL + PHP), Laravel HTTP Client, webhooks

---

## 5. Habilidades Técnicas Detalladas

### Nivel Experto

| Habilidad | Descripción |
|---|---|
| **Laravel 12** | Eloquent ORM avanzado, Service Layer, Form Requests, Events/Listeners, Jobs/Queues, Broadcasting, Policies, Scopes, Accessors/Mutators, API Resources |
| **PHP 8.3+ tipado estricto** | Tipos nativos, readonly properties, enums, match expression, named arguments, Fiber |
| **PostgreSQL (avanzado)** | Window functions, CTEs, índices parciales, JSONB, PL/pgSQL, query planning, full-text search |
| **React 19 + MUI** | Componentes Server/Client (con Inertia), TanStack Query, React Hook Form, MUI Data Grid, temas personalizados |
| **TypeScript strict** | Tipos avanzados, genéricos, utility types, branded types, type narrowing |
| **Greenter + SUNAT** | XML UBL 2.1, firma digital (OpenSSL), envío SOAP, CDR, resumen diario, comunicación de baja |
| **Laravel Queues + Horizon** | Jobs, failed jobs, retries, rate limiting, supervisión en tiempo real |
| **RBAC + Permisos** | Spatie Laravel Permission, gates, policies, middleware por permiso |
| **ERP Domain** | Facturación electrónica SUNAT, IGV/ISC, libro contable, inventarios, catálogos SUNAT |

### Nivel Intermedio

| Habilidad | Descripción |
|---|---|
| **Docker + Laravel Sail** | Entorno de desarrollo contenerizado, multi-stage builds |
| **Redis** | Cache patterns, Sesiones, Queues, Rate Limiting, Locks |
| **CI/CD** | GitHub Actions: PHPUnit, Pint (estilo), PHPStan (estático), deploy vía Envoyer o Forge |
| **Testing** | Pest/PHPUnit (unit + feature + integration), Playwright (E2E), HTTP tests |
| **Monitoreo** | Laravel Pulse + Sentry, logs estructurados, métricas personalizadas |
| **APIs REST** | Diseño de recursos, versionamiento, documentación con Scribe o Scramble |
| **Frontend performance** | Lazy loading, code splitting, bundle analysis, MUI tree-shaking |
| **Laravel Events + Broadcasting** | WebSockets (Laravel Reverb o Pusher), event-driven module communication |

### Nivel Básico (Deseable)

| Habilidad | Descripción |
|---|---|
| **Laravel Spark / Cashier** | Facturación recurrente, suscripciones SaaS |
| **Terraform / Envoyer** | IaC, deployments zero-downtime |
| **Machine Learning** | Modelos básicos con Laravel + Python para forecasting de demanda |
| **Mobile** | React Native para app de repartidores/vendedores de ruta |
| **FilamentPHP** | Admin panel rápido para gestión interna |
| **Laravel Nova** | Panel de administración comercial (licenciado) |

---

## 6. Habilidades Blandas

| Habilidad | Descripción |
|---|---|
| **Pensamiento arquitectónico** | Capacidad de ver el sistema completo y tomar decisiones que beneficien al producto a largo plazo, no solo al sprint actual |
| **Comunicación con stakeholders no técnicos** | Traducir requisitos de negocio (SUNAT, IGV, logística) en decisiones técnicas. Explicar trade-offs a gerentes y contadores |
| **Liderazgo técnico** | Mentor de desarrolladores junior, code review, definición de estándares y convenciones (Pint, PHPStan, Pest) |
| **Gestión de deuda técnica** | Saber cuándo acelerar (time-to-market) y cuándo pagar deuda técnica. No sobreingenierizar |
| **Orientación al dominio** | Interés genuino por entender el negocio: cómo funciona una venta, una factura electrónica, un canje de puntos |
| **Toma de decisiones documentada** | Architecture Decision Records (ADR) para cada decisión importante: por qué se eligió X sobre Y |
| **Rápido prototipado** | Laravel permite tener un módulo funcional en días. Debe saber equilibrar velocidad con calidad |
| **Enfoque en calidad** | No solo código que funciona, sino código que es mantenible, testeable, y observable |

---

## 7. Formación y Experiencia

| Nivel | Requisito |
|---|---|
| **Formación base** | Ingeniería en Sistemas, Ciencias de la Computación, o afines |
| **Experiencia total** | 5+ años en desarrollo de software |
| **Experiencia en Laravel** | 3+ años en Laravel (preferiblemente L9+) |
| **Experiencia en React** | 2+ años en React con TypeScript |
| **Experiencia en ERP** | 2+ años en sistemas ERP, preferiblemente peruanos con facturación electrónica SUNAT |
| **Experiencia en PostgreSQL** | 2+ años con PostgreSQL (migraciones, consultas complejas, tuning) |
| **Certificaciones deseables** | Laravel Certified, AWS Certified, PHPStan Level 8 |
| **Idiomas** | Español nativo, Inglés técnico (lectura de documentación, RFCs) |

---

## 8. Seniority

| Nivel | Años | Características |
|---|---|---|
| **Senior Fullstack** | 4-6 años | Domina el stack, implementa módulos completos, code review, resuelve problemas complejos |
| **Lead / Architect** | 6-9 años | Define arquitectura, lidera equipo técnico, toma decisiones de stack, gestiona deuda técnica |
| **Principal / Staff** | 9+ años | Arquitectura organizacional, estándares cross-team, mentoring, contributor al ecosistema Laravel |

---

## 9. Ejemplo de Output: Documento de Arquitectura

```markdown
# ADR-001: Arquitectura del Sistema ARMORA NextGen

## Contexto
ARMORA actual es un ERP Java (JSP/Servlets) + jQuery + Semantic UI.
Se requiere modernización completa manteniendo operación continua.

## Decisión
Modular Monolith con Laravel 12 + React 19 + MUI + PostgreSQL.
Migración gradual con Strangler Fig Pattern.

## Módulos Priorizados (Fase 1)
1. Auth (Sanctum + Spatie RBAC)
2. Customers (CRUD + validación DNI/RUC con API SUNAT)
3. Products (catálogo + clases/subclases + IGV/ISC)
4. Sales (ventas + preventas + notas de crédito + envío SUNAT con Greenter)

## Stack
- Backend: PHP 8.3 + Laravel 12 + PostgreSQL 16 + Redis 7
- Frontend: React 19 + TypeScript + MUI 6 + TanStack Query + Vite
- SUNAT: Greenter 5.x + Laravel Queues (Redis) + Horizon
- Infra: Docker + Laravel Forge + AWS (EC2/RDS/ElastiCache)
- CI/CD: GitHub Actions + Envoyer (zero-downtime)
- Monitoreo: Laravel Pulse + Sentry + Prometheus

## Estrategia de Migración
1. Identificar endpoints del legacy (60+ REST endpoints en catalogo.js)
2. Construir API Laravel en paralelo montada en subdominio (api.armorasac.com)
3. Migrar frontend módulo por módulo (React SPA con Inertia o standalone)
4. Proxy reverso con Nginx: legacy sigue operando para módulos no migrados
5. Completar migración y desmantelar legacy

## Estructura de Módulos
app/Modules/
├── Auth/          → Sanctum + Spatie RBAC
├── Customers/     → Clientes, DNI/RUC, tipos de negocio
├── Products/      → Productos, clases, IGV/ISC, listas de precio
├── Sales/         → Ventas, preventas, notas de crédito, GREENTER
├── Purchases/     → Compras, proveedores
├── Inventory/     → Almacenes, kardex, ajustes
├── Finance/       → Libro contable, tributos, SUNAT PLE
├── Logistics/     → Rutas, zonas, transportistas
└── Loyalty/       → Canje de puntos, premios
```

---

## 10. Ciclo de Trabajo Típico

```
1. Análisis de requisitos (con usuario de negocio / contador)
   ↓
2. Crear migración (php artisan make:migration)
   ↓
3. Crear Modelo con relaciones + scopes + soft-deletes
   ↓
4. Crear Service con lógica de negocio (sin HTTP acoplado)
   ↓
5. Crear Form Request con validación + reglas personalizadas
   ↓
6. Crear Controller delgado (solo llama al Service)
   ↓
7. Crear pruebas (Pest: feature + unit)
   ↓
8. Crear componente React + MUI (con TanStack Query)
   ↓
9. Code review + PHPStan Level 8 + Pint
   ↓
10. Deploy (CI/CD → staging → canary → production)
   ↓
11. Monitoreo (Laravel Pulse + Sentry + logs)
```

---

> **Nota**: Este perfil está diseñado para un profesional que domina el ecosistema **Laravel + React** y entiende que un ERP no es una app CRUD — maneja transacciones financieras, tributos peruanos (IGV, ISC, detracciones), facturación electrónica SUNAT, y requiere integridad de datos absoluta. Laravel ofrece la velocidad de desarrollo necesaria para crecer rápido; PostgreSQL y las transacciones con `DB::transaction()` garantizan la consistencia que un ERP exige.
