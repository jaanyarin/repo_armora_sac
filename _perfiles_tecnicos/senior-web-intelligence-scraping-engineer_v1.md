# Senior Web Intelligence & Scraping Engineer

## Objetivo del Rol

Profesional especializado en ingeniería inversa de aplicaciones web, capaz de realizar análisis técnico profundo de sitios web para determinar su stack tecnológico completo (frontend, backend, base de datos, infraestructura, autenticación), estructura del sitio, funcionalidades, y contenido. Así mismo, debe poder implementar pipelines de extracción de datos (web scraping) robustos, evasivos y escalables, adaptándose a sitios con alta protección anti-bot.

---

## 1. Análisis Técnico Profundo

### 1.1 Frontend

| Aspecto | Qué detectar | Herramientas |
|---|---|---|
| Framework/Librería | React, Vue, Angular, Svelte, Next.js, Nuxt, Gatsby, Astro | Wappalyzer, WhatWeb, inspección de bundles JS,分析法 de `__NEXT_DATA__`, `__NUXT__`, `window.__vue__`, `__REACT_DEVTOOLS_GLOBAL_HOOK__` |
| Versiones específicas | React 18 vs 19, Angular 15 vs 16, Vue 2 vs 3 | Source maps, paquetes npm expuestos, patrones de API |
| CSR vs SSR vs SSG | Cómo se renderiza el contenido | Deshabilitar JS en navegador, analizar HTML inicial, detectar hidratación |
| Bundlers | Webpack, Vite, Turbopack, esbuild, Rollup | Nombres de chunks, hash patterns, presencia de `__vite__` |
| CSS Framework | Tailwind, Bootstrap, Material UI, Chakra, Ant Design | Clases CSS, estilos embebidos, variables CSS |
| State Management | Redux, Zustand, Pinia, Vuex, Recoil | Archivos JS, patrones en código fuente |
| PWA | Service workers, manifest.json, offline support | `navigator.serviceWorker`, `manifest.json` |

### 1.2 Backend

| Aspecto | Qué detectar | Herramientas |
|---|---|---|
| Lenguaje/Framework | Node (Express, Nest, Fastify), Python (Django, Flask, FastAPI), PHP (Laravel, Symfony), Ruby (Rails), Java (Spring), Go, Rust | Headers HTTP (`X-Powered-By`, `Server`), cookies de sesión, patrones de ruteo, errores 404/500 |
| API Architecture | REST, GraphQL, gRPC, SOAP | Patrones de endpoint, `__graphql` paths, `application/grpc` |
| Versiones de servidor | nginx, Apache, Caddy, IIS, Cloudflare | Headers `Server`, `CF-Ray`, cookies `__cfduid` |
| ORM/DB Layer | Prisma, Sequelize, TypeORM, Django ORM, ActiveRecord, Hibernate | Patrones de query en URLs, error messages, naming de tablas |
| Cache Layer | Redis, Varnish, Memcached, CDN | Headers `X-Cache`, `X-Varnish`, `Age`, `Cache-Control` |
| Message Queues | RabbitMQ, Kafka, Bull, Sidekiq | Headers, patrones de async en endpoints |

### 1.3 Base de Datos

| Aspecto | Indicadores |
|---|---|
| PostgreSQL | Errores con `psycopg2`, drivers `pg`, secuencias `nextval`, esquema `public` |
| MySQL/MariaDB | Errores con `mysqli`, `PDO`, `InnoDB`, `auto_increment` |
| MongoDB | Errores con ObjectId (`_id` de 24 hex), `$regex`, `$where` |
| SQL Server | Errores con `mssql`, `Microsoft.EntityFrameworkCore.SqlServer` |
| Redis (como DB primaria) | Endpoints con keys de sesión, patrones `SESS:` |
| Elasticsearch | Endpoints `/_search`, headers `Elasticsearch` |
| Inferencia por ORM | Prisma (Postgres/MySQL/SQLite), Mongoose (MongoDB), ActiveRecord (MySQL/Postgres/SQLite) |

