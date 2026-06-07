export interface Moneda {
  id: number;
  codigo: string;
  nombre: string;
  simbolo: string;
  activo: boolean;
}

export interface UnidadMedida {
  id: number;
  codigo_sunat: string;
  nombre: string;
  simbolo: string;
  activo: boolean;
}

export interface Pais {
  id: number;
  codigo: string;
  nombre: string;
  activo: boolean;
}

export interface Departamento {
  id: number;
  codigo_ubigeo: string;
  pais_id: number;
  nombre: string;
  activo: boolean;
}

export interface Provincia {
  id: number;
  codigo_ubigeo: string;
  departamento_id: number;
  nombre: string;
  activo: boolean;
}

export interface Ubigeo {
  id: number;
  codigo_ubigeo: string;
  pais_id: number;
  departamento_id: number;
  provincia_id: number;
  nombre: string;
  activo: boolean;
}

export interface DocumentoSimbolo {
  id: number;
  codigo_sunat: string;
  nombre: string;
  simbolo: string;
  activo: boolean;
}

export interface DocumentoTipo {
  id: number;
  codigo_sunat: string;
  nombre: string;
  simbolo_id: number;
  requiere_ruc: boolean;
  activo: boolean;
}

export interface Rol {
  id: number;
  name: string;
  guard_name: string;
  descripcion: string;
  activo: boolean;
}

export interface Permiso {
  id: number;
  name: string;
  guard_name: string;
  descripcion: string;
  modulo: string;
  activo: boolean;
}

export interface TipoAfeccionIgv {
  id: number;
  codigo_sunat: string;
  nombre: string;
  tributo_asociado: string;
  activo: boolean;
}

export interface TipoCalculoIsc {
  id: number;
  codigo_sunat: string;
  nombre: string;
  descripcion: string;
  activo: boolean;
}

export interface NotaCreditoTipo {
  id: number;
  codigo_sunat: string;
  nombre: string;
  activo: boolean;
}

export interface SegmentoCliente {
  id: number;
  codigo: string;
  nombre: string;
  activo: boolean;
}

export interface TipoCliente {
  id: number;
  nombre: string;
  activo: boolean;
}

export interface DimProductoClase {
  id: number;
  nombre: string;
  descripcion: string | null;
  activo: boolean;
}

export interface DimProductoSubclase {
  id: number;
  clase_id: number;
  nombre: string;
  activo: boolean;
}

export interface FamiliaSunat {
  id: number;
  codigo: string;
  nombre: string;
  activo: boolean;
}

export interface ClaseSunat {
  id: number;
  codigo: string;
  nombre: string;
  familia_id: number;
  activo: boolean;
}

export interface ListaPrecio {
  id: number;
  moneda_id: number;
  nombre: string;
  activa: boolean;
}

export interface Usuario {
  id: number;
  codigo: string;
  username: string;
  name: string;
  nombre_completo: string;
  email: string;
  dni: string | null;
  ruc: string | null;
  telefono: string | null;
  activo: boolean;
  ultimo_acceso: string | null;
  roles: string[];
  permissions: string[];
  created_at: string;
}

