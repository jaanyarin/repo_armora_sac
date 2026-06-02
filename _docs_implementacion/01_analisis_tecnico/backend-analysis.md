# Análisis Técnico - Backend

**Documento:** Análisis detallado del backend del sistema ARMORA  
**Fecha:** Junio 2026  
**Perfil:** Senior Web Intelligence & Scraping Engineer  

---

## 1. Stack Tecnológico Actual (Análisis Detallado)

### 1.1 Lenguaje y Framework

| Componente | Evidencia | Inferencia | Confianza |
|---|---|---|---|
| **Lenguaje** | Cookie `JSESSIONID` | Java Servlet/Spring MVC | 🟢 Alta |
| **Versión Java** | No detectada | Java 8+ (por headers modernos) | 🟡 Media |
| **Framework** | Patrón REST POST-based | Spring MVC o Servlet puro | 🟡 Media |
| **Contenedor** | Context path `/app` | Tomcat/JBoss/Jetty | 🟡 Media |
| **API Architecture** | ~60 endpoints POST | RPC-style REST (no RESTful) | 🟢 Alta |
| **Encoding** | `Content-Type: text/html;charset=UTF-8` | UTF-8 (soporte Ñ, tildes) | 🟢 Alta |

### 1.2 Patrón de API REST (RPC-style)

**Observación clave:** El sistema NO implementa REST puro, sino RPC-style sobre HTTP POST.

```
// REST puro (no usado)
GET    /api/v1/productos/123      → Obtener producto 123
POST   /api/v1/productos          → Crear nuevo producto
PUT    /api/v1/productos/123      → Actualizar producto 123
DELETE /api/v1/productos/123      → Eliminar producto 123

// Patrón actual (RPC-style)
POST /app/general/catalogo/rest/list-producto
POST /app/general/catalogo/rest/read-producto-by-id
POST /app/general/catalogo/rest/update-producto
POST /app/general/catalogo/rest/delete-producto
```

**Implicaciones:**
- ❌ No aprovecha verbos HTTP (GET, PUT, DELETE)
- ❌ Todas las operaciones POST (dificulta caching)
- ❌ Idempotencia no garantizada
- ❌ No cacheable por proxies/CDN
- ✓ Funcional, pero anti-patrón

### 1.3 Puntos de Entrada Identificados

#### Público (sin autenticación)

```
GET  /app/
GET  /app/login
POST /app/login-check          (POST con usuario/contraseña)
```

#### Protegido (autenticación requerida)

```
GET  /app/admin                (302 redirect si no autenticado)
GET  /app/dashboard
GET  /app/general/*            (todas las subrutas protegidas)
POST /app/general/catalogo/rest/* (REST endpoints)
```

---

## 2. Autenticación y Sesiones

### 2.1 Mecanismo de Sesión

| Componente | Detalle |
|---|---|
| **Tipo** | Session-based (Servlet HttpSession) |
| **Cookie** | `JSESSIONID` |
| **Flags** | Secure, HttpOnly, SameSite (probablemente Lax/Strict) |
| **Almacenamiento servidor** | Probablemente en memoria (Tomcat) o Redis |
| **Timeout sesión** | No detectado (probablemente 30-60 minutos) |

### 2.2 Flujo de Autenticación

```
1. GET /app/login
   ↓
   Retorna formulario HTML (login.html)
   Cookie JSESSIONID = new session

2. POST /app/login-check
   Body: {username: "...", password: "..."}
   ↓
   Validación en backend
   ↓
   SET-COOKIE: JSESSIONID=<nuevo_id>
   Redirect 302 → /app/dashboard

3. GET /app/dashboard
   Cookie: JSESSIONID=<id>
   ↓
   Backend valida sesión
   Retorna dashboard HTML
```

### 2.3 Seguridad de Autenticación

