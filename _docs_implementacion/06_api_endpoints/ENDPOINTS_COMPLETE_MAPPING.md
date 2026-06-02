# Mapeo Completo de Endpoints REST - ARMORA

**Documento:** Documentación exhaustiva de los 60+ endpoints REST del sistema ARMORA actual  
**Fecha:** Junio 2026  
**Fuente:** Análisis de `catalogo.js` y scraping de armorasac.com/app  

---

## 1. Estructura de Endpoints

**Patrón:** `POST /app/general/catalogo/rest/<endpoint-name>`

### 1.1 Convenciones

| Patrón | Descripción | Ejemplo |
|---|---|---|
| `list-<entidad>` | Obtener listado (paginado) | `list-cliente` |
| `read-<entidad>-by-<criterio>` | Obtener por criterio específico | `read-producto-by-id` |
| `read-<entidad>-parametros` | Obtener parámetros/config | `read-producto-parametros` |
| `update-<entidad>-<campo>` | Actualizar campo específico | `update-cliente-dni` |
| `mis-<entidades>` | Obtener registros del usuario logueado | `mis-zonas`, `mis-rutas` |

---

## 2. Endpoints de Catálogos Maestros (35 endpoints)

### 2.1 Configuración y Estatus

```
POST /app/general/catalogo/rest/read-configuracion
    ↓ Retorna configuración global del sistema
    Response: { idioma, zona_horaria, formato_fecha, formato_moneda, ... }

POST /app/general/catalogo/rest/read-status
    ↓ Estado actual del sistema (health check)
    Response: { status: "OK", version, uptime, ... }
```

### 2.2 Documentos (Facturación SUNAT)

```
POST /app/general/catalogo/rest/list-documento
    ↓ Tipos de documento (factura, boleta, etc)
    Response: [ { id: 1, nombre: "Factura", codigo: "01" }, ... ]

POST /app/general/catalogo/rest/list-documento-simbolo
    ↓ Símbolos de serie de documentos
    Response: [ { id: 1, simbolo: "F", documento_id: 1 }, ... ]
```

### 2.3 Datos de Registro (Perú)

```
POST /app/general/catalogo/rest/list-sexo
    ↓ Opciones de sexo (M, F, Otro)

POST /app/general/catalogo/rest/list-estado-civil
    ↓ Estados civiles (Soltero, Casado, Divorciado, Viudo)

POST /app/general/catalogo/rest/list-pais
    ↓ Lista de países (Perú, Bolivia, Chile, etc)
```

### 2.4 Geolocalización (Perú)

```
POST /app/general/catalogo/rest/list-mapa-rutas
    ↓ Mapa de rutas de distribución
    Response: [ { id, nombre, zona_id, ubigeo_id, ... } ]

POST /app/general/catalogo/rest/list-departamento
    ↓ Departamentos peruanos (24)
    Response: [ { id: 1, nombre: "Lima", codigo: "15" }, ... ]

POST /app/general/catalogo/rest/list-provincia
    ↓ Provincias peruanas (por departamento)
    Request: { departamento_id: 15 }
    Response: [ { id, nombre, codigo, ... } ]

POST /app/general/catalogo/rest/list-ubigeo
    ↓ UBIGEO (Código de localización geográfica)
    Request: { provincia_id: 150131 }
    Response: [ { id, ubigeo, nombre, latitud, longitud } ]

POST /app/general/catalogo/rest/list-dia-semana
    ↓ Días de la semana (español/inglés)
    Response: [ { id: 1, nombre: "Lunes", codigo: "LUN" }, ... ]
```

### 2.5 Clientes y Tipos

```
POST /app/general/catalogo/rest/list-cliente-tipo
    ↓ Tipos de cliente (Mayorista, Minorista, Distribuidor, etc)

POST /app/general/catalogo/rest/list-cliente-tipo-negocio
    ↓ Tipos de negocio del cliente (Comercial, Industrial, Agrícola, etc)
```

### 2.6 Moneda y Finanzas

```
POST /app/general/catalogo/rest/list-moneda
    ↓ Monedas (PEN, USD, EUR, etc)
    Response: [ { id: 1, codigo: "PEN", nombre: "Sol", simbolo: "S/" }, ... ]

POST /app/general/catalogo/rest/list-tipo-redondeo
    ↓ Métodos de redondeo (Normal, Hacia arriba, Hacia abajo)
```

### 2.7 Productos y Unidades

