# Orden de Creación de Tablas - Análisis de Dependencias

**Documento:** Secuencia de creación de tablas basada en relaciones de dependencia  
**Propósito:** Guiar el desarrollo de la base de datos sin errores de FK  
**Fecha:** Junio 2026  

---

## 📌 Contexto

Cuando creamos tablas en PostgreSQL con Foreign Keys (FK), **el orden importa**: no puedes crear una tabla con FK a otra que aún no existe. Este documento ordena las ~40 tablas en **8 niveles de dependencia**, de "sin dependencias" a "depende de todo".

---

## 🗂️ NIVELES DE CREACIÓN (Orden Correcto)

### **NIVEL 0: Tablas Base (Sin Dependencias)**

**Estas tablas NO dependen de nada. Crearlas primero.**

| # | Tabla | Tipo | Razón | FK |
|---|---|---|---|---|
| 1 | `moneda` | Dimensión | Referencia para todas las transacciones | ❌ |
| 2 | `unidad_medida` | Dimensión | Referencia para productos/stocks | ❌ |
| 3 | `pais` | Dimensión | Base de la pirámide geográfica | ❌ |
| 4 | `dia_semana` | Dimensión | Calendarios y reportes | ❌ |
| 5 | `rol` | Dimensión | Control de acceso | ❌ |
| 6 | `permiso` | Dimensión | Control de acceso | ❌ |
| 7 | `documento_simbolo` | Dimensión | Símbolos de doc (F, B, NC) | ❌ |

**SQL de creación (en orden):**
```sql
-- Nivel 0: Crear primero, en cualquier orden
CREATE TABLE moneda (id SERIAL PRIMARY KEY, codigo VARCHAR(3), nombre VARCHAR(100));
CREATE TABLE unidad_medida (id SERIAL PRIMARY KEY, simbolo VARCHAR(10), nombre VARCHAR(100));
CREATE TABLE pais (id SERIAL PRIMARY KEY, codigo VARCHAR(3), nombre VARCHAR(100));
CREATE TABLE dia_semana (id SERIAL PRIMARY KEY, nombre VARCHAR(20));
CREATE TABLE rol (id SERIAL PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT);
CREATE TABLE permiso (id SERIAL PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT);
CREATE TABLE documento_simbolo (id SERIAL PRIMARY KEY, simbolo VARCHAR(5), descripcion VARCHAR(100));
```

---

### **NIVEL 1: Catálogos que Dependen de Nivel 0**

**Dependen de:** moneda, unidad_medida, pais, rol, permiso  
**Bloqueadas por:** Nada  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `usuario` | ❌ | Tabla de usuarios, sin FK |
| 2 | `usuario_rol` | usuario, rol | Asignación M:N de roles a usuarios |
| 3 | `tipo_cliente` | ❌ | Categorización de clientes |
| 4 | `segmento_sunat` | ❌ | Segmento fiscal SUNAT |
| 5 | `familia_sunat` | ❌ | Familia SUNAT para productos |
| 6 | `clase_sunat` | ❌ | Clasificación SUNAT de productos |
| 7 | `producto_clase` | ❌ | Clase interna de productos |
| 8 | `producto_subclase` | ❌ | Subclase de productos |
| 9 | `tipo_afeccion_igv` | ❌ | Tipo de afección al IGV |
| 10 | `tipo_calculo_isc` | ❌ | Cómo calcular ISC |
| 11 | `tipo_venta` | ❌ | Tipos de venta (al por mayor, menor) |
| 12 | `tipo_compra` | ❌ | Tipos de compra |
| 13 | `documento_tipo` | documento_simbolo | Tipos de documento (Factura, Boleta) |
| 14 | `nota_credito_tipo` | ❌ | Tipos de NC |
| 15 | `estado_civil` | ❌ | Estados civiles |
| 16 | `sexo` | ❌ | M/F para contactos |
| 17 | `departamento` | pais | Departamentos por país |
| 18 | `provincia` | departamento | Provincias por departamento |
| 19 | `tipo_cambio` | moneda (x2) | Tipo de cambio entre monedas |
| 20 | `lista_precios` | moneda | Listas de precios por moneda |
| 21 | `transportista` | ❌ | Datos de transportistas |
| 22 | `premio` | ❌ | Premios para canje de puntos |

