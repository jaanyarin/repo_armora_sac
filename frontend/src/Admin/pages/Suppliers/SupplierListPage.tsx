import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box, Typography, Button, TextField, IconButton, Chip, MenuItem,
  Dialog, DialogTitle, DialogContent, DialogActions, Alert, Tooltip,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import type { GridColDef, GridRenderCellParams } from '@mui/x-data-grid';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { proveedoresApi } from '../../../shared/api/endpoints';
import type { Proveedor } from '../../../shared/types';
import { useAuthStore } from '../../../shared/hooks/useAuth';

export default function SupplierListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const userPermissions = useAuthStore(s => s.user?.permissions ?? []);
  const canDelete = userPermissions.includes('eliminar-proveedores');
  const [search, setSearch] = useState('');
  const [activoFilter, setActivoFilter] = useState<string>('');
  const [paginationModel, setPaginationModel] = useState({ page: 0, pageSize: 15 });
  const [deleteTarget, setDeleteTarget] = useState<Proveedor | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  const { data, isLoading } = useQuery({
    queryKey: ['proveedores', search, activoFilter, paginationModel],
    queryFn: () => proveedoresApi.list({
      search,
      activo: activoFilter || '',
      per_page: paginationModel.pageSize,
      page: paginationModel.page + 1,
    }).then(r => r.data),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => proveedoresApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['proveedores'] });
      setDeleteTarget(null);
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      setErrorMsg(err.response?.data?.message || 'Error al eliminar el proveedor.');
    },
  });

  const handleDelete = useCallback((proveedor: Proveedor) => {
    setErrorMsg(null);
    setDeleteTarget(proveedor);
  }, []);

  const columns: GridColDef[] = [
    { field: 'codigo', headerName: 'Código', width: 120 },
    { field: 'numero_documento', headerName: 'RUC/DNI', width: 130 },
    { field: 'nombre_completo', headerName: 'Razón Social', flex: 1, minWidth: 200 },
    { field: 'telefono', headerName: 'Teléfono', width: 130 },
    { field: 'email', headerName: 'Email', width: 200 },
    {
      field: 'activo', headerName: 'Activo', width: 90, align: 'center', headerAlign: 'center',
      renderCell: (params: GridRenderCellParams<Proveedor>) => (
        <Chip
          label={params.value ? 'Sí' : 'No'}
          color={params.value ? 'success' : 'default'}
          size="small"
        />
      ),
    },
    {
      field: 'acciones', headerName: 'Acciones', width: 100, sortable: false, filterable: false,
      renderCell: (params: GridRenderCellParams<Proveedor>) => (
        <Box>
          <Tooltip title="Editar">
            <IconButton size="small" color="primary" onClick={() => navigate(`/admin/proveedores/${params.row.id}/editar`)}>
              <EditIcon fontSize="small" />
            </IconButton>
          </Tooltip>
          {canDelete && (
            <Tooltip title="Eliminar">
              <IconButton size="small" color="error" onClick={() => handleDelete(params.row)}>
                <DeleteIcon fontSize="small" />
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
        <Typography variant="h5">Proveedores</Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => navigate('/admin/proveedores/nuevo')}>
          Nuevo Proveedor
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
          placeholder="Buscar por código, RUC/DNI o razón social..."
          value={search}
          onChange={(e) => { setSearch(e.target.value); setPaginationModel(p => ({ ...p, page: 0 })); }}
          sx={{ flex: 1, maxWidth: 450 }}
        />
        <TextField
          select
          size="small"
          label="Activo"
          value={activoFilter}
          onChange={(e) => { setActivoFilter(e.target.value); setPaginationModel(p => ({ ...p, page: 0 })); }}
          sx={{ minWidth: 140 }}
        >
          <MenuItem value="">Todos</MenuItem>
          <MenuItem value="1">Activos</MenuItem>
          <MenuItem value="0">Inactivos</MenuItem>
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

      <Dialog open={!!deleteTarget} onClose={() => setDeleteTarget(null)}>
        <DialogTitle>Eliminar proveedor</DialogTitle>
        <DialogContent>
          ¿Eliminar al proveedor <strong>{deleteTarget?.nombre_completo}</strong>? Esta acción es reversible (soft delete).
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setDeleteTarget(null)}>Cancelar</Button>
          <Button
            variant="contained"
            color="error"
            onClick={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
            disabled={deleteMutation.isPending}
          >
            Eliminar
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