```
POST /app/general/catalogo/rest/list-unidad-medida
    ↓ Unidades de medida (kg, l, metros, piezas, cajas, etc)
    Response: [ { id, codigo: "kg", nombre: "Kilogramo", abreviatura: "KG" }, ... ]

POST /app/general/catalogo/rest/list-producto-clase
    ↓ Clases de producto (Bebidas, Alimentos, Electrónica, etc)

POST /app/general/catalogo/rest/list-producto-subclase
    ↓ Subclases (Bebidas → Refrescos, Jugos, Cerveza, etc)
    Request: { clase_id: 1 }

POST /app/general/catalogo/rest/list-producto
    ↓ Catálogo completo de productos (paginado)
    Request: { page: 1, pageSize: 50, filters: { clase_id?, activo? } }
    Response: { data: [ ... ], total: 1234, page: 1 }

POST /app/general/catalogo/rest/list-producto-with-unidad-medida
    ↓ Productos con unidad de medida precargada
```

### 2.8 Productos - Impuestos

```
POST /app/general/catalogo/rest/list-producto-tipo-afeccion-igv
    ↓ Tipo de afectación de IGV (Gravada, Exonerada, Inafecta)
    Response: [ { id: 1, codigo: "10", nombre: "Gravada" }, ... ]

POST /app/general/catalogo/rest/list-producto-tipo-calculo-isc
    ↓ Tipo de cálculo de ISC (No lleva ISC, Cigarrillos, Bebidas, Combustible)
    Response: [ { id: 1, nombre: "No lleva ISC", porcentaje: 0 }, ... ]
```

### 2.9 Precios y Almacenes

```
POST /app/general/catalogo/rest/list-lista-precios
    ↓ Listas de precios disponibles (Mayorista, Minorista, Distribuidor, etc)
    Response: [ { id: 1, nombre: "Mayorista", descuento_percent: 10 }, ... ]

POST /app/general/catalogo/rest/list-almacen
    ↓ Almacenes/depósitos
    Response: [ { id: 1, nombre: "Almacén Central", ubicacion: "Lima", ... } ]
```

### 2.10 Gestión de Personal y Rutas

```
POST /app/general/catalogo/rest/list-rol-with-rol-categoria
    ↓ Roles y categorías de roles
    Response: [ { id: 1, nombre: "Vendedor", categoria: "Comercial" }, ... ]

POST /app/general/catalogo/rest/list-zona
    ↓ Zonas geográficas de distribución
    Response: [ { id: 1, nombre: "Zona Centro", ubigeo_id: 150131 }, ... ]

POST /app/general/catalogo/rest/list-ruta-with-zona
    ↓ Rutas de distribución con su zona asignada
    Response: [ { id: 1, nombre: "Ruta 01", zona_id: 1, clientes_count: 45 }, ... ]

POST /app/general/catalogo/rest/list-personal-with-codigo
    ↓ Personal (vendedores, transportistas, etc)
    Response: [ { id: 1, codigo: "V001", nombre: "Juan Pérez", tipo: "Vendedor" }, ... ]
```

### 2.11 Tipos de Transacciones

```
POST /app/general/catalogo/rest/list-venta-tipo
    ↓ Tipos de venta (Venta Normal, Preventa, Nota Crédito, etc)
    Response: [ { id: 1, nombre: "Venta Normal", codigo: "01" }, ... ]

POST /app/general/catalogo/rest/list-compra-tipo
    ↓ Tipos de compra
    Response: [ { id: 1, nombre: "Compra Normal", codigo: "01" }, ... ]
```

### 2.12 Catálogos SUNAT (Facturación Electrónica)

```
POST /app/general/catalogo/rest/list-segmento-sunat
    ↓ Segmento SUNAT (Clasificación de cliente)
    Response: [ { id: 1, codigo: "01", nombre: "Comercial", ... } ]

POST /app/general/catalogo/rest/list-familia-sunat
    ↓ Familia de productos SUNAT
    Response: [ { id: 1, codigo: "01", nombre: "Alimentos y Bebidas" } ]

POST /app/general/catalogo/rest/list-clase-sunat
    ↓ Clase de productos SUNAT
    Request: { familia_sunat_id: 1 }

POST /app/general/catalogo/rest/list-clasificacion-sunat
    ↓ Clasificación detallada SUNAT
```

---

## 3. Endpoints Operativos Específicos (25 endpoints)

### 3.1 Datos del Usuario Logueado

