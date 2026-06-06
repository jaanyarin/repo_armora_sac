import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Typography,
  Button,
  TextField,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TablePagination,
  Paper,
  IconButton,
  Tooltip,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Checkbox,
  FormControl,
  MenuItem,
  Select,
  CircularProgress,
  Snackbar,
  Alert,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import LockIcon from '@mui/icons-material/Lock';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import DeleteIcon from '@mui/icons-material/Delete';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { personalApi } from '../../../shared/api/endpoints';
import type { AxiosResponse } from 'axios';

interface PersonalRow {
  id: number;
  codigo: string;
  username: string;
  nombre_completo: string;
  numero_documento: string | null;
  documento_identidad: { id: number; codigo: string; nombre: string } | null;
  activo: boolean;
  roles: string[];
}

export default function PersonalListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [page, setPage] = useState(0);
  const [perPage, setPerPage] = useState(15);
  const [search, setSearch] = useState('');
  const [selected, setSelected] = useState<number[]>([]);
  const [deleteDialog, setDeleteDialog] = useState<{ open: boolean; personal: PersonalRow | null }>({ open: false, personal: null });
  const [passwordDialog, setPasswordDialog] = useState<{ open: boolean; personal: PersonalRow | null; password: string; confirm: string }>({ open: false, personal: null, password: '', confirm: '' });
  const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({ open: false, message: '', severity: 'success' });

  const { data, isLoading } = useQuery({
    queryKey: ['personal', page, perPage, search],
    queryFn: async () => {
      const params: Record<string, string | number | boolean> = {
        per_page: perPage,
        page: page + 1,
      };
      if (search) params.search = search;
      const res = await personalApi.list(params);
      return res.data;
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => personalApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['personal'] });
      setSnackbar({ open: true, message: 'Personal eliminado correctamente.', severity: 'success' });
      setDeleteDialog({ open: false, personal: null });
    },
    onError: () => {
      setSnackbar({ open: true, message: 'Error al eliminar personal.', severity: 'error' });
    },
  });

  const toggleActivoMutation = useMutation({
    mutationFn: (id: number) => personalApi.toggleActivo(id),
    onSuccess: (res: AxiosResponse) => {
      queryClient.invalidateQueries({ queryKey: ['personal'] });
      setSnackbar({ open: true, message: res.data?.message || 'Estado actualizado.', severity: 'success' });
    },
    onError: () => {
      setSnackbar({ open: true, message: 'Error al cambiar estado.', severity: 'error' });
    },
  });

  const resetPasswordMutation = useMutation({
    mutationFn: ({ id, password }: { id: number; password: string }) =>
      personalApi.resetPassword(id, password, password),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['personal'] });
      setSnackbar({ open: true, message: 'Contraseña actualizada correctamente.', severity: 'success' });
      setPasswordDialog({ open: false, personal: null, password: '', confirm: '' });
    },
    onError: () => {
      setSnackbar({ open: true, message: 'Error al actualizar contraseña.', severity: 'error' });
    },
  });

  const rows: PersonalRow[] = data?.data ?? [];
  const total = data?.total ?? 0;

  const handleSelectAll = useCallback((checked: boolean) => {
    const currentRows: PersonalRow[] = data?.data ?? [];
    if (checked) {
      setSelected(currentRows.map(r => r.id));
    } else {
      setSelected([]);
    }
  }, [data]);

  const handleSelectOne = useCallback((id: number) => {
    setSelected(prev =>
      prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]
    );
  }, []);

  const getDocumentoLabel = (row: PersonalRow): string => {
    if (!row.numero_documento || !row.documento_identidad) return '-';
    return `${row.documento_identidad.codigo}: ${row.numero_documento}`;
  };

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h5" fontWeight={700}>
          Gestión del Personal
        </Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => navigate('/admin/personal/nuevo')}>
          Crear Personal
        </Button>
      </Box>

      <Paper sx={{ mb: 2, p: 2 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 2 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <Typography variant="body2" color="text.secondary">Mostrando</Typography>
            <FormControl size="small" sx={{ minWidth: 80 }}>
              <Select
                value={perPage}
                onChange={(e) => { setPerPage(Number(e.target.value)); setPage(0); }}
              >
                {[10, 25, 50, 100, 250, 500].map(n => (
                  <MenuItem key={n} value={n}>{n}</MenuItem>
                ))}
              </Select>
            </FormControl>
            <Typography variant="body2" color="text.secondary">registros</Typography>
          </Box>
          <TextField
            size="small"
            placeholder="Buscar"
            variant="outlined"
            value={search}
            onChange={(e) => { setSearch(e.target.value); setPage(0); }}
            sx={{ minWidth: 250 }}
          />
        </Box>
      </Paper>

      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell padding="checkbox">
                <Checkbox
                  indeterminate={selected.length > 0 && selected.length < rows.length}
                  checked={rows.length > 0 && selected.length === rows.length}
                  onChange={(e) => handleSelectAll(e.target.checked)}
                />
              </TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Código</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Login</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Nombre Completo</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Documento</TableCell>
              <TableCell sx={{ fontWeight: 600 }}>Estado</TableCell>
              <TableCell sx={{ fontWeight: 600 }} align="center">Acciones</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {isLoading ? (
              <TableRow>
                <TableCell colSpan={7} align="center" sx={{ py: 8 }}>
                  <CircularProgress />
                </TableCell>
              </TableRow>
            ) : rows.length === 0 ? (
              <TableRow>
                <TableCell colSpan={7} align="center" sx={{ py: 8, color: 'text.secondary' }}>
                  {search ? 'No se encontraron resultados.' : 'No hay personal registrado.'}
                </TableCell>
              </TableRow>
            ) : (
              rows.map((row) => (
                <TableRow
                  key={row.id}
                  hover
                  selected={selected.includes(row.id)}
                  sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                >
                  <TableCell padding="checkbox">
                    <Checkbox
                      checked={selected.includes(row.id)}
                      onChange={() => handleSelectOne(row.id)}
                    />
                  </TableCell>
                  <TableCell>
                    <Typography variant="body2" fontFamily="monospace">{row.codigo}</Typography>
                  </TableCell>
                  <TableCell>{row.username}</TableCell>
                  <TableCell sx={{ fontWeight: 500 }}>{row.nombre_completo}</TableCell>
                  <TableCell>
                    <Typography variant="body2" color="text.secondary">
                      {getDocumentoLabel(row)}
                    </Typography>
                  </TableCell>
                  <TableCell>
                    <Chip
                      label={row.activo ? 'HABI' : 'INHA'}
                      size="small"
                      sx={{
                        fontWeight: 600,
                        bgcolor: row.activo ? '#d1fae5' : '#fee2e2',
                        color: row.activo ? '#065f46' : '#991b1b',
                      }}
                    />
                  </TableCell>
                  <TableCell align="center">
                    <Box sx={{ display: 'flex', justifyContent: 'center', gap: 0.5 }}>
                      <Tooltip title="Editar">
                        <IconButton size="small" onClick={() => navigate(`/admin/personal/${row.id}/editar`)}>
                          <EditIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title="Cambiar Contraseña">
                        <IconButton size="small" onClick={() => setPasswordDialog({ open: true, personal: row, password: '', confirm: '' })}>
                          <LockIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title={row.activo ? 'Inhabilitar Personal' : 'Habilitar Personal'}>
                        <IconButton size="small" onClick={() => toggleActivoMutation.mutate(row.id)}>
                          {row.activo ? <CancelIcon fontSize="small" color="error" /> : <CheckCircleIcon fontSize="small" color="success" />}
                        </IconButton>
                      </Tooltip>
                      <Tooltip title="Eliminar">
                        <IconButton size="small" onClick={() => setDeleteDialog({ open: true, personal: row })}>
                          <DeleteIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                    </Box>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
        <TablePagination
          component="div"
          count={total}
          page={page}
          onPageChange={(_, newPage) => setPage(newPage)}
          rowsPerPage={perPage}
          rowsPerPageOptions={[]}
          labelRowsPerPage=""
          labelDisplayedRows={({ from, to, count }) => `${from}-${to} de ${count}`}
        />
      </TableContainer>

      {/* Delete Dialog */}
      <Dialog open={deleteDialog.open} onClose={() => setDeleteDialog({ open: false, personal: null })}>
        <DialogTitle>Confirmar Eliminación</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Está seguro de eliminar a <strong>{deleteDialog.personal?.nombre_completo}</strong> ({deleteDialog.personal?.codigo})?
          </Typography>
          <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
            Esta acción realizará un borrado lógico (soft delete).
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setDeleteDialog({ open: false, personal: null })}>Cancelar</Button>
          <Button
            variant="contained"
            color="error"
            onClick={() => deleteDialog.personal && deleteMutation.mutate(deleteDialog.personal.id)}
          >
            Eliminar
          </Button>
        </DialogActions>
      </Dialog>

      {/* Reset Password Dialog */}
      <Dialog
        open={passwordDialog.open}
        onClose={() => setPasswordDialog({ open: false, personal: null, password: '', confirm: '' })}
        maxWidth="sm"
        fullWidth
      >
        <DialogTitle>Cambiar Contraseña</DialogTitle>
        <DialogContent>
          <Typography sx={{ mb: 2 }}>
            Nueva contraseña para <strong>{passwordDialog.personal?.nombre_completo}</strong>
          </Typography>
          <TextField
            fullWidth
            label="Nueva Contraseña"
            type="password"
            value={passwordDialog.password}
            onChange={(e) => setPasswordDialog(prev => ({ ...prev, password: e.target.value }))}
            sx={{ mb: 2 }}
          />
          <TextField
            fullWidth
            label="Confirmar Contraseña"
            type="password"
            value={passwordDialog.confirm}
            onChange={(e) => setPasswordDialog(prev => ({ ...prev, confirm: e.target.value }))}
            error={!!passwordDialog.password && !!passwordDialog.confirm && passwordDialog.password !== passwordDialog.confirm}
            helperText={!!passwordDialog.password && !!passwordDialog.confirm && passwordDialog.password !== passwordDialog.confirm ? 'Las contraseñas no coinciden.' : ''}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setPasswordDialog({ open: false, personal: null, password: '', confirm: '' })}>Cancelar</Button>
          <Button
            variant="contained"
            onClick={() => {
              if (passwordDialog.personal && passwordDialog.password && passwordDialog.password === passwordDialog.confirm) {
                resetPasswordMutation.mutate({ id: passwordDialog.personal.id, password: passwordDialog.password });
              }
            }}
            disabled={!passwordDialog.password || passwordDialog.password !== passwordDialog.confirm}
          >
            Actualizar
          </Button>
        </DialogActions>
      </Dialog>

      {/* Snackbar */}
      <Snackbar
        open={snackbar.open}
        autoHideDuration={4000}
        onClose={() => setSnackbar(prev => ({ ...prev, open: false }))}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert severity={snackbar.severity} variant="filled" sx={{ width: '100%' }}>
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
}
