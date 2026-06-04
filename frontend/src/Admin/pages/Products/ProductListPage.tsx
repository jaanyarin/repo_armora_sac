import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Box, Typography, Button, TextField, IconButton, Chip } from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import type { GridColDef, GridRenderCellParams } from '@mui/x-data-grid';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { productsApi } from '../../../shared/api/endpoints';
import type { Product } from '../../../shared/types';

export default function ProductListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [paginationModel, setPaginationModel] = useState({ page: 0, pageSize: 15 });

  const { data, isLoading } = useQuery({
    queryKey: ['products', search, paginationModel],
    queryFn: () => productsApi.list({ search, per_page: paginationModel.pageSize, page: paginationModel.page + 1 }).then(r => r.data),
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => productsApi.delete(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['products'] }),
  });

  const handleDelete = useCallback((id: number) => {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
      deleteMutation.mutate(id);
    }
  }, [deleteMutation]);

  const columns: GridColDef[] = [
    { field: 'codigo', headerName: 'Código', width: 110 },
    { field: 'nombre', headerName: 'Nombre', flex: 1, minWidth: 200 },
    { field: 'codigo_sunat', headerName: 'Cód. SUNAT', width: 110 },
    {
      field: 'unidad_medida', headerName: 'U.M.', width: 80,
      renderCell: (params: GridRenderCellParams<Product>) => params.row.unidad_medida?.simbolo || '-',
    },
    {
      field: 'precio_venta', headerName: 'Precio S/', width: 110,
      renderCell: (params: GridRenderCellParams<Product>) => `S/ ${Number(params.value).toFixed(2)}`,
    },
    {
      field: 'stock_actual', headerName: 'Stock', width: 90,
    },
    {
      field: 'activo', headerName: 'Estado', width: 100,
      renderCell: (params: GridRenderCellParams<Product>) => (
        <Chip label={params.value ? 'Activo' : 'Inactivo'} color={params.value ? 'success' : 'default'} size="small" />
      ),
    },
    {
      field: 'acciones', headerName: '', width: 80, sortable: false,
      renderCell: (params: GridRenderCellParams<Product>) => (
        <Box>
          <IconButton size="small" onClick={() => navigate(`/admin/productos/${params.row.id}/editar`)}>
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
        <Typography variant="h5">Productos</Typography>
        <Button variant="contained" startIcon={<AddIcon />} onClick={() => navigate('/admin/productos/nuevo')}>
          Nuevo Producto
        </Button>
      </Box>
      <Box sx={{ mb: 2 }}>
        <TextField size="small" placeholder="Buscar por nombre, código..." value={search}
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