### 1.4 Infraestructura

| Aspecto | Herramientas/Métodos |
|---|---|
| Proveedor cloud | AWS, GCP, Azure, DigitalOcean, Hetzner, OVH | IP ranges, headers `x-amz-`, `x-guploader-`, `x-azure-` |
| CDN | Cloudflare, Akamai, Fastly, CloudFront, StackPath | Headers `CF-*`, `Akamai-*`, `X-Cache: hit from cloudfront` |
| DNS | dnspython, dig, whois | `nslookup`, `dig ANY`, registros MX, CNAME, TXT |
| SSL/TLS | Let's Encrypt, DigiCert, Cloudflare SSL | `openssl s_client`, cert-info, validez, emisor |
| Container/Orch | Docker, Kubernetes, ECS, Fargate | Headers `x-amz-ecs`, patrones de ruteo `/api/v1/pods` |
| CI/CD | GitHub Actions, GitLab CI, Jenkins, CircleCI | Archivos de configuración expuestos, headers |

### 1.5 Autenticación

| Método | Detectores |
|---|---|
| JWT | Almacenamiento en localStorage/sessionStorage, cookies con `access_token`, patrón `eyJ` (base64 JWT) |
| Session-based | Cookies `sessionid`, `connect.sid`, `PHPSESSID`, `JSESSIONID` |
| OAuth2 (Google, GitHub, etc.) | Endpoints `/auth/google`, `/auth/github`, `state` parameter |
| SSO (SAML, LDAP) | Endpoints `/saml/`, `SAMLResponse`, `RelayState` |
| MFA/2FA | Formularios con código TOTP, endpoints `/verify-2fa`, `/mfa` |
| Magic Links | Endpoints `/magic-link`, `/verify-email`, `token` en query param |
| WebAuthn/FIDO2 | Presencia de `credentials.create()`, `navigator.credentials` |
| API Keys | Headers `X-API-Key`, `Authorization: Bearer`, query params `api_key=` |

### 1.6 Estructura del Sitio

| Elemento | Método de detección |
|---|---|
| Sitemap | `/sitemap.xml`, `/sitemap_index.xml`, `/robots.txt` |
| Robots.txt | `/robots.txt` — rutas permitidas/bloqueadas |
| Navegación principal | Análisis de `nav`, `header`, menús en HTML, breadcrumbs |
| Jerarquía de páginas | Crawling breadth-first, patrones de URL `/category/subcategory/product` |
| Paginación | Parámetros `?page=`, `?offset=`, `?start=`, `?cursor=`, infinite scroll |
| Formularios | `<form>`, inputs, CSRF tokens, métodos GET/POST |
| Endpoints API ocultos | Source maps, JS bundles, patrones de fetch/XHR, `__NEXT_DATA__` |
| Archivos estáticos | `/assets/`, `/static/`, `/build/`, bundles JS/CSS |

### 1.7 Funcionalidades del Sitio

| Funcionalidad | Indicadores |
|---|---|
| Búsqueda | Input de búsqueda, endpoint `/search`, `?q=`, autocompletado |
| Carrito/Compras | Endpoints `/cart`, `/checkout`, `/add-to-cart`, localStorage |
| Login/Registro | `/login`, `/register`, `/auth`, `/signup`, `/forgot-password` |
| Panel de usuario | `/dashboard`, `/account`, `/profile`, `/settings` |
| Notificaciones | WebSockets, endpoints `/notifications`, Service Workers |
| Upload de archivos | `<input type="file">`, `/upload`, multipart/form-data |
| Comentarios/Reviews | `/comment`, `/review`, `/rating`, formularios con rating |
| Pagos | Integración Stripe, PayPal, MercadoPago — detectado por scripts externos |
| Chat/Soporte | WebSockets, librerías como Socket.io, Pusher, Firebase |
| Suscripciones/Newsletter | `/subscribe`, `/newsletter`, endpoints de email |
| Mapas/Geolocalización | Google Maps API, Leaflet, Mapbox, `Geolocation API` |
| Redes sociales | OG tags, share buttons, login social, embedded feeds |
| Exportación/Descargas | `/download`, `/export`, `/csv`, `/pdf`, `/report` |
| Blog/Noticias | `/blog`, `/news`, `/article`, `/post` con paginación |