**SQL de creación:**
```sql
-- Nivel 1: Dependen de Nivel 0
CREATE TABLE usuario (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255),
    nombre_completo VARCHAR(255),
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuario_rol (
    usuario_id INT NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
    rol_id INT NOT NULL REFERENCES rol(id) ON DELETE CASCADE,
    PRIMARY KEY (usuario_id, rol_id)
);

CREATE TABLE tipo_cliente (id SERIAL PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT);
CREATE TABLE segmento_sunat (id SERIAL PRIMARY KEY, codigo VARCHAR(10), nombre VARCHAR(100));
CREATE TABLE familia_sunat (id SERIAL PRIMARY KEY, codigo VARCHAR(10), nombre VARCHAR(100));
CREATE TABLE clase_sunat (id SERIAL PRIMARY KEY, codigo VARCHAR(10), nombre VARCHAR(100));
CREATE TABLE producto_clase (id SERIAL PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT);
CREATE TABLE producto_subclase (id SERIAL PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT);
CREATE TABLE tipo_afeccion_igv (id SERIAL PRIMARY KEY, codigo VARCHAR(10), descripcion VARCHAR(255));
CREATE TABLE tipo_calculo_isc (id SERIAL PRIMARY KEY, codigo VARCHAR(10), descripcion VARCHAR(255));
CREATE TABLE tipo_venta (id SERIAL PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT);
CREATE TABLE tipo_compra (id SERIAL PRIMARY KEY, nombre VARCHAR(100), descripcion TEXT);

CREATE TABLE documento_tipo (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    simbolo_id INT NOT NULL REFERENCES documento_simbolo(id),
    codigo VARCHAR(2),
    requiere_ruc BOOLEAN DEFAULT true
);

CREATE TABLE nota_credito_tipo (id SERIAL PRIMARY KEY, nombre VARCHAR(100), codigo VARCHAR(10));
CREATE TABLE estado_civil (id SERIAL PRIMARY KEY, nombre VARCHAR(50));
CREATE TABLE sexo (id SERIAL PRIMARY KEY, nombre VARCHAR(20));

CREATE TABLE departamento (
    id SERIAL PRIMARY KEY,
    pais_id INT NOT NULL REFERENCES pais(id),
    codigo VARCHAR(3),
    nombre VARCHAR(100)
);

CREATE TABLE provincia (
    id SERIAL PRIMARY KEY,
    departamento_id INT NOT NULL REFERENCES departamento(id),
    codigo VARCHAR(3),
    nombre VARCHAR(100)
);

CREATE TABLE tipo_cambio (
    id SERIAL PRIMARY KEY,
    moneda_origen_id INT NOT NULL REFERENCES moneda(id),
    moneda_destino_id INT NOT NULL REFERENCES moneda(id),
    valor DECIMAL(10, 4),
    fecha_vigencia DATE
);

CREATE TABLE lista_precios (
    id SERIAL PRIMARY KEY,
    moneda_id INT NOT NULL REFERENCES moneda(id),
    nombre VARCHAR(100),
    activa BOOLEAN DEFAULT true
);

CREATE TABLE transportista (id SERIAL PRIMARY KEY, nombre VARCHAR(255), ruc VARCHAR(15));
CREATE TABLE premio (id SERIAL PRIMARY KEY, nombre VARCHAR(255), descripcion TEXT, costo_puntos INT);
```

---

### **NIVEL 2: Dimensiones Geográficas + Productos Base**

**Dependen de:** Nivel 0-1 (pais, departamento, provincia, moneda, unidad_medida, etc.)  
**Bloqueadas por:** Nada  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `ubigeo` | pais, departamento, provincia | Pirámide geográfica completa |
| 2 | `producto` | producto_clase, producto_subclase, unidad_medida, tipo_afeccion_igv, tipo_calculo_isc | Maestro de productos |
| 3 | `almacen` | ubigeo | Ubicaciones de almacenes |
| 4 | `zona` | ubigeo | Zonas de distribución |
| 5 | `vendedor` | usuario, zona (opcional) | Datos de vendedores |

