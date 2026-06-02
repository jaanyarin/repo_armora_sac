# Análisis Técnico: armorasac.com/app

## Stack Tecnológico

### Frontend
| Componente | Tecnología | Versión | Evidencia |
|---|---|---|---|
| CSS Framework | Semantic UI (custom build "dyvent") | 2.4.2 | `semantic-dyvent.min.css` v2.4.2 en comentarios del bundle |
| JavaScript Library | jQuery | 3.4.1 | `jquery-3.4.1.min.js` |
| jQuery Plugin | jquery-serialize-object | - | `jquery-serialize-object.min.js` |
| UI Components | Custom JS framework (classes: Input, Calendar, Modal, CustomTable, CustomForm, SelectDropdown, Product, Combo, Detail, etc.) | - | `clases.js` |
| PWA | Web App Manifest (standalone) | - | `site.webmanifest` con icons 192x192 y 384x384 |
| Íconos | Favicon + Apple Touch Icon + Android Chrome icons | - | favicon de Jul 2023 |
| Fuente | Google Fonts: Lato (400, 700, 400italic, 700italic) | - | `@import url(https://fonts.googleapis.com/css?family=Lato...)` |
| **Framework SPA** | **No detectado** | - | Sin React/Vue/Angular. Sitio tradicional con jQuery + Semantic UI. Renderizado del lado servidor (JSP) |

### Backend
| Componente | Tecnología | Evidencia |
|---|---|---|
| Lenguaje | Java | Cookie `JSESSIONID` (HttpOnly, Secure, Path=/app) |
| Framework | Java Servlet / Spring MVC (probable) | Patrón REST `/general/catalogo/rest/*`, POST-based API |
| Contenedor Servlet | Apache Tomcat o similar | Headers `Server` no expuesto (buena práctica), estructura `/app/` como context path |
| API Architecture | REST (POST-based RPC-style) | Todas las rutas POST a `/general/*` |
| Autenticación | Session-based (JSESSIONID) | Cookie Secure + HttpOnly |
| CSRF | **No implementado** | `var csrf = false;` en HTML |
| Encoding | UTF-8 | `Content-Type: text/html;charset=UTF-8` |

### Base de Datos (inferida por funcionalidad)
No se puede determinar con certeza, pero por el tipo de aplicación (ERP peruano con IGV/ISC/SUNAT) y stack Java, las opciones más probables son:

| Motor | Probabilidad | Razones |
|---|---|---|
| **PostgreSQL** | Alta | Stack Java + ERP, soporte nativo de transacciones complejas |
| **MySQL/MariaDB** | Media | Alternativa común en sistemas legacy peruanos |
| **Oracle** | Baja | Sobredimensionado para el tipo de app |

**Entidades identificadas por los endpoints REST:**
- `cliente` (con DNI/RUC, tipo negocio)
- `producto` (clase, subclase, IGV, ISC, unidad medida, lista precios)
- `venta` (tipos: preventa, nota crédito error/total/parcial)
- `compra` (tipos)
- `proveedor`
- `personal` (vendedores, transportistas)
- `ruta` / `zona`
- `almacen`
- `moneda`
- `documento` (factura, boleta, etc.)
- `canje` / `premio` (programa de puntos)
- `item-venta`

### Infraestructura

| Componente | Detalle |
|---|---|
| **IP** | `38.210.245.233` |
| **Ubicación** | Lima, Perú (lat: -12.0432, lon: -77.0282) |
| **ISP / Hosting** | ON EMPRESAS S.A.C. (AS27843) — Proveedor peruano |
| **Servidor Web** | No expuesto en headers (seguridad por oscuridad) |
| **SSL/TLS** | Habilitado (HTTPS) |
| **CDN** | No detectado |
| **DNS** | Sin Cloudflare (NS no revisado, pero sin headers CF) |

---

## 2. Headers HTTP de Seguridad

```
HTTP/1.1 200 OK
Cache-Control: private
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000 ; includeSubDomains
X-Frame-Options: DENY
Set-Cookie: JSESSIONID=...; Path=/app; Secure; HttpOnly
Content-Type: text/html;charset=UTF-8
Content-Language: en-US
```

| Header | Estado | Notas |
|---|---|---|
| `Strict-Transport-Security` | ✅ | max-age=31536000 (1 año), includeSubDomains |
| `X-Frame-Options: DENY` | ✅ | Protección contra clickjacking |
| `X-Content-Type-Options: nosniff` | ✅ | Previene MIME sniffing |
| `X-XSS-Protection: 1; mode=block` | ✅ | Protección XSS (legacy browsers) |
| `Content-Security-Policy` | ❌ **No implementado** | Riesgo de inyección XSS |
| `Referrer-Policy` | ❌ **No implementado** | |
| `Permissions-Policy` | ❌ **No implementado** | |
| `CORS` | Parcial | OPTIONS con origin externo retorna 403, Allow: GET,HEAD,POST,PUT,DELETE,TRACE,OPTIONS,PATCH |

