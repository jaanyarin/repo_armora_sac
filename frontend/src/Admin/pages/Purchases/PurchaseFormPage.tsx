import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Alert,
  AlertTitle,
  Autocomplete,
  Box,
  Button,
  Card,
  CardContent,
  Chip,
  CircularProgress,
  Divider,
  Grid,
  IconButton,
  Paper,
  Skeleton,
  Snackbar,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TextField,
  Tooltip,
  Typography,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import BlockIcon from '@mui/icons-material/Block';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import DeleteIcon from '@mui/icons-material/Delete';
import OpenInNewIcon from '@mui/icons-material/OpenInNew';
import SaveIcon from '@mui/icons-material/Save';
import StorefrontIcon from '@mui/icons-material/Storefront';
import SyncIcon from '@mui/icons-material/Sync';
import WarehouseIcon from '@mui/icons-material/Warehouse';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  catalogApi,
  empresaApi,
  productsApi,
  proveedoresApi,
  purchasesApi,
} from '../../../shared/api/endpoints';
import type {
  Almacen,
  CompraItemPayload,
  Moneda,
  Product,
  Proveedor,
  UnidadMedida,
} from '../../../shared/types';
import { calcularTotalesIgv } from '../../../shared/utils/igvCalculator';

interface ItemRow {
  key: string;
  producto: Product | null;
  unidad_medida_id: number | null;
  cantidad: number;
  precio_unitario: number;
  descuento_linea: number;
  costSynced: boolean;
}

function useDebouncedValue<T>(value: T, delay = 350): T {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const t = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(t);
  }, [value, delay]);
  return debounced;
}

function emptyItem(): ItemRow {
  return {
    key: crypto.randomUUID(),
    producto: null,
    unidad_medida_id: null,
    cantidad: 1,
    precio_unitario: 0,
    descuento_linea: 0,
    costSynced: true,
  };
}

