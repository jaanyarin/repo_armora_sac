import { Box, Typography } from '@mui/material';
import PeopleOutlineIcon from '@mui/icons-material/PeopleOutline';

export default function SupplierListPage() {
  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', py: 12 }}>
      <PeopleOutlineIcon sx={{ fontSize: 64, color: '#9ca3af', mb: 2 }} />
      <Typography variant="h5" fontWeight={600} gutterBottom>
        Proveedores
      </Typography>
      <Typography color="text.secondary">
        Módulo de Proveedores — Próximamente
      </Typography>
    </Box>
  );
}