---

## 3. Autenticación

| Aspecto | Detalle |
|---|---|
| **Método** | Session-based con JSESSIONID (HttpOnly, Secure) |
| **Login URL** | `POST /app/login-check` |
| **Campos** | `username` (text), `password` (password), `remember-me` (checkbox) |
| **Validación frontend** | Campos requeridos (empty), validación inline on blur |
| **Redirección post-login** | No determinada (requiere credenciales) |
| **Logout** | No determinado |
| **OAuth2 / SSO** | No detectado |
| **JWT** | No detectado |
| **CSRF** | ❌ Deshabilitado (`csrf = false`) — vulnerable a CSRF |

---

## 4. Estructura del Sitio

### Rutas Públicas (sin autenticación)
| Ruta | Método | Descripción |
|---|---|---|
| `/app/` | GET | Login page |
| `/app/login` | GET | Login page (alias/redirect) |
| `/app/login-check` | POST | Procesa login |

### Rutas Protegidas (requieren autenticación — retornan 302 redirect)
| Ruta | Inferencia |
|---|---|
| `/app/admin` | Panel de administración |
| `/app/dashboard` | Dashboard principal |
| `/app/general` | Módulo general |

### API Interna (POST-based, bajo `/app/general/catalogo/rest/`)
Endpoints expuestos en `catalogo.js` (~60 endpoints):

**Catálogos/Maestros:**
- `read-configuracion` / `read-status`
- `list-documento` / `list-documento-simbolo`
- `list-sexo` / `list-estado-civil`
- `list-pais` / `list-mapa-rutas`
- `list-departamento` / `list-provincia` / `list-ubigeo`
- `list-dia-semana`
- `list-cliente-tipo` / `list-cliente-tipo-negocio`
- `list-moneda`
- `list-unidad-medida`
- `list-rol-with-rol-categoria` / `list-ruta-with-zona`
- `list-lista-precios` / `list-almacen`
- `list-zona` / `list-ruta`
- `list-producto-clase` / `list-producto-subclase`
- `list-producto` / `list-producto-with-unidad-medida`
- `list-personal-with-codigo`
- `list-producto-tipo-afeccion-igv` / `list-producto-tipo-calculo-isc`
- `list-compra-tipo` / `list-venta-tipo`
- `list-tipo-redondeo`
- `list-proveedor-with-codigo`
- `list-segmento-sunat` / `list-familia-sunat` / `list-clase-sunat` / `list-clasificacion-sunat`

**Operativos:**
- `mis-zonas` / `mis-rutas`
- `list-cliente-with-codigo-by-dia-atencion`
- `list-productos-by-lista-precios`
- `list-productos-combos-by-lista-precios`
- `read-producto-parametros` / `read-combo-parametros`
- `list-tipo-nota-credito-error` / `list-tipo-nota-credito-total` / `list-tipo-nota-credito-parcial`
- `list-vendedores-activos` / `list-transportistas-activos` / `list-unidades-transporte-activos`
- `list-venta-tipo-preventa`
- `read-fechas-documentos`
- `list-productos-activos`
- `list-productos-servicio` / `read-servicio-parametros`
- `list-requisitos-canjes-activos`
- `read-cliente-parametros` / `list-cliente-parametros`
- `list-canje-premio` / `read-canje-parametros` / `list-canje-parametros`
- `list-producto-for-cambio` / `read-producto-parametros-for-cambio` / `list-producto-parametros-for-cambio`
- `list-item-venta` / `read-item-venta-parametros` / `list-item-venta-parametros`
- `update-cliente-dni` / `update-cliente-ruc`

---

## 5. Funcionalidades del Sitio