export default function PurchaseFormPage() {
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const [proveedor, setProveedor] = useState<Proveedor | null>(null);
  const [almacen, setAlmacen] = useState<Almacen | null>(null);
  const [moneda, setMoneda] = useState<Moneda | null>(null);
  const [fechaEmision, setFechaEmision] = useState(new Date().toISOString().slice(0, 10));
  const [observaciones, setObservaciones] = useState('');
  const [items, setItems] = useState<ItemRow[]>([emptyItem()]);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [snack, setSnack] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false,
    message: '',
    severity: 'success',
  });
  const [proveedorSearch, setProveedorSearch] = useState('');
  const debouncedProveedorSearch = useDebouncedValue(proveedorSearch, 350);

  const { data: empresa } = useQuery({
    queryKey: ['empresa-config'],
    queryFn: () => empresaApi.get().then((r) => r.data),
  });
  const comprasBloqueadas = Boolean(empresa?.compras_bloqueadas);

  const { data: proveedoresData, isLoading: loadingProveedores } = useQuery({
    queryKey: ['proveedores-search', debouncedProveedorSearch],
    queryFn: () =>
      proveedoresApi
        .list({ search: debouncedProveedorSearch, per_page: 20, activo: true })
        .then((r) => r.data?.data || []),
    enabled: debouncedProveedorSearch.length >= 1,
  });

  const { data: productsData, isLoading: loadingProductos } = useQuery({
    queryKey: ['products-for-purchase'],
    queryFn: () =>
      productsApi.list({ per_page: 200, activo: true }).then((r) => r.data?.data || []),
  });

  const { data: almacenesData, isLoading: loadingAlmacenes } = useQuery({
    queryKey: ['almacenes'],
    queryFn: () => catalogApi.almacenes().then((r) => r.data?.data || []),
  });

  const { data: monedasData } = useQuery({
    queryKey: ['monedas'],
    queryFn: () => catalogApi.monedas().then((r) => r.data?.data || []),
  });

  const { data: unidadesData } = useQuery({
    queryKey: ['unidades-medida'],
    queryFn: () => catalogApi.unidadesMedida().then((r) => r.data?.data || []),
  });

  const monedaEfectiva: Moneda | null = useMemo(() => {
    if (moneda) return moneda;
    if (!monedasData || monedasData.length === 0) return null;
    return monedasData.find((m: Moneda) => m.codigo === 'PEN') || monedasData[0];
  }, [moneda, monedasData]);

  const almacenEfectivo: Almacen | null = useMemo(() => {
    if (almacen) return almacen;
    if (!almacenesData || almacenesData.length === 0) return null;
    return almacenesData.find((a: Almacen) => a.principal) || almacenesData[0];
  }, [almacen, almacenesData]);

  const createMutation = useMutation({
    mutationFn: (payload: Record<string, unknown>) => purchasesApi.create(payload),
    onSuccess: (response: { data?: { data?: { codigo?: string } } }) => {
      const compra = response?.data?.data;
      const codigo = compra?.codigo || 'OK';
      queryClient.invalidateQueries({ queryKey: ['compras'] });
      queryClient.invalidateQueries({ queryKey: ['products'] });
      queryClient.invalidateQueries({ queryKey: ['stock'] });
      queryClient.invalidateQueries({ queryKey: ['kardex'] });
      setSnack({
        open: true,
        severity: 'success',
        message: `Compra ${codigo} creada exitosamente.`,
      });
      setTimeout(() => navigate('/admin/compras'), 800);
    },
    onError: (err: {
      response?: { data?: { message?: string; errors?: Record<string, string[]> } };
    }) => {
      const msg = err.response?.data?.message || 'Error al crear la compra.';
      const errors = err.response?.data?.errors;
      if (errors) {
        const flat: Record<string, string> = {};
        for (const [key, arr] of Object.entries(errors)) {
          if (Array.isArray(arr) && arr.length > 0) flat[key] = arr[0];
        }
        setFieldErrors(flat);
        const first = Object.values(flat)[0];
        setSubmitError(first ? `${msg}: ${first}` : msg);
      } else {
        setFieldErrors({});
        setSubmitError(msg);
      }
    },
  });

  const updateItem = <K extends keyof ItemRow>(key: string, field: K, value: ItemRow[K]) => {
    setItems((prev) => prev.map((i) => (i.key === key ? { ...i, [field]: value } : i)));
  };

  const addItem = () => {
    setItems((prev) => [...prev, emptyItem()]);
  };

  const removeItem = (key: string) => {
    setItems((prev) => (prev.length > 1 ? prev.filter((i) => i.key !== key) : prev));
  };

  const totales = useMemo(() => {
    return calcularTotalesIgv(
      items.map((i) => ({
        cantidad: i.cantidad,
        precio_unitario: i.precio_unitario,
        descuento_linea: i.descuento_linea,
      })),
    );
  }, [items]);

  const itemsCount = items.filter((i) => i.producto).length;
  const proveedoresList: Proveedor[] = proveedoresData || [];
  const productsList: Product[] = productsData || [];
  const almacenesList: Almacen[] = almacenesData || [];
  const unidadesList: UnidadMedida[] = unidadesData || [];
  const monedaSimbolo = monedaEfectiva?.simbolo || 'S/';
  const formDisabled = comprasBloqueadas || createMutation.isPending;

  const validate = (): boolean => {
    const errs: Record<string, string> = {};
    if (!proveedor) errs.proveedor = 'Selecciona un proveedor.';
    if (!fechaEmision) errs.fechaEmision = 'La fecha de emisión es obligatoria.';
    if (itemsCount === 0) errs.items = 'Agrega al menos un ítem con producto seleccionado.';
    setFieldErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const handleSubmit = (estado: 'borrador' | 'confirmada') => {
    setSubmitError(null);
    setFieldErrors({});
    if (comprasBloqueadas) {
      setSubmitError('Las compras están deshabilitadas. No se puede crear una nueva compra.');
      return;
    }
    if (!validate()) {
      setSubmitError('Revisa los campos marcados antes de continuar.');
      return;
    }

    const validItems: CompraItemPayload[] = items
      .filter((i) => i.producto && i.unidad_medida_id && i.cantidad > 0 && i.precio_unitario >= 0)
      .map((i) => ({
        producto_id: i.producto!.id,
        unidad_medida_id: i.unidad_medida_id!,
        cantidad: Number(i.cantidad),
        precio_unitario: Number(i.precio_unitario),
        descuento_linea: Number(i.descuento_linea) || 0,
      }));

    createMutation.mutate({
      proveedor_id: proveedor!.id,
      almacen_id: almacenEfectivo?.id ?? null,
      moneda_id: monedaEfectiva?.id ?? null,
      fecha_emision: fechaEmision,
      observaciones: observaciones || null,
      origen: 'admin',
      estado,
      items: validItems,
    });
  };

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 2, gap: 2, flexWrap: 'wrap' }}>
        <Box>
          <Typography variant="h5" fontWeight={600}>
            Nueva Compra
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Registra una orden de compra y, al confirmar, el stock se incrementará automáticamente en el almacén destino.
          </Typography>
        </Box>
        <Stack direction="row" spacing={1}>
          <Button onClick={() => navigate('/admin/compras')}>Volver al listado</Button>
        </Stack>
      </Box>

      {comprasBloqueadas && (
        <Alert severity="warning" icon={<BlockIcon />} sx={{ mb: 2 }}>
          <AlertTitle>Compras deshabilitadas</AlertTitle>
          El administrador ha bloqueado el registro de compras. Podrás continuar cuando se habiliten nuevamente desde{' '}
          <strong>Configuración → Empresa → Bloqueo</strong>.
        </Alert>
      )}

      {submitError && (
        <Alert severity="error" sx={{ mb: 2 }} onClose={() => setSubmitError(null)}>
          {submitError}
        </Alert>
      )}

      <Paper sx={{ p: 3, mb: 2 }}>
        <Typography variant="subtitle1" fontWeight={600} gutterBottom>
          Datos generales
        </Typography>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 5 }}>
            {loadingProveedores && proveedorSearch ? (
              <Skeleton variant="rounded" height={56} />
            ) : (
              <Autocomplete
                options={proveedoresList}
                getOptionLabel={(p) => `${p.codigo} · ${p.nombre_completo}`}
                isOptionEqualToValue={(o, v) => o.id === v.id}
                value={proveedor}
                onChange={(_, v) => setProveedor(v)}
                onInputChange={(_, v) => setProveedorSearch(v)}
                disabled={formDisabled}
                filterOptions={(x) => x}
                renderInput={(params) => (
                  <TextField
                    {...params}
                    label="Proveedor"
                    required
                    size="small"
                    error={Boolean(fieldErrors.proveedor)}
                    helperText={fieldErrors.proveedor || 'Busca por código, RUC/DNI o razón social'}
                  />
                )}
              />
            )}
            <Button
              size="small"
              startIcon={<OpenInNewIcon />}
              sx={{ mt: 0.5, textTransform: 'none' }}
              onClick={() => navigate('/admin/proveedores/nuevo')}
              disabled={formDisabled}
            >
              Crear nuevo proveedor
            </Button>
          </Grid>

          <Grid size={{ xs: 12, md: 3 }}>
            {loadingAlmacenes ? (
              <Skeleton variant="rounded" height={56} />
            ) : (
              <Autocomplete
                options={almacenesList}
                getOptionLabel={(a) => a.nombre}
                isOptionEqualToValue={(o, v) => o.id === v.id}
                value={almacenEfectivo}
                onChange={(_, v) => setAlmacen(v)}
                disabled={formDisabled}
                renderInput={(params) => (
                  <TextField
                    {...params}
                    label="Almacén destino"
                    size="small"
                    required
                    InputProps={{
                      ...params.InputProps,
                      startAdornment: <WarehouseIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 0.5 }} />,
                    }}
                    helperText="El stock se sumará a este almacén al confirmar."
                  />
                )}
              />
            )}
          </Grid>

          <Grid size={{ xs: 12, md: 2 }}>
            <TextField
              fullWidth
              size="small"
              type="date"
              label="Fecha emisión"
              required
              value={fechaEmision}
              onChange={(e) => setFechaEmision(e.target.value)}
              slotProps={{ inputLabel: { shrink: true } }}
              disabled={formDisabled}
              error={Boolean(fieldErrors.fechaEmision)}
              helperText={fieldErrors.fechaEmision}
            />
          </Grid>

          <Grid size={{ xs: 12, md: 2 }}>
            <Autocomplete
              options={monedasData || []}
              getOptionLabel={(m: Moneda) => `${m.codigo} · ${m.nombre}`}
              isOptionEqualToValue={(o, v) => o.id === v.id}
              value={monedaEfectiva}
              onChange={(_, v) => setMoneda(v)}
              disabled={formDisabled}
              renderInput={(params) => (
                <TextField {...params} label="Moneda" size="small" />
              )}
            />
          </Grid>

          <Grid size={{ xs: 12 }}>
            <TextField
              fullWidth
              size="small"
              label="Observaciones"
              multiline
              rows={2}
              value={observaciones}
              onChange={(e) => setObservaciones(e.target.value)}
              disabled={formDisabled}
              placeholder="Notas internas, número de orden de compra del proveedor, condiciones, etc."
            />
          </Grid>
        </Grid>
      </Paper>

      <Paper sx={{ p: 3, mb: 2 }}>
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
          <Box>
            <Typography variant="subtitle1" fontWeight={600}>
              Productos
            </Typography>
            <Typography variant="caption" color="text.secondary">
              {itemsCount} producto{itemsCount === 1 ? '' : 's'} agregado{itemsCount === 1 ? '' : 's'} · el precio se sugiere del costo promedio del producto.
            </Typography>
          </Box>
          <Button startIcon={<AddIcon />} onClick={addItem} size="small" variant="outlined" disabled={formDisabled}>
            Agregar producto
          </Button>
        </Box>

        {fieldErrors.items && (
          <Alert severity="warning" sx={{ mb: 2 }}>
            {fieldErrors.items}
          </Alert>
        )}

        {loadingProductos ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', py: 4 }}>
            <CircularProgress />
          </Box>
        ) : itemsCount === 0 ? (
          <Card variant="outlined" sx={{ borderStyle: 'dashed', bgcolor: '#fafbfc' }}>
            <CardContent sx={{ textAlign: 'center', py: 5 }}>
              <StorefrontIcon sx={{ fontSize: 48, color: 'text.disabled', mb: 1 }} />
              <Typography variant="body1" color="text.secondary" gutterBottom>
                Aún no has agregado productos.
              </Typography>
              <Typography variant="caption" color="text.disabled">
                Usa el botón <strong>Agregar producto</strong> o el buscador de la tabla para empezar.
              </Typography>
            </CardContent>
          </Card>
        ) : (
          <Box sx={{ overflowX: 'auto' }}>
            <Table size="small">
              <TableHead>
                <TableRow>
                  <TableCell sx={{ minWidth: 280 }}>Producto</TableCell>
                  <TableCell sx={{ width: 100 }}>Unidad</TableCell>
                  <TableCell align="right" sx={{ width: 110 }}>Cantidad</TableCell>
                  <TableCell align="right" sx={{ width: 150 }}>Precio Unit.</TableCell>
                  <TableCell align="right" sx={{ width: 110 }}>Descuento</TableCell>
                  <TableCell align="right" sx={{ width: 110 }}>Subtotal</TableCell>
                  <TableCell sx={{ width: 50 }}></TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {items.map((item, idx) => {
                  const lineCalc = item.cantidad * item.precio_unitario - (item.descuento_linea || 0);
                  return (
                    <TableRow key={item.key}>
                      <TableCell>
                        <Autocomplete
                          size="small"
                          options={productsList}
                          getOptionLabel={(p) => `${p.codigo} · ${p.nombre}`}
                          isOptionEqualToValue={(o, v) => o.id === v.id}
                          value={item.producto}
                          onChange={(_, v) => {
                            updateItem(item.key, 'producto', v);
                            if (v) {
                              if (!item.unidad_medida_id) {
                                updateItem(item.key, 'unidad_medida_id', v.unidad_medida_id);
                              }
                              updateItem(item.key, 'precio_unitario', Number(v.costo_promedio) || 0);
                              updateItem(item.key, 'costSynced', true);
                            } else {
                              updateItem(item.key, 'costSynced', false);
                            }
                          }}
                          disabled={formDisabled}
                          renderInput={(params) => (
                            <TextField {...params} placeholder="Buscar producto por código o nombre..." />
                          )}
                        />
                        {item.producto && (
                          <Stack direction="row" spacing={0.5} sx={{ mt: 0.5 }}>
                            <Chip
                              size="small"
                              label={`Stock actual: ${item.producto.stock_actual}`}
                              variant="outlined"
                              sx={{ height: 20, fontSize: 11 }}
                            />
                            {item.costSynced && (
                              <Chip
                                size="small"
                                icon={<SyncIcon sx={{ fontSize: 12 }} />}
                                label="Costo sincronizado"
                                color="primary"
                                variant="outlined"
                                sx={{ height: 20, fontSize: 11 }}
                              />
                            )}
                          </Stack>
                        )}
                      </TableCell>
                      <TableCell>
                        <Autocomplete
                          size="small"
                          options={unidadesList}
                          getOptionLabel={(u: UnidadMedida) => u.simbolo}
                          isOptionEqualToValue={(o, v) => o.id === v.id}
                          value={unidadesList.find((u) => u.id === item.unidad_medida_id) || null}
                          onChange={(_, v) => updateItem(item.key, 'unidad_medida_id', v?.id ?? null)}
                          disabled={formDisabled}
                          sx={{ minWidth: 80 }}
                          renderInput={(params) => <TextField {...params} placeholder="UND" />}
                        />
                      </TableCell>
                      <TableCell align="right">
                        <TextField
                          type="number"
                          size="small"
                          sx={{ width: 90 }}
                          inputProps={{ min: 0.01, step: 0.01 }}
                          value={item.cantidad}
                          onChange={(e) => updateItem(item.key, 'cantidad', Number(e.target.value))}
                          disabled={formDisabled}
                        />
                      </TableCell>
                      <TableCell align="right">
                        <TextField
                          type="number"
                          size="small"
                          sx={{ width: 130 }}
                          inputProps={{ min: 0, step: 0.01 }}
                          value={item.precio_unitario}
                          onChange={(e) => {
                            updateItem(item.key, 'precio_unitario', Number(e.target.value));
                            updateItem(item.key, 'costSynced', false);
                          }}
                          disabled={formDisabled}
                          InputProps={{
                            startAdornment: (
                              <Typography variant="caption" color="text.secondary" sx={{ mr: 0.5 }}>
                                {monedaSimbolo}
                              </Typography>
                            ),
                          }}
                        />
                      </TableCell>
                      <TableCell align="right">
                        <TextField
                          type="number"
                          size="small"
                          sx={{ width: 90 }}
                          inputProps={{ min: 0, step: 0.01 }}
                          value={item.descuento_linea}
                          onChange={(e) => updateItem(item.key, 'descuento_linea', Number(e.target.value))}
                          disabled={formDisabled}
                        />
                      </TableCell>
                      <TableCell align="right">
                        <Typography variant="body2" fontWeight={500}>
                          {monedaSimbolo} {lineCalc.toFixed(2)}
                        </Typography>
                        {idx === 0 && (
                          <Typography variant="caption" color="text.disabled" sx={{ display: 'block' }}>
                            (antes de IGV)
                          </Typography>
                        )}
                      </TableCell>
                      <TableCell>
                        <Tooltip title={items.length === 1 ? 'Debe quedar al menos un ítem' : 'Quitar ítem'}>
                          <span>
                            <IconButton
                              size="small"
                              color="error"
                              onClick={() => removeItem(item.key)}
                              disabled={items.length === 1 || formDisabled}
                            >
                              <DeleteIcon fontSize="small" />
                            </IconButton>
                          </span>
                        </Tooltip>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </Box>
        )}
      </Paper>

      <Box
        sx={{
          position: 'sticky',
          bottom: 0,
          zIndex: 5,
          bgcolor: 'background.paper',
          borderTop: 1,
          borderColor: 'divider',
          boxShadow: '0 -4px 12px rgba(0,0,0,0.05)',
          borderRadius: 1,
          p: 2,
          mb: 2,
        }}
      >
        <Stack spacing={2} sx={{ flexDirection: { xs: 'column', md: 'row' }, justifyContent: 'space-between', alignItems: { md: 'center' } }}>
          <Stack direction="row" spacing={3} sx={{ flex: 1, flexWrap: 'wrap' }}>
            <Box>
              <Typography variant="caption" color="text.secondary">
                Subtotal (gravada)
              </Typography>
              <Typography variant="body1" fontWeight={500}>
                {monedaSimbolo} {totales.subtotal.toFixed(2)}
              </Typography>
            </Box>
            <Box>
              <Typography variant="caption" color="text.secondary">
                IGV (18%)
              </Typography>
              <Typography variant="body1" fontWeight={500}>
                {monedaSimbolo} {totales.igv.toFixed(2)}
              </Typography>
            </Box>
            <Divider orientation="vertical" flexItem />
            <Box>
              <Typography variant="caption" color="text.secondary">
                Total
              </Typography>
              <Typography variant="h6" color="primary" fontWeight={700}>
                {monedaSimbolo} {totales.total.toFixed(2)}
              </Typography>
            </Box>
          </Stack>

          <Stack direction="row" spacing={1}>
            <Button
              variant="outlined"
              startIcon={<SaveIcon />}
              onClick={() => handleSubmit('borrador')}
              disabled={formDisabled}
            >
              Guardar borrador
            </Button>
            <Button
              variant="contained"
              startIcon={<CheckCircleIcon />}
              onClick={() => handleSubmit('confirmada')}
              disabled={formDisabled}
            >
              Crear y confirmar
            </Button>
          </Stack>
        </Stack>
      </Box>

      <Snackbar
        open={snack.open}
        autoHideDuration={4000}
        onClose={() => setSnack({ ...snack, open: false })}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      >
        <Alert
          severity={snack.severity}
          variant="filled"
          onClose={() => setSnack({ ...snack, open: false })}
        >
          {snack.message}
        </Alert>
      </Snackbar>
    </Box>
  );
}
