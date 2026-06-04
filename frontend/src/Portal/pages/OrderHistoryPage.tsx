import { useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box, Typography, Paper, Chip, Divider, Button, Grid, TextField, InputAdornment,
  Table, TableHead, TableRow, TableCell, TableBody, IconButton, Dialog, DialogTitle, DialogContent, DialogActions,
  CircularProgress, Alert, Tabs, Tab,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import VisibilityIcon from '@mui/icons-material/Visibility';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import { useQuery } from '@tanstack/react-query';
import { salesApi } from '../../shared/api/endpoints';
import type { Sale } from '../../shared/types';

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

export default function OrderHistoryPage() {
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const [estadoTab, setEstadoTab] = useState<string>('todos');
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Sale | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const perPage = 10;

  const estadoFilter = estadoTab === 'todos' ? undefined : estadoTab;

  const { data, isLoading, isError } = useQuery({
    queryKey: ['portal-orders', search, estadoTab, page],
    queryFn: () => salesApi.list({
      search: search || '',
      estado: estadoFilter ?? '',
      origen: 'portal',
      per_page: perPage,
      page,
    }).then(r => r.data),
  });

  const handleVer = useCallback(async (id: string) => {
    try {
      const response = await salesApi.find(id);
      setSelected(response.data);
    } catch {
      setErrorMsg('No se pudo cargar el detalle del pedido.');
    }
  }, []);

  const orders: Sale[] = data?.data || [];
  const total = data?.total || 0;
  const lastPage = Math.max(1, Math.ceil(total / perPage));

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
        <Typography variant="h5" fontWeight={600}>Mis Pedidos</Typography>
        <Button variant="contained" startIcon={<ShoppingCartIcon />} onClick={() => navigate('/portal/pedidos/nuevo')}>
          Nuevo Pedido
        </Button>
      </Box>

      {errorMsg && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={() => setErrorMsg(null)}>
          {errorMsg}
        </Alert>
      )}

      <TextField
        fullWidth size="small" placeholder="Buscar por código..."
        value={search}
        onChange={(e) => { setSearch(e.target.value); setPage(1); }}
        slotProps={{
          input: { startAdornment: <InputAdornment position="start"><SearchIcon /></InputAdornment> },
        }}
        sx={{ mb: 2 }}
      />

      <Tabs
        value={estadoTab}
        onChange={(_, v) => { setEstadoTab(v); setPage(1); }}
        sx={{ mb: 2, borderBottom: 1, borderColor: 'divider' }}
        variant="scrollable"
        scrollButtons="auto"
      >
        <Tab label="Todos" value="todos" />
        <Tab label="Borradores" value="borrador" />
        <Tab label="Confirmados" value="confirmada" />
        <Tab label="Pagados" value="pagada" />
        <Tab label="Anulados" value="anulada" />
      </Tabs>

      {isLoading ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', py: 4 }}><CircularProgress /></Box>
      ) : isError ? (
        <Alert severity="error">Error al cargar los pedidos.</Alert>
      ) : orders.length === 0 ? (
        <Paper sx={{ p: 4, textAlign: 'center' }}>
          <Typography color="text.secondary">No se encontraron pedidos.</Typography>
        </Paper>
      ) : (
        <>
          <Paper>
            <Table size="small">
              <TableHead>
                <TableRow>
                  <TableCell>Código</TableCell>
                  <TableCell>Fecha</TableCell>
                  <TableCell align="right">Total</TableCell>
                  <TableCell>Estado</TableCell>
                  <TableCell align="center">Acciones</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {orders.map((order) => (
                  <TableRow key={order.id} hover>
                    <TableCell><strong>{order.codigo}</strong></TableCell>
                    <TableCell>{order.fecha_emision || '-'}</TableCell>
                    <TableCell align="right">S/ {Number(order.total).toFixed(2)}</TableCell>
                    <TableCell>
                      <Chip
                        label={ESTADO_LABELS[order.estado] || order.estado}
                        color={ESTADO_COLORS[order.estado] || 'default'}
                        size="small"
                      />
                    </TableCell>
                    <TableCell align="center">
                      <IconButton size="small" onClick={() => handleVer(order.id)}>
                        <VisibilityIcon fontSize="small" />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </Paper>

          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mt: 2 }}>
            <Typography variant="body2" color="text.secondary">
              {total} pedido{total !== 1 ? 's' : ''} en total
            </Typography>
            <Box sx={{ display: 'flex', gap: 1, alignItems: 'center' }}>
              <Button disabled={page <= 1} onClick={() => setPage(p => p - 1)} size="small">Anterior</Button>
              <Typography variant="body2">Página {page} de {lastPage}</Typography>
              <Button disabled={page >= lastPage} onClick={() => setPage(p => p + 1)} size="small">Siguiente</Button>
            </Box>
          </Box>
        </>
      )}

      <Dialog open={!!selected} onClose={() => setSelected(null)} maxWidth="md" fullWidth>
        <DialogTitle>Detalle del pedido {selected?.codigo}</DialogTitle>
        <DialogContent>
          {selected && (
            <Box>
              <Grid container spacing={2} sx={{ mb: 2 }}>
                <Grid size={{ xs: 6 }}>
                  <Typography variant="caption" color="text.secondary">Cliente</Typography>
                  <Typography variant="body2">{selected.cliente?.nombre_completo || '-'}</Typography>
                </Grid>
                <Grid size={{ xs: 6 }}>
                  <Typography variant="caption" color="text.secondary">Fecha emisión</Typography>
                  <Typography variant="body2">{selected.fecha_emision || '-'}</Typography>
                </Grid>
                <Grid size={{ xs: 6 }}>
                  <Typography variant="caption" color="text.secondary">Estado</Typography>
                  <Box sx={{ mt: 0.5 }}>
                    <Chip
                      label={ESTADO_LABELS[selected.estado] || selected.estado}
                      color={ESTADO_COLORS[selected.estado] || 'default'}
                      size="small"
                    />
                  </Box>
                </Grid>
                <Grid size={{ xs: 6 }}>
                  <Typography variant="caption" color="text.secondary">Origen</Typography>
                  <Typography variant="body2">{selected.origen}</Typography>
                </Grid>
              </Grid>
              {selected.observaciones && (
                <Box sx={{ mb: 2 }}>
                  <Typography variant="caption" color="text.secondary">Observaciones</Typography>
                  <Typography variant="body2">{selected.observaciones}</Typography>
                </Box>
              )}
              <Divider sx={{ my: 2 }} />
              <Typography variant="subtitle2" gutterBottom>Ítems</Typography>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Producto</TableCell>
                    <TableCell align="right">Cantidad</TableCell>
                    <TableCell align="right">Precio</TableCell>
                    <TableCell align="right">Total</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {selected.items?.map((item) => (
                    <TableRow key={item.id}>
                      <TableCell>{item.producto?.nombre || `Producto #${item.producto_id}`}</TableCell>
                      <TableCell align="right">{item.cantidad}</TableCell>
                      <TableCell align="right">S/ {Number(item.precio_unitario).toFixed(2)}</TableCell>
                      <TableCell align="right">S/ {Number(item.total).toFixed(2)}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
              <Box sx={{ mt: 2, textAlign: 'right' }}>
                <Typography variant="caption" color="text.secondary">Subtotal: S/ {Number(selected.subtotal).toFixed(2)}</Typography><br />
                <Typography variant="caption" color="text.secondary">IGV: S/ {Number(selected.igv).toFixed(2)}</Typography><br />
                <Typography variant="h6" sx={{ mt: 1 }}>Total: S/ {Number(selected.total).toFixed(2)}</Typography>
              </Box>
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setSelected(null)}>Cerrar</Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
