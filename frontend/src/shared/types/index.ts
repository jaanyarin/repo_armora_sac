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

export interface ProductoClase {
  id: number;
  nombre: string;
  descripcion: string | null;
  activo: boolean;
}

export interface ProductoSubclase {
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
  producto_clase: ProductoClase | null;
  producto_subclase_id: number | null;
  producto_subclase: ProductoSubclase | null;
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