---

## 2. Análisis de Contenido

| Aspecto | Descripción |
|---|---|
| Metadatos SEO | Title, meta description, OG tags, Twitter cards, schema.org JSON-LD, canonical, hreflang |
| Schema Markup | Product, Article, FAQ, BreadcrumbList, Organization, LocalBusiness, Review |
| Estructura semántica | Jerarquía de headings (h1-h6), landmarks ARIA, sections, articles, aside |
| Extracción de contenido | NLP (spaCy, NLTK) para identificar entidades, temas, sentimiento, categorías |
| Multimedia | Imágenes, videos, iframes, lazy loading, formatos (WebP, AVIF), alt texts |
| Idiomas | Tags `hreflang`, `lang`, detección de idioma por contenido |
| Sitemaps dinámicos | Sitemaps de imágenes, videos, news |
| Contenido dinámico | Carga por scroll (infinite scroll), load more, tabs, accordeons, modales |

---

## 3. Seguridad

| Aspecto | Qué revisar | Herramientas |
|---|---|---|
| Headers de seguridad | CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy | `curl -I`, securityheaders.com |
| CSP (Content Security Policy) | Lista de dominios permitidos, `script-src`, `style-src`, report-uri | Analizar header CSP, buscar bypasses potenciales |
| CORS | Headers `Access-Control-Allow-Origin`, preflight requests | `curl -X OPTIONS -H "Origin: https://evil.com"` |
| WAF | Cloudflare, AWS WAF, ModSecurity, Imperva, Akamai Kona | Patrones de bloqueo, `CF-WAF` headers, challenge pages |
| Rate Limiting | 429 Too Many Requests, `Retry-After` header | Testing incremental de requests |
| Fingerprinting | TLS fingerprint (JA3), HTTP/2 fingerprint, canvas fingerprint | `curl_cffi`, puppeteer-extra-plugin-stealth |
| Honeypots | Campos ocultos en formularios, `<div style="display:none">`, `visibility:hidden` | Inspección de elementos invisibles |
| Exposición de rutas sensibles | `/admin`, `/api/docs`, `/swagger`, `/graphql?introspection`, `/.env`, `/wp-admin`, `/config` | Fuzzing con wordlists |
| Exposición de información | Versiones de software, stack trace, debug mode, email leaks | Error triggering, revisar comentarios HTML, JS source maps |
| Protección de datos | PII en URLs, tokens en query params, localStorage de datos sensibles | Revisar almacenamiento, patrones de URL |

---

## 4. Stack Tecnológico (Herramientas Esenciales)

### Lenguajes
- **Python** (avanzado): Scrapy, Playwright, Requests, httpx, curl_cffi, BeautifulSoup, lxml
- **JavaScript/Node.js**: Puppeteer, Playwright, axios, cheerio, puppeteer-extra-plugin-stealth
- **Bash**: Automatización, pipes, procesamiento de texto

### Scraping & Browser Automation
- **Scrapy**: Framework completo para scraping a gran escala (middlewares, pipelines, exporters)
- **Playwright** (Python/Node): Multi-browser, soporte de contextos, stealth mejorado
- **Puppeteer + Stealth Plugin**: Evasión de detección de headless Chrome
- **Selenium**: Legacy, útil para sitios muy específicos
- **curl_cffi**: Emulación de fingerprint TLS (JA3) a nivel HTTP
- **BeautifulSoup / lxml**: Parseo rápido de HTML estático

