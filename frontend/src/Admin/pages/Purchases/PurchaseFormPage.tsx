import { Box, Typography } from '@mui/material';
import StorefrontIcon from '@mui/icons-material/Storefront';

export default function PurchaseFormPage() {
  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', py: 12 }}>
      <StorefrontIcon sx={{ fontSize: 64, color: '#9ca3af', mb: 2 }} />
      <Typography variant="h5" fontWeight={600} gutterBottom>
        Nueva Compra
      </Typography>
      <Typography color="text.secondary">
        Formulario de Compra — Próximamente
      </Typography>
    </Box>
  );
}
