import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ThemeProvider, CssBaseline } from '@mui/material';
import { adminTheme } from './shared/theme';
import ProtectedRoute from './shared/components/ProtectedRoute';
import LoginPage from './shared/components/LoginPage';
import NotFoundPage from './shared/components/NotFoundPage';
import AdminLayout from './Admin/layouts/AdminLayout';
import AdminDashboardPage from './Admin/pages/DashboardPage';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: 1, refetchOnWindowFocus: false, staleTime: 5 * 60 * 1000 },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <ThemeProvider theme={adminTheme}>
        <CssBaseline />
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/admin" element={<ProtectedRoute />}>
              <Route element={<AdminLayout />}>
                <Route index element={<Navigate to="dashboard" replace />} />
                <Route path="dashboard" element={<AdminDashboardPage />} />
                <Route path="clientes" element={<Typography sx={{ p: 4 }}>Módulo de Clientes — Próximamente</Typography>} />
                <Route path="productos" element={<Typography sx={{ p: 4 }}>Módulo de Productos — Próximamente</Typography>} />
                <Route path="ventas" element={<Typography sx={{ p: 4 }}>Módulo de Ventas — Próximamente</Typography>} />
                <Route path="inventario" element={<Typography sx={{ p: 4 }}>Módulo de Inventario — Próximamente</Typography>} />
                <Route path="logistica" element={<Typography sx={{ p: 4 }}>Módulo de Logística — Próximamente</Typography>} />
                <Route path="configuracion" element={<Typography sx={{ p: 4 }}>Configuración — Próximamente</Typography>} />
              </Route>
            </Route>
            <Route path="/" element={<Navigate to="/login" replace />} />
            <Route path="*" element={<NotFoundPage />} />
          </Routes>
        </BrowserRouter>
      </ThemeProvider>
    </QueryClientProvider>
  );
}