export interface Customer {
  id: number;
  codigo: string;
  tipo_documento: string;
  numero_documento: string;
  nombre_completo: string;
  nombre_comercial: string | null;
  direccion: string | null;
  ubigeo_id: number | null;
  ubigeo: Ubigeo | null;
  email: string | null;
  telefono: string | null;
  tipo_cliente_id: number | null;
  tipo_cliente: TipoCliente | null;
  segmento_id: number | null;
  segmento: SegmentoCliente | null;
  lista_precio_id: number | null;
  limite_credito: number;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

export interface Product {
  id: number;
  codigo: string;
  codigo_sunat: string | null;
  nombre: string;
  descripcion: string | null;
  unidad_medida_id: number;
  unidad_medida: UnidadMedida | null;
  producto_clase_id: number | null;
  producto_clase: DimProductoClase | null;
  producto_subclase_id: number | null;
  producto_subclase: DimProductoSubclase | null;
  familia_sunat_id: number | null;
  familia_sunat: FamiliaSunat | null;
  clase_sunat_id: number | null;
  clase_sunat: ClaseSunat | null;
  tipo_afeccion_igv_id: number | null;
  tipo_afeccion_igv: TipoAfeccionIgv | null;
  tipo_calculo_isc_id: number | null;
  tipo_calculo_isc: TipoCalculoIsc | null;
  precio_venta: number;
  precio_venta_usd: number;
  costo_promedio: number;
  stock_minimo: number;
  stock_actual: number;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

export interface AuthState {
  user: Usuario | null;
  token: string | null;
  isAuthenticated: boolean;
  login: (login: string, password: string) => Promise<void>;
  logout: () => void;
  loadUser: () => Promise<void>;
}

export interface SaleItem {
  id: string;
  venta_id: string;
  producto_id: number;
  producto?: {
    id: number;
    codigo: string;
    nombre: string;
    unidad_medida?: string;
  };
  unidad_medida_id: number;
  numero_linea: number;
  cantidad: number;
  precio_unitario: number;
  descuento_linea: number;
  subtotal: number;
  igv: number;
  total: number;
  observaciones: string | null;
}

export interface Sale {
  id: string;
  codigo: string;
  cliente_id: number;
  cliente?: {
    id: number;
    codigo: string;
    nombre_completo: string;
    numero_documento: string;
  };
  usuario_id: number;
  usuario?: {
    id: number;
    nombre_completo: string;
  };
  documento_tipo_id: number | null;
  serie: string | null;
  numero: string | null;
  fecha_emision: string | null;
  fecha_vencimiento: string | null;
  moneda_id: number | null;
  subtotal: number;
  descuento_global: number;
  igv: number;
  isc: number;
  total: number;
  saldo_pendiente: number;
  estado: 'borrador' | 'confirmada' | 'anulada' | 'pagada' | 'parcial';
  observaciones: string | null;
  origen: 'admin' | 'portal';
  items?: SaleItem[];
  items_count?: number;
  notas_credito?: Array<{
    id: string;
    codigo: string;
    motivo: string;
    total: number;
    estado: string;
  }>;
  created_at: string | null;
  updated_at: string | null;
}

export interface SaleItemPayload {
  producto_id: number;
  unidad_medida_id: number;
  cantidad: number;
  precio_unitario: number;
  descuento_linea?: number;
  observaciones?: string;
}

export interface SalePayload {
  cliente_id: number;
  fecha_emision: string;
  estado?: 'borrador' | 'confirmada';
  observaciones?: string;
  origen?: 'admin' | 'portal';
  items: SaleItemPayload[];
}

export interface CreditNote {
  id: string;
  codigo: string;
  venta_id: string;
  usuario_id: number;
  motivo: string;
  total: number;
  estado: 'emitida' | 'anulada';
  created_at: string | null;
}

export interface Stock {
  id: number;
  producto_id: number;
  almacen_id: number | null;
  cantidad_disponible: number;
  ultima_actualizacion: string | null;
  producto?: Product;
}

export interface InventoryMovement {
  id: string;
  producto_id: number;
  almacen_id: number | null;
  tipo_movimiento: 'entrada' | 'salida' | 'ajuste' | 'transferencia';
  referencia_tipo: string | null;
  referencia_id: string | null;
  cantidad: number;
  precio_unitario: number;
  valor_total: number;
  saldo_anterior: number;
  saldo_nuevo: number;
  observaciones: string | null;
  usuario_id: number;
  fecha_movimiento: string | null;
  producto?: Product;
}

export interface CartItem {
  producto: Product;
  cantidad: number;
  precio_unitario: number;
}

export interface EmpresaConfig {
  id: number;
  razon_social: string | null;
  nombre_comercial: string | null;
  ruc: string | null;
  email: string | null;
  pais_id: number | null;
  telefono_fijo: string | null;
  telefono_celular: string | null;
  departamento_id: number | null;
  provincia_id: number | null;
  ubigeo_id: number | null;
  direccion: string | null;
  referencia: string | null;
  porcentaje_igv: number | null;
  boleta_monto_dni: number | null;
  envio_auto_sunat: boolean;
  consolidado_requerimientos: boolean;
  consolidado_liquidaciones: boolean;
  resumen_liquidacion: boolean;
  preventa_nota_pedido: boolean;
  periodo_fecha_inicio: string | null;
  periodo_fecha_fin: string | null;
  comision_defecto: number | null;
  hora_cierre: string | null;
  comision_neto: boolean;
  preview_fecha_inicio: string | null;
  preview_fecha_fin: string | null;
  preview: boolean;
  imagen_login: string | null;
  imagen_home: string | null;
  imagen_reporte: string | null;
  imagen_firma: string | null;
  ventas_bloqueadas: boolean;
  compras_bloqueadas: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface Personal {
  id: number;
  codigo: string;
  username: string;
  name: string;
  nombre_completo: string;
  apellido_paterno: string | null;
  apellido_materno: string | null;
  nombres: string | null;
  email: string | null;
  documento_identidad_id: number | null;
  documento_identidad: { id: number; codigo: string; nombre: string; longitud: string | null; regex: string | null } | null;
  numero_documento: string | null;
  sexo_id: number | null;
  sexo: Sexo | null;
  estado_civil_id: number | null;
  estado_civil: EstadoCivil | null;
  fecha_nacimiento: string | null;
  pais_id: number | null;
  pais: { id: number; nombre: string } | null;
  telefono: string | null;
  telefono_fijo: string | null;
  telefono_celular: string | null;
  departamento_id: number | null;
  provincia_id: number | null;
  ubigeo_id: number | null;
  direccion: string | null;
  referencia: string | null;
  foto_path: string | null;
  foto_url: string | null;
  activo: boolean;
  ultimo_acceso: string | null;
  password_changed_at: string | null;
  roles: string[];
  permisos_directos: string[];
  listas_precios: { id: number; nombre: string }[];
  almacenes: { id: number; nombre: string }[];
  created_at: string;
  updated_at: string;
}

export interface PersonalPayload {
  codigo?: string;
  username: string;
  apellido_paterno: string;
  apellido_materno: string;
  nombres: string;
  email?: string | null;
  documento_identidad_id?: number | null;
  numero_documento?: string | null;
  sexo_id?: number | null;
  estado_civil_id?: number | null;
  fecha_nacimiento?: string | null;
  pais_id?: number | null;
  telefono?: string | null;
  telefono_fijo?: string | null;
  telefono_celular?: string | null;
  departamento_id?: number | null;
  provincia_id?: number | null;
  ubigeo_id?: number | null;
  direccion?: string | null;
  referencia?: string | null;
  password?: string;
  password_confirmation?: string;
  activo?: boolean;
  roles?: string[];
  permisos?: number[];
  listas_precios?: number[];
  almacenes?: number[];
}

export interface DocumentoIdentidad {
  id: number;
  codigo: string;
  nombre: string;
  longitud: string | null;
  regex: string | null;
  pais_codigo: string | null;
  activo: boolean;
}

export interface Sexo {
  id: number;
  codigo: string;
  nombre: string;
  activo: boolean;
}

export interface EstadoCivil {
  id: number;
  codigo: string;
  nombre: string;
  activo: boolean;
}

export interface PermisoAgrupado {
  modulo: string;
  permisos: { id: number; name: string; descripcion: string }[];
}

export interface Proveedor {
  id: string;
  codigo: string;
  tipo_documento_id: number | null;
  numero_documento: string | null;
  nombre_completo: string;
  direccion: string | null;
  telefono: string | null;
  email: string | null;
  contacto_nombre: string | null;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

export interface Almacen {
  id: number;
  codigo: string;
  nombre: string;
  direccion: string | null;
  telefono: string | null;
  principal: boolean;
  activo: boolean;
}

export interface CompraItem {
  id: string;
  compra_id: string;
  producto_id: number;
  producto?: {
    id: number;
    codigo: string;
    nombre: string;
  };
  unidad_medida_id: number;
  numero_linea: number;
  cantidad: number;
  precio_unitario: number;
  descuento_linea: number;
  subtotal: number;
  igv: number;
  total: number;
  observaciones: string | null;
}

export type CompraEstado = 'borrador' | 'confirmada' | 'anulada' | 'pagada' | 'parcial';

export interface Compra {
  id: string;
  codigo: string;
  proveedor_id: string;
  proveedor?: Proveedor;
  usuario_id: number;
  documento_tipo_id: number | null;
  serie: string | null;
  numero: string | null;
  fecha_emision: string | null;
  fecha_vencimiento: string | null;
  moneda_id: number | null;
  almacen_id: number | null;
  subtotal: number;
  descuento_global: number;
  igv: number;
  isc: number;
  total: number;
  saldo_pendiente: number;
  estado: CompraEstado;
  observaciones: string | null;
  origen: 'admin' | 'portal';
  items?: CompraItem[];
  created_at: string | null;
  updated_at: string | null;
}

export interface CompraItemPayload {
  producto_id: number;
  unidad_medida_id: number;
  cantidad: number;
  precio_unitario: number;
  descuento_linea?: number;
  observaciones?: string;
}

export interface CompraPayload {
  proveedor_id: string;
  almacen_id?: number | null;
  fecha_emision: string;
  fecha_vencimiento?: string | null;
  documento_tipo_id?: number | null;
  serie?: string | null;
  numero?: string | null;
  moneda_id?: number | null;
  observaciones?: string | null;
  estado?: 'borrador' | 'confirmada';
  origen?: 'admin' | 'portal';
  items: CompraItemPayload[];
}

export interface ProductoClase {
  id: string;
  codigo: string;
  nombre: string;
  slug: string;
  descripcion: string | null;
  licor: boolean;
  orden: number;
  activo: boolean;
  subclases_count?: number;
  subclases?: ProductoSubclase[];
  created_at?: string | null;
  updated_at?: string | null;
  deleted_at?: string | null;
}

export interface ProductoSubclase {
  id: string;
  clase_id: string;
  clase?: { id: string; codigo: string; nombre: string } | null;
  codigo: string;
  nombre: string;
  slug: string;
  descripcion: string | null;
  orden: number;
  activo: boolean;
  created_at?: string | null;
  updated_at?: string | null;
  deleted_at?: string | null;
}

export interface ProductoClasePayload {
  codigo?: string | null;
  nombre: string;
  slug?: string | null;
  descripcion?: string | null;
  licor?: boolean;
  orden?: number;
  activo?: boolean;
}

export interface ProductoSubclasePayload {
  clase_id: string;
  codigo?: string | null;
  nombre: string;
  slug?: string | null;
  descripcion?: string | null;
  orden?: number;
  activo?: boolean;
}
