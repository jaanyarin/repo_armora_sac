export interface SidebarItem {
  label: string;
  path: string;
}

export interface SidebarSection {
  title: string;
  items: SidebarItem[];
}

export const sidebarMenu: SidebarSection[] = [
  {
    title: "Productos y Servicios",
    items: [
      { label: "Clases y Subclases", path: "/admin/productos/clases" },
      { label: "Crear Producto", path: "/admin/productos/nuevo" },
      { label: "Gestión Productos", path: "/admin/productos" },
      { label: "Reportes Productos", path: "/admin/productos/reportes" },
      { label: "Crear Servicio", path: "/admin/servicios/nuevo" },
      { label: "Gestión Servicios", path: "/admin/servicios" },
      { label: "Crear Combo", path: "/admin/combos/nuevo" },
      { label: "Gestión Combos", path: "/admin/combos" },
      { label: "Reportes Combos", path: "/admin/combos/reportes" },
    ],
  },
  {
    title: "Compras y Proveedores",
    items: [
      { label: "Crear Compra", path: "/admin/compras/nueva" },
      { label: "Gestión de Compras", path: "/admin/compras" },
      { label: "Crear Proveedor", path: "/admin/proveedores/nuevo" },
      { label: "Gestión de Proveedores", path: "/admin/proveedores" },
    ],
  },
  {
    title: "Almacenes e Inventario",
    items: [
      { label: "Gestión Almacenes", path: "/admin/almacenes" },
      { label: "Reportes Almacenes", path: "/admin/almacenes/reportes" },
      { label: "Inventario de Stocks", path: "/admin/inventario" },
      { label: "Gestión de Inventarios", path: "/admin/inventario/gestion" },
    ],
  },
  {
    title: "Clientes y Preventas",
    items: [
      { label: "Crear Cliente", path: "/admin/clientes/nuevo" },
      { label: "Gestión Clientes", path: "/admin/clientes" },
      { label: "Habilitar Ventas Clientes", path: "/admin/clientes/habilitar-ventas" },
      { label: "Cambio Día Atención", path: "/admin/clientes/cambio-dia" },
      { label: "Reportes Clientes", path: "/admin/clientes/reportes" },
      { label: "Gestión Preventas", path: "/admin/preventas" },
      { label: "Reportes Preventas", path: "/admin/preventas/reportes" },
    ],
  },
  {
    title: "Ventas",
    items: [
      { label: "Crear Venta Productos", path: "/admin/ventas/nueva" },
      { label: "Crear Venta Servicios", path: "/admin/ventas/nueva-servicio" },
      { label: "Gestión de Ventas", path: "/admin/ventas" },
      { label: "Puntos de Ventas", path: "/admin/ventas/puntos" },
      { label: "Gestión Notas Pedido", path: "/admin/ventas/notas-pedido" },
      { label: "Impresión de Ventas", path: "/admin/ventas/impresion" },
      { label: "Entregas Parciales", path: "/admin/ventas/entregas" },
      { label: "Fileteo Automático", path: "/admin/ventas/fileteo" },
      { label: "Reportes de Ventas", path: "/admin/ventas/reportes" },
    ],
  },
  {
    title: "Postventa y Ajustes",
    items: [
      { label: "Gestión Notas Crédito", path: "/admin/notas-credito" },
      { label: "Devolución Transportista", path: "/admin/notas-credito/devoluciones" },
      { label: "Impresión Notas Crédito", path: "/admin/notas-credito/impresion" },
      { label: "Reportes Notas Crédito", path: "/admin/notas-credito/reportes" },
      { label: "Crear Cambio Productos", path: "/admin/cambios/nuevo" },
      { label: "Asignar Fecha Entrega Cambios", path: "/admin/cambios/asignar-entrega" },
      { label: "Gestión Cambio Productos", path: "/admin/cambios" },
      { label: "Impresión Cambio Productos", path: "/admin/cambios/impresion" },
      { label: "Crear Req/Liq Cambios", path: "/admin/cambios/req-liq/nuevo" },
      { label: "Gestión Req/Liq Cambios", path: "/admin/cambios/req-liq" },
      { label: "Reporte Cambios Productos", path: "/admin/cambios/reportes" },
      { label: "Crear Canje", path: "/admin/canjes/nuevo" },
      { label: "Asignar Fecha Entrega Canjes", path: "/admin/canjes/asignar-entrega" },
      { label: "Gestión Canjes", path: "/admin/canjes" },
      { label: "Impresión Canjes", path: "/admin/canjes/impresion" },
      { label: "Crear Req/Liq Canjes", path: "/admin/canjes/req-liq/nuevo" },
      { label: "Gestión Req/Liq Canjes", path: "/admin/canjes/req-liq" },
      { label: "Reportes Canjes", path: "/admin/canjes/reportes" },
    ],
  },
  {
    title: "Premios y Concursos",
    items: [
      { label: "Requisitos de Canjes", path: "/admin/premios/requisitos" },
      { label: "Crear Premio Canje", path: "/admin/premios/nuevo" },
      { label: "Gestión Premio Canje", path: "/admin/premios" },
      { label: "Reportes Premio Canje", path: "/admin/premios/reportes" },
      { label: "Crear Concurso", path: "/admin/concursos/nuevo" },
      { label: "Gestión Concursos", path: "/admin/concursos" },
      { label: "Reportes Concursos", path: "/admin/concursos/reportes" },
    ],
  },
  {
    title: "Distribución, Zonas y Rutas",
    items: [
      { label: "Gestión Zonas y Rutas", path: "/admin/zonas-rutas" },
      { label: "Habilitar Ventas en Rutas", path: "/admin/zonas-rutas/habilitar-ventas" },
      { label: "Reportes Zonas y Rutas", path: "/admin/zonas-rutas/reportes" },
      { label: "Crear Mapa de Rutas", path: "/admin/mapas-rutas/nuevo" },
      { label: "Gestión de Mapas de Rutas", path: "/admin/mapas-rutas" },
      { label: "Reportes de Mapas de Rutas", path: "/admin/mapas-rutas/reportes" },
      { label: "Crear Unidad Trans.", path: "/admin/unidades-transporte/nuevo" },
      { label: "Gestión Unidades de Trans.", path: "/admin/unidades-transporte" },
      { label: "Reportes Unidades de Trans.", path: "/admin/unidades-transporte/reportes" },
      { label: "Mis Devoluciones", path: "/admin/transportistas/devoluciones" },
      { label: "Mis Requerimientos", path: "/admin/transportistas/requerimientos" },
      { label: "Mis Liquidaciones", path: "/admin/transportistas/liquidaciones" },
    ],
  },
  {
    title: "SUNAT y Documentos Electrónicos",
    items: [
      { label: "Envíos Pendientes SUNAT", path: "/admin/sunat/envios-pendientes" },
      { label: "Gestión Envíos SUNAT", path: "/admin/sunat" },
      { label: "Reportes Envíos SUNAT", path: "/admin/sunat/reportes" },
      { label: "Crear Resumen Diario", path: "/admin/resumen-diario/nuevo" },
      { label: "Gestión de Resúmenes", path: "/admin/resumen-diario" },
      { label: "Reportes de Resúmenes", path: "/admin/resumen-diario/reportes" },
      { label: "Crear Comunicación de Baja", path: "/admin/comunicacion-baja/nuevo" },
      { label: "Gestión de Bajas", path: "/admin/comunicacion-baja" },
      { label: "Reportes de Baja", path: "/admin/comunicacion-baja/reportes" },
    ],
  },
  {
    title: "Requerimientos y Liquidaciones",
    items: [
      { label: "Crear Requerimiento", path: "/admin/requerimientos/nuevo" },
      { label: "Gestión Requerimientos", path: "/admin/requerimientos" },
      { label: "Reportes Requerimientos", path: "/admin/requerimientos/reportes" },
      { label: "Crear Liquidación", path: "/admin/liquidaciones/nuevo" },
      { label: "Gestión Liquidaciones", path: "/admin/liquidaciones" },
      { label: "Reportes Liquidaciones", path: "/admin/liquidaciones/reportes" },
    ],
  },
  {
    title: "Reportes e Informes",
    items: [
      { label: "Informes de Requerimientos", path: "/admin/informes/requerimientos" },
      { label: "Informes de Liquidaciones", path: "/admin/informes/liquidaciones" },
      { label: "Informes de Almacenes", path: "/admin/informes/almacenes" },
      { label: "Entregas Parciales", path: "/admin/informes/entregas-parciales" },
      { label: "Crear Reporte Comisiones", path: "/admin/comisiones/nuevo" },
      { label: "Gestión Reporte Comisiones", path: "/admin/comisiones" },
      { label: "Ajuste de Comisiones", path: "/admin/comisiones/ajuste" },
      { label: "Crear Reporte Cobertura", path: "/admin/cobertura/nuevo" },
      { label: "Gestión Reporte Cobertura", path: "/admin/cobertura" },
    ],
  },
  {
    title: "Vendedor",
    items: [
      { label: "Crear Cliente", path: "/admin/vendedor/crear-cliente" },
      { label: "Clientes", path: "/admin/vendedor/clientes" },
      { label: "Crear Preventa", path: "/admin/vendedor/crear-preventa" },
      { label: "Preventas", path: "/admin/vendedor/preventas" },
      { label: "Ventas", path: "/admin/vendedor/ventas" },
      { label: "Crear Cambio Productos", path: "/admin/vendedor/crear-cambio" },
      { label: "Cambios de Productos", path: "/admin/vendedor/cambios" },
      { label: "Crear Canje", path: "/admin/vendedor/crear-canje" },
      { label: "Canjes", path: "/admin/vendedor/canjes" },
      { label: "Stock de Productos", path: "/admin/vendedor/stock" },
      { label: "Comisiones", path: "/admin/vendedor/comisiones" },
      { label: "Concursos", path: "/admin/vendedor/concursos" },
    ],
  },
  {
    title: "Personal",
    items: [
      { label: "Crear Personal", path: "/admin/personal/nuevo" },
      { label: "Gestión Personal", path: "/admin/personal" },
      { label: "Reportes Personal", path: "/admin/personal/reportes" },
    ],
  },
  {
    title: "Configuración",
    items: [
      { label: "Configuración Empresa", path: "/admin/configuracion" },
      { label: "Configuración Impresión", path: "/admin/configuracion/impresion" },
      { label: "Configuración SUNAT", path: "/admin/configuracion/sunat" },
      { label: "Configuración Alertas", path: "/admin/configuracion/alertas" },
    ],
  },
];
