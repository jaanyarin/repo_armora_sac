# Senior Fullstack ERP Architect

## Objetivo del Rol

Profesional con capacidad de planear, diseñar y desarrollar sistemas fullstack de misión crítica, con enfoque en **sistemas ERP** (planificación de recursos empresariales). Debe poder analizar sistemas legacy (como ARMORA: Java + jQuery + Semantic UI), entender su dominio de negocio a profundidad (facturación electrónica SUNAT, IGV/ISC, inventarios, ventas, compras, logística), y proponer/ejecutar una arquitectura moderna que sea mantenible, escalable, segura y preparada para los próximos 5-10 años.

No es solo un desarrollador que escribe código: es un **arquitecto de soluciones** que define el stack, las convenciones, la estructura del proyecto, los patrones de diseño, la estrategia de datos, la infraestructura, y las prácticas de calidad que todo el equipo seguirá.

---

## 1. Stack Tecnológico Requerido

### 1.1 Dominio Principal (Backend)

| Componente | Tecnología | Razón |
|---|---|---|
| **Lenguaje** | Java 21+ (LTS) o TypeScript/Node.js 22 LTS | Java para robustez transaccional y ecosistema enterprise; TypeScript para velocidad de desarrollo y equipo fullstack unificado |
| **Framework** | Spring Boot 3.x + Spring Modulith **o** NestJS + DDD | Spring Modulith ofrece módulos con boundaries forzados sin la sobrecarga de microservicios; NestJS ofrece arquitectura modular con TypeScript |
| **ORM** | JPA/Hibernate 6 + jOOQ para consultas complejas **o** Prisma/Drizzle + SQL crudo para TypeScript | JPA para 80% CRUD, jOOQ para el 20% que requiere control fino de SQL |
| **Base de Datos** | PostgreSQL 16+ | Integridad transaccional ACID, JSONB, pgvector, full-text search |
| **Cache** | Redis 7 | Sesiones, rate limiting, caching de consultas, colas |
| **Mensajería** | Apache Kafka o RabbitMQ | Workflows asíncronos, event-driven entre módulos |
| **Seguridad** | Spring Security 6 + OAuth2/JWT + RBAC **o** Passport.js/Better Auth + JWT | Autenticación stateless, control de acceso basado en roles |
| **API** | REST (OpenAPI 3.1) + GraphQL (opcional para reportes) | REST como estándar, GraphQL para dashboards complejos |
| **Migraciones** | Flyway o Liquibase | Control de versiones de base de datos |

### 1.2 Dominio Principal (Frontend)

| Componente | Tecnología | Razón |
|---|---|---|
| **Framework** | React 19 + Next.js 15/16 (App Router) | SSR, RSC, Server Actions, rutas API colocalizadas |
| **Lenguaje** | TypeScript (strict mode) | Type safety end-to-end |
| **Estilos** | Tailwind CSS 4 + shadcn/ui | Utilidades rápidas, componentes accesibles, sin runtime CSS-in-JS |
| **Estado** | TanStack Query (server state) + Zustand (client state) | Cache, refetch, paginación; estado local mínimo |
| **Formularios** | React Hook Form + Zod | Validación performante con esquemas tipados |
| **Build Tool** | Turbopack (Next.js) / Vite (SPA) | Dev server rápido, HMR instantáneo |
| **Testing** | Vitest + Playwright | Unit + integración + E2E |
| **PWA** | Next.js PWA / Service Workers | Capacidad offline parcial para zonas sin conectividad |

### 1.3 Infraestructura y DevOps

| Componente | Tecnología |
|---|---|
| **Contenedores** | Docker + Docker Compose (dev) |
| **Orquestación** | Kubernetes (prod) / Railway o Coolify (startup) |
| **CI/CD** | GitHub Actions / GitLab CI |
| **Cloud** | AWS (ECS/RDS/ElastiCache/MSK) o Azure |
| **Monitoring** | Prometheus + Grafana + Sentry + OpenTelemetry |
| **Logs** | ELK Stack (Elasticsearch + Logstash + Kibana) o Loki + Grafana |
| **Secret Management** | HashiCorp Vault / AWS Secrets Manager |
| **IaC** | Terraform / Pulumi |

---

## 2. Arquitectura de Software

### 2.1 Patrón Arquitectónico

**Modular Monolith como punto de partida** con capacidad de evolucionar a microservicios cuando se justifique.

