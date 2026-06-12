import { useState } from 'react';
import {
  Box,
  Typography,
  Card,
  CardContent,
  CardActions,
  Button,
  Autocomplete,
  TextField,
  Snackbar,
  Alert,
  Divider,
} from '@mui/material';
import FileExcelIcon from '@mui/icons-material/Description';
import Inventory2Icon from '@mui/icons-material/Inventory2';
import { useQuery } from '@tanstack/react-query';
import { productoClasesApi, productoSubclasesApi } from '../../../shared/api/endpoints';

export default function ReportesProductosPage() {
  const [selectedClase, setSelectedClase] = useState<{ id: string; nombre: string } | null>(null);
  const [selectedSubclase, setSelectedSubclase] = useState<{ id: string; nombre: string } | null>(null);
  const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false, message: '', severity: 'success',
  });

  const { data: clases, isLoading: clasesLoading } = useQuery({
    queryKey: ['producto-clases-reporte'],
    queryFn: async () => {
      const res = await productoClasesApi.list({ per_page: 200, sort_by: 'orden', sort_dir: 'asc' });
      return (res.data?.data ?? []) as Array<{ id: string; codigo: string; nombre: string }>;
    },
  });

  const { data: subclases, isLoading: subclasesLoading } = useQuery({
    queryKey: ['producto-subclases-reporte', selectedClase?.id],
    queryFn: async () => {
      if (!selectedClase) return [];
      const res = await productoSubclasesApi.list({ per_page: 300, clase_id: selectedClase.id, sort_by: 'orden', sort_dir: 'asc' });
      return (res.data?.data ?? []) as Array<{ id: string; codigo: string; nombre: string }>;
    },
    enabled: !!selectedClase,
  });

  const claseOptions = (clases ?? []).map((c: { id: string; nombre: string }) => ({ id: c.id, nombre: c.nombre }));
  const subclaseOptions = (subclases ?? []).map((s: { id: string; nombre: string }) => ({ id: s.id, nombre: s.nombre }));

  const handleGenerarReporte = async () => {
    try {
      const token = localStorage.getItem('auth_token');
      const params = new URLSearchParams({ _t: String(Date.now()) });
      if (selectedClase) params.set('clase_id', selectedClase.id);
      if (selectedSubclase) params.set('subclase_id', selectedSubclase.id);
      const url = `/api/products/reportes/productos?${params.toString()}`;
      const win = window.open('about:blank', '_blank');
      if (!win) {
        setSnackbar({ open: true, message: 'Permita ventanas emergentes para visualizar el reporte.', severity: 'error' });
        return;
      }
      const res = await fetch(url, {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) {
        win.close();
        setSnackbar({ open: true, message: 'Error al generar el reporte. Verifique sus permisos.', severity: 'error' });
        return;
      }
      const html = await res.text();
      win.document.open();
      win.document.write(html);
      win.document.close();
      setSnackbar({ open: true, message: 'Reporte de productos activos generado.', severity: 'success' });
    } catch {
      setSnackbar({ open: true, message: 'Error al generar el reporte.', severity: 'error' });
    }
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ fontWeight: 700, mb: 1 }}>
        Reportes Productos
      </Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 4 }}>
        Genera reportes imprimibles de los productos activos del sistema. Usa &laquo;Imprimir / Guardar PDF&raquo; en la barra superior del reporte para exportarlo a PDF.
      </Typography>

      <Box sx={{ maxWidth: 500 }}>
        <Card elevation={2}>
          <CardContent sx={{ textAlign: 'center', py: 4 }}>
            <Inventory2Icon sx={{ fontSize: 56, color: 'primary.main', mb: 2 }} />
            <Typography variant="h5" sx={{ fontWeight: 600, mb: 1 }}>
              Productos
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
              Reporte detallado de los productos activos del sistema.
            </Typography>
            <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, textAlign: 'left' }}>
              <Autocomplete
                size="small"
                options={claseOptions}
                getOptionLabel={(opt) => opt.nombre}
                isOptionEqualToValue={(opt, val) => opt.id === val.id}
                value={selectedClase}
                onChange={(_, val) => {
                  setSelectedClase(val);
                  setSelectedSubclase(null);
                }}
                loading={clasesLoading}
                renderInput={(params) => (
                  <TextField {...params} label="Clase" placeholder="Seleccione una clase" />
                )}
              />
              <Autocomplete
                size="small"
                options={subclaseOptions}
                getOptionLabel={(opt) => opt.nombre}
                isOptionEqualToValue={(opt, val) => opt.id === val.id}
                value={selectedSubclase}
                onChange={(_, val) => setSelectedSubclase(val)}
                loading={subclasesLoading}
                disabled={!selectedClase}
                renderInput={(params) => (
                  <TextField {...params} label="Subclase" placeholder="Seleccione una subclase" />
                )}
              />
            </Box>
          </CardContent>
          <Divider />
          <CardActions sx={{ p: 0 }}>
            <Button
              fullWidth
              size="large"
              startIcon={<FileExcelIcon />}
              onClick={handleGenerarReporte}
              sx={{
                bgcolor: '#22c55e',
                color: 'white',
                py: 1.5,
                fontWeight: 600,
                borderRadius: 0,
                '&:hover': { bgcolor: '#16a34a' },
              }}
            >
              Generar Reporte
            </Button>
          </CardActions>
        </Card>
      </Box>

      <Snackbar
        open={snackbar.open}
        autoHideDuration={4000}
        onClose={() => setSnackbar({ ...snackbar, open: false })}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert
          onClose={() => setSnackbar({ ...snackbar, open: false })}
          severity={snackbar.severity}
          sx={{ width: '100%' }}
        >
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
}