### Anti-Detección y Bypass
- **Proxies residenciales**: BrightData, Oxylabs, Smartproxy, IPRoyal, Webshare
- **Rotación de User-Agent y headers**: Patrones realistas por dispositivo y navegador
- **Session/Cookie persistence**: Manejo de sesiones largas, renew automático
- **Captcha Solvers**: 2Captcha, Capsolver, Anti-Captcha, DeathByCaptcha
- **Cloudflare bypass**: cloudscraper, flare_solverr, capsolver CF turnstile
- **Fingerprint evasion**: TLS fingerprint (JA3/JA4), HTTP/2 fingerprint, canvas/webgl/audio spoofing
- **Retry & backoff strategies**: Exponential backoff, jitter, circuit breaker
- **Request fingerprinting**: Emulación de patrones humanos (mouse movement, scroll, timing)

### Análisis Técnico
- **Wappalyzer** (CLI/API): Detección automática de tecnologías
- **WhatWeb**: Reconocimiento de fingerprint de servidores y aplicaciones
- **curl/wget avanzado**: Headers personalizados, cookies, redirecciones, timing
- **OpenSSL / s_client**: Análisis de certificados TLS, handshake
- **dnspython**: Resolución DNS, registros MX, CNAME, TXT, ANY
- **dig / nslookup**: Consultas DNS rápidas
- **Browser DevTools Protocol (CDP)**: Control programático del navegador
- **source-map CLI**: Extracción de source maps para código ofuscado

### Proxy e Infraestructura
- **BrightData / Oxylabs / Smartproxy**: Redes de proxies residenciales y de datacenter
- **Rotating proxies**: Integración con Scrapy-rotating-proxies, custom middleware
- **Tor (stem/requests)**: Proxy socks5, cambio de circuito programático
- **Balanceo de requests**: Distribución de requests entre múltiples IPs
- **Rate limiting**: Control de velocidad, respeto de `Retry-After`, random delays

### Procesamiento y Almacenamiento
- **PostgreSQL**: Almacenamiento estructurado relacional
- **MongoDB**: Almacenamiento semi-estructurado, documentos flexibles
- **Redis**: Caché, colas, rate limiting distribuido, sesiones
- **S3 / MinIO**: Almacenamiento de objetos (HTML, imágenes, archivos)
- **Apache Kafka / RabbitMQ**: Colas de mensajes para pipelines distribuidos
- **DuckDB**: Análisis rápido de datos extraídos en formato parquet/csv

### Monitoreo y Alertas
- **Prometheus + Grafana**: Métricas de scraping (requests, éxito/fallo, latencia, IPs rotadas)
- **Sentry**: Tracking de errores, excepciones, fallos de scrapers
- **Healthchecks / Uptime Kuma**: Monitoreo de schedulers y jobs
- **Logging estructurado**: JSON logs, log aggregation (Loki, ELK)

---

## 5. Habilidades Técnicas

### Nivel Experto (Dominio Completo)
- **Web Scraping & Crawling**: Diseño de scrapers desde simples (GET requests) hasta complejos (browser automation con evasión)
- **Anti-detección y bypass**: Fingerprinting TLS, canvas/webgl/audio spoofing, rotación de IPs, captcha solving, evasión de WAFs
- **Browser Automation**: Playwright, Puppeteer, manejo de contextos múltiples, perfiles de navegador, stealth plugins
- **OSINT técnico**: Wappalyzer, WhatWeb, Shodan, Censys, SecurityTrails, DNS recon
- **HTTP/Redes**: Headers, cookies, sesiones, TLS/SSL, DNS, proxies, balanceo, rate limiting
- **Ingeniería inversa web**: Lectura de bundles JS ofuscados, extracción de APIs desde source maps, identificación de CSR vs SSR
- **Análisis de seguridad web**: Headers de seguridad, CSP, CORS, WAF fingerprinting, honeypot detection, fuzzing
- **Bases de datos**: PostgreSQL (avanzado), MongoDB, Redis, modelado de datos extraídos