```
POST /app/general/catalogo/rest/mis-zonas
    ↓ Zonas asignadas al vendedor logueado
    Response: [ { id: 1, nombre: "Zona Centro", clientes: 45 } ]

POST /app/general/catalogo/rest/mis-rutas
    ↓ Rutas asignadas al vendedor logueado
    Response: [ { id: 1, nombre: "Ruta 01-Centro", clientes: 15 } ]
```

### 3.2 Búsqueda de Clientes

```
POST /app/general/catalogo/rest/list-cliente-with-codigo-by-dia-atencion
    ↓ Clientes por día de atención (para rutas)
    Request: { dia_semana: 1, zona_id: 1 }
    Response: [ { id, codigo, nombre, direccion, telefono, ... } ]
```

### 3.3 Productos y Listas de Precios

```
POST /app/general/catalogo/rest/list-productos-by-lista-precios
    ↓ Productos disponibles en una lista de precios específica
    Request: { lista_precio_id: 1 }
    Response: [ { id, codigo, nombre, precio, igv, isc, ... } ]

POST /app/general/catalogo/rest/list-productos-combos-by-lista-precios
    ↓ Combos/paquetes disponibles en lista de precios
    Request: { lista_precio_id: 1 }
    Response: [ { id, nombre, precio_combo, items: [ ... ] } ]

POST /app/general/catalogo/rest/read-producto-parametros
    ↓ Parámetros y configuración de producto
    Request: { producto_id: 123 }
    Response: { 
        producto: { id, nombre, ... },
        stock: { almacen_central: 50, almacen_secundario: 30 },
        precios: [ ... ],
        combos_que_lo_contienen: [ ... ]
    }

POST /app/general/catalogo/rest/read-combo-parametros
    ↓ Parámetros de combo (items, cantidad, precio)
    Request: { combo_id: 456 }
    Response: { combo: { ... }, items: [ { producto, cantidad, ... } ] }
```

### 3.4 Notas de Crédito

```
POST /app/general/catalogo/rest/list-tipo-nota-credito-error
    ↓ Tipos de nota de crédito por ERROR (descuento, devolución, etc)

POST /app/general/catalogo/rest/list-tipo-nota-credito-total
    ↓ Tipos de nota de crédito TOTAL (anulación completa)

POST /app/general/catalogo/rest/list-tipo-nota-credito-parcial
    ↓ Tipos de nota de crédito PARCIAL (devolución parcial)
```

### 3.5 Personal Activo

```
POST /app/general/catalogo/rest/list-vendedores-activos
    ↓ Vendedores disponibles para asignar ventas
    Response: [ { id: 1, codigo: "V001", nombre: "Juan Pérez", zona_id: 1 } ]

POST /app/general/catalogo/rest/list-transportistas-activos
    ↓ Transportistas disponibles para despacho
    Response: [ { id: 1, codigo: "T001", nombre: "Carlos Salazar", empresa: "Transportes XYZ" } ]

POST /app/general/catalogo/rest/list-unidades-transporte-activos
    ↓ Vehículos disponibles para despacho
    Response: [ { id: 1, placa: "ABC-1234", tipo: "Camión", transportista_id: 1 } ]
```

### 3.6 Ventas y Preventas

```
POST /app/general/catalogo/rest/list-venta-tipo-preventa
    ↓ Tipos de preventa (Pedido, Separado, etc)
    Response: [ { id: 1, nombre: "Pedido", dias_validez: 7 } ]

POST /app/general/catalogo/rest/read-fechas-documentos
    ↓ Rango de fechas disponibles para documentos
    Response: {
        fecha_min: "2025-01-01",
        fecha_max: "2026-12-31",
        fecha_sistema: "2026-06-02"
    }
```

### 3.7 Productos Activos y Servicios

```
POST /app/general/catalogo/rest/list-productos-activos
    ↓ Solo productos activos (excluye inactivos)
    Response: [ { id, codigo, nombre, precio, ... } ]

POST /app/general/catalogo/rest/list-productos-servicio
    ↓ Servicios (servicios técnicos, consultoría, etc)
    Response: [ { id, nombre, precio_por_unidad, ... } ]

POST /app/general/catalogo/rest/read-servicio-parametros
    ↓ Parámetros de servicio específico
    Request: { servicio_id: 789 }
    Response: { servicio: { ... }, duracion: "horas", precio: ... }
```

### 3.8 Programa de Canje