**SQL de creación:**
```sql
-- Nivel 2
CREATE TABLE ubigeo (
    id SERIAL PRIMARY KEY,
    pais_id INT NOT NULL REFERENCES pais(id),
    departamento_id INT REFERENCES departamento(id),
    provincia_id INT REFERENCES provincia(id),
    codigo VARCHAR(6),
    descripcion VARCHAR(255),
    UNIQUE (codigo)
);

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
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE almacen (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    ubigeo_id INT NOT NULL REFERENCES ubigeo(id),
    direccion TEXT,
    telefono VARCHAR(20)
);

CREATE TABLE zona (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    ubigeo_id INT NOT NULL REFERENCES ubigeo(id),
    descripcion TEXT
);

CREATE TABLE vendedor (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuario(id),
    zona_id INT REFERENCES zona(id),
    comision_porcentaje DECIMAL(5, 2),
    activo BOOLEAN DEFAULT true
);
```

---

### **NIVEL 3: Ruta (Depende de Zona)**

**Depende de:** zona, ubigeo (nivel 2)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `ruta` | zona, ubigeo | Rutas de distribución geográfica |

**SQL:**
```sql
-- Nivel 3
CREATE TABLE ruta (
    id SERIAL PRIMARY KEY,
    zona_id INT NOT NULL REFERENCES zona(id),
    nombre VARCHAR(100),
    descripcion TEXT,
    activa BOOLEAN DEFAULT true
);

CREATE TABLE mapa_rutas (
    id SERIAL PRIMARY KEY,
    ruta_id INT NOT NULL REFERENCES ruta(id),
    numero_secuencia INT,
    cliente_id INT,  -- FK opcional, lo hacemos en nivel 4
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    distancia_siguiente_punto DECIMAL(8, 2)
);
```

---

### **NIVEL 4: Maestros de Negocio (Cliente, Proveedor)**

**Dependen de:** Nivel 0-3 (tipo_cliente, segmento_sunat, ubigeo, zona)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `cliente` | tipo_cliente, segmento_sunat, ubigeo, zona | Maestro principal de clientes |
| 2 | `proveedor` | ubigeo, lista_precios (opcional) | Maestro de proveedores |

**SQL:**
```sql
-- Nivel 4
CREATE TABLE cliente (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL,
    nombre_comercial VARCHAR(255) NOT NULL,
    razon_social VARCHAR(255),
    dni_ruc VARCHAR(15) UNIQUE,
    tipo_cliente_id INT REFERENCES tipo_cliente(id),
    segmento_sunat_id INT REFERENCES segmento_sunat(id),
    condicion_pago VARCHAR(100),
    credito_disponible DECIMAL(12, 2),
    direccion_principal TEXT,
    telefono_principal VARCHAR(20),
    email_principal VARCHAR(255),
    ubigeo_id INT REFERENCES ubigeo(id),
    zona_id INT REFERENCES zona(id),
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE proveedor (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL,
    razon_social VARCHAR(255),
    ruc VARCHAR(15) UNIQUE,
    contacto_nombre VARCHAR(255),
    contacto_telefono VARCHAR(20),
    ubigeo_id INT REFERENCES ubigeo(id),
    activo BOOLEAN DEFAULT true
);
```

---

### **NIVEL 5: Stock (Depende de Producto + Almacén)**

**Depende de:** producto, almacen (nivel 2)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `stock` | producto, almacen | Estado actual de inventario |

**SQL:**
```sql
-- Nivel 5
CREATE TABLE stock (
    id SERIAL PRIMARY KEY,
    almacen_id INT NOT NULL REFERENCES almacen(id),
    producto_id INT NOT NULL REFERENCES producto(id),
    cantidad_disponible DECIMAL(10, 2),
    cantidad_comprometida DECIMAL(10, 2),
    cantidad_minima DECIMAL(10, 2),
    cantidad_maxima DECIMAL(10, 2),
    última_actualizacion TIMESTAMP,
    UNIQUE (almacen_id, producto_id)
);
```

---

### **NIVEL 6: Transacciones Principales (Venta, Compra)**

**Dependen de:** Nivel 0-4 (cliente, proveedor, vendedor, tipo_venta, tipo_compra, documento_tipo, moneda, usuario)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `venta` | cliente, vendedor, tipo_venta, documento_tipo, moneda, usuario | Transacción principal |
| 2 | `compra` | proveedor, tipo_compra, documento_tipo, moneda, usuario | Transacción de compra |