### Nivel Intermedio (Uso Independiente)
- **Python avanzado**: Async/await, multithreading, multiprocessing, decorators, context managers
- **JavaScript/Node.js**: Async, Promises, Event Loop, streams
- **Docker**: Contenerización de scrapers, entornos reproducibles
- **Linux/Shell**: Automatización, cron, systemd, administración de servidores
- **Git**: Control de versiones, CI/CD básico
- **APIs REST/GraphQL**: Consumo, rate limiting, autenticación, paginación

### Nivel Básico (Capacidad de Aprendizaje)
- **Cloud (AWS/GCP/Azure)**: EC2, Lambda, S3, CloudWatch, Functions, Cloud Scheduler
- **Orquestación**: Apache Airflow, Prefect, Dagster (scheduleo de scrapers)
- **NLP básico**: spaCy, NLTK para extracción de entidades y categorización
- **ETL/ELT**: Pipelines de transformación y carga de datos extraídos

---

## 6. Habilidades Blandas

| Habilidad | Descripción |
|---|---|
| **Pensamiento forense y analítico** | Capacidad de inspeccionar tráfico, reconstruir flujos, deducir stack tecnológico a partir de pistas indirectas |
| **Adaptabilidad y resolución de problemas** | Los sitios web cambian constantemente (rediseños, parches anti-bot, nuevas versiones). Debe debuggear y reparar scrapers rotos rápidamente |
| **Ética profesional y legal** | Conocimiento de límites legales (ToS, robots.txt, leyes de protección de datos como GDPR, CCPA), precedentes (hiQ Labs vs LinkedIn, Craigslist vs 3Taps) |
| **Comunicación técnica** | Capacidad de documentar hallazgos, emitir informes detallados de análisis técnico, y explicar decisiones técnicas a stakeholders no técnicos |
| **Trabajo bajo presión** | Los scrapers productivos se rompen en producción; debe responder rápido para minimizar pérdida de datos |
| **Orientación a resultados** | Enfoque en obtener los datos requeridos de manera eficiente, evitando over-engineering |
| **Curiosidad técnica** | Motivación para explorar nuevas tecnologías, frameworks, patrones anti-bot y técnicas de evasión |

---

## 7. Roles Específicos Según Contexto

### Contexto 1: Investigación de Mercado / Competencia
- **Rol**: Competitive Intelligence Lead / Data Analyst Senior
- **Enfoque**: Análisis de catálogos de productos, precios, disponibilidad, reseñas — extracción masiva y comparativa
- **Stack adicional**: Pandas, Jupyter, visualización de datos (Plotly, Metabase), dashboards

### Contexto 2: Ciberseguridad / Red Team
- **Rol**: Cybersecurity Researcher / OSINT Specialist / Red Team Operator
- **Enfoque**: Reconocimiento pasivo y activo de superficies de ataque, detección de vulnerabilidades, identificación de vectores de entrada
- **Stack adicional**: Burp Suite, Nuclei, Nmap, Shodan, Censys, Metasploit, OWASP ZAP

### Contexto 3: Data Engineering / Big Data
- **Rol**: Senior Data Engineer (Web Data Extraction)
- **Enfoque**: Pipelines de extracción escalables, orquestación, almacenamiento distribuido, calidad de datos, monitoreo
- **Stack adicional**: Airflow, Spark, Kafka, dbt, Great Expectations, Terraform, Kubernetes

### Contexto 4: Producto / Growth
- **Rol**: Growth Engineer / Product Intelligence Analyst
- **Enfoque**: Extracción de datos para feature discovery, análisis de UX, identificación de patrones de producto en competidores
- **Stack adicional**: Hotjar / FullStory (identificación de tools), Mixpanel/Amplitude detection, analytics forensics

### Contexto 5: Legal / Compliance
- **Rol**: Digital Forensics Analyst / eDiscovery Specialist
- **Enfoque**: Preservación forense de evidencia web, cadena de custodia, extracción legalmente admisible, cumplimiento normativo
- **Stack adicional**: HTTrack/wget para preservación forense, tools de hashing, chain of custody documentation

