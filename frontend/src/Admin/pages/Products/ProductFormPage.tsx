import { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Box, Typography, TextField, Button, MenuItem, Grid, CircularProgress, Alert, Switch, FormControlLabel, Divider, Chip } from '@mui/material';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '../../../shared/lib/zodResolver';
import { z } from 'zod';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { productsApi, catalogApi, inventoryApi } from '../../../shared/api/endpoints';

const productSchema = z.object({
  codigo: z.string().max(20).nullable().optional(),
  codigo_sunat: z.string().max(10).nullable().optional(),
  nombre: z.string().min(1, 'El nombre es obligatorio').max(255),
  descripcion: z.string().nullable().optional(),
  unidad_medida_id: z.number({ required_error: 'Requerido' }),
  producto_clase_id: z.number().nullable().optional(),
  producto_subclase_id: z.number().nullable().optional(),
  familia_sunat_id: z.number().nullable().optional(),
  clase_sunat_id: z.number().nullable().optional(),
  tipo_afeccion_igv_id: z.number().nullable().optional(),
  afecto_isc: z.boolean().optional(),
  tipo_calculo_isc_id: z.number().nullable().optional(),
  precio_venta: z.number().min(0).nullable().optional(),
  precio_venta_usd: z.number().min(0).nullable().optional(),
  costo_promedio: z.number().min(0).nullable().optional(),
  stock_minimo: z.number().min(0).nullable().optional(),
  stock_actual: z.number().min(0).nullable().optional(),
  activo: z.boolean().optional(),
});

type ProductForm = z.infer<typeof productSchema>;

