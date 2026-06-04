import { Box, Typography, Card, CardContent, Grid } from '@mui/material';
import { useNavigate } from 'react-router-dom';
import InventoryIcon from '@mui/icons-material/Inventory';
import ReceiptIcon from '@mui/icons-material/Receipt';
import LocalShippingIcon from '@mui/icons-material/LocalShipping';
import { useAuthStore } from '../../shared/hooks/useAuth';

const quickLinks = [
  { title: 'Catálogo de Productos', icon: <InventoryIcon sx={{ fontSize: 40 }} />, path: '/portal/productos', color: '#1565c0' },
  { title: 'Mis Pedidos', icon: <ReceiptIcon sx={{ fontSize: 40 }} />, path: '#', color: '#2e7d32', disabled: true },
  { title: 'Tracking de Envíos', icon: <LocalShippingIcon sx={{ fontSize: 40 }} />, path: '#', color: '#e65100', disabled: true },
];

export default function PortalDashboardPage() {
  const user = useAuthStore((s) => s.user);
  const navigate = useNavigate();

  return (
    <Box>
      <Typography variant="h5" fontWeight={600} gutterBottom>
        Bienvenido, {user?.nombre_completo || 'Cliente'}
      </Typography>
      <Typography variant="body1" color="text.secondary" sx={{ mb: 4 }}>
        Panel de cliente — explora nuestro catálogo y gestiona tus pedidos.
      </Typography>
      <Grid container spacing={3}>
        {quickLinks.map((link) => (
          <Grid size={{ xs: 12, sm: 6, md: 4 }} key={link.title}>
            <Card
              sx={{
                cursor: link.disabled ? 'not-allowed' : 'pointer',
                opacity: link.disabled ? 0.5 : 1,
                transition: '0.2s',
                '&:hover': link.disabled ? {} : { transform: 'translateY(-4px)', boxShadow: 4 },
              }}
              onClick={() => { if (!link.disabled) navigate(link.path); }}
            >
              <CardContent sx={{ textAlign: 'center', py: 4 }}>
                <Box sx={{ color: link.color, mb: 2 }}>{link.icon}</Box>
                <Typography variant="h6">{link.title}</Typography>
                {link.disabled && <Typography variant="caption" color="text.disabled">Próximamente</Typography>}
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>
    </Box>
  );
}