---

## 8. Formación Recomendada

| Nivel | Formación |
|---|---|
| **Base** | Ingeniería en Sistemas, Ciencias de la Computación, Ingeniería de Software o afines |
| **Especialización** | Cursos avanzados de web scraping (Udemy/Coursera/Pluralsight), certificaciones en ciberseguridad (OSCP, eJPT, CST) |
| **Maestría (deseable)** | Data Science, Ciberseguridad, Big Data, Business Intelligence |
| **Auto-didacta** | Participación activa en comunidades: r/webscraping, scrapinghub, foros de bypass anti-bot, GH scraping repos |

---

## 9. Seniority

| Nivel | Años de exp. | Características |
|---|---|---|
| **Junior** | 0-2 años | Sabe usar BeautifulSoup/Requests, HTML/CSS básico, scraping estático simple |
| **Semi-Senior** | 2-4 años | Domina Scrapy, Playwright/Puppeteer, entiende rate limiting, proxies, captchas básicos |
| **Senior** | 4-7 años | Anti-detección avanzado (TLS fingerprint, canvas spoofing), bypass de WAFs, GraphQL scraping, arquitectura de scrapers distribuidos |
| **Lead / Architect** | 7+ años | Diseña sistemas completos de extracción, orquesta equipos, define estrategia anti-bloqueo, investiga técnicas nuevas, emite informes de análisis técnico detallados |

---

## 10. Ejemplo de Output de Análisis Técnico

```markdown
# Análisis Técnico: example.com

## Stack Tecnológico
- **Frontend**: React 18.2 + Next.js 14 (SSR + ISR), Tailwind CSS, Zustand, Vite
- **Backend**: Node.js 20 + Express + tRPC (API Gateway), microservicios en Go
- **Base de Datos**: PostgreSQL 15 (Primario) + Redis 7 (Cache/Sessions) + Elasticsearch (Búsqueda)
- **Infraestructura**: AWS (ECS Fargate + RDS + ElastiCache + CloudFront), Terraform, Docker, GitHub Actions
- **Autenticación**: JWT + OAuth2 (Google/GitHub) + MFA (TOTP)
- **CDN/WAF**: Cloudflare (Enterprise) con Turnstile Captcha

## Estructura del Sitio
- `/` → Landing + Featured Products
- `/products` → Catálogo con filtros (GET /api/products?category=&price=&page=)
- `/products/[id]` → Página de detalle
- `/cart` → Carrito (localStorage + API)
- `/checkout` → Checkout con Stripe Elements
- `/account/*` → Panel de usuario (Dashboard, Orders, Settings)
- `/blog` → Blog con paginación infinite scroll

## Funcionalidades Identificadas
- Búsqueda con autocompletado (Elasticsearch)
- Carrito de compras persistente (localStorage + sync POST /api/cart/sync)
- Autenticación OAuth2 + JWT + MFA
- Pagos con Stripe (Elements + Webhooks)
- Notificaciones en tiempo real (WebSockets + Push API)
- Upload de imágenes de perfil (presigned S3 URLs)
- Sistema de reviews/ratings (CRUD + paginación)
- Newsletter (POST /api/subscribe con captcha)
- Chat en vivo (Socket.io + Redis pub/sub)

## Headers de Seguridad
- CSP: restrictivo (solo dominios propios + Stripe + Cloudflare)
- HSTS: max-age=31536000; includeSubDomains; preload
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy: geolocation=(), camera=(), microphone=()

## Observaciones de Seguridad
- Source maps habilitados en producción → expone bundles JS
- Endpoint /api/graphql con introspection activada
- API key visible en localStorage
- Sin rate limiting visible en endpoints críticos (/api/auth/login)
```

---

> **Nota**: Este perfil es una referencia. Dependiendo del contexto específico de la organización, algunos skills pueden ser más relevantes que otros. La flexibilidad y capacidad de adaptación son clave.