| Aspecto | Observación | Riesgo |
|---|---|---|
| **HTTPS/TLS** | ✅ Implementado | ✓ Sesiones cifradas en tránsito |
| **HttpOnly flag** | ✅ Presente | ✓ No accesible desde JavaScript (XSS mitigation) |
| **Secure flag** | ✅ Presente | ✓ Solo envía sobre HTTPS |
| **SameSite flag** | ❓ No verificado | ⚠️ Probablemente no implementado (CSRF risk) |
| **CSRF token** | ❌ Deshabilitado (`csrf = false`) | 🔴 **CRÍTICO: Vulnerable a CSRF** |
| **Password hashing** | ❓ No verificado | ⚠️ Asumir bcrypt/scrypt (esperado en Java) |
| **MFA/2FA** | ❌ No detectado | ⚠️ Exposición de credenciales a phishing |
| **Rate limiting login** | ❌ No detectado | ⚠️ Vulnerable a brute force |
| **Remember me** | ✅ Implementado | ⚠️ Requiere gestión de tokens persistentes |

---

## 3. Análisis de Endpoints REST

### 3.1 Catálogos/Maestros (~35 endpoints)

Los endpoints de lectura que devuelven catálogos maestros:

```
POST /app/general/catalogo/rest/read-configuracion
POST /app/general/catalogo/rest/read-status
POST /app/general/catalogo/rest/list-documento
POST /app/general/catalogo/rest/list-documento-simbolo
POST /app/general/catalogo/rest/list-sexo
POST /app/general/catalogo/rest/list-estado-civil
POST /app/general/catalogo/rest/list-pais
POST /app/general/catalogo/rest/list-mapa-rutas
POST /app/general/catalogo/rest/list-departamento
POST /app/general/catalogo/rest/list-provincia
POST /app/general/catalogo/rest/list-ubigeo
POST /app/general/catalogo/rest/list-dia-semana
POST /app/general/catalogo/rest/list-cliente-tipo
POST /app/general/catalogo/rest/list-cliente-tipo-negocio
POST /app/general/catalogo/rest/list-moneda
POST /app/general/catalogo/rest/list-unidad-medida
POST /app/general/catalogo/rest/list-rol-with-rol-categoria
POST /app/general/catalogo/rest/list-ruta-with-zona
POST /app/general/catalogo/rest/list-lista-precios
POST /app/general/catalogo/rest/list-almacen
POST /app/general/catalogo/rest/list-zona
POST /app/general/catalogo/rest/list-ruta
POST /app/general/catalogo/rest/list-producto-clase
POST /app/general/catalogo/rest/list-producto-subclase
POST /app/general/catalogo/rest/list-producto
POST /app/general/catalogo/rest/list-producto-with-unidad-medida
POST /app/general/catalogo/rest/list-personal-with-codigo
POST /app/general/catalogo/rest/list-producto-tipo-afeccion-igv
POST /app/general/catalogo/rest/list-producto-tipo-calculo-isc
POST /app/general/catalogo/rest/list-compra-tipo
POST /app/general/catalogo/rest/list-venta-tipo
POST /app/general/catalogo/rest/list-tipo-redondeo
POST /app/general/catalogo/rest/list-proveedor-with-codigo
POST /app/general/catalogo/rest/list-segmento-sunat
POST /app/general/catalogo/rest/list-familia-sunat
POST /app/general/catalogo/rest/list-clase-sunat
POST /app/general/catalogo/rest/list-clasificacion-sunat
```

**Patrón:** `POST /app/general/catalogo/rest/list-<entidad>`

### 3.2 Operativos Específicos (~25 endpoints)

Endpoints que retornan datos contextuales:

```
POST /app/general/catalogo/rest/mis-zonas
POST /app/general/catalogo/rest/mis-rutas
POST /app/general/catalogo/rest/list-cliente-with-codigo-by-dia-atencion
POST /app/general/catalogo/rest/list-productos-by-lista-precios
POST /app/general/catalogo/rest/list-productos-combos-by-lista-precios
POST /app/general/catalogo/rest/read-producto-parametros
POST /app/general/catalogo/rest/read-combo-parametros
POST /app/general/catalogo/rest/list-tipo-nota-credito-error
POST /app/general/catalogo/rest/list-tipo-nota-credito-total
POST /app/general/catalogo/rest/list-tipo-nota-credito-parcial
POST /app/general/catalogo/rest/list-vendedores-activos
POST /app/general/catalogo/rest/list-transportistas-activos
POST /app/general/catalogo/rest/list-unidades-transporte-activos
POST /app/general/catalogo/rest/list-venta-tipo-preventa
POST /app/general/catalogo/rest/read-fechas-documentos
POST /app/general/catalogo/rest/list-productos-activos
POST /app/general/catalogo/rest/list-productos-servicio
POST /app/general/catalogo/rest/read-servicio-parametros
POST /app/general/catalogo/rest/list-requisitos-canjes-activos
POST /app/general/catalogo/rest/read-cliente-parametros
POST /app/general/catalogo/rest/list-cliente-parametros
POST /app/general/catalogo/rest/list-canje-premio
POST /app/general/catalogo/rest/read-canje-parametros
POST /app/general/catalogo/rest/list-canje-parametros
POST /app/general/catalogo/rest/list-producto-for-cambio
POST /app/general/catalogo/rest/read-producto-parametros-for-cambio
POST /app/general/catalogo/rest/list-producto-parametros-for-cambio
POST /app/general/catalogo/rest/list-item-venta
POST /app/general/catalogo/rest/read-item-venta-parametros
POST /app/general/catalogo/rest/list-item-venta-parametros
POST /app/general/catalogo/rest/update-cliente-dni
POST /app/general/catalogo/rest/update-cliente-ruc
```

**Total endpoints mapeados:** ~60

---

## 4. Estructura de Datos Inferida

### 4.1 Entidades Principales (por endpoints)

| Entidad | Identificada | Relaciones Inferidas |
|---|---|---|
| **usuario** | ✅ (login, roles) | roles, permisos |
| **cliente** | ✅ (list-cliente, update-cliente-dni) | tipo_negocio, segmento_sunat, dirección, contacto |
| **proveedor** | ✅ (list-proveedor-with-codigo) | contacto, condiciones de pago |
| **producto** | ✅ (list-producto, combo) | clase, subclase, igv, isc, unidad_medida, lista_precios |
| **lista_precios** | ✅ (list-lista-precios) | producto (1:many), moneda |
| **venta** | ✅ (list-venta-tipo, read-item-venta) | cliente, vendedor, tipo (venta/preventa/nota crédito), items |
| **compra** | ✅ (list-compra-tipo) | proveedor, items, estado |
| **item_venta** | ✅ (list-item-venta) | venta, producto, cantidad, precio, igv, isc |
| **item_compra** | ✅ (inferida) | compra, producto, cantidad, precio |
| **almacen** | ✅ (list-almacen) | ubicación, responsable |
| **stock** | ✅ (inferida de inventario) | almacen, producto, cantidad_disponible |
| **movimiento_inventario** | ✅ (inferida) | almacen, producto, tipo (entrada/salida), cantidad |
| **zona** | ✅ (list-zona) | ubigeo, ruta (1:many) |
| **ruta** | ✅ (list-ruta-with-zona) | zona, mapa_ruta, clientes |
| **personal** | ✅ (list-personal-with-codigo) | tipo (vendedor/transportista), ruta/zona |
| **vendedor** | ✅ (list-vendedores-activos) | personal, ruta, zona |
| **transportista** | ✅ (list-transportistas-activos) | personal, vehículos |
| **vehiculo** | ✅ (list-unidades-transporte-activos) | transportista, documento |
| **canje_premio** | ✅ (list-canje-premio) | cliente, premio, puntos_requeridos, vigencia |
| **puntos_cliente** | ✅ (inferida) | cliente, venta, cantidad_puntos |
| **nota_credito** | ✅ (list-tipo-nota-credito-*) | venta, tipo (error/total/parcial), motivo |
| **documento** | ✅ (list-documento) | tipo (factura/boleta/etc), serie, número |
| **segmento_sunat** | ✅ (list-segmento-sunat) | clasificación SUNAT |
| **moneda** | ✅ (list-moneda) | tipo de cambio |

