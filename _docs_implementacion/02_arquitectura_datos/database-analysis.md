# Análisis Técnico - Base de Datos

**Documento:** Análisis de la estructura de datos del sistema ARMORA  
**Fecha:** Junio 2026  
**Perfil:** Senior Web Intelligence & Scraping Engineer  

---

## 1. Inferencia del Motor de Base de Datos

### 1.1 Indicios Técnicos

| Indicador | Observación | Puntaje |
|---|---|---|
| **Stack backend** | Java (Spring MVC probable) | PostgreSQL: 70% |
| **Transacciones ACID** | Sistema financiero/ERP requerido | PostgreSQL: 80% |
| **Tipos complejos** | IGV, ISC, detracciones, retenciones | PostgreSQL: 90% |
| **Reportes complejos** | Libro mayor, PLE, análisis | PostgreSQL: 85% |
| **Full-text search** | No detectado, pero probable para catálogos | PostgreSQL: 75% |
| **JSON/JSONB** | Unlikely pero posible para auditoría | PostgreSQL: 80% |
| **Ubicación (Perú)** | ON EMPRESAS (proveedor peruano) | PostgreSQL/MySQL: 50/50 |

**Conclusión:** **PostgreSQL 12+** es la opción más probable (85-90% confianza)

### 1.2 Alternativas Consideradas

| BD | Probabilidad | Razón |
|---|---|---|
| **PostgreSQL 12+** | 🟢 85-90% | Stack Java, integridad transaccional, soporte advanced |
| **MySQL/MariaDB 8** | 🟡 10-15% | Alternativa común en Perú, pero menos robusto |
| **Oracle 12c+** | 🔴 1-2% | Sobredimensionado, costo alto |

---

## 2. Modelo de Datos Inferido (ER Diagram)

### 2.1 Dimensiones (Catálogos Maestros)

Las siguientes tablas representan **dimensiones** (datos de referencia):

```
DIMENSIONES:
├── usuario
├── rol
├── permiso
├── cliente_tipo
├── segmento_sunat
├── familia_sunat
├── clase_sunat
├── producto_clase
├── producto_subclase
├── producto_tipo_afeccion_igv
├── producto_tipo_calculo_isc
├── unidad_medida
├── moneda
├── tipo_cambio
├── venta_tipo
├── compra_tipo
├── documento_tipo
├── documento_simbolo
├── nota_credito_tipo
├── estado_civil
├── sexo
├── pais
├── departamento
├── provincia
├── ubigeo
├── dia_semana
├── lista_precios
├── almacen
├── zona
├── ruta
├── transportista
└── mapa_rutas
```

### 2.2 Entidades de Negocio (Hechos)

Las siguientes tablas representan **hechos** (transacciones):

```
HECHOS:
├── cliente (maestro + transaccional)
├── proveedor (maestro + transaccional)
├── producto (maestro + transaccional)
├── venta (transaccional)
│   └── item_venta (detalle transaccional)
├── compra (transaccional)
│   └── item_compra (detalle transaccional)
├── nota_credito (transaccional)
│   └── item_nota_credito (detalle transaccional)
├── stock (estado actual)
├── movimiento_inventario (transaccional)
├── canje_premio (transaccional)
├── punto_cliente (transaccional)
├── asiento_contable (transaccional - Finance)
├── libro_venta (transaccional - SUNAT)
├── libro_compra (transaccional - SUNAT)
└── auditoría (transaccional)
```

### 2.3 Diagrama ER Simplificado

