import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box, Typography, IconButton, TextField, Chip, Tooltip,
  FormControl, InputLabel, Select, MenuItem, InputAdornment, Paper,
} from '@mui/material';
import { DataGrid } from '@mui/x-data-grid';
import type { GridColDef, GridRenderCellParams } from '@mui/x-data-grid';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import RemoveRedEyeIcon from '@mui/icons-material/RemoveRedEye';
import InventoryIcon from '@mui/icons-material/Inventory';
import SearchIcon from '@mui/icons-material/Search';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { productsApi } from '../../../shared/api/endpoints';
import type { Product } from '../../../shared/types';

const PAGE_SIZE_OPTIONS = [10, 25, 50, 100, 250, 500];

const claseValueGetter = (_value: unknown, row: Product) => row.producto_clase?.nombre || '-';
const subclaseValueGetter = (_value: unknown, row: Product) => row.producto_subclase?.nombre || '-';

export default function ProductListPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [paginationModel, setPaginationModel] = useState({ page: 0, pageSize: 10 });

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

  const handleSearchChange = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    setSearch(e.target.value);
    setPaginationModel(p => ({ ...p, page: 0 }));
  }, []);

  const handlePageSizeChange = useCallback((e: React.ChangeEvent<{ value: unknown }>) => {
    const newSize = e.target.value as number;
    setPaginationModel(p => ({ ...p, pageSize: newSize, page: 0 }));
  }, []);

  const columns: GridColDef[] = [
    { field: 'codigo', headerName: 'Código', width: 110 },
    { field: 'codigo_sunat', headerName: 'SKU', width: 110 },
    { field: 'clase', headerName: 'Clase', width: 150, valueGetter: claseValueGetter },
    { field: 'subclase', headerName: 'Subclase', width: 150, valueGetter: subclaseValueGetter },
    { field: 'nombre', headerName: 'Nombre', flex: 1, minWidth: 200 },
    {
      field: 'visible', headerName: 'Visible', width: 100,
      renderCell: (params: GridRenderCellParams<Product>) => (
        <Chip label={params.row.activo ? 'Activo' : 'Inactivo'} color={params.row.activo ? 'success' : 'default'} size="small" />
      ),
    },
    {
      field: 'acciones', headerName: 'Acciones', width: 150, sortable: false, filterable: false,
      renderCell: (params: GridRenderCellParams<Product>) => (
        <Box sx={{ display: 'flex', gap: 0.5 }}>
          <Tooltip title="Ver detalle">
            <IconButton size="small" onClick={() => navigate(`/admin/productos/${params.row.id}`)}>
              <RemoveRedEyeIcon fontSize="small" />
            </IconButton>
          </Tooltip>
          <Tooltip title="Editar producto">
            <IconButton size="small" onClick={() => navigate(`/admin/productos/${params.row.id}/editar`)}>
              <EditIcon fontSize="small" />
            </IconButton>
          </Tooltip>
          <Tooltip title="Ver inventario">
            <IconButton size="small" onClick={() => {}}>
              <InventoryIcon fontSize="small" />
            </IconButton>
          </Tooltip>
          <Tooltip title="Eliminar producto">
            <IconButton size="small" color="error" onClick={() => handleDelete(params.row.id)}>
              <DeleteIcon fontSize="small" />
            </IconButton>
          </Tooltip>
        </Box>
      ),
    },
  ];

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
        <Typography variant="h5" sx={{ fontWeight: 600 }}>
          Gestión de Productos
        </Typography>
        <Tooltip title="Crear Producto">
          <IconButton
            color="primary"
            onClick={() => navigate('/admin/productos/nuevo')}
            sx={{ bgcolor: 'primary.main', color: 'white', '&:hover': { bgcolor: 'primary.dark' } }}
          >
            <AddIcon />
          </IconButton>
        </Tooltip>
      </Box>

      <Paper sx={{ p: 1.5, mb: 2, bgcolor: 'grey.50' }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <FormControl size="small" sx={{ minWidth: 130 }}>
            <InputLabel id="rows-per-page-label">Mostrando</InputLabel>
            <Select
              labelId="rows-per-page-label"
              value={paginationModel.pageSize}
              label="Mostrando"
              onChange={(e) => handlePageSizeChange(e as React.ChangeEvent<{ value: unknown }>)}
            >
              {PAGE_SIZE_OPTIONS.map(size => (
                <MenuItem key={size} value={size}>{size}</MenuItem>
              ))}
            </Select>
          </FormControl>
          <TextField
            size="small"
            placeholder="Filtrar por nombre, código..."
            value={search}
            onChange={handleSearchChange}
            sx={{ ml: 'auto', width: 320 }}
            slotProps={{
              input: {
                startAdornment: (
                  <InputAdornment position="start">
                    <SearchIcon fontSize="small" color="action" />
                  </InputAdornment>
                ),
              },
            }}
          />
        </Box>
      </Paper>

      <DataGrid
        rows={data?.data || []}
        columns={columns}
        loading={isLoading}
        rowCount={data?.total || 0}
        paginationMode="server"
        paginationModel={paginationModel}
        onPaginationModelChange={setPaginationModel}
        pageSizeOptions={PAGE_SIZE_OPTIONS}
        disableRowSelectionOnClick
        checkboxSelection
        autoHeight
        getRowId={(row) => row.id}
        sx={{ bgcolor: 'background.paper' }}
        localeText={{
          footerTotalRows: 'Total de productos:',
        }}
      />
    </Box>
  );
}
