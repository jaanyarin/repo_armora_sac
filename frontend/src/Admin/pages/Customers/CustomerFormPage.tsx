import { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Box, Typography, TextField, Button, MenuItem, Grid, CircularProgress, Alert } from '@mui/material';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { customersApi, catalogApi } from '../../../shared/api/endpoints';

const customerSchema = z.object({
  tipo_documento: z.string().min(1, 'Requerido'),
  numero_documento: z.string().min(1, 'Requerido').max(15),
  nombre_completo: z.string().min(1, 'El nombre es obligatorio').max(255),
  nombre_comercial: z.string().max(255).nullable().optional(),
  direccion: z.string().max(255).nullable().optional(),
  ubigeo_id: z.number().nullable().optional(),
  email: z.string().email('Email inválido').nullable().optional().or(z.literal('')),
  telefono: z.string().max(20).nullable().optional(),
  tipo_cliente_id: z.number().nullable().optional(),
  segmento_id: z.number().nullable().optional(),
  lista_precio_id: z.number().nullable().optional(),
  limite_credito: z.number().min(0).nullable().optional(),
  activo: z.boolean().optional(),
});

type CustomerForm = z.infer<typeof customerSchema>;

export default function CustomerFormPage() {
  const { id } = useParams();
  const isEdit = !!id;
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const { data: customerData, isLoading: loadingCustomer } = useQuery({
    queryKey: ['customer', id],
    queryFn: () => customersApi.find(Number(id)).then(r => r.data),
    enabled: isEdit,
  });

  const { data: tiposCliente } = useQuery({ queryKey: ['catalog', 'tipos-cliente'], queryFn: () => catalogApi.tiposCliente().then(r => r.data) });
  const { data: segmentos } = useQuery({ queryKey: ['catalog', 'segmentos'], queryFn: () => catalogApi.segmentos().then(r => r.data) });
  const { data: listaPrecios } = useQuery({ queryKey: ['catalog', 'lista-precios'], queryFn: () => catalogApi.listaPrecios().then(r => r.data) });

  const { control, handleSubmit, reset, formState: { errors } } = useForm<CustomerForm>({
    resolver: zodResolver(customerSchema),
    defaultValues: { tipo_documento: 'DNI', activo: true, limite_credito: 0 },
  });

  useEffect(() => {
    if (customerData) {
      reset({
        tipo_documento: customerData.tipo_documento,
        numero_documento: customerData.numero_documento,
        nombre_completo: customerData.nombre_completo,
        nombre_comercial: customerData.nombre_comercial,
        direccion: customerData.direccion,
        ubigeo_id: customerData.ubigeo_id,
        email: customerData.email || '',
        telefono: customerData.telefono,
        tipo_cliente_id: customerData.tipo_cliente_id,
        segmento_id: customerData.segmento_id,
        lista_precio_id: customerData.lista_precio_id,
        limite_credito: customerData.limite_credito,
        activo: customerData.activo,
      });
    }
  }, [customerData, reset]);

  const mutation = useMutation({
    mutationFn: (data: CustomerForm) =>
      isEdit ? customersApi.update(Number(id), data) : customersApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['customers'] });
      navigate('/admin/clientes');
    },
  });

  const onSubmit = (data: CustomerForm) => mutation.mutate(data);

  if (isEdit && loadingCustomer) return <CircularProgress />;

  return (
    <Box>
      <Typography variant="h5" mb={2}>{isEdit ? 'Editar Cliente' : 'Nuevo Cliente'}</Typography>
      {mutation.isError && <Alert severity="error" sx={{ mb: 2 }}>Error al guardar el cliente.</Alert>}
      <Box component="form" onSubmit={handleSubmit(onSubmit)} sx={{ maxWidth: 800 }}>
        <Grid container spacing={2}>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="tipo_documento" control={control} render={({ field }) => (
              <TextField {...field} select fullWidth label="Tipo Doc." error={!!errors.tipo_documento} helperText={errors.tipo_documento?.message}>
                <MenuItem value="DNI">DNI</MenuItem>
                <MenuItem value="RUC">RUC</MenuItem>
                <MenuItem value="CE">CE</MenuItem>
                <MenuItem value="PASAPORTE">Pasaporte</MenuItem>
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="numero_documento" control={control} render={({ field }) => (
              <TextField {...field} fullWidth label="N° Documento" error={!!errors.numero_documento} helperText={errors.numero_documento?.message} />
            )} />
          </Grid>
          <Grid size={{ xs: 12, md: 5 }}>
            <Controller name="nombre_completo" control={control} render={({ field }) => (
              <TextField {...field} fullWidth label="Nombre / Razón Social" error={!!errors.nombre_completo} helperText={errors.nombre_completo?.message} />
            )} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Controller name="nombre_comercial" control={control} render={({ field }) => (
              <TextField {...field} value={field.value || ''} fullWidth label="Nombre Comercial" />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="email" control={control} render={({ field }) => (
              <TextField {...field} fullWidth label="Email" error={!!errors.email} helperText={errors.email?.message} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="telefono" control={control} render={({ field }) => (
              <TextField {...field} value={field.value || ''} fullWidth label="Teléfono" />
            )} />
          </Grid>
          <Grid size={{ xs: 12 }}>
            <Controller name="direccion" control={control} render={({ field }) => (
              <TextField {...field} value={field.value || ''} fullWidth label="Dirección" multiline rows={2} />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="tipo_cliente_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Tipo Cliente"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                <MenuItem value="">Ninguno</MenuItem>
                {tiposCliente?.map((tc: { id: number; nombre: string }) => (
                  <MenuItem key={tc.id} value={tc.id}>{tc.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="segmento_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Segmento"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                <MenuItem value="">Ninguno</MenuItem>
                {segmentos?.map((s: { id: number; nombre: string }) => (
                  <MenuItem key={s.id} value={s.id}>{s.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="lista_precio_id" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? ''} select fullWidth label="Lista Precio"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}>
                <MenuItem value="">Ninguno</MenuItem>
                {listaPrecios?.map((lp: { id: number; nombre: string }) => (
                  <MenuItem key={lp.id} value={lp.id}>{lp.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="limite_credito" control={control} render={({ field }) => (
              <TextField {...field} value={field.value ?? 0} fullWidth label="Límite Crédito" type="number"
                onChange={(e) => field.onChange(Number(e.target.value))} />
            )} />
          </Grid>
        </Grid>
        <Box sx={{ mt: 3, display: 'flex', gap: 2 }}>
          <Button type="submit" variant="contained" disabled={mutation.isPending}>
            {mutation.isPending ? <CircularProgress size={20} /> : (isEdit ? 'Guardar Cambios' : 'Crear Cliente')}
          </Button>
          <Button variant="outlined" onClick={() => navigate('/admin/clientes')}>Cancelar</Button>
        </Box>
      </Box>
    </Box>
  );
}