```
┌─────────────────────────────────────────────────────────────┐
│                     USUARIOS & ACCESO                        │
├─────────────────────────────────────────────────────────────┤
│ usuario ──────(1:many)─────> rol                            │
│    |                           |                             │
│    └───────(1:many)─────────> permiso                       │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   MAESTROS DE PRODUCTO                       │
├─────────────────────────────────────────────────────────────┤
│ producto ────(many:1)──────> clase                          │
│    |          (many:1)──────> subclase                      │
│    |          (many:1)──────> unidad_medida                 │
│    |          (many:1)──────> tipo_afeccion_igv             │
│    |          (many:1)──────> tipo_calculo_isc              │
│    |          (1:many)──────> lista_precio ──(many:1)──> moneda
│    |          (1:many)──────> stock ──(many:1)──> almacen   │
│    |          (1:many)──────> movimiento_inventario         │
│    └──────(many:1)──────> segmento_sunat                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  CLIENTES & GEOLOCALIZACIÓN                  │
├─────────────────────────────────────────────────────────────┤
│ cliente ──(many:1)──────> tipo_cliente                      │
│    |      (many:1)──────> segmento_sunat                    │
│    |      (1:many)──────> venta                             │
│    |      (1:many)──────> punto_cliente                     │
│    |      (1:many)──────> canje_premio                      │
│    └──(many:1)──────> ubigeo ──(many:1)──> zona            │
│                                                |              │
│ zona ──(many:1)──────> ubigeo                              │
│    |   (1:many)──────> ruta                                 │
│    |   (1:many)──────> vendedor                             │
│    └──(1:many)──────> mapa_rutas                            │
│                                                              │
│ ruta ──(many:1)──────> zona                                │
│    |   (1:many)──────> cliente                              │
│    └───(1:many)──────> vendedor                             │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  VENTAS & DOCUMENTOS                         │
├─────────────────────────────────────────────────────────────┤
│ venta ──(many:1)──────> cliente                            │
│    |   (many:1)──────> vendedor                            │
│    |   (many:1)──────> tipo_venta                          │
│    |   (many:1)──────> documento_tipo                      │
│    |   (1:many)──────> item_venta ──(many:1)──> producto   │
│    |   (1:many)──────> nota_credito                        │
│    └──(1:many)──────> libro_venta (SUNAT)                  │
│                                                              │
│ nota_credito ──(many:1)──────> venta                       │
│    |          (many:1)──────> tipo_nota_credito            │
│    └─────(1:many)──────> item_nota_credito                 │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  COMPRAS & PROVEEDORES                       │
├─────────────────────────────────────────────────────────────┤
│ proveedor ──(1:many)──────> compra                         │
│    |        (1:many)──────> item_compra                    │
│    └───(1:many)──────> libro_compra (SUNAT)                │
│                                                              │
│ compra ──(many:1)──────> proveedor                         │
│    |   (many:1)──────> tipo_compra                         │
│    |   (1:many)──────> item_compra ──(many:1)──> producto  │
│    └──(1:many)──────> libro_compra                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                 INVENTARIO & ALMACENES                       │
├─────────────────────────────────────────────────────────────┤
│ almacen ──(1:many)──────> stock                            │
│    |     (1:many)──────> movimiento_inventario             │
│    └────(many:1)──────> ubigeo                             │
│                                                              │
│ stock ──(many:1)──────> producto                           │
│    |  (many:1)──────> almacen                              │
│    └─(1:many)──────> movimiento_inventario                 │
│                                                              │
│ movimiento_inventario ──(many:1)──────> producto           │
│    |                    (many:1)──────> almacen            │
│    └──────────────────(many:1)──────> venta (opcional)     │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                  LEALTAD & CANJES                            │
├─────────────────────────────────────────────────────────────┤
│ cliente ──(1:many)──────> punto_cliente                    │
│                ↓                                              │
│    venta ─(1:many)─> punto_cliente ─(many:1)─> cliente    │
│                                                              │
│ cliente ──(1:many)──────> canje_premio                     │
│    └──────┴──(many:1)──────> premio                        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   FINANZAS & SUNAT                           │
├─────────────────────────────────────────────────────────────┤
│ venta ──────(1:1)──────> libro_venta                       │
│ compra ─────(1:1)──────> libro_compra                      │
│                                                              │
│ asiento_contable ──(many:1)──────> venta                   │
│    |               (many:1)──────> compra                  │
│    |               (many:1)──────> nota_credito            │
│    └─────────────(many:1)──────> cuenta_contable           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                   AUDITORÍA & LOGS                           │
├─────────────────────────────────────────────────────────────┤
│ auditoría ──(many:1)──────> usuario                        │
│    |       (many:1)──────> tabla_auditada                  │
│    └──────────────────────────────────────────────────────  │
│       registra cambios en: venta, compra, cliente, etc.    │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Tablas Clave (DDL Estimado)

### 3.1 Tabla USUARIO (Dimensión)

```sql
CREATE TABLE usuario (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255),
    nombre_completo VARCHAR(255),
    password_hash VARCHAR(255) NOT NULL,  -- bcrypt/argon2
    activo BOOLEAN DEFAULT true,
    ultimo_acceso TIMESTAMP,
    fecha_contratacion DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_activo (activo)
);

