import apiClient from './client';

export const authApi = {
  login: (username: string, password: string) =>
    apiClient.post('/auth/login', { username, password }),
  logout: () => apiClient.post('/auth/logout'),
  me: () => apiClient.get('/auth/me'),
};

export const catalogApi = {
  monedas: () => apiClient.get('/catalogos/monedas'),
  unidadesMedida: () => apiClient.get('/catalogos/unidades-medida'),
  paises: () => apiClient.get('/catalogos/paises'),
  departamentos: () => apiClient.get('/catalogos/departamentos'),
  provincias: (departamentoId: number) =>
    apiClient.get(`/catalogos/departamentos/${departamentoId}/provincias`),
  ubigeos: (provinciaId: number) =>
    apiClient.get(`/catalogos/provincias/${provinciaId}/ubigeos`),
  documentosSimbolo: () => apiClient.get('/catalogos/documentos-simbolo'),
  documentosTipo: () => apiClient.get('/catalogos/documentos-tipo'),
  roles: () => apiClient.get('/catalogos/roles'),
  permisos: () => apiClient.get('/catalogos/permisos'),
  tipoAfeccionIgv: () => apiClient.get('/catalogos/tipo-afeccion-igv'),
  tipoCalculoIsc: () => apiClient.get('/catalogos/tipo-calculo-isc'),
};
