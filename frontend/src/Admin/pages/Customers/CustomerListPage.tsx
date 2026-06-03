import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Box, Typography, Button, TextField, IconButton, Chip } from '@mui/material';
import { DataGrid, GridColDef, GridRenderCellParams } from '@mui/x-data-grid';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { customersApi } from '../../../shared/api/endpoints';
import type { Customer } from '../../../shared/types';

export default function CustomerListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [paginationModel, setPaginationModel] = useState({ page: 0, pageSize: 15 });

  const { data, isLoading } = useQuery({
    queryKey: ['customers', search, paginationModel],
    queryFn: () => customersApi.list({ search, per_page: paginationModel.pageSize, page: paginationModel.page + 1 }).then(r => r.data),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => customersApi.delete(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['customers'] }),
  });

  const handleDelete = useCallback((id: number) => {
    if (confirm('¿Estás seguro de eliminar este cliente?')) {
      deleteMutation.mutate(id);
    }
  }, [deleteMutation]);

  const columns: GridColDef[] = [
    { field: 'codigo', headerName: 'Código', width: 110 },
    { field: 'numero_documento', headerName: 'Doc.', width: 120 },
    { field: 'nombre_completo', headerName: 'Nombre / Razón Social', flex: 1, minWidth: 200 },
    { field: 'email', headerName: 'Email', width: 200 },
    { field: 'telefono', headerName: 'Teléfono', width: 130 },
    {
      field: 'tipo_cliente', headerName: 'Tipo', width: 120,
      renderCell: (params: GridRenderCellParams<Customer>) => params.row.tipo_cliente?.nombre || '-',
    },
    {
      field: 'activo', headerName: 'Estado', width: 100,
      renderCell: (params: GridRenderCellParams<Customer>) => (
        <Chip label={params.value ? 'Activo' : 'Inactivo'} color={params.value ? 'success' : 'default'} size="small" />
      ),
    },
    {
      field: 'acciones', headerName: '', width: 80, sortable: false,
      renderCell: (params: GridRenderCellParams<Customer>) => (
        <Box>
          <IconButton size="small" onClick={() => navigate(`/admin/clientes/${params.row.id}/editar`)}>
            <EditIcon fontSize="small" />
          </IconButton>
          <IconButton size="small" color="error" onClick={() => handleDelete(params.row.id)}>
            <DeleteIcon fontSize="small" />
          </IconButton>
        </Box>
      ),
    },
  ];

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
        <Typography variant="h5">Clientes</Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => navigate('/admin/clientes/nuevo')}>
          Nuevo Cliente
        </Button>
      </Box>
      <Box sx={{ mb: 2 }}>
        <TextField size="small" placeholder="Buscar por nombre, documento o código..." value={search}
          onChange={(e) => { setSearch(e.target.value); setPaginationModel(p => ({ ...p, page: 0 })); }} sx={{ width: 350 }} />
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
      />
    </Box>
  );
}