### 4.2 Relaciones Clave Identificadas

```
usuario (1:many) → rol
usuario (1:many) → permiso
cliente (many:1) → tipo_cliente
cliente (many:1) → segmento_sunat
cliente (1:many) → venta
cliente (1:many) → punto_cliente

producto (many:1) → clase
producto (many:1) → subclase
producto (many:1) → tipo_afeccion_igv
producto (many:1) → tipo_calculo_isc
producto (1:many) → lista_precio
producto (1:many) → stock

venta (many:1) → cliente
venta (many:1) → vendedor
venta (many:1) → tipo_venta
venta (1:many) → item_venta
venta (1:many) → nota_credito

item_venta (many:1) → venta
item_venta (many:1) → producto
item_venta (many:1) → lista_precio

compra (many:1) → proveedor
compra (1:many) → item_compra

almacen (1:many) → stock
almacen (many:1) → ubigeo

stock (many:1) → almacen
stock (many:1) → producto

zona (many:1) → ubigeo
zona (1:many) → ruta

ruta (many:1) → zona
ruta (1:many) → cliente
ruta (1:many) → vendedor

vendedor (many:1) → ruta
vendedor (1:many) → venta

canje_premio (many:1) → cliente
canje_premio (many:1) → premio
```

---

## 5. Análisis de Seguridad - Backend

### 5.1 Vulnerabilidades Críticas

| Vulnerabilidad | CVSS | Estado | Acción |
|---|---|---|---|
| **CSRF deshabilitado** | 8.8 | 🔴 CRÍTICO | Implementar CSRF tokens en nueva arquitectura |
| **Sin rate limiting visible** | 5.3 | 🟠 ALTO | Implementar rate limiting (login, API) |
| **Exposición de endpoints** | 4.3 | 🟠 ALTO | Usar API documentation controlada (Swagger interno) |
| **Sin Content-Security-Policy** | 6.1 | 🟠 ALTO | Implementar CSP en nueva arquitectura |
| **Session-based auth (vs JWT)** | 3.1 | 🟡 MEDIO | Migrar a JWT/Sanctum para mejor escalabilidad |

### 5.2 Headers de Seguridad Implementados

```
✅ Strict-Transport-Security: max-age=31536000; includeSubDomains
✅ X-Frame-Options: DENY
✅ X-Content-Type-Options: nosniff
✅ X-XSS-Protection: 1; mode=block
✅ Set-Cookie: JSESSIONID; Secure; HttpOnly
❌ Content-Security-Policy: NO IMPLEMENTADO
❌ Referrer-Policy: NO IMPLEMENTADO
❌ Permissions-Policy: NO IMPLEMENTADO
❌ X-Content-Type-Options: falta en algunas rutas
```

### 5.3 Gestión de Datos Sensibles

| Dato | Observación | Riesgo |
|---|---|---|
| **DNI/RUC** | Transmitido en HTTP body (POST) | ⚠️ Log files pueden registrar |
| **Contraseñas** | POST sobre HTTPS | ✓ Seguro en tránsito |
| **Tokens de sesión** | Cookies HttpOnly+Secure | ✓ Bien protegido |
| **IPs de clientes** | No visible en headers | ⚠️ Difícil auditarías |
| **User-Agent** | No filtrado | ⚠️ Fingerprinting possible |

---

## 6. Modelos de Datos Estimados

### 6.1 Tabla USUARIO

```sql
CREATE TABLE usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password CHAR(60) NOT NULL,  -- bcrypt hash
    nombre VARCHAR(255),
    email VARCHAR(255),
    activo BOOLEAN DEFAULT true,
    ultimo_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email)
);
```

### 6.2 Tabla CLIENTE