```
POST /app/general/catalogo/rest/list-requisitos-canjes-activos
    ↓ Requisitos para participar en canjes
    Response: [ { id, descripcion: "Mínimo 50 puntos", activo: true } ]

POST /app/general/catalogo/rest/list-canje-premio
    ↓ Premios disponibles para canje
    Response: [
        { id: 1, nombre: "Bono S/. 50", puntos_requeridos: 100, stock: 45 },
        { id: 2, nombre: "Producto XYZ", puntos_requeridos: 200, stock: 10 }
    ]

POST /app/general/catalogo/rest/read-canje-parametros
    ↓ Parámetros globales del programa de canje
    Response: {
        puntos_por_sol: 1,
        vigencia_dias: 365,
        fecha_reset: "2026-12-31"
    }

POST /app/general/catalogo/rest/list-canje-parametros
    ↓ Parámetros por tipo de cliente/rango
    Response: [ { cliente_tipo_id: 1, puntos_por_sol: 1.5 } ]
```

### 3.9 Parámetros de Cliente

```
POST /app/general/catalogo/rest/read-cliente-parametros
    ↓ Parámetros específicos de cliente (límite crédito, día de pago, etc)
    Request: { cliente_id: 456 }
    Response: {
        cliente: { id, nombre, ... },
        credito_limite: 50000,
        credito_disponible: 30000,
        dia_pago_preferido: 20,
        descuento_comercial: 5
    }

POST /app/general/catalogo/rest/list-cliente-parametros
    ↓ Parámetros por tipo de cliente
    Response: [
        { cliente_tipo_id: 1, dias_credito: 30, descuento_max: 10 }
    ]
```

### 3.10 Cambios y Devoluciones

```
POST /app/general/catalogo/rest/list-producto-for-cambio
    ↓ Productos elegibles para cambio/devolución
    Response: [ { id, nombre, precio, estado: "Devolvible" } ]

POST /app/general/catalogo/rest/read-producto-parametros-for-cambio
    ↓ Parámetros para cambio de producto específico
    Request: { producto_id: 123 }
    Response: { producto: { ... }, dias_cambio: 30, condiciones: [ ... ] }

POST /app/general/catalogo/rest/list-producto-parametros-for-cambio
    ↓ Políticas de cambio por tipo de producto
    Response: [ { producto_clase_id: 1, dias_cambio: 30, max_cambios: 2 } ]
```

### 3.11 Líneas de Venta

```
POST /app/general/catalogo/rest/list-item-venta
    ↓ Items de una venta específica
    Request: { venta_id: 789 }
    Response: [
        { id: 1, producto_id: 123, cantidad: 10, precio: 50, total: 500 }
    ]

POST /app/general/catalogo/rest/read-item-venta-parametros
    ↓ Parámetros de línea de venta
    Request: { item_venta_id: 456 }
    Response: { item: { ... }, producto: { ... }, lista_precio: { ... } }

POST /app/general/catalogo/rest/list-item-venta-parametros
    ↓ Configuración general para items de venta
    Response: {
        descuento_maximo: 50,
        decimales_cantidad: 2,
        igv_defecto: 18
    }
```

### 3.12 Actualizaciones de Datos Maestros

```
POST /app/general/catalogo/rest/update-cliente-dni
    ↓ Actualizar DNI de cliente
    Request: { cliente_id: 123, dni: "12345678" }
    Response: { success: true, cliente: { ... } }

POST /app/general/catalogo/rest/update-cliente-ruc
    ↓ Actualizar RUC de cliente
    Request: { cliente_id: 123, ruc: "20123456789" }
    Response: { success: true, cliente: { ... } }
```

---

## 4. Patrones de Solicitud y Respuesta

### 4.1 Request Típica - Listado Paginado

```http
POST /app/general/catalogo/rest/list-cliente HTTP/1.1
Content-Type: application/json
Cookie: JSESSIONID=ABC123DEF456...

{
    "page": 1,
    "pageSize": 50,
    "search": {
        "nombre": "Acme",
        "tipo_cliente_id": 1,
        "activo": true
    },
    "sort": {
        "field": "nombre",
        "direction": "ASC"
    }
}
```

### 4.2 Response Típica - Listado

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "codigo": "CLI001",
            "nombre": "Acme Corporation SA",
            "dni_ruc": "20123456789",
            "tipo_cliente": "Mayorista",
            "zona_id": 1,
            "credito_limite": 50000,
            "activo": true,
            "createdAt": "2024-01-15"
        },
        ...
    ],
    "total": 1245,
    "page": 1,
    "pageSize": 50,
    "pages": 25
}
```

### 4.3 Request Típica - Read/Detalle

```http
POST /app/general/catalogo/rest/read-producto-parametros HTTP/1.1
Content-Type: application/json
Cookie: JSESSIONID=ABC123DEF456...

