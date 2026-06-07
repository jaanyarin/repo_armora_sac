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
  Grid,
} from '@mui/material';
import PictureAsPdfIcon from '@mui/icons-material/PictureAsPdf';
import GroupsIcon from '@mui/icons-material/Groups';
import PersonIcon from '@mui/icons-material/Person';
import { useQuery } from '@tanstack/react-query';
import { personalApi } from '../../../shared/api/endpoints';

interface PersonalOption {
  id: number;
  codigo: string;
  nombre_completo: string;
  activo: boolean;
}

export default function ReportesPersonalPage() {
  const [selected, setSelected] = useState<PersonalOption | null>(null);
  const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false, message: '', severity: 'success',
  });

  const { data, isLoading } = useQuery({
    queryKey: ['personal-para-reportes'],
    queryFn: async () => {
      const res = await personalApi.list({ per_page: 500 });
      const rows = (res.data?.data ?? []) as Array<{
        id: number;
        codigo: string;
        nombre_completo: string;
        activo: boolean;
      }>;
      return rows;
    },
  });

  const options: PersonalOption[] = (data ?? []).map((p: { id: number; codigo: string; nombre_completo: string; activo: boolean }) => ({
    id: p.id,
    codigo: p.codigo,
    nombre_completo: p.nombre_completo,
    activo: p.activo,
  }));

  const handleGenerarActivo = async () => {
    try {
      const token = localStorage.getItem('auth_token');
      const url = `/api/personal/reportes/personal-activo?_t=${Date.now()}`;
      const win = window.open('about:blank', '_blank');
      if (!win) {
        setSnackbar({ open: true, message: 'Permita ventanas emergentes para visualizar el reporte.', severity: 'error' });
        return;
      }
      const res = await fetch(url, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const html = await res.text();
      win.document.open();
      win.document.write(html);
      win.document.close();
      setSnackbar({ open: true, message: 'Reporte de personal activo generado.', severity: 'success' });
    } catch {
      setSnackbar({ open: true, message: 'Error al generar el reporte.', severity: 'error' });
    }
  };

  const handleGenerarFicha = async () => {
    if (!selected) {
      setSnackbar({ open: true, message: 'Debe seleccionar un personal.', severity: 'error' });
      return;
    }
    try {
      const token = localStorage.getItem('auth_token');
      const url = `/api/personal/reportes/ficha-personal?pid=${selected.id}&_t=${Date.now()}`;
      const win = window.open('about:blank', '_blank');
      if (!win) {
        setSnackbar({ open: true, message: 'Permita ventanas emergentes para visualizar el reporte.', severity: 'error' });
        return;
      }
      const res = await fetch(url, {
        headers: { Authorization: `Bearer ${token}` },
      });
      const html = await res.text();
      win.document.open();
      win.document.write(html);
      win.document.close();
      setSnackbar({ open: true, message: `Ficha de ${selected.nombre_completo} generada.`, severity: 'success' });
    } catch {
      setSnackbar({ open: true, message: 'Error al generar la ficha.', severity: 'error' });
    }
  };

  return (
    <Box>
      <Typography variant="h4" sx={{ fontWeight: 700, mb: 1 }}>
        Reportes de Personal
      </Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 4 }}>
        Genera reportes imprimibles del personal del sistema. Usa &laquo;Imprimir / Guardar PDF&raquo; en la barra superior del reporte para exportarlo a PDF.
      </Typography>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Card elevation={2} sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
            <CardContent sx={{ flexGrow: 1, textAlign: 'center', py: 4 }}>
              <GroupsIcon sx={{ fontSize: 56, color: 'primary.main', mb: 2 }} />
              <Typography variant="h5" sx={{ fontWeight: 600, mb: 1 }}>
                Personal Activo
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                Lista todo el personal activo del sistema.
              </Typography>
              <Typography variant="caption" color="text.secondary">
                Reporte tabular con c&oacute;digo, nombre completo, documento, email, roles, almacenes y listas asignadas.
              </Typography>
            </CardContent>
            <Divider />
            <CardActions sx={{ p: 0 }}>
              <Button
                fullWidth
                size="large"
                startIcon={<PictureAsPdfIcon />}
                onClick={handleGenerarActivo}
                sx={{
                  bgcolor: '#f97316',
                  color: 'white',
                  py: 1.5,
                  fontWeight: 600,
                  borderRadius: 0,
                  '&:hover': { bgcolor: '#ea580c' },
                }}
              >
                Generar Reporte
              </Button>
            </CardActions>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, md: 6 }}>
          <Card elevation={2} sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
            <CardContent sx={{ flexGrow: 1, textAlign: 'center', py: 4 }}>
              <PersonIcon sx={{ fontSize: 56, color: 'primary.main', mb: 2 }} />
              <Typography variant="h5" sx={{ fontWeight: 600, mb: 1 }}>
                Ficha Personal
              </Typography>
              <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                Ficha con los datos del personal seleccionado.
              </Typography>
              <Autocomplete
                size="small"
                options={options}
                getOptionLabel={(opt) => `${opt.codigo} — ${opt.nombre_completo}`}
                isOptionEqualToValue={(opt, val) => opt.id === val.id}
                value={selected}
                onChange={(_, val) => setSelected(val)}
                loading={isLoading}
                renderInput={(params) => (
                  <TextField
                    {...params}
                    label="Seleccionar personal"
                    placeholder="Buscar por código o nombre..."
                    sx={{ mt: 1 }}
                  />
                )}
                sx={{ textAlign: 'left' }}
              />
            </CardContent>
            <Divider />
            <CardActions sx={{ p: 0 }}>
              <Button
                fullWidth
                size="large"
                startIcon={<PictureAsPdfIcon />}
                onClick={handleGenerarFicha}
                disabled={!selected}
                sx={{
                  bgcolor: '#f97316',
                  color: 'white',
                  py: 1.5,
                  fontWeight: 600,
                  borderRadius: 0,
                  '&:hover': { bgcolor: '#ea580c' },
                  '&.Mui-disabled': { bgcolor: '#fed7aa', color: 'white' },
                }}
              >
                Generar Reporte
              </Button>
            </CardActions>
          </Card>
        </Grid>
      </Grid>

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