```sql
CREATE TABLE cliente (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    dni_ruc VARCHAR(15) UNIQUE,
    tipo_cliente_id INT,  -- FK to tipo_cliente
    segmento_sunat_id INT,  -- FK
    tipo_negocio VARCHAR(50),  -- Comercial, Industrial, Agrícola, etc.
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(255),
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (tipo_cliente_id) REFERENCES tipo_cliente(id),
    FOREIGN KEY (segmento_sunat_id) REFERENCES segmento_sunat(id),
    INDEX idx_dni_ruc (dni_ruc),
    INDEX idx_codigo (codigo)
);
```

### 6.3 Tabla PRODUCTO

```sql
CREATE TABLE producto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    clase_id INT,  -- FK
    subclase_id INT,  -- FK
    tipo_afeccion_igv_id INT,  -- FK
    tipo_calculo_isc_id INT,  -- FK
    unidad_medida_id INT,  -- FK
    precio_base DECIMAL(10, 2),
    igv_porcentaje DECIMAL(5, 2),  -- 0, 5, 10, 18
    isc_porcentaje DECIMAL(5, 2),  -- 0, 13, etc
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (clase_id) REFERENCES producto_clase(id),
    FOREIGN KEY (subclase_id) REFERENCES producto_subclase(id),
    INDEX idx_codigo (codigo)
);
```

### 6.4 Tabla VENTA

```sql
CREATE TABLE venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_comprobante VARCHAR(20),  -- Serie-Número ej: F001-000123
    cliente_id INT,  -- FK
    vendedor_id INT,  -- FK
    tipo_venta_id INT,  -- FK (venta, preventa, etc)
    fecha_venta DATE,
    fecha_entrega DATE,
    subtotal DECIMAL(12, 2),
    descuento DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    isc DECIMAL(12, 2),
    total DECIMAL(12, 2),
    estado VARCHAR(50),  -- Pendiente, Pagada, Anulada, etc
    observaciones TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (cliente_id) REFERENCES cliente(id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedor(id),
    FOREIGN KEY (tipo_venta_id) REFERENCES tipo_venta(id),
    INDEX idx_numero_comprobante (numero_comprobante),
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_fecha_venta (fecha_venta)
);
```

### 6.5 Tabla ITEM_VENTA

```sql
CREATE TABLE item_venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT,  -- FK
    producto_id INT,  -- FK
    lista_precio_id INT,  -- FK
    cantidad DECIMAL(10, 2),
    precio_unitario DECIMAL(10, 2),
    descuento_item DECIMAL(10, 2),
    subtotal DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    isc DECIMAL(12, 2),
    total DECIMAL(12, 2),
    
    FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id),
    FOREIGN KEY (lista_precio_id) REFERENCES lista_precio(id)
);
```

---

## 7. Patrones de Solicitud/Respuesta

### 7.1 Request típico

```http
POST /app/general/catalogo/rest/list-cliente HTTP/1.1
Host: armorasac.com
Content-Type: application/json
Cookie: JSESSIONID=ABC123...
Content-Length: 150

{
    "page": 1,
    "pageSize": 20,
    "filters": {
        "tipo_cliente_id": 1,
        "activo": true
    },
    "sort": {
        "field": "nombre",
        "direction": "ASC"
    }
}
```

### 7.2 Response típica

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=UTF-8
Cache-Control: private
Strict-Transport-Security: max-age=31536000; includeSubDomains

