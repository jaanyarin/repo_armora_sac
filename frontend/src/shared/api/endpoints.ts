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
  provincias: (departamentoId?: number, config?: { signal?: AbortSignal }) =>
    apiClient.get(`/catalog/provincias${departamentoId ? `/${departamentoId}` : ''}`, config),
  ubigeos: (provinciaId?: number, config?: { signal?: AbortSignal }) =>
    apiClient.get(`/catalog/ubigeos${provinciaId ? `/${provinciaId}` : ''}`, config),
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
  almacenes: () => apiClient.get('/catalog/almacenes'),
  documentosIdentidad: () => apiClient.get('/catalog/documentos-identidad'),
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

export const salesApi = {
  list: (params?: Record<string, string | number | boolean>) =>
    apiClient.get('/sales', { params }),
  find: (id: string) => apiClient.get(`/sales/${id}`),
  create: (data: Record<string, unknown>) =>
    apiClient.post('/sales', data),
  update: (id: string, data: Record<string, unknown>) =>
    apiClient.put(`/sales/${id}`, data),
  confirmar: (id: string) => apiClient.post(`/sales/${id}/confirmar`),
  anular: (id: string) => apiClient.post(`/sales/${id}/anular`),
  emitirNotaCredito: (id: string, data: { motivo: string; nota_credito_tipo_id?: number }) =>
    apiClient.post(`/sales/${id}/nota-credito`, data),
};

export const empresaApi = {
  get: () => apiClient.get('/empresa'),
  update: (data: Record<string, unknown>) => apiClient.put('/empresa', data),
  uploadImage: (tipo: string, file: File) => {
    const formData = new FormData();
    formData.append('imagen', file);
    return apiClient.post(`/empresa/imagen/${tipo}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
  resetImage: (tipo: string) => apiClient.delete(`/empresa/imagen/${tipo}`),
  actualizarDecimales: () => apiClient.post('/empresa/actualizar-decimales'),
};

export const inventoryApi = {
  stock: (params?: Record<string, string | number | boolean>) =>
    apiClient.get('/inventory/stock', { params }),
  stockByProduct: (productId: number) =>
    apiClient.get(`/inventory/stock/${productId}`),
  kardex: (params?: Record<string, string | number | boolean>) =>
    apiClient.get('/inventory/kardex', { params }),
};

export const personalApi = {
  list: (params?: Record<string, string | number | boolean>) =>
    apiClient.get('/personal', { params }),
  find: (id: number) => apiClient.get(`/personal/${id}`),
  create: (data: Record<string, unknown>) =>
    apiClient.post('/personal', data),
  update: (id: number, data: Record<string, unknown>) =>
    apiClient.put(`/personal/${id}`, data),
  delete: (id: number) => apiClient.delete(`/personal/${id}`),
  uploadPhoto: (id: number, file: File) => {
    const formData = new FormData();
    formData.append('foto', file);
    return apiClient.post(`/personal/${id}/foto`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
  resetPhoto: (id: number) => apiClient.delete(`/personal/${id}/foto`),
  rolesDisponibles: () => apiClient.get('/personal/roles-disponibles'),
  permisosAgrupados: () => apiClient.get('/personal/permisos-agrupados'),
};
