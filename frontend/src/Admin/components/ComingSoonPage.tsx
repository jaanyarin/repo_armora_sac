import { useLocation } from 'react-router-dom';
import { Box, Typography } from '@mui/material';
import ConstructionIcon from '@mui/icons-material/Construction';

export default function ComingSoonPage() {
  const location = useLocation();
  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', py: 12 }}>
      <ConstructionIcon sx={{ fontSize: 64, color: '#9ca3af', mb: 2 }} />
      <Typography variant="h5" fontWeight={600} gutterBottom>
        Módulo en desarrollo
      </Typography>
      <Typography color="text.secondary">
        Esta funcionalidad estará disponible próximamente.
      </Typography>
      <Typography variant="caption" color="text.disabled" sx={{ mt: 1 }}>
        Ruta: {location.pathname}
      </Typography>
    </Box>
  );
}
