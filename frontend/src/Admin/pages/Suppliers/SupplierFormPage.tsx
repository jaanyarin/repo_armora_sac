import { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Box, Typography, TextField, Button, MenuItem, Grid, CircularProgress, Alert } from '@mui/material';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '../../../shared/lib/zodResolver';
import { z } from 'zod';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { proveedoresApi, catalogApi } from '../../../shared/api/endpoints';

const supplierSchema = z.object({
  tipo_documento_id: z.number().nullable().optional(),
  numero_documento: z.string().min(1, 'El número de documento es obligatorio').max(20),
  nombre_completo: z.string().min(1, 'La razón social es obligatoria').max(200),
  direccion: z.string().max(200).nullable().optional(),
  telefono: z.string().max(20).nullable().optional(),
  email: z.string().email('Email inválido').nullable().optional().or(z.literal('')),
  contacto_nombre: z.string().max(200).nullable().optional(),
  activo: z.boolean().optional(),
});

type SupplierForm = z.infer<typeof supplierSchema>;

export default function SupplierFormPage() {
  const { id } = useParams();
  const isEdit = !!id;
  const navigate = useNavigate();
  const queryClient = useQueryClient();

  const { data: supplierData, isLoading: loadingSupplier } = useQuery({
    queryKey: ['proveedor', id],
    queryFn: () => proveedoresApi.find(id!).then(r => r.data?.data),
    enabled: isEdit,
  });

  const { data: documentoTipos } = useQuery({
    queryKey: ['catalog', 'documento-tipos'],
    queryFn: () => catalogApi.documentoTipos().then(r => r.data || []),
  });

  const { control, handleSubmit, reset, formState: { errors } } = useForm<SupplierForm>({
    resolver: zodResolver(supplierSchema),
    defaultValues: { activo: true },
  });

  useEffect(() => {
    if (supplierData) {
      reset({
        tipo_documento_id: supplierData.tipo_documento_id,
        numero_documento: supplierData.numero_documento,
        nombre_completo: supplierData.nombre_completo,
        direccion: supplierData.direccion,
        telefono: supplierData.telefono,
        email: supplierData.email || '',
        contacto_nombre: supplierData.contacto_nombre,
        activo: supplierData.activo,
      });
    }
  }, [supplierData, reset]);

  const mutation = useMutation({
    mutationFn: (data: SupplierForm) =>
      isEdit ? proveedoresApi.update(id!, data) : proveedoresApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['proveedores'] });
      navigate('/admin/proveedores');
    },
  });

  const onSubmit = (data: SupplierForm) => mutation.mutate(data);

  if (isEdit && loadingSupplier) return <CircularProgress />;

  return (
    <Box>
      <Typography variant="h5" mb={2}>{isEdit ? 'Editar Proveedor' : 'Nuevo Proveedor'}</Typography>
      {mutation.isError && <Alert severity="error" sx={{ mb: 2 }}>Error al guardar el proveedor.</Alert>}
      <Box component="form" onSubmit={handleSubmit(onSubmit)} sx={{ maxWidth: 800 }}>
        <Grid container spacing={2}>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="tipo_documento_id" control={control} render={({ field }) => (
              <TextField
                {...field}
                value={field.value ?? ''}
                select
                fullWidth
                label="Tipo Documento"
                onChange={(e) => field.onChange(e.target.value ? Number(e.target.value) : null)}
              >
                <MenuItem value="">Ninguno</MenuItem>
                {(documentoTipos || []).map((td: { id: number; nombre: string }) => (
                  <MenuItem key={td.id} value={td.id}>{td.nombre}</MenuItem>
                ))}
              </TextField>
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="numero_documento" control={control} render={({ field }) => (
              <TextField
                {...field}
                fullWidth
                label="N° Documento"
                error={!!errors.numero_documento}
                helperText={errors.numero_documento?.message}
              />
            )} />
          </Grid>
          <Grid size={{ xs: 12, md: 5 }}>
            <Controller name="nombre_completo" control={control} render={({ field }) => (
              <TextField
                {...field}
                fullWidth
                label="Razón Social"
                error={!!errors.nombre_completo}
                helperText={errors.nombre_completo?.message}
              />
            )} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Controller name="contacto_nombre" control={control} render={({ field }) => (
              <TextField
                {...field}
                value={field.value || ''}
                fullWidth
                label="Nombre de Contacto"
              />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="email" control={control} render={({ field }) => (
              <TextField
                {...field}
                fullWidth
                label="Email"
                error={!!errors.email}
                helperText={errors.email?.message}
              />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 4 }}>
            <Controller name="telefono" control={control} render={({ field }) => (
              <TextField
                {...field}
                value={field.value || ''}
                fullWidth
                label="Teléfono"
              />
            )} />
          </Grid>
          <Grid size={{ xs: 12 }}>
            <Controller name="direccion" control={control} render={({ field }) => (
              <TextField
                {...field}
                value={field.value || ''}
                fullWidth
                label="Dirección"
                multiline
                rows={2}
              />
            )} />
          </Grid>
          <Grid size={{ xs: 6, md: 3 }}>
            <Controller name="activo" control={control} render={({ field }) => (
              <TextField
                {...field}
                value={field.value ? '1' : '0'}
                select
                fullWidth
                label="Activo"
                onChange={(e) => field.onChange(e.target.value === '1')}
              >
                <MenuItem value="1">Activo</MenuItem>
                <MenuItem value="0">Inactivo</MenuItem>
              </TextField>
            )} />
          </Grid>
        </Grid>
        <Box sx={{ mt: 3, display: 'flex', gap: 2 }}>
          <Button type="submit" variant="contained" disabled={mutation.isPending}>
            {mutation.isPending ? <CircularProgress size={20} /> : (isEdit ? 'Guardar Cambios' : 'Crear Proveedor')}
          </Button>
          <Button variant="outlined" onClick={() => navigate('/admin/proveedores')}>Cancelar</Button>
        </Box>
      </Box>
    </Box>
  );
}
