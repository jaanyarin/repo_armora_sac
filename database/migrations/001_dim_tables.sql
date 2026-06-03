-- ============================================================
-- MIGRACIÓN INICIAL: TABLAS DIMENSIONALES (NIVEL 0 y 1)
-- Proyecto: ARMORA NextGen (Laravel + React + PostgreSQL)
-- Fecha: Junio 2026
-- Basado en: Catálogos SUNAT N° 01-09, 13 | ISO 3166-1, 4217 | UN/ECE rec 20
-- ============================================================

-- ============================================
-- NIVEL 0: TABLAS BASE (SIN DEPENDENCIAS)
-- ============================================

-- 1. dim_moneda (SUNAT Cat. 02 - ISO 4217)
CREATE TABLE dim_moneda (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(3)   UNIQUE NOT NULL,
    nombre      VARCHAR(100) NOT NULL,
    simbolo     VARCHAR(5),
    activo      BOOLEAN DEFAULT true,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_moneda (codigo, nombre, simbolo) VALUES
    ('PEN', 'Sol Peruano',      'S/'),
    ('USD', 'Dólar Americano',  '$');

-- 2. dim_unidad_medida (SUNAT Cat. 03 - UN/ECE rec 20)
CREATE TABLE dim_unidad_medida (
    id              SERIAL PRIMARY KEY,
    codigo_sunat    VARCHAR(3)  UNIQUE NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    simbolo         VARCHAR(20),
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_unidad_medida (codigo_sunat, nombre, simbolo) VALUES
    ('NIU', 'UNIDADES (BIENES)',     'UND'),
    ('ZZ',  'UNIDADES (SERVICIOS)',  'SERV'),
    ('KGM', 'KILOGRAMO',             'KG'),
    ('LBR', 'LIBRAS',                'LB'),
    ('LTR', 'LITRO',                 'LT'),
    ('GLL', 'US GALON (3,7843L)',    'GL'),
    ('BX',  'CAJA',                  'CAJ'),
    ('PK',  'PAQUETE',               'PQT'),
    ('DZN', 'DOCENA',                'DOC'),
    ('C62', 'PIEZAS',                'PZ'),
    ('MTR', 'METRO',                 'M'),
    ('MTK', 'METRO CUADRADO',        'M2'),
    ('MTQ', 'METRO CÚBICO',          'M3'),
    ('TNE', 'TONELADAS',             'T'),
    ('GRM', 'GRAMO',                 'GR'),
    ('MLT', 'MILILITRO',             'ML'),
    ('FOT', 'PIES',                  'PIE'),
    ('INH', 'PULGADAS',              'INCH'),
    ('CA',  'LATAS',                 'LAT'),
    ('BO',  'BOTELLAS',              'BOT'),
    ('BG',  'BOLSA',                 'BOLS'),
    ('BJ',  'BALDE',                 'BALD'),
    ('BLL', 'BARRILES',              'BRL'),
    ('CT',  'CARTONES',              'CTON'),
    ('PR',  'PAR',                   'PAR'),
    ('SET', 'JUEGO',                 'JGO'),
    ('KT',  'KIT',                   'KIT'),
    ('CEN', 'CIENTO DE UNIDADES',    'CTO'),
    ('MLL', 'MILLARES',              'MLL'),
    ('KWH', 'KILOVATIO HORA',        'KWxH'),
    ('HUR', 'HORA',                  'HR'),
    ('SA',  'SACO',                  'SCO'),
    ('ST',  'PLIEGO',                'PLGO'),
    ('RM',  'RESMA',                 'RESM'),
    ('PF',  'PALETAS',               'PAL'),
    ('DR',  'TAMBOR',                'TAMB'),
    ('STN', 'TONELADA CORTA',        'TON'),
    ('LTN', 'TONELADA LARGA',        NULL),
    ('CMT', 'CENTÍMETRO LINEAL',     'CM'),
    ('CMK', 'CENTÍMETRO CUADRADO',   'CM2'),
    ('CMQ', 'CENTÍMETRO CÚBICO',     'CM3'),
    ('KTM', 'KILOMETRO',             'KM'),
    ('MMT', 'MILÍMETRO',             'MM'),
    ('MMK', 'MILÍMETRO CUADRADO',    'MM2'),
    ('MMQ', 'MILÍMETRO CÚBICO',      'MM3'),
    ('ONZ', 'ONZAS',                 'ONZ'),
    ('YRD', 'YARDA',                 'YD'),
    ('YDK', 'YARDA CUADRADA',        'YD2'),
    ('QD',  'CUARTO DE DOCENA',      '1/4 DOC'),
    ('HD',  'MEDIA DOCENA',          '1/2 DOC');

-- 3. dim_pais (SUNAT Cat. 04 - ISO 3166-1)
CREATE TABLE dim_pais (
    id          SERIAL PRIMARY KEY,
    codigo      VARCHAR(2)  UNIQUE NOT NULL,
    nombre      VARCHAR(100) NOT NULL,
    activo      BOOLEAN DEFAULT true,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_pais (codigo, nombre) VALUES
    ('PE', 'PERÚ'), ('AR', 'ARGENTINA'), ('BO', 'BOLIVIA'),
    ('BR', 'BRASIL'), ('CL', 'CHILE'), ('CO', 'COLOMBIA'),
    ('EC', 'ECUADOR'), ('PY', 'PARAGUAY'), ('UY', 'URUGUAY'),
    ('VE', 'VENEZUELA'), ('MX', 'MÉXICO'), ('US', 'ESTADOS UNIDOS'),
    ('CN', 'CHINA'), ('JP', 'JAPÓN'), ('ES', 'ESPAÑA'),
    ('DE', 'ALEMANIA'), ('FR', 'FRANCIA'), ('IT', 'ITALIA'),
    ('UK', 'REINO UNIDO'), ('CA', 'CANADÁ'), ('KR', 'COREA DEL SUR'),
    ('IN', 'INDIA');

-- 4. dim_dia_semana
CREATE TABLE dim_dia_semana (id SERIAL PRIMARY KEY, nombre VARCHAR(20));
INSERT INTO dim_dia_semana (nombre) VALUES
    ('Lunes'), ('Martes'), ('Miércoles'), ('Jueves'),
    ('Viernes'), ('Sábado'), ('Domingo');

-- 5. dim_rol (compatible Spatie Permission)
CREATE TABLE dim_rol (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    guard_name  VARCHAR(40) DEFAULT 'api',
    descripcion TEXT,
    activo      BOOLEAN DEFAULT true,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    UNIQUE (name, guard_name)
);
INSERT INTO dim_rol (name, guard_name, descripcion) VALUES
    ('Super-Admin',    'api', 'Acceso total al sistema'),
    ('Admin',          'api', 'Administración del sistema'),
    ('Gerente',        'api', 'Visión gerencial de todos los módulos'),
    ('Vendedor',       'api', 'Ventas y gestión de clientes'),
    ('Jefe-Almacen',   'api', 'Supervisión de inventario y almacenes'),
    ('Almacenero',     'api', 'Gestión de stock y movimientos'),
    ('Contador',       'api', 'Módulo financiero y SUNAT'),
    ('Logistica',      'api', 'Gestión de rutas y transportistas'),
    ('Comprador',      'api', 'Órdenes de compra y proveedores'),
    ('Portal-Cliente', 'api', 'Autoservicio para clientes'),
    ('Portal-Proveedor', 'api', 'Autoservicio para proveedores');

-- 6. dim_permiso (compatible Spatie Permission)
CREATE TABLE dim_permiso (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    guard_name  VARCHAR(40) DEFAULT 'api',
    descripcion TEXT,
    modulo      VARCHAR(50),
    activo      BOOLEAN DEFAULT true,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP,
    UNIQUE (name, guard_name)
);
INSERT INTO dim_permiso (name, guard_name, descripcion, modulo) VALUES
    ('ver-usuarios',     'api', 'Ver listado de usuarios',       'Auth'),
    ('crear-usuarios',   'api', 'Crear nuevos usuarios',         'Auth'),
    ('asignar-roles',    'api', 'Asignar roles a usuarios',      'Auth'),
    ('ver-clientes',     'api', 'Ver listado de clientes',       'Customers'),
    ('crear-clientes',   'api', 'Crear nuevos clientes',         'Customers'),
    ('ver-productos',    'api', 'Ver catálogo de productos',     'Products'),
    ('crear-productos',  'api', 'Crear productos',               'Products'),
    ('editar-precios',   'api', 'Modificar precios',             'Products'),
    ('ver-ventas',       'api', 'Ver ventas realizadas',         'Sales'),
    ('crear-ventas',     'api', 'Crear nuevas ventas',           'Sales'),
    ('anular-ventas',    'api', 'Anular ventas',                 'Sales'),
    ('nota-credito',     'api', 'Emitir notas de crédito',       'Sales'),
    ('ver-stock',        'api', 'Consultar stock',               'Inventory'),
    ('ajustar-stock',    'api', 'Realizar ajustes de stock',     'Inventory'),
    ('kardex',           'api', 'Consultar kardex valorizado',   'Inventory'),
    ('ver-compras',      'api', 'Ver órdenes de compra',         'Purchases'),
    ('crear-compras',    'api', 'Crear órdenes de compra',       'Purchases'),
    ('aprobar-compras',  'api', 'Aprobar órdenes de compra',     'Purchases'),
    ('ver-finanzas',     'api', 'Ver módulo financiero',         'Finance'),
    ('enviar-sunat',     'api', 'Enviar comprobantes a SUNAT',   'Finance'),
    ('ver-rutas',        'api', 'Ver rutas de distribución',     'Logistics'),
    ('ver-dashboard',    'api', 'Acceder al dashboard principal','Dashboard'),
    ('ver-reportes',     'api', 'Generar reportes',              'Dashboard');

-- 7. dim_documento_simbolo (SUNAT Cat. 01)
CREATE TABLE dim_documento_simbolo (
    id              SERIAL PRIMARY KEY,
    codigo_sunat    VARCHAR(2)  UNIQUE NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    simbolo         VARCHAR(5),
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_documento_simbolo (codigo_sunat, nombre, simbolo) VALUES
    ('01', 'FACTURA',                          'F'),
    ('03', 'BOLETA DE VENTA',                  'B'),
    ('07', 'NOTA DE CRÉDITO',                  'NC'),
    ('08', 'NOTA DE DÉBITO',                   'ND'),
    ('09', 'GUÍA DE REMISIÓN REMITENTE',       'GR'),
    ('12', 'TICKET DE MÁQUINA REGISTRADORA',   'T'),
    ('20', 'COMPROBANTE DE RETENCIÓN',         'RET'),
    ('31', 'GUÍA DE REMISIÓN TRANSPORTISTA',   'GT'),
    ('40', 'COMPROBANTE DE PERCEPCIÓN',        'PER');

-- ============================================
-- NIVEL 1: CATÁLOGOS CON DEPENDENCIAS
-- ============================================

-- 8. dim_departamento (SUNAT Cat. 13 / INEI)
CREATE TABLE dim_departamento (
    id              SERIAL PRIMARY KEY,
    codigo_ubigeo   VARCHAR(2)  NOT NULL,
    pais_id         INT NOT NULL REFERENCES dim_pais(id),
    nombre          VARCHAR(100) NOT NULL,
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (codigo_ubigeo)
);
INSERT INTO dim_departamento (codigo_ubigeo, pais_id, nombre) VALUES
    ('01', 1, 'AMAZONAS'), ('02', 1, 'ÁNCASH'), ('03', 1, 'APURÍMAC'),
    ('04', 1, 'AREQUIPA'), ('05', 1, 'AYACUCHO'), ('06', 1, 'CAJAMARCA'),
    ('07', 1, 'CALLAO'), ('08', 1, 'CUSCO'), ('09', 1, 'HUANCAVELICA'),
    ('10', 1, 'HUÁNUCO'), ('11', 1, 'ICA'), ('12', 1, 'JUNÍN'),
    ('13', 1, 'LA LIBERTAD'), ('14', 1, 'LAMBAYEQUE'), ('15', 1, 'LIMA'),
    ('16', 1, 'LORETO'), ('17', 1, 'MADRE DE DIOS'), ('18', 1, 'MOQUEGUA'),
    ('19', 1, 'PASCO'), ('20', 1, 'PIURA'), ('21', 1, 'PUNO'),
    ('22', 1, 'SAN MARTÍN'), ('23', 1, 'TACNA'), ('24', 1, 'TUMBES'),
    ('25', 1, 'UCAYALI');

-- 9. dim_provincia (SUNAT Cat. 13 / INEI) - 196 registros totales
CREATE TABLE dim_provincia (
    id              SERIAL PRIMARY KEY,
    codigo_ubigeo   VARCHAR(4)  NOT NULL,
    departamento_id INT NOT NULL REFERENCES dim_departamento(id),
    nombre          VARCHAR(100) NOT NULL,
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (codigo_ubigeo)
);
INSERT INTO dim_provincia (codigo_ubigeo, departamento_id, nombre) VALUES
    ('0101', 1, 'CHACHAPOYAS'), ('0102', 1, 'BAGUA'),
    ('0103', 1, 'BONGARÁ'), ('0104', 1, 'CONDORCANQUI'),
    ('0105', 1, 'LUYA'), ('0106', 1, 'RODRÍGUEZ DE MENDOZA'),
    ('0107', 1, 'UTCUBAMBA'),
    ('0201', 2, 'HUARAZ'), ('0202', 2, 'AIJA'),
    ('0203', 2, 'ANTONIO RAYMONDI'), ('0204', 2, 'ASUNCIÓN'),
    ('0205', 2, 'BOLOGNESI'), ('0206', 2, 'CARHUAZ'),
    ('0207', 2, 'CARLOS F. FITZCARRALD'), ('0208', 2, 'CASMA'),
    ('0209', 2, 'CORONGO'), ('0210', 2, 'HUARI'),
    ('0211', 2, 'HUARMEY'), ('0212', 2, 'HUAYLAS'),
    ('0213', 2, 'MARISCAL LUZURIAGA'), ('0214', 2, 'OCROS'),
    ('0215', 2, 'PALLASCA'), ('0216', 2, 'POMABAMBA'),
    ('0217', 2, 'RECUAY'), ('0218', 2, 'SANTA'),
    ('0219', 2, 'SIHUAS'), ('0220', 2, 'YUNGAY'),
    ('0401', 4, 'AREQUIPA'), ('0402', 4, 'CAMANÁ'),
    ('0403', 4, 'CARAVELÍ'), ('0404', 4, 'CASTILLA'),
    ('0405', 4, 'CAYLLOMA'), ('0406', 4, 'CONDESUYOS'),
    ('0407', 4, 'ISLAY'), ('0408', 4, 'LA UNIÓN'),
    ('1501', 15, 'LIMA'), ('1502', 15, 'BARRANCA'),
    ('1503', 15, 'CAJATAMBO'), ('1504', 15, 'CANTA'),
    ('1505', 15, 'CAÑETE'), ('1506', 15, 'HUARAL'),
    ('1507', 15, 'HUAROCHIRÍ'), ('1508', 15, 'HUAURA'),
    ('1509', 15, 'OYÓN'), ('1510', 15, 'YAUYOS');

-- 10. dim_ubigeo (SUNAT Cat. 13 / INEI) - muestra de Lima Metropolitana
CREATE TABLE dim_ubigeo (
    id              SERIAL PRIMARY KEY,
    codigo_ubigeo   VARCHAR(6)  UNIQUE NOT NULL,
    pais_id         INT NOT NULL REFERENCES dim_pais(id),
    departamento_id INT NOT NULL REFERENCES dim_departamento(id),
    provincia_id    INT NOT NULL REFERENCES dim_provincia(id),
    nombre          VARCHAR(150) NOT NULL,
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_ubigeo (codigo_ubigeo, pais_id, departamento_id, provincia_id, nombre) VALUES
    ('150101', 1, 15, 37, 'LIMA'),
    ('150102', 1, 15, 37, 'ANCON'),
    ('150103', 1, 15, 37, 'ATE'),
    ('150104', 1, 15, 37, 'BARRANCO'),
    ('150105', 1, 15, 37, 'BREÑA'),
    ('150106', 1, 15, 37, 'CARABAYLLO'),
    ('150107', 1, 15, 37, 'COMAS'),
    ('150108', 1, 15, 37, 'CHACLACAYO'),
    ('150109', 1, 15, 37, 'CHORRILLOS'),
    ('150110', 1, 15, 37, 'LA MOLINA'),
    ('150111', 1, 15, 37, 'LA VICTORIA'),
    ('150112', 1, 15, 37, 'LINCE'),
    ('150113', 1, 15, 37, 'LOS OLIVOS'),
    ('150114', 1, 15, 37, 'LURIGANCHO'),
    ('150115', 1, 15, 37, 'LURIN'),
    ('150116', 1, 15, 37, 'MAGDALENA DEL MAR'),
    ('150117', 1, 15, 37, 'MIRAFLORES'),
    ('150118', 1, 15, 37, 'PACHACAMAC'),
    ('150119', 1, 15, 37, 'PUCUSANA'),
    ('150120', 1, 15, 37, 'PUENTE PIEDRA'),
    ('150121', 1, 15, 37, 'PUNTA NEGRA'),
    ('150122', 1, 15, 37, 'PUNTA HERMOSA'),
    ('150123', 1, 15, 37, 'RIMAC'),
    ('150124', 1, 15, 37, 'SAN BARTOLO'),
    ('150125', 1, 15, 37, 'SAN BORJA'),
    ('150126', 1, 15, 37, 'SAN ISIDRO'),
    ('150127', 1, 15, 37, 'SAN JUAN DE LURIGANCHO'),
    ('150128', 1, 15, 37, 'SAN JUAN DE MIRAFLORES'),
    ('150129', 1, 15, 37, 'SAN LUIS'),
    ('150130', 1, 15, 37, 'SAN MARTÍN DE PORRES'),
    ('150131', 1, 15, 37, 'SAN MIGUEL'),
    ('150132', 1, 15, 37, 'SANTA ANITA'),
    ('150133', 1, 15, 37, 'SANTIAGO DE SURCO'),
    ('150134', 1, 15, 37, 'SURQUILLO'),
    ('150135', 1, 15, 37, 'VILLA EL SALVADOR'),
    ('150136', 1, 15, 37, 'VILLA MARÍA DEL TRIUNFO');

-- 11. dim_tipo_afeccion_igv (SUNAT Cat. 07)
CREATE TABLE dim_tipo_afeccion_igv (
    id              SERIAL PRIMARY KEY,
    codigo_sunat    VARCHAR(2)  UNIQUE NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    tributo_asociado VARCHAR(4),
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_tipo_afeccion_igv (codigo_sunat, nombre, tributo_asociado) VALUES
    ('10', 'Gravado - Operación Onerosa',              '1000'),
    ('11', 'Gravado - Retiro por premio',              '1000'),
    ('12', 'Gravado - Retiro por donación',            '1000'),
    ('13', 'Gravado - Retiro',                          '1000'),
    ('14', 'Gravado - Retiro por publicidad',          '1000'),
    ('15', 'Gravado - Bonificaciones',                 '1000'),
    ('16', 'Gravado - Retiro por entrega a trabajadores', '1000'),
    ('17', 'Gravado - IVAP',                           '1016'),
    ('20', 'Exonerado - Operación Onerosa',            '9997'),
    ('21', 'Exonerado - Transferencia gratuita',       '9997'),
    ('30', 'Inafecto - Operación Onerosa',             '9998'),
    ('31', 'Inafecto - Retiro por Bonificación',       '9998'),
    ('32', 'Inafecto - Retiro',                        '9998'),
    ('33', 'Inafecto - Retiro por Muestras Médicas',   '9998'),
    ('34', 'Inafecto - Retiro por Convenio Colectivo', '9998'),
    ('35', 'Inafecto - Retiro por premio',             '9998'),
    ('36', 'Inafecto - Retiro por publicidad',         '9998'),
    ('37', 'Inafecto - Transferencia gratuita',        '9998'),
    ('40', 'Exportación de Bienes o Servicios',        '9995');

-- 12. dim_tipo_calculo_isc (SUNAT Cat. 08)
CREATE TABLE dim_tipo_calculo_isc (
    id              SERIAL PRIMARY KEY,
    codigo_sunat    VARCHAR(2)  UNIQUE NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    descripcion     TEXT,
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_tipo_calculo_isc (codigo_sunat, nombre, descripcion) VALUES
    ('01', 'Sistema al Valor',                'Porcentaje sobre el valor de venta'),
    ('02', 'Sistema Específico',              'Monto fijo por unidad vendida'),
    ('03', 'Sistema al Valor según Precio de Venta al Público', 'Porcentaje sobre el PVP');

-- 13. dim_nota_credito_tipo (SUNAT Cat. 09)
CREATE TABLE dim_nota_credito_tipo (
    id              SERIAL PRIMARY KEY,
    codigo_sunat    VARCHAR(2)  UNIQUE NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    activo          BOOLEAN DEFAULT true,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_nota_credito_tipo (codigo_sunat, nombre) VALUES
    ('01', 'Anulación de la operación'),
    ('02', 'Anulación por error en el RUC'),
    ('03', 'Corrección por error en la descripción'),
    ('04', 'Descuento global'),
    ('05', 'Descuento por ítem'),
    ('06', 'Devolución total'),
    ('07', 'Devolución por ítem'),
    ('08', 'Bonificación'),
    ('09', 'Disminución en el valor'),
    ('10', 'Otros conceptos');

-- 14-24. Catálogos de negocio (sin código SUNAT fijo)
CREATE TABLE dim_segmento_sunat (
    id         SERIAL PRIMARY KEY,
    codigo     VARCHAR(10) UNIQUE NOT NULL,
    nombre     VARCHAR(100) NOT NULL,
    activo     BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_segmento_sunat (codigo, nombre) VALUES
    ('GRAN_EMP', 'Gran Empresa'), ('MEDIANA', 'Mediana Empresa'),
    ('PEQUENA', 'Pequeña Empresa'), ('MICRO', 'Microempresa'),
    ('PUBLICO', 'Sector Público'), ('RUS', 'Régimen Único Simplificado'),
    ('NO_DOM', 'No Domiciliado');

CREATE TABLE dim_tipo_cliente (
    id SERIAL PRIMARY KEY, nombre VARCHAR(100) NOT NULL, activo BOOLEAN DEFAULT true, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO dim_tipo_cliente (nombre) VALUES
    ('Mayorista'), ('Minorista'), ('Corporativo'), ('Consumidor Final'), ('Distribuidor'), ('Gobierno');

CREATE TABLE dim_familia_sunat (id SERIAL PRIMARY KEY, codigo VARCHAR(10) UNIQUE NOT NULL, nombre VARCHAR(100) NOT NULL, activo BOOLEAN DEFAULT true, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT INTO dim_familia_sunat (codigo, nombre) VALUES
    ('ALIM','Alimentos y Bebidas'),('TEXT','Textiles'),('QUIM','Químicos'),
    ('FARM','Farmacéuticos'),('MADR','Madera y Papel'),('MIN','Minería'),
    ('MET','Metales'),('MAQ','Maquinaria'),('VEH','Vehículos'),('SERV','Servicios');

CREATE TABLE dim_clase_sunat (id SERIAL PRIMARY KEY, codigo VARCHAR(10) UNIQUE NOT NULL, nombre VARCHAR(100) NOT NULL, familia_id INT REFERENCES dim_familia_sunat(id), activo BOOLEAN DEFAULT true);
INSERT INTO dim_clase_sunat (codigo, nombre, familia_id) VALUES
    ('LACT','Lácteos',1),('CARN','Cárnicos',1),('BEB','Bebidas',1),
    ('PREN','Prendas de Vestir',2),('LIMP','Limpieza',3),
    ('MED','Medicamentos',4),('MUEB','Muebles',8),('COMP','Computación',8);

CREATE TABLE dim_producto_clase (id SERIAL PRIMARY KEY, nombre VARCHAR(100) NOT NULL, descripcion TEXT, activo BOOLEAN DEFAULT true);
INSERT INTO dim_producto_clase (nombre) VALUES ('Producto Terminado'),('Materia Prima'),('Insumo'),('Envase'),('Combo'),('Servicio'),('Activo Fijo');

CREATE TABLE dim_producto_subclase (id SERIAL PRIMARY KEY, clase_id INT NOT NULL REFERENCES dim_producto_clase(id), nombre VARCHAR(100) NOT NULL, activo BOOLEAN DEFAULT true);
INSERT INTO dim_producto_subclase (clase_id, nombre) VALUES
    (1,'Terminado de Venta'),(1,'Terminado para Promoción'),(2,'MP Nacional'),(2,'MP Importada'),
    (3,'Insumo Directo'),(3,'Insumo Indirecto'),(4,'Envase Primario'),(4,'Envase Secundario'),
    (5,'Pack Promocional'),(5,'Combo Temporada');

CREATE TABLE dim_tipo_venta (id SERIAL PRIMARY KEY, nombre VARCHAR(100) NOT NULL, activo BOOLEAN DEFAULT true);
INSERT INTO dim_tipo_venta (nombre) VALUES ('Venta Directa'),('Preventa'),('Venta por Mayor'),('Venta por Menor'),('Venta Itinerante'),('Venta Online');

CREATE TABLE dim_tipo_compra (id SERIAL PRIMARY KEY, nombre VARCHAR(100) NOT NULL, activo BOOLEAN DEFAULT true);
INSERT INTO dim_tipo_compra (nombre) VALUES ('Compra Nacional'),('Importación'),('Compra Local'),('Servicio'),('Activo Fijo'),('Gasto Operativo');

CREATE TABLE dim_estado_civil (id SERIAL PRIMARY KEY, nombre VARCHAR(50));
INSERT INTO dim_estado_civil (nombre) VALUES ('Soltero'),('Casado'),('Divorciado'),('Viudo'),('Conviviente');

CREATE TABLE dim_sexo (id SERIAL PRIMARY KEY, nombre VARCHAR(20));
INSERT INTO dim_sexo (nombre) VALUES ('Masculino'),('Femenino');

CREATE TABLE dim_documento_tipo (
    id SERIAL PRIMARY KEY, codigo_sunat VARCHAR(2) UNIQUE NOT NULL, nombre VARCHAR(100) NOT NULL,
    simbolo_id INT NOT NULL REFERENCES dim_documento_simbolo(id), requiere_ruc BOOLEAN DEFAULT true, activo BOOLEAN DEFAULT true
);
INSERT INTO dim_documento_tipo (codigo_sunat, nombre, simbolo_id, requiere_ruc) VALUES
    ('01','FACTURA',1,true),('03','BOLETA DE VENTA',2,false),('07','NOTA DE CRÉDITO',3,true),
    ('08','NOTA DE DÉBITO',4,true),('09','GUÍA DE REMISIÓN REMITENTE',5,true),
    ('20','COMPROBANTE DE RETENCIÓN',7,true),('31','GUÍA DE REMISIÓN TRANSPORTISTA',8,true),
    ('40','COMPROBANTE DE PERCEPCIÓN',9,true);

CREATE TABLE dim_tipo_cambio (
    id SERIAL PRIMARY KEY, moneda_origen_id INT NOT NULL REFERENCES dim_moneda(id),
    moneda_destino_id INT NOT NULL REFERENCES dim_moneda(id), valor DECIMAL(10,4) NOT NULL,
    fecha_vigencia DATE NOT NULL, UNIQUE (moneda_origen_id, moneda_destino_id, fecha_vigencia)
);
INSERT INTO dim_tipo_cambio (moneda_origen_id, moneda_destino_id, valor, fecha_vigencia) VALUES
    (1,1,1.0000,CURRENT_DATE),(1,2,0.2700,CURRENT_DATE),(2,1,3.7200,CURRENT_DATE),(2,2,1.0000,CURRENT_DATE);

CREATE TABLE dim_lista_precios (
    id SERIAL PRIMARY KEY, moneda_id INT NOT NULL REFERENCES dim_moneda(id), nombre VARCHAR(100) NOT NULL, activa BOOLEAN DEFAULT true
);
INSERT INTO dim_lista_precios (nombre, moneda_id) VALUES
    ('Lista General PEN',1),('Lista General USD',2),('Lista Mayorista PEN',1),('Lista Promocional PEN',1);

CREATE TABLE dim_usuario (
    id SERIAL PRIMARY KEY, codigo VARCHAR(20) UNIQUE NOT NULL, username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255), nombre_completo VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20), activo BOOLEAN DEFAULT true, ultimo_acceso TIMESTAMP, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dim_usuario_rol (
    rol_id INT NOT NULL REFERENCES dim_rol(id) ON DELETE CASCADE,
    model_type VARCHAR(100) NOT NULL DEFAULT 'App\Models\User',
    model_id INT NOT NULL REFERENCES dim_usuario(id) ON DELETE CASCADE,
    PRIMARY KEY (rol_id, model_id, model_type)
);

CREATE TABLE dim_rol_permiso (
    permiso_id INT NOT NULL REFERENCES dim_permiso(id) ON DELETE CASCADE,
    rol_id INT NOT NULL REFERENCES dim_rol(id) ON DELETE CASCADE,
    PRIMARY KEY (permiso_id, rol_id)
);
