import { useState, useMemo } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { Box, Drawer } from '@mui/material';
import Topbar from '../components/Topbar';
import Sidebar from '../components/Sidebar';
import { sidebarMenu } from '../data/sidebarMenu';

const DRAWER_WIDTH = 280;

function findMenuItem(pathname: string): { label: string; path: string } | null {
  for (const section of sidebarMenu) {
    for (const item of section.items) {
      if (pathname === item.path || pathname.startsWith(item.path + '/')) {
        return item;
      }
    }
  }
  return null;
}

function deriveTitle(pathname: string): string {
  const matched = findMenuItem(pathname);
  if (matched) return matched.label;

  if (pathname === '/admin/dashboard') return 'Dashboard';
  if (pathname.startsWith('/admin/clientes')) {
    if (pathname.includes('/nuevo')) return 'Nuevo Cliente';
    if (pathname.includes('/editar')) return 'Editar Cliente';
    return 'Clientes';
  }
  if (pathname.startsWith('/admin/productos')) {
    if (pathname.includes('/nuevo')) return 'Nuevo Producto';
    if (pathname.includes('/editar')) return 'Editar Producto';
    return 'Productos';
  }
  if (pathname.startsWith('/admin/ventas')) {
    if (pathname.includes('/nueva')) return 'Nueva Venta';
    if (pathname.includes('/editar')) return 'Editar Venta';
    return 'Ventas';
  }
  if (pathname.startsWith('/admin/compras')) {
    if (pathname.includes('/nueva')) return 'Nueva Compra';
    return 'Compras';
  }
  if (pathname.startsWith('/admin/proveedores')) {
    if (pathname.includes('/nuevo')) return 'Nuevo Proveedor';
    return 'Proveedores';
  }
  if (pathname.startsWith('/admin/personal')) {
    if (pathname.includes('/reportes')) return 'Reportes Personal';
    if (pathname.includes('/nuevo')) return 'Nuevo Personal';
    if (pathname.includes('/editar')) return 'Editar Personal';
    return 'Personal';
  }
  return 'ARMORA';
}

export default function AdminLayout() {
  const [mobileOpen, setMobileOpen] = useState(false);
  const location = useLocation();

  const title = useMemo(() => deriveTitle(location.pathname), [location.pathname]);

  return (
    <Box sx={{ display: 'flex', minHeight: '100vh', bgcolor: '#f4f6f8' }}>
      <Topbar
        title={title}
        path={location.pathname}
        onToggleSidebar={() => setMobileOpen(!mobileOpen)}
      />

      <Box
        component="nav"
        sx={{ width: { md: DRAWER_WIDTH }, flexShrink: { md: 0 }, position: 'relative', zIndex: 1200 }}
      >
        <Drawer
          variant="temporary"
          open={mobileOpen}
          onClose={() => setMobileOpen(false)}
          ModalProps={{ keepMounted: true }}
          sx={{
            display: { xs: 'block', md: 'none' },
            '& .MuiDrawer-paper': { width: DRAWER_WIDTH },
          }}
        >
          <Sidebar onClose={() => setMobileOpen(false)} />
        </Drawer>

        <Drawer
          variant="permanent"
          sx={{
            display: { xs: 'none', md: 'block' },
            '& .MuiDrawer-paper': {
              width: DRAWER_WIDTH,
              borderRight: 'none',
              bgcolor: '#1f2937',
            },
          }}
          open
        >
          <Sidebar onClose={() => {}} />
        </Drawer>
      </Box>

      <Box
        component="main"
        sx={{
          flexGrow: 1,
          mt: '62px',
          ml: 0,
          p: 3,
          minHeight: 'calc(100vh - 62px)',
          width: { md: `calc(100% - ${DRAWER_WIDTH}px)` },
        }}
      >
        <Outlet />
      </Box>
    </Box>
  );
}
