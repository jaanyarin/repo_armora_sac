import { Box, Typography } from '@mui/material';
import StorefrontIcon from '@mui/icons-material/Storefront';

export default function PurchaseListPage() {
  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', py: 12 }}>
      <StorefrontIcon sx={{ fontSize: 64, color: '#9ca3af', mb: 2 }} />
      <Typography variant="h5" fontWeight={600} gutterBottom>
        Compras
      </Typography>
      <Typography color="text.secondary">
        Módulo de Compras — Próximamente
      </Typography>
    </Box>
  );
}