export default function ProductFormPage() {
  const { id } = useParams();
  const isEdit = !!id;
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const { data: productData, isLoading: loadingProduct } = useQuery({
    queryKey: ['product', id],
    queryFn: () => productsApi.find(Number(id)).then(r => r.data),
    enabled: isEdit,
  });

  const { data: unidadesMedida } = useQuery({ queryKey: ['catalog', 'unidades-medida'], queryFn: () => catalogApi.unidadesMedida().then(r => r.data) });
  const { data: productoClases } = useQuery({ queryKey: ['catalog', 'producto-clases'], queryFn: () => catalogApi.productoClases().then(r => r.data) });
  const { data: familias } = useQuery({ queryKey: ['catalog', 'familias'], queryFn: () => catalogApi.familias().then(r => r.data) });
  const { data: igvTipos } = useQuery({ queryKey: ['catalog', 'tipos-afeccion-igv'], queryFn: () => catalogApi.tipoAfeccionIgv().then(r => r.data) });
  const { data: iscTipos } = useQuery({ queryKey: ['catalog', 'tipos-calculo-isc'], queryFn: () => catalogApi.tipoCalculoIsc().then(r => r.data) });
  const { data: almacenes } = useQuery({ queryKey: ['catalog', 'almacenes'], queryFn: () => catalogApi.almacenes().then(r => r.data) });

  const { watch, control, handleSubmit, reset, formState: { errors } } = useForm<ProductForm>({
    resolver: zodResolver(productSchema),
    defaultValues: { activo: true, afecto_isc: false, precio_venta: 0, precio_venta_usd: 0, costo_promedio: 0, stock_minimo: 0, stock_actual: 0 },
  });

  const selectedClaseId = watch('producto_clase_id');
  const selectedFamiliaId = watch('familia_sunat_id');
  const afectoIsc = watch('afecto_isc');

  const { data: productoSubclases } = useQuery({
    queryKey: ['catalog', 'producto-subclases', selectedClaseId],
    queryFn: () => catalogApi.productoSubclases(selectedClaseId ?? undefined).then(r => r.data),
    enabled: !!selectedClaseId,
  });

  const { data: clases } = useQuery({
    queryKey: ['catalog', 'clases'],
    queryFn: () => catalogApi.clases().then(r => r.data),
  });

  const { data: stockPorAlmacen } = useQuery({
    queryKey: ['inventory', 'stock-by-product', id],
    queryFn: () => inventoryApi.stockByProduct(Number(id)).then(r => r.data),
    enabled: isEdit,
  });

  const filteredClases = selectedFamiliaId
    ? (clases || []).filter((c: { id: number; familia_id: number }) => c.familia_id === selectedFamiliaId)
    : clases || [];

  useEffect(() => {
    if (productData) {
      reset({
        codigo: productData.codigo,
        codigo_sunat: productData.codigo_sunat,
        nombre: productData.nombre,
        descripcion: productData.descripcion,
        unidad_medida_id: productData.unidad_medida_id,
        producto_clase_id: productData.producto_clase_id,
        producto_subclase_id: productData.producto_subclase_id,
        familia_sunat_id: productData.familia_sunat_id,
        clase_sunat_id: productData.clase_sunat_id,
        tipo_afeccion_igv_id: productData.tipo_afeccion_igv_id,
        afecto_isc: !!productData.tipo_calculo_isc_id,
        tipo_calculo_isc_id: productData.tipo_calculo_isc_id,
        precio_venta: productData.precio_venta,
        precio_venta_usd: productData.precio_venta_usd,
        costo_promedio: productData.costo_promedio,
        stock_minimo: productData.stock_minimo,
        stock_actual: productData.stock_actual,
        activo: productData.activo,
      });
    }
  }, [productData, reset]);

  const mutation = useMutation({
    mutationFn: (data: ProductForm) => {
      const payload = { ...data };
      if (!payload.afecto_isc) {
        delete payload.tipo_calculo_isc_id;
      }
      delete payload.afecto_isc;
      return isEdit ? productsApi.update(Number(id), payload) : productsApi.create(payload);
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
      navigate('/admin/productos');
    },
  });

  const onSubmit = (data: ProductForm) => mutation.mutate(data);

  if (isEdit && loadingProduct) return <CircularProgress />;

  return (
    <Box>
      <Typography variant="h5" mb={2}>{isEdit ? 'Editar Producto' : 'Nuevo Producto'}</Typography>
      {mutation.isError && <Alert severity="error" sx={{ mb: 2 }}>Error al guardar el producto.</Alert>}
      <Box component="form" onSubmit={handleSubmit(onSubmit)}>

        {/* === Section 1: Información Básica === */}
        <Typography variant="subtitle1" fontWeight={600} mb={1}>Información Básica</Typography>
        <Grid container spacing={2} mb={3}>
          <Grid size={{ xs: 12, md: 6 }}>
            <Controller name="nombre" control={control} render={({ field }) => (
              <TextField {...field} fullWidth label="Nombre del Producto *" error={!!errors.nombre} helperText={errors.nombre?.message} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="codigo" control={control} render={({ field }) => (
              <TextField {...field} value={field.value || ''} fullWidth label="Código" helperText="Dejar vacío para auto-generar" />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="codigo_sunat" control={control} render={({ field }) => (
              <TextField {...field} value={field.value || ''} fullWidth label="SKU (Código SUNAT)" />
            )} />
          </Grid>
          <Grid size={{ xs: 12 }}>
            <Controller name="descripcion" control={control} render={({ field }) => (
              <TextField {...field} value={field.value || ''} fullWidth label="Descripción" multiline rows={2} />
            )} />
          </Grid>
        </Grid>

        <Divider sx={{ mb: 2 }} />

        {/* === Section 2: Clasificación === */}
        <Typography variant="subtitle1" fontWeight={600} mb={1}>Clasificación</Typography>
        <Grid container spacing={2} mb={3}>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="unidad_medida_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="U. Medida *" error={!!errors.unidad_medida_id}
                onChange={(e) => field.onChange(Number(e.target.value))}>
                <MenuItem value="">Seleccionar</MenuItem>
                {unidadesMedida?.map((um: { id: number; nombre: string; simbolo: string }) => (
                  <MenuItem key={um.id} value={um.id}>{um.simbolo} - {um.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="producto_clase_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Clase Producto"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                <MenuItem value="">Ninguno</MenuItem>
                {productoClases?.map((pc: { id: number; nombre: string }) => (
                  <MenuItem key={pc.id} value={pc.id}>{pc.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="producto_subclase_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Subclase Producto"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}
                disabled={!selectedClaseId}>
                <MenuItem value="">{selectedClaseId ? 'Seleccionar' : 'Seleccione una clase primero'}</MenuItem>
                {productoSubclases?.map((ps: { id: number; nombre: string }) => (
                  <MenuItem key={ps.id} value={ps.id}>{ps.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="tipo_afeccion_igv_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Afec. IGV"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                <MenuItem value="">Ninguno</MenuItem>
                {igvTipos?.map((igv: { id: number; nombre: string }) => (
                  <MenuItem key={igv.id} value={igv.id}>{igv.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="familia_sunat_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Familia SUNAT"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                <MenuItem value="">Ninguno</MenuItem>
                {familias?.map((f: { id: number; nombre: string }) => (
                  <MenuItem key={f.id} value={f.id}>{f.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="clase_sunat_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Clase SUNAT"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                <MenuItem value="">{selectedFamiliaId ? 'Seleccionar' : 'Seleccione una familia primero'}</MenuItem>
                {filteredClases?.map((c: { id: number; nombre: string; codigo: string }) => (
                  <MenuItem key={c.id} value={c.id}>{c.codigo} - {c.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
        </Grid>

        <Divider sx={{ mb: 2 }} />

        {/* === Section 3: ISC === */}
        <Typography variant="subtitle1" fontWeight={600} mb={1}>ISC</Typography>
        <Grid container spacing={2} mb={3}>
          <Grid size={{ xs: 12 }}>
            <Controller name="afecto_isc" control={control} render={({ field }) => (
              <FormControlLabel control={<Switch checked={field.value || false} onChange={(e) => { field.onChange(e.target.checked); if (!e.target.checked) reset({ tipo_calculo_isc_id: null }); }} />} label="¿Afecto al ISC?" />
            )} />
          </Grid>
          {afectoIsc && (
            <Grid size={{ xs: 6, md: 4 }}>
              <Controller name="tipo_calculo_isc_id" control={control} render={({ field }) => (
                <TextField {...field} value={field.value ?? ''} select fullWidth label="Tipo Cálculo ISC"
                  onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                  <MenuItem value="">Seleccionar</MenuItem>
                  {iscTipos?.map((isc: { id: number; nombre: string }) => (
                    <MenuItem key={isc.id} value={isc.id}>{isc.nombre}</MenuItem>
                  ))}
                </TextField>
              )} />
            </Grid>
          )}
        </Grid>

        <Divider sx={{ mb: 2 }} />

        {/* === Section 4: Precios y Stock === */}
        <Typography variant="subtitle1" fontWeight={600} mb={1}>Precios y Stock</Typography>
        <Grid container spacing={2} mb={3}>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="precio_venta" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? 0} fullWidth label="Precio Venta S/" type="number"
                onChange={(e) => field.onChange(Number(e.target.value))} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="precio_venta_usd" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? 0} fullWidth label="Precio Venta USD" type="number"
                onChange={(e) => field.onChange(Number(e.target.value))} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="costo_promedio" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? 0} fullWidth label="Costo Promedio" type="number"
                onChange={(e) => field.onChange(Number(e.target.value))} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="stock_actual" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? 0} fullWidth label="Stock Actual" type="number"
                onChange={(e) => field.onChange(Number(e.target.value))} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="stock_minimo" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? 0} fullWidth label="Stock Mínimo" type="number"
                onChange={(e) => field.onChange(Number(e.target.value))} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="activo" control={control} render={({ field }) => (
              <FormControlLabel control={<Switch checked={field.value ?? true} onChange={(e) => field.onChange(e.target.checked)} />} label="Producto Activo" />
            )} />
          </Grid>
        </Grid>

        {/* === Section 5: Stock por Almacén (solo lectura en edición) === */}
        {isEdit && stockPorAlmacen && stockPorAlmacen.length > 0 && (
          <>
            <Divider sx={{ mb: 2 }} />
            <Typography variant="subtitle1" fontWeight={600} mb={1}>Stock por Almacén</Typography>
            <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 2, mb: 3 }}>
              {stockPorAlmacen.map((s: { almacen_id: number; cantidad_disponible: number; cantidad_comprometida: number; cantidad_minima: number; cantidad_maxima: number }) => {
                const almacen = (almacenes || []).find((a: { id: number }) => a.id === s.almacen_id);
                return (
                  <Box key={s.almacen_id} sx={{ border: 1, borderColor: 'divider', borderRadius: 1, p: 1.5, minWidth: 180 }}>
                    <Typography variant="body2" fontWeight={600}>{almacen?.nombre || `Almacén #${s.almacen_id}`}</Typography>
                    <Box sx={{ display: 'flex', gap: 1, mt: 0.5, flexWrap: 'wrap' }}>
                      <Chip label={`Disp: ${s.cantidad_disponible}`} size="small" color="primary" variant="outlined" />
                      <Chip label={`Comp: ${s.cantidad_comprometida}`} size="small" color="warning" variant="outlined" />
                      <Chip label={`Min: ${s.cantidad_minima}`} size="small" variant="outlined" />
                      <Chip label={`Max: ${s.cantidad_maxima}`} size="small" variant="outlined" />
                    </Box>
                  </Box>
                );
              })}
            </Box>
          </>
        )}

        <Box sx={{ mt: 3, display: 'flex', gap: 2 }}>
          <Button type="submit" variant="contained" disabled={mutation.isPending}>
            {mutation.isPending ? <CircularProgress size={20} /> : (isEdit ? 'Guardar Cambios' : 'Crear Producto')}
          </Button>
          <Button variant="outlined" onClick={() => navigate('/admin/productos')}>Cancelar</Button>
        </Box>
      </Box>
    </Box>
  );
}