```
src/
├── modules/
│   ├── auth/                  # Autenticación y autorización
│   │   ├── api/               # Controladores REST
│   │   ├── domain/            # Entidades, value objects, repositorios
│   │   ├── application/       # Casos de uso
│   │   ├── infrastructure/    # Implementaciones técnicas
│   │   └── api/api-docs/      # Documentación OpenAPI
│   ├── customers/             # Gestión de clientes
│   ├── products/              # Catálogo de productos
│   ├── inventory/             # Inventario y almacenes
│   ├── sales/                 # Ventas, preventas, notas de crédito
│   ├── purchases/             # Compras y proveedores
│   ├── finance/               # Libro contable, IGV, ISC, SUNAT
│   ├── logistics/             # Rutas, zonas, transportistas
│   ├── loyalty/               # Programa de canje y premios
│   └── notifications/         # Notificaciones, email, WebSockets
├── shared/
│   ├── kernel/                # Base classes, value objects genéricos
│   ├── security/              # Filtros JWT, RBAC, permisos
│   └── infrastructure/        # Configuración global, DB, cache, mq
├── bootstrap/
│   └── application/           # Punto de entrada (@SpringBootApplication)
└── docker/                    # Dockerfiles, docker-compose
```

**Principios:**
- **Package by feature** (no por capas técnicas)
- **Hexagonal Architecture** (ports & adapters) dentro de cada módulo
- **Domain-Driven Design (DDD)** para modelado de negocio
- **Event-Driven** para comunicación entre módulos (evita acoplamiento directo)

### 2.2 Estrategia de Base de Datos

- **Base única**, esquemas por módulo (namespace `module_name.*`)
- Transacciones ACID para operaciones financieras (libro contable, facturación)
- Event Sourcing para auditoría (tabla `domain_events` con todos los cambios)
- Migraciones con Flyway versionadas
- `deleted_at` para soft-delete en todas las entidades (nunca hard-delete)
- `cuid2` para IDs (no auto-increment, no expone volumen de registros)

### 2.3 Autenticación y Autorización

```
Modelo RBAC:
┌─────────────┐     ┌──────────────┐     ┌──────────────┐
│    Rol      │────>│  Permiso     │────>│  Recurso     │
│ (Vendedor)  │     │ (CREAR_VENTA)│     │ /sales/*     │
└─────────────┘     └──────────────┘     └──────────────┘
```

- JWT stateless con refresh tokens
- OAuth2 (Google/GitHub) para login social
- MFA opcional (TOTP)
- Auditoría de todas las acciones sensibles (quién, qué, cuándo, desde dónde)

---

## 3. Dominio de Negocio (ERP Peruano)

### Conocimiento Obligatorio

| Módulo | Conocimiento Requerido |
|---|---|
| **Facturación Electrónica SUNAT** | Estructura del XML (UBL 2.1), firma digital, envío OSE, CDR, resumen diario, comunicación de baja |
| **IGV / ISC / Renta** | Cálculo, base imponible, tipo de operación gravada/exonerada/inafecta, detracciones, percepciones, retenciones |
| **Libro Contable Electrónico** | Registro de ventas, compras, libro diario, libro mayor |
| **Catálogos SUNAT** | Tipos de documento, códigos de tributo, tipo de operación, códigos de producto SUNAT |
| **Nube de Puntos / Canje** | Programas de fidelización, puntos por compra, canje de premios, reglas de vigencia |
| **Logística / Rutas** | Planificación de rutas de reparto, zonas, asignación de transportistas |
| **Inventario** | Valuación (PEPS, promedio), ajustes, mermas, conteos cíclicos |
| **Compras** | Orden de compra, ingreso de almacén, validación de facturas de proveedores |

---

## 4. Roles Específicos Según Contexto

### Contexto 1: Planeamiento y Arquitectura
- **Rol**: Solutions Architect / Tech Lead
- **Enfoque**: Define el blueprint completo del sistema: stack, estructura de módulos, contratos de API, modelo de datos, estrategia de migración del legacy, infraestructura en la nube
- **Entregables**: Documento de arquitectura (ADR), diagramas C4, plan de módulos, plan de migración de datos

### Contexto 2: Desarrollo Fullstack
- **Rol**: Senior Fullstack Developer
- **Enfoque**: Implementa módulos completos end-to-end: backend (controladores, servicios, repositorios, eventos) + frontend (componentes Server/Client, Server Actions, formularios, dashboards)
- **Stack**: Java/Spring Boot o TypeScript/NestJS + React/Next.js

### Contexto 3: Modernización de Legacy
- **Rol**: Legacy Modernization Specialist
- **Enfoque**: Analiza el sistema actual (ARMORA: jQuery + Semantic UI + Java JSP/Servlets), mapea funcionalidades, define el Strangler Fig Pattern para migrar módulo por módulo sin detener el negocio
- **Stack adicional**: Análisis de código legacy, identificación de lógica de negocio oculta en JSPs