| Funcionalidad | Estado | Detalle |
|---|---|---|
| **Login/Autenticación** | ✅ | Sesión con remember-me |
| **Dashboard** | ✅ | `/app/dashboard` |
| **Administración** | ✅ | `/app/admin` |
| **Gestión de Clientes** | ✅ | CRUD con búsqueda por DNI/RUC, tipos, segmentos |
| **Catálogo de Productos** | ✅ | Jerarquía clase/subclase, IGV/ISC, unidad medida, combos |
| **Lista de Precios** | ✅ | Múltiples listas de precios |
| **Ventas** | ✅ | Tipos de venta, preventa, notas de crédito |
| **Compras** | ✅ | Tipos de compra |
| **Inventario/Almacén** | ✅ | Múltiples almacenes |
| **Rutas y Zonas** | ✅ | Gestión geográfica de ventas |
| **Vendedores** | ✅ | Vendedores activos |
| **Transportistas** | ✅ | Transportistas y unidades de transporte |
| **Programa de Canje/Puntos** | ✅ | Premios, requisitos, parámetros de canje |
| **Notas de Crédito** | ✅ | Error, total, parcial |
| **Cambios/Devoluciones** | ✅ | Productos para cambio |
| **Integración SUNAT** | ✅ | Segmento, familia, clase, clasificación SUNAT |
| **Tipo de Cambio** | ✅ | Moneda y redondeo |
| **Portal Cliente/Proveedor** | ❌ **No existe en legacy** | ARMORA actual es solo para uso interno (admin). Clientes/proveedores no tienen autoservicio — todo se gestiona por teléfono o visita. El nuevo sistema debe crear este portal desde cero |
| **PWA (offline)** | Parcial | Solo tiene manifest.json, no service worker detectado |
| **Búsqueda** | ✅ | Filtros por dropdowns en cascada |
| **Exportación/Reportes** | Probable | Tablas DataTable con exportación probable |

---

## 6. Observaciones de Seguridad

| Hallazgo | Severidad | Descripción |
|---|---|---|
| **CSRF deshabilitado** | 🔴 Alta | `csrf = false` explícito en el HTML. La app es vulnerable a Cross-Site Request Forgery |
| **Sin CSP** | 🟡 Media | No hay Content-Security-Policy, permitiendo potencial XSS |
| **Source maps / Bundles** | 🟢 Info | Sin source maps expuestos. Sin embargo, JS no ofuscado expone lógica de negocio |
| **Sin referrer-policy** | 🟢 Baja | Podría filtrar información en referrer headers |
| **Info en comentarios** | 🟢 Info | Sin información sensible en comentarios HTML |
| **robots.txt / sitemap.xml** | 🟢 Info | Retornan 404 (no existen). No hay exposición |
| **API endpoints expuestos** | 🟡 Media | ~60 endpoints REST listados en `catalogo.js` accesible públicamente. Aunque requieren sesión, el mapeo completo está visible |
| **Versión de jQuery 3.4.1** | 🟡 Media | Versión con vulnerabilidades conocidas (CVE-2020-11023, CVE-2020-11022) |
| **No rate limiting visible** | 🟡 Media | No se detectaron headers de rate limiting en endpoints de login |
| **Protocolo HTTP** | 🟢 Info | `const isHttps = false;` en login.js — el JS detecta si debe redirigir a HTTPS |

---

## 7. Archivos Estáticos Identificados

```
/app/
├── static/
│   ├── jquery/
│   │   ├── jquery-3.4.1.min.js
│   │   └── jquery-serialize-object.min.js
│   ├── semantic/
│   │   ├── semantic-dyvent.min.js
│   │   └── semantic-dyvent.min.css
│   ├── alert/
│   │   ├── ui-alert.js
│   │   └── ui-alert.css
│   ├── resources/
│   │   ├── css/blank.css
│   │   └── js/
│   │       ├── blank.js
│   │       ├── clases.js
│   │       └── catalogo.js
│   └── favicon/
│       ├── apple-touch-icon.png
│       ├── favicon-32x32.png
│       ├── favicon-16x16.png
│       ├── site.webmanifest
│       ├── safari-pinned-tab.svg
│       ├── android-chrome-192x192.png
│       └── android-chrome-384x384.png
├── resources/
│   └── general/
│       └── login/
│           ├── index.css
│           └── index.js
├── general/
│   └── loaded-resources/
│       └── rest/
│           └── login-image
├── login-check          (POST handler)
├── login                (GET view)
├── admin                (protegido)
├── dashboard            (protegido)
└── general/             (protegido)
    └── catalogo/
        └── rest/
            └── (60+ endpoints POST)
```

---

## 8. Resumen Ejecutivo

**ARMORA** es un **ERP web peruano** construido sobre **Java** (Servlet/Spring) con frontend tradicional **jQuery + Semantic UI 2.4.2**, alojado en un datacenter peruano (ON EMPRESAS S.A.C., Lima).

Se trata de un sistema de gestión empresarial completo que cubre:
- **Facturación electrónica** (con integración SUNAT)
- **Gestión de ventas** (preventa, notas de crédito)
- **Control de inventario** (almacenes, productos)
- **Gestión de clientes y rutas** (distribución)
- **Programa de fidelización** (canje de puntos)
- **Gestión de transporte** (transportistas, unidades)

La aplicación no usa frameworks SPA modernos (sin React/Vue/Angular), no tiene CSRF protection, y expone su mapeo completo de API REST en un JS público. Sin embargo, implementa buenas prácticas básicas de seguridad como HSTS, X-Frame-Options, y cookies HttpOnly+Secure.
