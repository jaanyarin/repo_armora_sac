import { Box, Typography } from '@mui/material';
import PersonAddAltIcon from '@mui/icons-material/PersonAddAlt';

export default function SupplierFormPage() {
  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', py: 12 }}>
      <PersonAddAltIcon sx={{ fontSize: 64, color: '#9ca3af', mb: 2 }} />
      <Typography variant="h5" fontWeight={600} gutterBottom>
        Nuevo Proveedor
      </Typography>
      <Typography color="text.secondary">
        Formulario de Proveedor — Próximamente
      </Typography>
    </Box>
  );
}