### Contexto 4: Infraestructura y DevOps
- **Rol**: DevOps Engineer con enfoque en aplicaciones Java/Node.js
- **Enfoque**: Docker multi-stage, pipelines CI/CD, Kubernetes, Terraform, monitoreo, logging centralizado, secret management
- **Stack adicional**: GitHub Actions, Helm, Prometheus, Grafana, Loki, OpenTelemetry

### Contexto 5: Integración SUNAT / Terceros
- **Rol**: Integration Engineer
- **Enfoque**: Integración con APIs de SUNAT (OSE), facturación electrónica, detracciones, consulta RUC/DNI, pasarelas de pago (Stripe, MercadoPago, Izipay), APIs de bancos
- **Stack adicional**: SOAP/XML, firmas digitales (Java KeyStore), API REST de terceros, webhooks

---

## 5. Habilidades Técnicas Detalladas

### Nivel Experto

| Habilidad | Descripción |
|---|---|
| **Spring Boot 3 + Spring Modulith** | Configuración, seguridad, transacciones, AOT, observabilidad con Micrometer + OpenTelemetry |
| **JPA/Hibernate 6 + jOOQ** | Fetch strategies, N+1 prevention, @EntityGraph, JOIN FETCH, Pageable, HikariCP tuning |
| **React 19 + Next.js App Router** | Server Components, Server Actions, Streaming SSR, ISR, caching strategies |
| **Tailwind CSS + shadcn/ui** | Design system, componentes accesibles, responsive design |
| **PostgreSQL (avanzado)** | Window functions, CTEs, índices parciales, JSONB, PL/pgSQL, pgvector, query planning |
| **Seguridad web** | OAuth2, JWT, RBAC, CSP, CORS, CSRF, HSTS, XSS prevention |
| **ERP Domain** | Facturación electrónica SUNAT, IGV/ISC, libro contable, inventarios, catálogos SUNAT |
| **DDD / Hexagonal Architecture** | Aggregates, Value Objects, Domain Events, Repositories, Ports & Adapters |
| **Event-Driven Architecture** | Kafka/RabbitMQ, event sourcing, CQRS básico, idempotencia de consumidores |

### Nivel Intermedio

| Habilidad | Descripción |
|---|---|
| **Docker / Kubernetes** | Multi-stage builds, health checks, probes, resource limits, ConfigMaps, Secrets |
| **CI/CD** | GitHub Actions: build, test, security scan, deploy |
| **Testing** | Vitest/JUnit, Playwright, contract testing (Pact), Testcontainers |
| **Monitoring** | Prometheus + Grafana dashboards, Sentry, structured logging, distributed tracing |
| **Redis** | Cache patterns, session store, rate limiting, pub/sub |
| **APIs REST / OpenAPI 3.1** | Diseño de contratos, versionamiento, documentación, clientes generados |
| **Frontend performance** | Core Web Vitals, lazy loading, bundle analysis, image optimization |
| **TypeScript (strict)** | Tipos avanzados, genéricos, utility types, branded types |

### Nivel Básico (Deseable)

| Habilidad | Descripción |
|---|---|
| **PL/pgSQL** | Funciones almacenadas, triggers, maintenance routines |
| **Terraform / Pulumi** | IaC para infraestructura cloud |
| **Machine Learning** | Modelos básicos para forecasting de demanda, detección de anomalías |
| **Mobile** | React Native para app de repartidores/vendedores de ruta |
| **Mensajería** | Apache Kafka: producers, consumers, stream processing |
| **Grafana Loki / ELK** | Log aggregation y análisis |
| **GraphQL** | Apollo Server/Federation para dashboards |

---

## 6. Habilidades Blandas

| Habilidad | Descripción |
|---|---|
| **Pensamiento arquitectónico** | Capacidad de ver el sistema completo y tomar decisiones que beneficien al producto a largo plazo, no solo al sprint actual |
| **Comunicación con stakeholders no técnicos** | Traducir requisitos de negocio (SUNAT, IGV, logística) en decisiones técnicas. Explicar trade-offs a gerentes y contadores |
| **Liderazgo técnico** | Mentor de desarrolladores junior, code review, definición de estándares y convenciones |
| **Gestión de deuda técnica** | Saber cuándo acelerar (time-to-market) y cuándo pagar deuda técnica. No sobreingenierizar |
| **Orientación al dominio** | Interés genuino por entender el negocio: cómo funciona una venta, una factura electrónica, un canje de puntos |
| **Toma de decisiones documentada** | Architecture Decision Records (ADR) para cada decisión importante: por qué se eligió X sobre Y |
| **Resiliencia y adaptabilidad** | Los requisitos de ERP cambian constantemente (nuevas leyes SUNAT, nuevos tipos de documento). Debe adaptar el sistema rápidamente sin romper lo existente |
| **Enfoque en calidad** | No solo código que funciona, sino código que es mantenible, testeable, y observable |