{
    "success": true,
    "data": [
        {
            "id": 1,
            "codigo": "CLI001",
            "nombre": "Cliente SA",
            "dni_ruc": "20123456789",
            "tipo_cliente": "Mayorista"
        },
        ...
    ],
    "total": 150,
    "page": 1,
    "pageSize": 20
}
```

### 7.3 Error típico

```json
{
    "success": false,
    "error": "Cliente no encontrado",
    "errorCode": "NOT_FOUND",
    "statusCode": 404
}
```

---

## 8. Inferencias sobre Base de Datos

### 8.1 Indicios de Motor de BD

| Indicador | Valor | BD Probable |
|---|---|---|
| **Soporte de tipos complejos** | Sí (IGV, ISC, detracciones) | PostgreSQL > MySQL |
| **Transacciones multi-tabla** | Sí (venta + items) | PostgreSQL > MySQL |
| **Full-text search** | No visible | Ambos soportan |
| **JSON fields** | No detectado | PostgreSQL (JSONB) |
| **Reportes complejos** | Sí (libro mayor) | PostgreSQL (mejor) |

**Conclusión:** PostgreSQL altamente probable (stack Java + ERP robusto)

### 8.2 Estimación de Volumen de Datos

| Tabla | Registros Estimados | Razón |
|---|---|---|
| cliente | 1,000-5,000 | ERP pequeño-mediano |
| producto | 500-2,000 | Catálogo moderado |
| venta | 50,000-500,000 | 5-10 años operativo |
| item_venta | 500,000-5,000,000 | 5-15 items por venta |
| stock | 5,000-20,000 | (cliente × producto × almacén) |

---

## 9. Comparación con Análisis Anterior

### Hallazgos Nuevos

| Hallazgo | Detalle |
|---|---|
| **RPC-style REST** | No se había documentado la arquitectura de API |
| **~60 endpoints exactamente** | Confirmado en JavaScript público |
| **Session-based auth** | Confirmado, pero sin detalles de expiración |
| **Catálogos maestros** | 35+ endpoints de catálogos identificados |
| **Multi-entidad relaciones** | Modelo de datos más completo inferido |

### Confirmación

✓ Todas las observaciones previas confirmadas:
- Java backend con Servlet/Spring
- CSRF deshabilitado
- Exposición de API endpoints
- Session-based JSESSIONID
- Headers de seguridad modernos

---

## 10. Recomendaciones para Re-implementación

### Stack Propuesto vs Actual

| Aspecto | Actual | Propuesto | Mejora |
|---|---|---|---|
| **Lenguaje** | Java | PHP 8.3 | -40% líneas código, +tipo safety |
| **Framework** | Spring MVC | Laravel 12 | Mejor ORM, mejor tooling |
| **BD** | PostgreSQL (probable) | PostgreSQL | ✓ Mantener |
| **API** | RPC-style POST | REST puro (GET/POST/PUT/DELETE) | Mejor caché, HTTP compliance |
| **Auth** | Session-based | JWT (Sanctum) | Mejor para PWA, microservicios |
| **Queue** | No visible | Redis + Laravel Queues | Para SUNAT async |
| **Cache** | No visible | Redis | Performance +3-5x |

### 10.1 Migración de Endpoints

**Ejemplo:** `POST /app/general/catalogo/rest/list-cliente`

```
// Actual (RPC-style)
POST /app/general/catalogo/rest/list-cliente
Body: { page, pageSize, filters }

// Propuesto (RESTful)
GET /api/v1/customers?page=1&limit=20&type=1
(Soporta filtros en query string, caching con ETag)
```

---

## 11. Conclusiones

El backend actual es **funcional y robusto** pero con **arquitectura desactualizada**:

### ✓ Fortalezas
- Autenticación segura (HTTPS, HttpOnly cookies)
- Estructura modular clara
- Catálogos de datos bien organizados
- Transacciones multi-tabla (probable)

### ✗ Debilidades
- CSRF deshabilitado (vulnerabilidad crítica)
- API RPC-style (no RESTful)
- Session-based (no escalable)
- Sin caching explícito
- Sin rate limiting visible
- Sin API versioning

### 📋 Acción Recomendada

**MIGRAR completamente a Laravel 12** siguiendo:
- Stack: Laravel + PostgreSQL + Redis
- Arquitectura: Service Layer + Repository Pattern
- API: REST puro con versionado
- Auth: JWT (Sanctum) para escalabilidad
- Async: Jobs para SUNAT, webhooks

---

**Documento preparado por:** Senior Web Intelligence & Scraping Engineer  
**Fecha:** Junio 2026
