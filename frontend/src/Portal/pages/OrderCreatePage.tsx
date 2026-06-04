import { useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box, Typography, Paper, Grid, TextField, Button, IconButton, Divider,
  Table, TableHead, TableRow, TableCell, TableBody, Alert, CircularProgress, Chip, MenuItem,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import RemoveIcon from '@mui/icons-material/Remove';
import DeleteIcon from '@mui/icons-material/Delete';
import ShoppingCartCheckoutIcon from '@mui/icons-material/ShoppingCartCheckout';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useCartStore } from '../../shared/hooks/useCart';
import { salesApi, customersApi } from '../../shared/api/endpoints';
import type { Customer } from '../../shared/types';
import { calcularTotalesIgv } from '../../shared/utils/igvCalculator';

export default function OrderCreatePage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { items, updateCantidad, remove, clear } = useCartStore();
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [observaciones, setObservaciones] = useState('');
  const [clienteId, setClienteId] = useState<number | null>(null);
  const [clienteSearch, setClienteSearch] = useState('');

  const { data: customersData } = useQuery({
    queryKey: ['portal-customers', clienteSearch],
    queryFn: () => customersApi.list({ search: clienteSearch, per_page: 20, activo: true }).then(r => r.data?.data || []),
    enabled: clienteSearch.length >= 2,
  });

  const createOrderMutation = useMutation({
    mutationFn: (payload: Record<string, unknown>) => salesApi.create(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['portal-orders'] });
      clear();
      navigate('/portal/pedidos');
    },
    onError: (err: { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }) => {
      const msg = err.response?.data?.message || 'Error al procesar el pedido.';
      const fieldErrors = err.response?.data?.errors;
      if (fieldErrors) {
        const firstError = Object.values(fieldErrors)[0]?.[0];
        setSubmitError(`${msg} ${firstError || ''}`.trim());
      } else {
        setSubmitError(msg);
      }
    },
  });

  const totales = useMemo(() => {
    return calcularTotalesIgv(
      items.map(i => ({ cantidad: i.cantidad, precio_unitario: i.precio_unitario })),
    );
  }, [items]);

  const handleSubmit = (estado: 'borrador' | 'confirmada') => {
    setSubmitError(null);
    if (items.length === 0) {
      setSubmitError('El carrito está vacío.');
      return;
    }
    if (!clienteId) {
      setSubmitError('Selecciona un cliente para el pedido.');
      return;
    }
    const validItems = items
      .filter(i => i.producto.unidad_medida_id && i.cantidad > 0)
      .map(i => ({
        producto_id: i.producto.id,
        unidad_medida_id: i.producto.unidad_medida_id,
        cantidad: i.cantidad,
        precio_unitario: i.precio_unitario,
      }));

    if (validItems.length === 0) {
      setSubmitError('Los items del carrito no son válidos.');
      return;
    }

    createOrderMutation.mutate({
      cliente_id: clienteId,
      fecha_emision: new Date().toISOString().slice(0, 10),
      estado,
      observaciones: observaciones || null,
      origen: 'portal',
      items: validItems,
    });
  };

  if (items.length === 0) {
    return (
      <Box>
        <Typography variant="h5" fontWeight={600} gutterBottom>Mi Pedido</Typography>
        <Paper sx={{ p: 4, textAlign: 'center' }}>
          <ShoppingCartCheckoutIcon sx={{ fontSize: 64, color: 'text.disabled' }} />
          <Typography color="text.secondary" sx={{ mt: 2 }}>
            Tu carrito está vacío.
          </Typography>
          <Button variant="contained" sx={{ mt: 2 }} onClick={() => navigate('/portal/productos')}>
            Ver productos
          </Button>
        </Paper>
      </Box>
    );
  }

  const customersList: Customer[] = customersData || [];

  return (
    <Box>
      <Typography variant="h5" fontWeight={600} gutterBottom>Mi Pedido</Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Revisa los productos, completa los datos y confirma tu pedido.
      </Typography>

      {submitError && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={() => setSubmitError(null)}>
          {submitError}
        </Alert>
      )}

      <Paper sx={{ p: 2, mb: 2 }}>
        <Table size="small">
          <TableHead>
            <TableRow>
              <TableCell>Producto</TableCell>
              <TableCell align="right">Precio</TableCell>
              <TableCell align="center">Cantidad</TableCell>
              <TableCell align="right">Subtotal</TableCell>
              <TableCell></TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {items.map((item) => (
              <TableRow key={item.producto.id}>
                <TableCell>
                  <Box>
                    <Typography variant="body2" fontWeight={600}>{item.producto.nombre}</Typography>
                    <Typography variant="caption" color="text.secondary">{item.producto.codigo}</Typography>
                    {item.producto.unidad_medida && (
                      <Chip label={item.producto.unidad_medida.simbolo} size="small" sx={{ ml: 1 }} />
                    )}
                  </Box>
                </TableCell>
                <TableCell align="right">S/ {item.precio_unitario.toFixed(2)}</TableCell>
                <TableCell align="center">
                  <Box sx={{ display: 'inline-flex', alignItems: 'center', gap: 0.5 }}>
                    <IconButton
                      size="small"
                      onClick={() => updateCantidad(item.producto.id, item.cantidad - 1)}
                    >
                      <RemoveIcon fontSize="small" />
                    </IconButton>
                    <TextField
                      type="number" size="small" sx={{ width: 70 }}
                      inputProps={{ min: 1, style: { textAlign: 'center' } }}
                      value={item.cantidad}
                      onChange={(e) => updateCantidad(item.producto.id, Math.max(1, Number(e.target.value)))}
                    />
                    <IconButton
                      size="small"
                      onClick={() => updateCantidad(item.producto.id, item.cantidad + 1)}
                    >
                      <AddIcon fontSize="small" />
                    </IconButton>
                  </Box>
                </TableCell>
                <TableCell align="right">S/ {(item.cantidad * item.precio_unitario).toFixed(2)}</TableCell>
                <TableCell>
                  <IconButton size="small" color="error" onClick={() => remove(item.producto.id)}>
                    <DeleteIcon fontSize="small" />
                  </IconButton>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </Paper>

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="subtitle1" fontWeight={600} gutterBottom>Datos del cliente</Typography>
            <TextField
              fullWidth size="small" label="Buscar cliente (DNI/RUC/nombre)"
              value={clienteSearch}
              onChange={(e) => setClienteSearch(e.target.value)}
              sx={{ mb: 2 }}
            />
            <TextField
              select fullWidth size="small" label="Cliente" required
              value={clienteId || ''}
              onChange={(e) => setClienteId(Number(e.target.value))}
            >
              <MenuItem value="" disabled>Selecciona un cliente</MenuItem>
              {customersList.map((c) => (
                <MenuItem key={c.id} value={c.id}>
                  {c.numero_documento} - {c.nombre_completo}
                </MenuItem>
              ))}
            </TextField>
            <TextField
              fullWidth size="small" label="Observaciones" multiline rows={3} sx={{ mt: 2 }}
              value={observaciones}
              onChange={(e) => setObservaciones(e.target.value)}
            />
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, md: 6 }}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="subtitle1" fontWeight={600} gutterBottom>Resumen</Typography>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', py: 0.5 }}>
              <Typography variant="body2" color="text.secondary">Subtotal:</Typography>
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
            <Box sx={{ display: 'flex', gap: 1, mt: 2 }}>
              <Button fullWidth variant="outlined" onClick={() => handleSubmit('borrador')}
                disabled={createOrderMutation.isPending}>
                Guardar borrador
              </Button>
              <Button
                fullWidth variant="contained" startIcon={<ShoppingCartCheckoutIcon />}
                onClick={() => handleSubmit('confirmada')}
                disabled={createOrderMutation.isPending}
              >
                {createOrderMutation.isPending ? <CircularProgress size={20} /> : 'Confirmar pedido'}
              </Button>
            </Box>
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
}
