import { Box, Typography, Button } from '@mui/material';
import { useNavigate } from 'react-router-dom';

export default function NotFoundPage() {
  const navigate = useNavigate();
  return (
    <Box sx={{ minHeight: '60vh', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 2 }}>
      <Typography variant="h2" fontWeight={700} color="primary">404</Typography>
      <Typography variant="h5" color="text.secondary" gutterBottom>Página no encontrada</Typography>
      <Button variant="contained" onClick={() => navigate('/admin/dashboard')}>
        Volver al Inicio
      </Button>
    </Box>
  );
}
