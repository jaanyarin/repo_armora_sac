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

const claseValueGetter = (_value: unknown, row: Product) => row.producto_clase?.nombre || '-';
const subclaseValueGetter = (_value: unknown, row: Product) => row.producto_subclase?.nombre || '-';

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
  { field: 'codigo_sunat', headerName: 'SKU', width: 110 },
  { field: 'clase', headerName: 'Clase', width: 150, valueGetter: claseValueGetter },
  { field: 'subclase', headerName: 'Subclase', width: 150, valueGetter: subclaseValueGetter },
  { field: 'nombre', headerName: 'Nombre', flex: 1, minWidth: 200 },
  { field: 'visible', headerName: 'Visible', width: 100, renderCell: (params: GridRenderCellParams<Product>) => (
    <Chip label={params.row.activo ? 'Activo' : 'Inactivo'} color={params.row.activo ? 'success' : 'default'} size="small" />
  ) },
  {
    field: 'acciones', headerName: '', width: 80, sortable: false,
renderCell: (params: GridRenderCellParams<Product>) => (
        <Box>
          <IconButton size="small" onClick={() => navigate(`/admin/productos/${params.row.id}`)}>
            <i className="black eye link icon" />
          </IconButton>
          <IconButton size="small" onClick={() => navigate(`/admin/productos/${params.row.id}/editar`)}>
            <EditIcon fontSize="small" />
          </IconButton>
          <IconButton size="small" onClick={() => {/* placeholder for warehouse action */}}>
            <i className="black warehouse link icon" />
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
        checkboxSelection
        autoHeight
      />
    </Box>
  );
}