{
    "producto_id": 789
}
```

### 4.4 Response Típica - Detalle

```json
{
    "success": true,
    "data": {
        "producto": {
            "id": 789,
            "codigo": "PROD001",
            "nombre": "Producto XYZ",
            "descripcion": "Descripción del producto",
            "precio_costo": 100,
            "precio_lista": 150,
            "igv_porcentaje": 18,
            "isc_porcentaje": 0,
            "activo": true
        },
        "stock": {
            "almacen_central": 100,
            "almacen_secundario": 50,
            "total": 150
        },
        "precios": [
            {
                "lista_precio_id": 1,
                "nombre": "Mayorista",
                "precio": 120,
                "descuento_percent": 20
            }
        ]
    }
}
```

### 4.5 Error Response

```json
{
    "success": false,
    "error": "Producto no encontrado",
    "errorCode": "NOT_FOUND",
    "statusCode": 404,
    "details": "El producto con ID 999 no existe en la base de datos"
}
```

---

## 5. Autenticación y Headers Requeridos

### 5.1 Headers Obligatorios

```http
POST /app/general/catalogo/rest/list-cliente HTTP/1.1
Host: armorasac.com
Content-Type: application/json
Cookie: JSESSIONID=ABC123...
```

### 5.2 Validación de Sesión

- **Requerido:** Cookie `JSESSIONID` válida
- **Sin autenticación:** HTTP 302 (redirect a `/app/login`)
- **Sesión expirada:** HTTP 401 (Unauthorized)

---

## 6. Notas de Implementación para Nueva API REST

### 6.1 Transformación de Endpoints (Actual → Propuesto)

```
ACTUAL (RPC-style POST)
POST /app/general/catalogo/rest/list-cliente
Body: { page, pageSize, filters }

PROPUESTO (REST puro)
GET /api/v1/customers?page=1&limit=50&type=1&search=Acme
Headers: Authorization: Bearer <JWT_TOKEN>

BENEFICIOS:
✅ Cacheable (GET con ETag)
✅ Standard HTTP semantics
✅ Mejor documentación (OpenAPI/Swagger)
✅ CORS más seguro
✅ Versioning explícito (/v1/)
```

### 6.2 Recursos RESTful Propuestos

```
AUTENTICACIÓN:
POST   /api/v1/auth/login              # Login (username/password)
POST   /api/v1/auth/logout             # Logout
POST   /api/v1/auth/refresh            # Refresh JWT token

CLIENTES:
GET    /api/v1/customers               # Listar (paginado, filtros)
GET    /api/v1/customers/:id           # Detalle
POST   /api/v1/customers               # Crear
PUT    /api/v1/customers/:id           # Actualizar
DELETE /api/v1/customers/:id           # Soft delete

PRODUCTOS:
GET    /api/v1/products                # Listar
GET    /api/v1/products/:id            # Detalle con stock
POST   /api/v1/products                # Crear
PUT    /api/v1/products/:id            # Actualizar

VENTAS:
GET    /api/v1/sales                   # Listar
GET    /api/v1/sales/:id               # Detalle
POST   /api/v1/sales                   # Crear venta
PUT    /api/v1/sales/:id/state         # Cambiar estado
POST   /api/v1/sales/:id/credit-notes  # Crear nota crédito

INVENTARIO:
GET    /api/v1/inventory/stock         # Stock actual
GET    /api/v1/inventory/movements     # Movimientos (kardex)
POST   /api/v1/inventory/adjustments   # Ajuste de stock

CATÁLOGOS:
GET    /api/v1/catalogs/customers-types
GET    /api/v1/catalogs/products-classes
GET    /api/v1/catalogs/price-lists
... etc ...
```

---

## 7. Conclusión

**Total de endpoints analizados:** 60+

**Categorización:**
- 35 endpoints de catálogos maestros (dimension tables)
- 25 endpoints operativos específicos (business logic)

**Para la re-implementación en Laravel:**
- Agrupar en Resource Controllers (Laravel convention)
- Usar versioning de API (`/api/v1/`)
- Implementar proper HTTP semantics (GET/POST/PUT/DELETE)
- Documentar con OpenAPI/Swagger
- Implementar rate limiting, CORS, JWT auth

---

**Documento preparado por:** Senior Web Intelligence & Scraping Engineer  
**Fecha:** Junio 2026
