import { useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box, Typography, Paper, Grid, TextField, Button, IconButton, Autocomplete,
  Divider, Alert, CircularProgress, Table, TableHead, TableRow, TableCell, TableBody, MenuItem,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import DeleteIcon from '@mui/icons-material/Delete';
import SaveIcon from '@mui/icons-material/Save';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { customersApi, productsApi, salesApi, catalogApi } from '../../../shared/api/endpoints';
import type { Customer, Product, SaleItemPayload, UnidadMedida } from '../../../shared/types';
import { calcularTotalesIgv } from '../../../shared/utils/igvCalculator';

interface ItemRow {
  key: string;
  producto: Product | null;
  unidad_medida_id: number | null;
  cantidad: number;
  precio_unitario: number;
}

export default function SaleFormPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [cliente, setCliente] = useState<Customer | null>(null);
  const [fechaEmision, setFechaEmision] = useState(new Date().toISOString().slice(0, 10));
  const [observaciones, setObservaciones] = useState('');
  const [items, setItems] = useState<ItemRow[]>([
    { key: crypto.randomUUID(), producto: null, unidad_medida_id: null, cantidad: 1, precio_unitario: 0 },
  ]);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [clienteSearch, setClienteSearch] = useState('');

  const { data: customersData, isLoading: loadingClientes } = useQuery({
    queryKey: ['customers-autocomplete', clienteSearch],
    queryFn: () => customersApi.list({ search: clienteSearch, per_page: 20, activo: true }).then(r => r.data?.data || []),
    enabled: clienteSearch.length >= 2,
  });

  const { data: productsData, isLoading: loadingProductos } = useQuery({
    queryKey: ['products-autocomplete'],
    queryFn: () => productsApi.list({ per_page: 100, activo: true }).then(r => r.data?.data || []),
  });

  const { data: unidadesData } = useQuery({
    queryKey: ['unidades-medida'],
    queryFn: () => catalogApi.unidadesMedida().then(r => r.data?.data || []),
  });
  const unidades: UnidadMedida[] = unidadesData || [];

  const createMutation = useMutation({
    mutationFn: (payload: Record<string, unknown>) => salesApi.create(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sales'] });
      queryClient.invalidateQueries({ queryKey: ['products'] });
      navigate('/admin/ventas');
    },
    onError: (err: { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }) => {
      const msg = err.response?.data?.message || 'Error al crear la venta.';
      const fieldErrors = err.response?.data?.errors;
      if (fieldErrors) {
        const firstError = Object.values(fieldErrors)[0]?.[0];
        setSubmitError(`${msg} ${firstError || ''}`.trim());
      } else {
        setSubmitError(msg);
      }
    },
  });

  const updateItem = <K extends keyof ItemRow>(key: string, field: K, value: ItemRow[K]) => {
    setItems(prev => prev.map(i => i.key === key ? { ...i, [field]: value } : i));
  };

  const addItem = () => {
    setItems(prev => [...prev, { key: crypto.randomUUID(), producto: null, unidad_medida_id: null, cantidad: 1, precio_unitario: 0 }]);
  };

  const removeItem = (key: string) => {
    setItems(prev => prev.length > 1 ? prev.filter(i => i.key !== key) : prev);
  };

  const totales = useMemo(() => {
    return calcularTotalesIgv(
      items.map(i => ({ cantidad: i.cantidad, precio_unitario: i.precio_unitario })),
    );
  }, [items]);

  const handleSubmit = (estado: 'borrador' | 'confirmada') => {
    setSubmitError(null);
    if (!cliente) {
      setSubmitError('Selecciona un cliente.');
      return;
    }
    const validItems: SaleItemPayload[] = items
      .filter(i => i.producto && i.unidad_medida_id && i.cantidad > 0 && i.precio_unitario >= 0)
      .map(i => ({
        producto_id: i.producto!.id,
        unidad_medida_id: i.unidad_medida_id!,
        cantidad: i.cantidad,
        precio_unitario: i.precio_unitario,
      }));

    if (validItems.length === 0) {
      setSubmitError('Agrega al menos un ítem válido.');
      return;
    }

    createMutation.mutate({
      cliente_id: cliente.id,
      fecha_emision: fechaEmision,
      estado,
      observaciones: observaciones || null,
      origen: 'admin',
      items: validItems,
    });
  };

  const customersList: Customer[] = customersData || [];
  const productsList: Product[] = productsData || [];

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
        <Typography variant="h5">Nueva Venta</Typography>
        <Button onClick={() => navigate('/admin/ventas')}>Cancelar</Button>
      </Box>

      {submitError && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={() => setSubmitError(null)}>
          {submitError}
        </Alert>
      )}

      <Paper sx={{ p: 3, mb: 2 }}>
        <Typography variant="subtitle1" fontWeight={600} gutterBottom>Datos generales</Typography>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 6 }}>
            <Autocomplete
              options={customersList}
              getOptionLabel={(c) => `${c.numero_documento} - ${c.nombre_completo}`}
              isOptionEqualToValue={(o, v) => o.id === v.id}
              value={cliente}
              onChange={(_, v) => setCliente(v)}
              onInputChange={(_, v) => setClienteSearch(v)}
              loading={loadingClientes}
              renderInput={(params) => (
                <TextField {...params} label="Cliente" required size="small" />
              )}
            />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField
              fullWidth size="small" type="date" label="Fecha emisión" required
              value={fechaEmision}
              onChange={(e) => setFechaEmision(e.target.value)}
              slotProps={{ inputLabel: { shrink: true } }}
            />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField
              fullWidth size="small" label="Origen" value="Admin" disabled
            />
          </Grid>
          <Grid size={{ xs: 12 }}>
            <TextField
              fullWidth size="small" label="Observaciones" multiline rows={2}
              value={observaciones}
              onChange={(e) => setObservaciones(e.target.value)}
            />
          </Grid>
        </Grid>
      </Paper>

      <Paper sx={{ p: 3, mb: 2 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
          <Typography variant="subtitle1" fontWeight={600}>Ítems</Typography>
          <Button startIcon={<AddIcon />} onClick={addItem} size="small">Agregar ítem</Button>
        </Box>

        {loadingProductos ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', py: 4 }}><CircularProgress /></Box>
        ) : (
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell sx={{ width: '40%' }}>Producto</TableCell>
                <TableCell>Unidad</TableCell>
                <TableCell align="right">Cantidad</TableCell>
                <TableCell align="right">Precio Unit.</TableCell>
                <TableCell align="right">Subtotal</TableCell>
                <TableCell></TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {items.map((item) => {
                const lineTotal = item.cantidad * item.precio_unitario;
                return (
                  <TableRow key={item.key}>
                    <TableCell>
                      <Autocomplete
                        size="small"
                        options={productsList}
                        getOptionLabel={(p) => `${p.codigo} - ${p.nombre}`}
                        isOptionEqualToValue={(o, v) => o.id === v.id}
                        value={item.producto}
                        onChange={(_, v) => {
                          updateItem(item.key, 'producto', v);
                          if (v?.unidad_medida_id) {
                            updateItem(item.key, 'unidad_medida_id', v.unidad_medida_id);
                          }
                          if (v) {
                            updateItem(item.key, 'precio_unitario', v.precio_venta);
                          }
                        }}
                        renderInput={(params) => <TextField {...params} placeholder="Buscar producto..." />}
                      />
                    </TableCell>
                    <TableCell>
                      <TextField
                        select size="small" sx={{ minWidth: 100 }}
                        value={item.unidad_medida_id || ''}
                        onChange={(e) => updateItem(item.key, 'unidad_medida_id', Number(e.target.value))}
                      >
                        {unidades.map((u) => (
                          <MenuItem key={u.id} value={u.id}>{u.simbolo}</MenuItem>
                        ))}
                      </TextField>
                    </TableCell>
                    <TableCell align="right">
                      <TextField
                        type="number" size="small" sx={{ width: 90 }}
                        inputProps={{ min: 0, step: 0.01 }}
                        value={item.cantidad}
                        onChange={(e) => updateItem(item.key, 'cantidad', Number(e.target.value))}
                      />
                    </TableCell>
                    <TableCell align="right">
                      <TextField
                        type="number" size="small" sx={{ width: 110 }}
                        inputProps={{ min: 0, step: 0.01 }}
                        value={item.precio_unitario}
                        onChange={(e) => updateItem(item.key, 'precio_unitario', Number(e.target.value))}
                      />
                    </TableCell>
                    <TableCell align="right">
                      S/ {lineTotal.toFixed(2)}
                    </TableCell>
                    <TableCell>
                      <IconButton size="small" color="error" onClick={() => removeItem(item.key)}>
                        <DeleteIcon fontSize="small" />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        )}
      </Paper>

      <Paper sx={{ p: 3, mb: 2 }}>
        <Grid container justifyContent="flex-end">
          <Grid size={{ xs: 12, md: 4 }}>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', py: 0.5 }}>
              <Typography variant="body2" color="text.secondary">Subtotal (gravada):</Typography>
              <Typography variant="body2">S/ {totales.subtotal.toFixed(2)}</Typography>
            </Box>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', py: 0.5 }}>
              <Typography variant="body2" color="text.secondary">IGV (18%):</Typography>
              <Typography variant="body2">S/ {totales.igv.toFixed(2)}</Typography>
            </Box>
            <Divider sx={{ my: 1 }} />
            <Box sx={{ display: 'flex', justifyContent: 'space-between', py: 0.5 }}>
              <Typography variant="h6">Total:</Typography>
              <Typography variant="h6" color="primary">S/ {totales.total.toFixed(2)}</Typography>
            </Box>
          </Grid>
        </Grid>
      </Paper>

      <Box sx={{ display: 'flex', gap: 2, justifyContent: 'flex-end' }}>
        <Button
          variant="outlined" startIcon={<SaveIcon />}
          onClick={() => handleSubmit('borrador')}
          disabled={createMutation.isPending}
        >
          Guardar borrador
        </Button>
        <Button
          variant="contained" startIcon={<SaveIcon />}
          onClick={() => handleSubmit('confirmada')}
          disabled={createMutation.isPending}
        >
          Confirmar y registrar
        </Button>
      </Box>
    </Box>
  );
}