---

## 7. Formación y Experiencia

| Nivel | Requisito |
|---|---|
| **Formación base** | Ingeniería en Sistemas, Ciencias de la Computación, o afines |
| **Experiencia total** | 6+ años en desarrollo de software |
| **Experiencia en ERP** | 3+ años en sistemas ERP, preferiblemente peruanos con facturación electrónica SUNAT |
| **Experiencia fullstack** | 4+ años combinando backend + frontend |
| **Experiencia en migraciones** | 1+ proyecto de modernización de sistema legacy a arquitectura moderna |
| **Certificaciones deseables** | AWS Solutions Architect, Spring Professional, Kubernetes (CKA), Scrum Master |
| **Idiomas** | Español nativo, Inglés técnico (lectura de documentación, RFCs, artículos) |

---

## 8. Seniority

| Nivel | Años | Características |
|---|---|---|
| **Senior Fullstack** | 5-7 años | Domina el stack, implementa módulos completos, code review, resuelve problemas complejos |
| **Lead / Architect** | 7-10 años | Define arquitectura, lidera equipo técnico, toma decisiones de stack, gestiona deuda técnica, se comunica con stakeholders |
| **Principal / Staff** | 10+ años | Arquitectura organizacional, define estándares cross-team, investiga nuevas tecnologías, mentoring de leads |

---

## 9. Ejemplo de Output: Documento de Arquitectura

```markdown
# ADR-001: Arquitectura del Sistema ARMORA NextGen

## Contexto
ARMORA actual es un ERP Java (JSP/Servlets) + jQuery + Semantic UI.
Se requiere modernización completa manteniendo operación continua.

## Decisión
Modular Monolith con Spring Boot 3 + Spring Modulith + PostgreSQL.
Frontend con Next.js 16 + React 19 + Tailwind CSS + shadcn/ui.
Migración gradual con Strangler Fig Pattern.

## Módulos Priorizados (Fase 1)
1. Auth (JWT + RBAC)
2. Customers (CRUD + DNI/RUC validation)
3. Products (catálogo + clases + IGV/ISC)
4. Sales (ventas + preventas + notas de crédito)

## Stack
- Backend: Java 21 + Spring Boot 3.4 + Spring Modulith 1.3
- Frontend: Next.js 16 + React 19 + Tailwind 4 + shadcn/ui
- DB: PostgreSQL 16 + Flyway + jOOQ (consultas complejas)
- Cache: Redis 7
- Mensajería: RabbitMQ (eventos internos)
- Infra: Docker + AWS ECS Fargate + RDS + ElastiCache
- CI/CD: GitHub Actions
- Monitoreo: OpenTelemetry + Prometheus + Grafana + Sentry

## Estrategia de Migración
1. Identificar endpoints del legacy (60+ REST endpoints en catalogo.js)
2. Implementar nuevo módulo en paralelo con API Gateway que rutea
3. Migrar frontend módulo por módulo (micro-frontends con Next.js)
4. Legacy opera hasta que todos los módulos estén migrados
5. Desmantelar legacy al completar migración

## Diagramas
[C4 diagrams: Context, Container, Component, Code]
```

---

## 10. Ciclo de Trabajo Típico

```
1. Análisis de requisitos (con usuario de negocio)
   ↓
2. Descomposición en módulos y bounded contexts
   ↓
3. Definición de contratos de API (OpenAPI specs)
   ↓
4. Modelado de datos (entidades, agregados, eventos)
   ↓
5. Implementación del backend (API + lógica + persistencia)
   ↓
6. Implementación del frontend (componentes + formularios + dashboards)
   ↓
7. Tests (unitarios + integración + E2E)
   ↓
8. Code review + documentación (ADR si aplica)
   ↓
9. Deploy (CI/CD → staging → canary → production)
   ↓
10. Monitoreo (métricas, logs, alertas)
```

---

> **Nota**: Este perfil está diseñado para un profesional que no solo escribe código, sino que **diseña sistemas**. Busca a alguien que entienda que un ERP no es una app CRUD — es el sistema nervioso central de un negocio, donde un error en un cálculo de IGV puede costar miles de soles en multas SUNAT.
