import { useEffect, lazy, Suspense } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider, CssBaseline, Box, CircularProgress, Typography } from '@mui/material';
import { adminTheme } from './shared/theme';
import ProtectedRoute from './shared/components/ProtectedRoute';
import LoginPage from './shared/components/LoginPage';
import NotFoundPage from './shared/components/NotFoundPage';
import AdminLayout from './Admin/layouts/AdminLayout';
import AdminDashboardPage from './Admin/pages/DashboardPage';
import PortalLayout from './Portal/layouts/PortalLayout';
import { useAuthStore } from './shared/hooks/useAuth';

const CustomerListPage = lazy(() => import('./Admin/pages/Customers/CustomerListPage'));
const CustomerFormPage = lazy(() => import('./Admin/pages/Customers/CustomerFormPage'));
const ProductListPage = lazy(() => import('./Admin/pages/Products/ProductListPage'));
const ProductFormPage = lazy(() => import('./Admin/pages/Products/ProductFormPage'));
const SaleListPage = lazy(() => import('./Admin/pages/Sales/SaleListPage'));
const SaleFormPage = lazy(() => import('./Admin/pages/Sales/SaleFormPage'));
const PortalLoginPage = lazy(() => import('./Portal/pages/PortalLoginPage'));
const PortalDashboardPage = lazy(() => import('./Portal/pages/PortalDashboardPage'));
const ProductCatalogPage = lazy(() => import('./Portal/pages/ProductCatalogPage'));
const OrderCreatePage = lazy(() => import('./Portal/pages/OrderCreatePage'));
const OrderHistoryPage = lazy(() => import('./Portal/pages/OrderHistoryPage'));

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: 1, refetchOnWindowFocus: false, staleTime: 5 * 60 * 1000 },
  },
});

function SuspenseWrapper({ children }: { children: React.ReactNode }) {
  return <Suspense fallback={<Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}><CircularProgress /></Box>}>{children}</Suspense>;
}

function AppWithAuth() {
  const loadUser = useAuthStore((s) => s.loadUser);

  useEffect(() => {
    loadUser();
  }, [loadUser]);

  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/admin" element={<ProtectedRoute />}>
        <Route element={<AdminLayout />}>
          <Route index element={<Navigate to="dashboard" replace />} />
          <Route path="dashboard" element={<AdminDashboardPage />} />
          <Route path="clientes" element={<SuspenseWrapper><CustomerListPage /></SuspenseWrapper>} />
          <Route path="clientes/nuevo" element={<SuspenseWrapper><CustomerFormPage /></SuspenseWrapper>} />
          <Route path="clientes/:id/editar" element={<SuspenseWrapper><CustomerFormPage /></SuspenseWrapper>} />
          <Route path="productos" element={<SuspenseWrapper><ProductListPage /></SuspenseWrapper>} />
          <Route path="productos/nuevo" element={<SuspenseWrapper><ProductFormPage /></SuspenseWrapper>} />
          <Route path="productos/:id/editar" element={<SuspenseWrapper><ProductFormPage /></SuspenseWrapper>} />
          <Route path="ventas" element={<SuspenseWrapper><SaleListPage /></SuspenseWrapper>} />
          <Route path="ventas/nueva" element={<SuspenseWrapper><SaleFormPage /></SuspenseWrapper>} />
          <Route path="ventas/:id" element={<SuspenseWrapper><SaleListPage /></SuspenseWrapper>} />
          <Route path="inventario" element={<SuspenseWrapper><Typography sx={{ p: 4 }}>Módulo de Inventario — Próximamente</Typography></SuspenseWrapper>} />
          <Route path="logistica" element={<SuspenseWrapper><Typography sx={{ p: 4 }}>Módulo de Logística — Próximamente</Typography></SuspenseWrapper>} />
          <Route path="configuracion" element={<SuspenseWrapper><Typography sx={{ p: 4 }}>Configuración — Próximamente</Typography></SuspenseWrapper>} />
        </Route>
      </Route>
      <Route path="/portal" element={<PortalLayout />}>
        <Route index element={<Navigate to="productos" replace />} />
        <Route path="productos" element={<SuspenseWrapper><ProductCatalogPage /></SuspenseWrapper>} />
        <Route path="dashboard" element={<SuspenseWrapper><PortalDashboardPage /></SuspenseWrapper>} />
        <Route path="login" element={<SuspenseWrapper><PortalLoginPage /></SuspenseWrapper>} />
        <Route path="pedidos" element={<SuspenseWrapper><OrderHistoryPage /></SuspenseWrapper>} />
        <Route path="pedidos/nuevo" element={<SuspenseWrapper><OrderCreatePage /></SuspenseWrapper>} />
      </Route>
      <Route path="/" element={<Navigate to="/login" replace />} />
      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  );
}

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider theme={adminTheme}>
        <CssBaseline />
        <BrowserRouter>
          <AppWithAuth />
        </BrowserRouter>
      </ThemeProvider>
    </QueryClientProvider>
  );
}