**SQL:**
```sql
-- Nivel 6
CREATE TABLE venta (
    id SERIAL PRIMARY KEY,
    numero_comprobante VARCHAR(20) UNIQUE NOT NULL,
    serie VARCHAR(10),
    numero VARCHAR(10),
    cliente_id INT NOT NULL REFERENCES cliente(id),
    vendedor_id INT NOT NULL REFERENCES vendedor(id),
    tipo_venta_id INT NOT NULL REFERENCES tipo_venta(id),
    documento_tipo_id INT NOT NULL REFERENCES documento_tipo(id),
    fecha_venta DATE NOT NULL,
    fecha_entrega DATE,
    moneda_id INT DEFAULT 1 REFERENCES moneda(id),
    subtotal DECIMAL(12, 2),
    descuento_global DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    total DECIMAL(12, 2),
    saldo_pendiente DECIMAL(12, 2),
    estado VARCHAR(50),
    observaciones TEXT,
    created_by INT REFERENCES usuario(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE compra (
    id SERIAL PRIMARY KEY,
    numero_comprobante VARCHAR(20) UNIQUE NOT NULL,
    proveedor_id INT NOT NULL REFERENCES proveedor(id),
    tipo_compra_id INT NOT NULL REFERENCES tipo_compra(id),
    documento_tipo_id INT NOT NULL REFERENCES documento_tipo(id),
    fecha_compra DATE NOT NULL,
    moneda_id INT DEFAULT 1 REFERENCES moneda(id),
    subtotal DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    total DECIMAL(12, 2),
    estado VARCHAR(50),
    created_by INT REFERENCES usuario(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### **NIVEL 7: Detalles de Transacciones**

**Dependen de:** venta, compra, producto, lista_precios (nivel 6)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `item_venta` | venta, producto, lista_precios, unidad_medida | Líneas de venta |
| 2 | `item_compra` | compra, producto, unidad_medida | Líneas de compra |
| 3 | `nota_credito` | venta, tipo_nota_credito, usuario | Notas crédito ligadas a venta |
| 4 | `item_nota_credito` | nota_credito | Líneas de NC |

**SQL:**
```sql
-- Nivel 7
CREATE TABLE item_venta (
    id SERIAL PRIMARY KEY,
    venta_id INT NOT NULL REFERENCES venta(id) ON DELETE CASCADE,
    producto_id INT NOT NULL REFERENCES producto(id),
    lista_precio_id INT REFERENCES lista_precios(id),
    numero_linea INT,
    cantidad DECIMAL(10, 2) NOT NULL,
    unidad_medida_id INT NOT NULL REFERENCES unidad_medida(id),
    precio_unitario DECIMAL(10, 2) NOT NULL,
    descuento_linea DECIMAL(12, 2),
    subtotal DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    total DECIMAL(12, 2),
    observaciones TEXT
);

CREATE TABLE item_compra (
    id SERIAL PRIMARY KEY,
    compra_id INT NOT NULL REFERENCES compra(id) ON DELETE CASCADE,
    producto_id INT NOT NULL REFERENCES producto(id),
    numero_linea INT,
    cantidad DECIMAL(10, 2) NOT NULL,
    unidad_medida_id INT NOT NULL REFERENCES unidad_medida(id),
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    total DECIMAL(12, 2)
);

