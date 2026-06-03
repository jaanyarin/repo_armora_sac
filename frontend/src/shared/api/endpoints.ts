import apiClient from './client';

export const authApi = {
  login: (login: string, password: string) =>
    apiClient.post('/auth/login', { login, password }),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get('/auth/me'),
};

export const catalogApi = {
  monedas: () => apiClient.get('/catalog/monedas'),
  unidadesMedida: () => apiClient.get('/catalog/unidades-medida'),
  paises: () => apiClient.get('/catalog/paises'),
  departamentos: () => apiClient.get('/catalog/departamentos'),
  provincias: (departamentoId?: number) =>
    apiClient.get(`/catalog/provincias${departamentoId ? `/${departamentoId}` : ''}`),
  ubigeos: (provinciaId?: number) =>
    apiClient.get(`/catalog/ubigeos${provinciaId ? `/${provinciaId}` : ''}`),
  documentos: () => apiClient.get('/catalog/documentos'),
  documentoTipos: () => apiClient.get('/catalog/documento-tipos'),
  roles: () => apiClient.get('/catalog/roles'),
  permisos: () => apiClient.get('/catalog/permisos'),
  tipoAfeccionIgv: () => apiClient.get('/catalog/tipos-afeccion-igv'),
  tipoCalculoIsc: () => apiClient.get('/catalog/tipos-calculo-isc'),
  notaCreditoTipos: () => apiClient.get('/catalog/nota-credito-tipos'),
  segmentos: () => apiClient.get('/catalog/segmentos'),
  tiposCliente: () => apiClient.get('/catalog/tipos-cliente'),
  familias: () => apiClient.get('/catalog/familias'),
  clases: () => apiClient.get('/catalog/clases'),
  productoClases: () => apiClient.get('/catalog/producto-clases'),
  productoSubclases: (claseId?: number) =>
    apiClient.get(`/catalog/producto-subclases${claseId ? `/${claseId}` : ''}`),
  tiposVenta: () => apiClient.get('/catalog/tipos-venta'),
  tiposCompra: () => apiClient.get('/catalog/tipos-compra'),
  tipoCambio: () => apiClient.get('/catalog/tipo-cambio'),
  listaPrecios: () => apiClient.get('/catalog/lista-precios'),
  estadosCivil: () => apiClient.get('/catalog/estados-civil'),
  sexos: () => apiClient.get('/catalog/sexos'),
};

export const customersApi = {
  list: (params?: Record<string, string | number | boolean>) =>
    apiClient.get('/customers', { params }),
  find: (id: number) => apiClient.get(`/customers/${id}`),
  create: (data: Record<string, unknown>) =>
    apiClient.post('/customers', data),
  update: (id: number, data: Record<string, unknown>) =>
    apiClient.put(`/customers/${id}`, data),
  delete: (id: number) => apiClient.delete(`/customers/${id}`),
};

export const productsApi = {
  list: (params?: Record<string, string | number | boolean>) =>
    apiClient.get('/products', { params }),
  find: (id: number) => apiClient.get(`/products/${id}`),
  create: (data: Record<string, unknown>) =>
    apiClient.post('/products', data),
  update: (id: number, data: Record<string, unknown>) =>
    apiClient.put(`/products/${id}`, data),
  delete: (id: number) => apiClient.delete(`/products/${id}`),
};
