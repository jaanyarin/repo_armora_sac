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

export interface Usuario {
  id: number;
  codigo: string;
  username: string;
  email: string;
  nombre_completo: string;
  activo: boolean;
  roles: Rol[];
}

export interface AuthState {
  user: Usuario | null;
  token: string | null;
  isAuthenticated: boolean;
  login: (username: string, password: string) => Promise<void>;
  logout: () => void;
}