CREATE TABLE nota_credito (
    id SERIAL PRIMARY KEY,
    venta_id INT NOT NULL REFERENCES venta(id),
    tipo_nota_credito_id INT NOT NULL REFERENCES nota_credito_tipo(id),
    numero_comprobante VARCHAR(20) UNIQUE NOT NULL,
    fecha_nc DATE,
    motivo TEXT,
    subtotal DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    total DECIMAL(12, 2),
    estado VARCHAR(50),
    created_by INT REFERENCES usuario(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE item_nota_credito (
    id SERIAL PRIMARY KEY,
    nota_credito_id INT NOT NULL REFERENCES nota_credito(id) ON DELETE CASCADE,
    producto_id INT NOT NULL REFERENCES producto(id),
    cantidad DECIMAL(10, 2),
    precio_unitario DECIMAL(10, 2),
    subtotal DECIMAL(12, 2),
    igv DECIMAL(12, 2)
);
```

---

### **NIVEL 8: Movimientos e Inventario**

**Dependen de:** almacen, producto, stock, venta, usuario (nivel 2-6)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `movimiento_inventario` | almacen, producto, usuario, venta (opcional) | Kardex de inventario |
| 2 | `punto_cliente` | cliente, venta | Acumulación de puntos |
| 3 | `canje_premio` | cliente, premio, usuario | Canje de puntos por premio |

**SQL:**
```sql
-- Nivel 8
CREATE TABLE movimiento_inventario (
    id SERIAL PRIMARY KEY,
    almacen_id INT NOT NULL REFERENCES almacen(id),
    producto_id INT NOT NULL REFERENCES producto(id),
    tipo_movimiento VARCHAR(50),
    referencia_tipo VARCHAR(50),
    referencia_id INT,
    cantidad DECIMAL(10, 2),
    precio_unitario DECIMAL(10, 2),
    valor_total DECIMAL(12, 2),
    saldo_anterior DECIMAL(10, 2),
    saldo_nuevo DECIMAL(10, 2),
    usuario_id INT REFERENCES usuario(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE punto_cliente (
    id SERIAL PRIMARY KEY,
    cliente_id INT NOT NULL REFERENCES cliente(id),
    venta_id INT NOT NULL REFERENCES venta(id),
    puntos_ganados DECIMAL(10, 2),
    puntos_canjeados DECIMAL(10, 2),
    puntos_vigentes DECIMAL(10, 2),
    fecha_vencimiento DATE,
    estado VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE canje_premio (
    id SERIAL PRIMARY KEY,
    cliente_id INT NOT NULL REFERENCES cliente(id),
    premio_id INT NOT NULL REFERENCES premio(id),
    puntos_utilizados INT,
    cantidad INT,
    fecha_canje DATE,
    estado VARCHAR(50),
    usuario_id INT REFERENCES usuario(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### **NIVEL 9: Finanzas + SUNAT**

**Dependen de:** venta, compra, nota_credito (nivel 6-7)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `libro_venta` | venta | Resumen SUNAT de ventas |
| 2 | `libro_compra` | compra | Resumen SUNAT de compras |
| 3 | `asiento_contable` | venta, compra, nota_credito | Asientos contables |

**SQL:**
```sql
-- Nivel 9
CREATE TABLE libro_venta (
    id SERIAL PRIMARY KEY,
    venta_id INT NOT NULL REFERENCES venta(id),
    periodo VARCHAR(7),  -- YYYY-MM
    ruc_cliente VARCHAR(15),
    serie_comprobante VARCHAR(10),
    numero_comprobante VARCHAR(10),
    fecha_emision DATE,
    base_imponible DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    isc DECIMAL(12, 2),
    otros_cargos DECIMAL(12, 2),
    total DECIMAL(12, 2),
    estado_sunat VARCHAR(50),
    hash_cdr VARCHAR(100)
);

CREATE TABLE libro_compra (
    id SERIAL PRIMARY KEY,
    compra_id INT NOT NULL REFERENCES compra(id),
    periodo VARCHAR(7),
    ruc_proveedor VARCHAR(15),
    serie_comprobante VARCHAR(10),
    numero_comprobante VARCHAR(10),
    fecha_emision DATE,
    base_imponible DECIMAL(12, 2),
    igv DECIMAL(12, 2),
    isc DECIMAL(12, 2),
    total DECIMAL(12, 2)
);

CREATE TABLE asiento_contable (
    id SERIAL PRIMARY KEY,
    venta_id INT REFERENCES venta(id),
    compra_id INT REFERENCES compra(id),
    nota_credito_id INT REFERENCES nota_credito(id),
    numero_asiento VARCHAR(20),
    fecha_asiento DATE,
    descripcion TEXT,
    debe DECIMAL(12, 2),
    haber DECIMAL(12, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### **NIVEL 10: Auditoría**

**Dependen de:** usuario, todas las tablas anteriores (nivel 0-9)  

| # | Tabla | FK Padre | Razón |
|---|---|---|---|
| 1 | `auditoria` | usuario | Registro de cambios globales |

**SQL:**
```sql
-- Nivel 10 (último)
CREATE TABLE auditoria (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuario(id),
    tabla_afectada VARCHAR(100),
    registro_id INT,
    tipo_operacion VARCHAR(20),  -- INSERT, UPDATE, DELETE
    valores_anteriores JSONB,
    valores_nuevos JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tabla_registro (tabla_afectada, registro_id)
);
```

---

## 📊 Tabla Resumen: Dependencias por Nivel

```
NIVEL 0 (7 tablas)    → Sin dependencias
    ↓
NIVEL 1 (22 tablas)   → Depende de Nivel 0
    ↓
NIVEL 2 (5 tablas)    → Depende de Nivel 0-1 (ubigeo, producto, almacen, zona, vendedor)
    ↓
NIVEL 3 (2 tablas)    → Depende de Nivel 2 (ruta, mapa_rutas)
    ↓
NIVEL 4 (2 tablas)    → Depende de Nivel 0-3 (cliente, proveedor)
    ↓
NIVEL 5 (1 tabla)     → Depende de Nivel 2 (stock)
    ↓
NIVEL 6 (2 tablas)    → Depende de Nivel 0-4 (venta, compra)
    ↓
NIVEL 7 (4 tablas)    → Depende de Nivel 6 (item_venta, item_compra, nota_credito, item_nota_credito)
    ↓
NIVEL 8 (3 tablas)    → Depende de Nivel 2-6 (movimiento_inventario, punto_cliente, canje_premio)
    ↓
NIVEL 9 (3 tablas)    → Depende de Nivel 6-7 (libro_venta, libro_compra, asiento_contable)
    ↓
NIVEL 10 (1 tabla)    → Depende de todos (auditoria)
```

---

## 🚀 Flujo de Desarrollo Recomendado

### **Fase 1: Infraestructura (NIVEL 0)**
```bash
# Semana 1
npm run migration:create  # Crear todas las tablas base
npm run seed:dimensiones  # Insertar datos base (monedas, países, etc.)
```

### **Fase 2: Catálogos (NIVEL 1 + 2)**
```bash
# Semana 2-3
npm run migration:create  # Crear catálogos
npm run seed:catalogos    # Insertar tipos de cliente, clases, etc.
```

### **Fase 3: Maestros (NIVEL 3 + 4)**
```bash
# Semana 4-5
npm run migration:create  # Crear rutas, clientes, proveedores
npm run test:master-data  # Validar integridad referencial
```

### **Fase 4: Transacciones (NIVEL 6 + 7 + 8)**
```bash
# Semana 6-7
npm run migration:create  # Crear venta, compra, movimientos
npm run test:transactions # Probar FK y cascadas
```

### **Fase 5: Finanzas (NIVEL 9)**
```bash
# Semana 8
npm run migration:create  # Crear libro_venta, asientos
```

### **Fase 6: Auditoría (NIVEL 10)**
```bash
# Semana 9
npm run migration:create  # Crear tabla de auditoría
npm run test:audit       # Validar triggers
```

---

## 🔍 Validación de Orden

**Antes de crear cada nivel, verifica:**

1. ✅ **Todas las FK referencias existen** en niveles anteriores
2. ✅ **No hay ciclos de dependencia** (A → B → A es MALO)
3. ✅ **Índices en FKs** para performance
4. ✅ **Constraints de integridad** correctos

**Ejemplo de validación:**
```sql
-- Verificar que venta.cliente_id apunta a cliente.id (existente)
SELECT 
    constraint_name,
    table_name,
    column_name,
    referenced_table_name,
    referenced_column_name
FROM information_schema.key_column_usage
WHERE table_name = 'venta' AND column_name = 'cliente_id';
```

---

## 📝 Conclusión

Este documento te permite:
1. ✅ Crear tablas **en el orden correcto** sin errores de FK
2. ✅ Identificar **qué depende de qué**
3. ✅ Planificar **sprints de desarrollo** por nivel
4. ✅ Validar **integridad referencial** antes de grabar datos

**Próximo paso:** Ir a `02_arquitectura_datos/database-schema.sql` y ejecutar las migraciones **nivel por nivel**.

---

**Documento preparado por:** Senior Web Intelligence & Scraping Engineer  
**Referencia:** Basado en `database-analysis.md` - Sección 2 (ER Diagram)  
**Fecha:** Junio 2026
