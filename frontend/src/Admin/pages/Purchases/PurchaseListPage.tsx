import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box, Typography, Button, TextField, IconButton, Chip, MenuItem,
  Dialog, DialogTitle, DialogContent, DialogActions, Alert, Tooltip,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import type { GridColDef, GridRenderCellParams } from '@mui/x-data-grid';
import AddIcon from '@mui/icons-material/Add';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { purchasesApi } from '../../../shared/api/endpoints';
import type { Compra } from '../../../shared/types';
import { useAuthStore } from '../../../shared/hooks/useAuth';

const ESTADO_COLORS: Record<string, 'default' | 'warning' | 'info' | 'success' | 'error'> = {
  borrador: 'default',
  confirmada: 'info',
  pagada: 'success',
  parcial: 'warning',
  anulada: 'error',
};

const ESTADO_LABELS: Record<string, string> = {
  borrador: 'Borrador',
  confirmada: 'Confirmada',
  pagada: 'Pagada',
  parcial: 'Pago parcial',
  anulada: 'Anulada',
};

export default function PurchaseListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const userPermissions = useAuthStore(s => s.user?.permissions ?? []);
  const canConfirmar = userPermissions.includes('confirmar-compras');
  const canAnular = userPermissions.includes('anular-compras');
  const [search, setSearch] = useState('');
  const [estadoFilter, setEstadoFilter] = useState<string>('');
  const [paginationModel, setPaginationModel] = useState({ page: 0, pageSize: 15 });
  const [confirmarTarget, setConfirmarTarget] = useState<Compra | null>(null);
  const [anularTarget, setAnularTarget] = useState<Compra | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  const { data, isLoading } = useQuery({
    queryKey: ['compras', search, estadoFilter, paginationModel],
    queryFn: () => purchasesApi.list({
      search,
      estado: estadoFilter || '',
      per_page: paginationModel.pageSize,
      page: paginationModel.page + 1,
    }).then(r => r.data),
  });

  const confirmarMutation = useMutation({
    mutationFn: (id: string) => purchasesApi.confirmar(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['compras'] });
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['stock'] });
      setConfirmarTarget(null);
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      setErrorMsg(err.response?.data?.message || 'Error al confirmar la compra.');
    },
  });

  const anularMutation = useMutation({
    mutationFn: (id: string) => purchasesApi.anular(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['compras'] });
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['stock'] });
      setAnularTarget(null);
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      setErrorMsg(err.response?.data?.message || 'Error al anular la compra.');
    },
  });

  const handleConfirmar = useCallback((compra: Compra) => {
    setErrorMsg(null);
    setConfirmarTarget(compra);
  }, []);

  const handleAnular = useCallback((compra: Compra) => {
    setErrorMsg(null);
    setAnularTarget(compra);
  }, []);

  const columns: GridColDef[] = [
    { field: 'codigo', headerName: 'Código', width: 130 },
    {
      field: 'proveedor', headerName: 'Proveedor', flex: 1, minWidth: 200,
      renderCell: (params: GridRenderCellParams<Compra>) => params.row.proveedor?.nombre_completo || '-',
    },
    { field: 'fecha_emision', headerName: 'F. Emisión', width: 110 },
    {
      field: 'total', headerName: 'Total', width: 120, align: 'right', headerAlign: 'right',
      renderCell: (params: GridRenderCellParams<Compra>) => `S/ ${Number(params.value).toFixed(2)}`,
    },
    {
      field: 'estado', headerName: 'Estado', width: 120,
      renderCell: (params: GridRenderCellParams<Compra>) => (
        <Chip
          label={ESTADO_LABELS[params.value as string] || params.value}
          color={ESTADO_COLORS[params.value as string] || 'default'}
          size="small"
        />
      ),
    },
    {
      field: 'items_count', headerName: 'Items', width: 70, align: 'center', headerAlign: 'center',
      valueGetter: (_: unknown, row: Compra) => row.items?.length ?? 0,
    },
    {
      field: 'origen', headerName: 'Origen', width: 90,
      renderCell: (params: GridRenderCellParams<Compra>) => (
        <Chip label={params.value === 'portal' ? 'Portal' : 'Admin'} size="small" variant="outlined" />
      ),
    },
    {
      field: 'acciones', headerName: 'Acciones', width: 120, sortable: false, filterable: false,
      renderCell: (params: GridRenderCellParams<Compra>) => (
        <Box>
          {canConfirmar && params.row.estado === 'borrador' && (
            <Tooltip title="Confirmar">
              <IconButton size="small" color="primary" onClick={() => handleConfirmar(params.row)}>
                <CheckCircleIcon fontSize="small" />
              </IconButton>
            </Tooltip>
          )}
          {canAnular && (params.row.estado === 'confirmada' || params.row.estado === 'pagada' || params.row.estado === 'parcial') && (
            <Tooltip title="Anular">
              <IconButton size="small" color="error" onClick={() => handleAnular(params.row)}>
                <CancelIcon fontSize="small" />
              </IconButton>
            </Tooltip>
          )}
        </Box>
      ),
    },
  ];

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
        <Typography variant="h5">Compras</Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => navigate('/admin/compras/nueva')}>
          Nueva Compra
        </Button>
      </Box>

      {errorMsg && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={() => setErrorMsg(null)}>
          {errorMsg}
        </Alert>
      )}

      <Box sx={{ display: 'flex', gap: 2, mb: 2 }}>
        <TextField
          size="small"
          placeholder="Buscar por código o proveedor..."
          value={search}
          onChange={(e) => { setSearch(e.target.value); setPaginationModel(p => ({ ...p, page: 0 })); }}
          sx={{ flex: 1, maxWidth: 450 }}
        />
        <TextField
          select
          size="small"
          label="Estado"
          value={estadoFilter}
          onChange={(e) => { setEstadoFilter(e.target.value); setPaginationModel(p => ({ ...p, page: 0 })); }}
          sx={{ minWidth: 180 }}
        >
          <MenuItem value="">Todos</MenuItem>
          <MenuItem value="borrador">Borrador</MenuItem>
          <MenuItem value="confirmada">Confirmada</MenuItem>
          <MenuItem value="pagada">Pagada</MenuItem>
          <MenuItem value="parcial">Pago parcial</MenuItem>
          <MenuItem value="anulada">Anulada</MenuItem>
        </TextField>
      </Box>

      <DataGrid
        rows={data?.data || []}
        columns={columns}
        loading={isLoading}
        rowCount={data?.total || 0}
        paginationMode="server"
        paginationModel={paginationModel}
        onPaginationModelChange={setPaginationModel}
        pageSizeOptions={[15, 25, 50]}
        disableRowSelectionOnClick
        autoHeight
        getRowId={(row) => row.id}
        sx={{ bgcolor: 'background.paper' }}
      />

      <Dialog open={!!confirmarTarget} onClose={() => setConfirmarTarget(null)}>
        <DialogTitle>Confirmar compra</DialogTitle>
        <DialogContent>
          ¿Confirmar la compra <strong>{confirmarTarget?.codigo}</strong>? Esta acción aumentará el stock en el almacén destino.
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setConfirmarTarget(null)}>Cancelar</Button>
          <Button
            variant="contained"
            onClick={() => confirmarTarget && confirmarMutation.mutate(confirmarTarget.id)}
            disabled={confirmarMutation.isPending}
          >
            Confirmar
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={!!anularTarget} onClose={() => setAnularTarget(null)}>
        <DialogTitle>Anular compra</DialogTitle>
        <DialogContent>
          ¿Anular la compra <strong>{anularTarget?.codigo}</strong>? El stock no se revertirá automáticamente.
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setAnularTarget(null)}>Cancelar</Button>
          <Button
            variant="contained"
            color="error"
            onClick={() => anularTarget && anularMutation.mutate(anularTarget.id)}
            disabled={anularMutation.isPending}
          >
            Anular
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