CREATE TABLE usuario_rol (
    usuario_id INT REFERENCES usuario(id) ON DELETE CASCADE,
    rol_id INT REFERENCES rol(id) ON DELETE CASCADE,
    PRIMARY KEY (usuario_id, rol_id),
    UNIQUE (usuario_id, rol_id)
);
```

### 3.2 Tabla CLIENTE (Fact/Maestro)

```sql
CREATE TABLE cliente (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL,
    nombre_comercial VARCHAR(255) NOT NULL,
    razon_social VARCHAR(255),
    dni_ruc VARCHAR(15) UNIQUE,
    tipo_cliente_id INT REFERENCES tipo_cliente(id),
    segmento_sunat_id INT REFERENCES segmento_sunat(id),
    tipo_negocio VARCHAR(100),
    condicion_pago VARCHAR(100),
    credito_disponible DECIMAL(12, 2),
    direccion_principal TEXT,
    telefono_principal VARCHAR(20),
    email_principal VARCHAR(255),
    contacto_nombre VARCHAR(255),
    contacto_titulo VARCHAR(100),
    contacto_telefono VARCHAR(20),
    ubigeo_id INT REFERENCES ubigeo(id),
    zona_id INT REFERENCES zona(id),
    activo BOOLEAN DEFAULT true,
    fecha_registro DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_dni_ruc (dni_ruc),
    INDEX idx_tipo_cliente (tipo_cliente_id),
    INDEX idx_zona (zona_id),
    INDEX idx_activo (activo),
    FOREIGN KEY (tipo_cliente_id) REFERENCES tipo_cliente(id),
    FOREIGN KEY (segmento_sunat_id) REFERENCES segmento_sunat(id),
    FOREIGN KEY (ubigeo_id) REFERENCES ubigeo(id),
    FOREIGN KEY (zona_id) REFERENCES zona(id)
);
```

### 3.3 Tabla PRODUCTO (Fact/Maestro)

```sql
CREATE TABLE producto (
    id SERIAL PRIMARY KEY,
    codigo_interno VARCHAR(50) UNIQUE NOT NULL,
    codigo_sunat VARCHAR(50),
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    marca VARCHAR(100),
    clase_id INT NOT NULL REFERENCES producto_clase(id),
    subclase_id INT NOT NULL REFERENCES producto_subclase(id),
    unidad_medida_id INT NOT NULL REFERENCES unidad_medida(id),
    tipo_afeccion_igv_id INT REFERENCES tipo_afeccion_igv(id),
    tipo_calculo_isc_id INT REFERENCES tipo_calculo_isc(id),
    precio_costo DECIMAL(10, 2),
    precio_lista DECIMAL(10, 2),
    porcentaje_igv DECIMAL(5, 2) DEFAULT 18.00,
    porcentaje_isc DECIMAL(5, 2) DEFAULT 0.00,
    porcentaje_descuento_maximo DECIMAL(5, 2),
    activo BOOLEAN DEFAULT true,
    es_combo BOOLEAN DEFAULT false,
    require_lote BOOLEAN DEFAULT false,
    require_serie BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_codigo_interno (codigo_interno),
    INDEX idx_codigo_sunat (codigo_sunat),
    INDEX idx_clase (clase_id),
    INDEX idx_activo (activo),
    FOREIGN KEY (clase_id) REFERENCES producto_clase(id),
    FOREIGN KEY (subclase_id) REFERENCES producto_subclase(id),
    FOREIGN KEY (unidad_medida_id) REFERENCES unidad_medida(id)
);
```

### 3.4 Tabla VENTA (Fact - Principal)

```sql
CREATE TABLE venta (
    id SERIAL PRIMARY KEY,
    numero_comprobante VARCHAR(20) UNIQUE NOT NULL,  -- Ej: F001-000123
    serie VARCHAR(10),
    numero VARCHAR(10),
    cliente_id INT NOT NULL REFERENCES cliente(id),
    vendedor_id INT NOT NULL REFERENCES vendedor(id),
    tipo_venta_id INT NOT NULL REFERENCES tipo_venta(id),
    documento_tipo_id INT NOT NULL REFERENCES documento_tipo(id),
    fecha_venta DATE NOT NULL,
    fecha_entrega DATE,
    fecha_pago DATE,
    moneda_id INT DEFAULT 1 REFERENCES moneda(id),  -- PEN
    subtotal DECIMAL(12, 2),
    descuento_global DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    isc DECIMAL(12, 2),
    detraccion DECIMAL(12, 2),
    total DECIMAL(12, 2),
    saldo_pendiente DECIMAL(12, 2),
    estado VARCHAR(50),  -- Pendiente, Pagada, Parcial, Anulada, etc
    observaciones TEXT,
    referencia_interna VARCHAR(100),
    numero_oe VARCHAR(20),  -- Orden de Entrada/Compra del cliente
    created_by INT REFERENCES usuario(id),
    updated_by INT REFERENCES usuario(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,  -- Soft delete
    
    INDEX idx_numero_comprobante (numero_comprobante),
    INDEX idx_cliente (cliente_id),
    INDEX idx_vendedor (vendedor_id),
    INDEX idx_fecha_venta (fecha_venta),
    INDEX idx_estado (estado),
    INDEX idx_serie_numero (serie, numero),
    FOREIGN KEY (cliente_id) REFERENCES cliente(id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedor(id),
    FOREIGN KEY (tipo_venta_id) REFERENCES tipo_venta(id),
    FOREIGN KEY (documento_tipo_id) REFERENCES documento_tipo(id),
    FOREIGN KEY (moneda_id) REFERENCES moneda(id)
);
```

### 3.5 Tabla ITEM_VENTA (Fact Detail)

```sql
CREATE TABLE item_venta (
    id SERIAL PRIMARY KEY,
    venta_id INT NOT NULL REFERENCES venta(id) ON DELETE CASCADE,
    producto_id INT NOT NULL REFERENCES producto(id),
    lista_precio_id INT REFERENCES lista_precio(id),
    numero_linea INT,
    cantidad DECIMAL(10, 2) NOT NULL,
    unidad_medida_id INT NOT NULL REFERENCES unidad_medida(id),
    precio_unitario DECIMAL(10, 2) NOT NULL,
    descuento_linea DECIMAL(12, 2),
    descuento_porcentaje DECIMAL(5, 2),
    subtotal DECIMAL(12, 2),
    igv_porcentaje DECIMAL(5, 2),
    igv DECIMAL(12, 2),
    isc DECIMAL(12, 2),
    total DECIMAL(12, 2),
    lote VARCHAR(50),
    serie VARCHAR(50),
    observaciones TEXT,
    
    INDEX idx_venta (venta_id),
    INDEX idx_producto (producto_id),
    FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES producto(id),
    FOREIGN KEY (unidad_medida_id) REFERENCES unidad_medida(id)
);
```

### 3.6 Tabla STOCK (Estado Actual)

```sql
CREATE TABLE stock (
    id SERIAL PRIMARY KEY,
    almacen_id INT NOT NULL REFERENCES almacen(id),
    producto_id INT NOT NULL REFERENCES producto(id),
    cantidad_disponible DECIMAL(10, 2),
    cantidad_comprometida DECIMAL(10, 2),
    cantidad_libre DECIMAL(10, 2) GENERATED ALWAYS AS 
        (cantidad_disponible - cantidad_comprometida) STORED,
    cantidad_minima DECIMAL(10, 2),
    cantidad_maxima DECIMAL(10, 2),
    valor_costo_total DECIMAL(12, 2),  -- cantidad * precio_costo promedio
    última_actualizacion TIMESTAMP,
    
    UNIQUE (almacen_id, producto_id),
    INDEX idx_almacen (almacen_id),
    INDEX idx_producto (producto_id),
    FOREIGN KEY (almacen_id) REFERENCES almacen(id),
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);
```

### 3.7 Tabla MOVIMIENTO_INVENTARIO (Fact - Transactional)

```sql
CREATE TABLE movimiento_inventario (
    id SERIAL PRIMARY KEY,
    almacen_id INT NOT NULL REFERENCES almacen(id),
    producto_id INT NOT NULL REFERENCES producto(id),
    tipo_movimiento VARCHAR(50),  -- Entrada, Salida, Ajuste, Merma
    referencia_tipo VARCHAR(50),  -- venta, compra, ajuste_manual, devolución
    referencia_id INT,  -- ID de venta, compra, etc
    cantidad DECIMAL(10, 2),
    precio_unitario DECIMAL(10, 2),
    valor_total DECIMAL(12, 2),
    saldo_anterior DECIMAL(10, 2),
    saldo_nuevo DECIMAL(10, 2),
    usuario_id INT REFERENCES usuario(id),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_almacen (almacen_id),
    INDEX idx_producto (producto_id),
    INDEX idx_created_at (created_at),
    INDEX idx_tipo_movimiento (tipo_movimiento),
    FOREIGN KEY (almacen_id) REFERENCES almacen(id),
    FOREIGN KEY (producto_id) REFERENCES producto(id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id)
);
```

### 3.8 Tabla PUNTO_CLIENTE (Loyalty - Fact)

```sql
CREATE TABLE punto_cliente (
    id SERIAL PRIMARY KEY,
    cliente_id INT NOT NULL REFERENCES cliente(id),
    venta_id INT NOT NULL REFERENCES venta(id),
    puntos_ganados DECIMAL(10, 2),
    puntos_canjeados DECIMAL(10, 2),
    puntos_vigentes DECIMAL(10, 2),
    fecha_vencimiento DATE,
    estado VARCHAR(50),  -- Vigente, Vencido, Canjeado
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento),
    FOREIGN KEY (cliente_id) REFERENCES cliente(id),
    FOREIGN KEY (venta_id) REFERENCES venta(id)
);
```

---

## 4. Volumen de Datos Estimado

| Tabla | Registros | Crecimiento Anual | Nota |
|---|---|---|---|
| usuario | 50-150 | 10-20 | Vendedores, admin |
| cliente | 1,000-5,000 | 5-10% | Mayoristas, minoristas |
| producto | 500-2,000 | 2-5% | Catálogo moderado |
| venta | 50,000-500,000 | 10-20% | Depende volumen negocio |
| item_venta | 500,000-5,000,000 | 10-20% | 5-15 items/venta |
| stock | 5,000-20,000 | 2-5% | (clientes × productos × almacenes) |
| movimiento_inventario | 1,000,000+ | 10-20% | Diario, kardex |
| punto_cliente | 100,000-1,000,000 | 10-20% | Por venta |

**Tamaño total estimado:** 50 GB - 200 GB (sin backups)

---

## 5. Índices Recomendados

```sql
-- Búsquedas frecuentes
CREATE INDEX idx_venta_cliente_fecha ON venta(cliente_id, fecha_venta DESC);
CREATE INDEX idx_item_venta_producto ON item_venta(venta_id, producto_id);
CREATE INDEX idx_stock_almacen_producto ON stock(almacen_id, producto_id);

-- Reportes
CREATE INDEX idx_movimiento_inv_periodo ON movimiento_inventario(almacen_id, producto_id, created_at DESC);
CREATE INDEX idx_venta_estado_fecha ON venta(estado, fecha_venta DESC);

-- SUNAT/Finanzas
CREATE INDEX idx_venta_serie_numero ON venta(serie, numero);
CREATE INDEX idx_venta_comprobante ON venta(numero_comprobante);
```

---

## 6. Conclusiones sobre Arquitectura de Datos

### ✓ Fortalezas del Diseño Actual
- Normalización correcta (3NF)
- Identificación clara de relaciones
- Soporte de soft deletes (auditoría)
- Campos de timestamp (trazabilidad)

### ✗ Debilidades Observadas
- Sin versionado explícito de datos
- Sin tablas de auditoría detalladas
- Posible falta de constraints de integridad

### 📋 Recomendaciones

Para la re-implementación en Laravel + PostgreSQL:
1. **Migrations como versioning** (Laravel migrations)
2. **Audit trail** (spatie/laravel-activitylog)
3. **Soft deletes** (use deleted_at timestamps)
4. **JSON fields** (JSONB en PostgreSQL para datos semi-estructurados)
5. **Índices optimizados** (por patrones de query observados)

---

**Documento preparado por:** Senior Web Intelligence & Scraping Engineer  
**Fecha:** Junio 2026
