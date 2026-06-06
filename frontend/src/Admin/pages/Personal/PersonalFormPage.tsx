import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
  Box, Typography, Paper, Stepper, Step, StepLabel, Button, Grid, TextField,
  Switch, FormControlLabel, CircularProgress, Alert, Autocomplete,
  Divider, Chip, Checkbox, FormGroup, FormControl, InputAdornment, IconButton,
  FormHelperText,
} from '@mui/material';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import DeleteIcon from '@mui/icons-material/Delete';
import VisibilityIcon from '@mui/icons-material/Visibility';
import VisibilityOffIcon from '@mui/icons-material/VisibilityOff';
import RadioButtonCheckedIcon from '@mui/icons-material/RadioButtonChecked';
import RadioButtonUncheckedIcon from '@mui/icons-material/RadioButtonUnchecked';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '../../../shared/lib/zodResolver';
import { z } from 'zod';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { personalApi, catalogApi } from '../../../shared/api/endpoints';
import type {
  Sexo, EstadoCivil, PermisoAgrupado, DocumentoIdentidad,
} from '../../../shared/types';

const personalSchema = z
  .object({
    username: z.string().min(5, 'Mínimo 5 caracteres').max(32, 'Máximo 32 caracteres').regex(/^[a-zA-Z0-9._-]+$/, 'Solo letras, números, puntos, guiones y guion bajo'),
    apellido_paterno: z.string().min(1, 'El apellido paterno es obligatorio').max(100),
    apellido_materno: z.string().min(1, 'El apellido materno es obligatorio').max(100),
    nombres: z.string().min(1, 'El nombre es obligatorio').max(150),
    password: z.string().min(5, 'Mínimo 5 caracteres').max(32).optional().or(z.literal('')),
    password_confirmation: z.string().optional(),
    numero_documento: z.string().max(20).optional().nullable(),
    documento_identidad_id: z.number().nullable().optional(),
    sexo_id: z.number().nullable().optional(),
    estado_civil_id: z.number().nullable().optional(),
    fecha_nacimiento: z.string().optional().nullable(),
    email: z.string().email('Email inválido').optional().nullable().or(z.literal('')),
    pais_id: z.number().nullable().optional(),
    telefono: z.string().max(20).optional().nullable(),
    telefono_fijo: z.string().max(20).optional().nullable(),
    telefono_celular: z.string().max(20).optional().nullable(),
    departamento_id: z.number().nullable().optional(),
    provincia_id: z.number().nullable().optional(),
    ubigeo_id: z.number().nullable().optional(),
    direccion: z.string().max(500).optional().nullable(),
    referencia: z.string().max(500).optional().nullable(),
    foto_path: z.string().optional().nullable(),
    activo: z.boolean().optional(),
    roles: z.array(z.string()).max(1, 'Solo se permite un rol por usuario').optional(),
    permisos: z.array(z.number()).optional(),
    listas_precios: z.array(z.number()).optional(),
    almacenes: z.array(z.number()).optional(),
  })
  .refine((data) => !data.password || data.password === data.password_confirmation, {
    message: 'La confirmación no coincide',
    path: ['password_confirmation'],
  });

type PersonalForm = z.infer<typeof personalSchema>;

const STEPS = [
  'Datos Personales',
  'Identidad',
  'Contacto y Ubicación',
  'Permisos y Accesos',
  'Fotografía',
  'Confirmación',
] as const;

interface FormDataState extends Partial<PersonalForm> {
  roles?: string[];
  permisos?: number[];
  listas_precios?: number[];
  almacenes?: number[];
  fotoFile?: File | null;
  fotoPreview?: string | null;
}

const defaultValues: FormDataState = {
  username: '',
  apellido_paterno: '',
  apellido_materno: '',
  nombres: '',
  password: '',
  password_confirmation: '',
  numero_documento: '',
  documento_identidad_id: null,
  sexo_id: null,
  estado_civil_id: null,
  fecha_nacimiento: '',
  email: '',
  pais_id: 1,
  telefono: '',
  telefono_fijo: '',
  telefono_celular: '',
  departamento_id: null,
  provincia_id: null,
  ubigeo_id: null,
  direccion: '',
  referencia: '',
  activo: true,
  roles: [],
  permisos: [],
  listas_precios: [],
  almacenes: [],
};

function buildNombreCompleto(ap: string, am: string, nom: string): string {
  return [ap, am, nom].map((v) => (v ?? '').trim()).filter(Boolean).join(' ');
}

export default function PersonalFormPage() {
  const { id } = useParams();
  const isEdit = !!id;
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const [step, setStep] = useState(0);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [fotoFile, setFotoFile] = useState<File | null>(null);
  const [fotoPreview, setFotoPreview] = useState<string | null>(null);
  const [showPassword, setShowPassword] = useState(false);
  const [showPasswordConfirm, setShowPasswordConfirm] = useState(false);

  const { data: personalData, isLoading: loadingPersonal } = useQuery({
    queryKey: ['personal', id],
    queryFn: () => personalApi.find(Number(id)).then(r => r.data?.data),
    enabled: isEdit,
  });

  const { data: sexos } = useQuery<Sexo[]>({ queryKey: ['catalog', 'sexos'], queryFn: () => catalogApi.sexos().then((r) => (r.data?.data ?? r.data ?? []) as Sexo[]) });
  const { data: estadosCiviles } = useQuery<EstadoCivil[]>({ queryKey: ['catalog', 'estados-civil'], queryFn: () => catalogApi.estadosCivil().then((r) => (r.data?.data ?? r.data ?? []) as EstadoCivil[]) });
  const { data: documentosIdentidad } = useQuery<DocumentoIdentidad[]>({ queryKey: ['catalog', 'documentos-identidad'], queryFn: () => catalogApi.documentosIdentidad().then((r) => (r.data?.data ?? r.data ?? []) as DocumentoIdentidad[]) });
  const { data: paises } = useQuery<{ id: number; nombre: string }[]>({ queryKey: ['catalog', 'paises'], queryFn: () => catalogApi.paises().then((r) => (r.data?.data ?? r.data ?? []) as { id: number; nombre: string }[]) });
  const { data: departamentos } = useQuery<{ id: number; nombre: string }[]>({ queryKey: ['catalog', 'departamentos', 1], queryFn: () => catalogApi.departamentos().then((r) => (r.data?.data ?? r.data ?? []) as { id: number; nombre: string }[]) });
  const { data: listaPrecios } = useQuery<{ id: number; nombre: string }[]>({ queryKey: ['catalog', 'lista-precios'], queryFn: () => catalogApi.listaPrecios().then((r) => (r.data?.data ?? r.data ?? []) as { id: number; nombre: string }[]) });
  const { data: almacenes } = useQuery<{ id: number; nombre: string }[]>({ queryKey: ['catalog', 'almacenes'], queryFn: () => catalogApi.almacenes().then((r) => (r.data?.data ?? r.data ?? []) as { id: number; nombre: string }[]) });

  const { data: rolesDisponibles } = useQuery<{ id: number; name: string; descripcion: string }[]>({ queryKey: ['personal', 'roles-disponibles'], queryFn: () => personalApi.rolesDisponibles().then((r) => (r.data?.data ?? r.data ?? []) as { id: number; name: string; descripcion: string }[]) });
  const { data: permisosAgrupados } = useQuery<PermisoAgrupado[]>({ queryKey: ['personal', 'permisos-agrupados'], queryFn: () => personalApi.permisosAgrupados().then((r) => (r.data?.data ?? r.data ?? []) as PermisoAgrupado[]) });

  const [departamentoId, setDepartamentoId] = useState<number | null>(null);
  const { data: provincias } = useQuery({
    queryKey: ['catalog', 'provincias', departamentoId],
    queryFn: () => catalogApi.provincias(departamentoId ?? undefined).then(r => r.data?.data || r.data),
    enabled: !!departamentoId,
  });
  const [provinciaId, setProvinciaId] = useState<number | null>(null);
  const { data: ubigeos } = useQuery({
    queryKey: ['catalog', 'ubigeos', provinciaId],
    queryFn: () => catalogApi.ubigeos(provinciaId ?? undefined).then(r => r.data?.data || r.data),
    enabled: !!provinciaId,
  });

  const { control, handleSubmit, watch, setValue, getValues, reset, formState: { errors } } = useForm<FormDataState>({
    resolver: zodResolver(personalSchema),
    defaultValues,
  });

  useEffect(() => {
    if (personalData) {
      reset({
        ...defaultValues,
        ...personalData,
        password: '',
        password_confirmation: '',
        roles: personalData.roles ?? [],
      });
      if (personalData.departamento_id) setDepartamentoId(personalData.departamento_id);
      if (personalData.provincia_id) setProvinciaId(personalData.provincia_id);
      if (personalData.foto_url) setFotoPreview(personalData.foto_url);
    }
  }, [personalData, reset]);

  const createMutation = useMutation({
    mutationFn: (data: FormDataState) => {
      const payload: Record<string, unknown> = { ...data };
      if (!isEdit && !payload.password) delete payload.password;
      if (payload.password === '') delete payload.password;
      if (payload.password_confirmation === '') delete payload.password_confirmation;
      if (!isEdit) {
        return personalApi.create(payload);
      }
      return personalApi.update(Number(id), payload);
    },
    onSuccess: async (response: { data?: { data?: { id: number } } }) => {
      const newId = response?.data?.data?.id;
      if (fotoFile && newId) {
        try { await personalApi.uploadPhoto(newId, fotoFile); } catch { /* se ignora; se muestra error general */ }
      }
      queryClient.invalidateQueries({ queryKey: ['personal'] });
      queryClient.invalidateQueries({ queryKey: ['personal', id] });
      navigate('/admin/personal');
    },
    onError: (err: { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }) => {
      const msg = err.response?.data?.errors
        ? Object.values(err.response.data.errors).flat().join(' ')
        : err.response?.data?.message ?? 'Error al guardar el personal.';
      setSubmitError(msg);
    },
  });

  const handleNext = () => {
    if (step === 0) {
      const username = getValues('username');
      const ap = getValues('apellido_paterno');
      const am = getValues('apellido_materno');
      const nom = getValues('nombres');
      if (!username || !ap || !am || !nom) {
        setSubmitError('Username, apellido paterno, apellido materno y nombres son obligatorios.');
        return;
      }
    }
    setSubmitError(null);
    setStep((s) => Math.min(s + 1, STEPS.length - 1));
  };
  const handleBack = () => setStep((s) => Math.max(s - 1, 0));

  const onSubmit = (data: FormDataState) => createMutation.mutate(data);

  const onFotoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
      setSubmitError('La imagen no debe superar los 2MB.');
      return;
    }
    if (!['image/png', 'image/jpeg'].includes(file.type)) {
      setSubmitError('Solo se permiten formatos: png, jpg, jpeg.');
      return;
    }
    setFotoFile(file);
    setFotoPreview(URL.createObjectURL(file));
    setSubmitError(null);
  };

  const onRemoveFoto = () => {
    setFotoFile(null);
    setFotoPreview(null);
  };

  const watched = watch();
  const selectedRoles: string[] = (watched.roles ?? []).slice(0, 1);
  const selectedPermisos: number[] = watched.permisos ?? [];
  const selectedListas: number[] = watched.listas_precios ?? [];
  const selectedAlmacenes: number[] = watched.almacenes ?? [];

  const togglePermiso = (permisoId: number) => {
    const current = (getValues('permisos') ?? []) as number[];
    const exists = current.includes(permisoId);
    const updated = exists ? current.filter((v) => v !== permisoId) : [...current, permisoId];
    setValue('permisos', updated, { shouldDirty: true });
  };

  const toggleLista = (id: number) => {
    const current = (getValues('listas_precios') ?? []) as number[];
    const exists = current.includes(id);
    const updated = exists ? current.filter((v) => v !== id) : [...current, id];
    setValue('listas_precios', updated, { shouldDirty: true });
  };

  const toggleAlmacen = (id: number) => {
    const current = (getValues('almacenes') ?? []) as number[];
    const exists = current.includes(id);
    const updated = exists ? current.filter((v) => v !== id) : [...current, id];
    setValue('almacenes', updated, { shouldDirty: true });
  };

  const selectedDocIdentidad = documentosIdentidad?.find((d) => d.id === watched.documento_identidad_id) ?? null;
  const docMaxLength = selectedDocIdentidad?.longitud ? Number(selectedDocIdentidad.longitud) : 20;

  const computedNombreCompleto = buildNombreCompleto(
    watched.apellido_paterno ?? '',
    watched.apellido_materno ?? '',
    watched.nombres ?? '',
  );

  if (isEdit && loadingPersonal) {
    return <Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}><CircularProgress /></Box>;
  }

  return (
    <Box>
      <Typography variant="h5" mb={2}>{isEdit ? 'Editar Personal' : 'Nuevo Personal'}</Typography>

      <Stepper activeStep={step} sx={{ mb: 3 }}>
        {STEPS.map((label) => (
          <Step key={label}><StepLabel>{label}</StepLabel></Step>
        ))}
      </Stepper>

      {submitError && <Alert severity="error" sx={{ mb: 2 }} onClose={() => setSubmitError(null)}>{submitError}</Alert>}

      <Paper sx={{ p: 3 }}>
        <Box component="form" onSubmit={handleSubmit(onSubmit)} noValidate>
          {step === 0 && (
            <Box>
              <Typography variant="h6" mb={2}>Datos Personales</Typography>
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="username" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Username" required error={!!errors.username} helperText={errors.username?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="apellido_paterno" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Apellido Paterno" required error={!!errors.apellido_paterno} helperText={errors.apellido_paterno?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="apellido_materno" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Apellido Materno" required error={!!errors.apellido_materno} helperText={errors.apellido_materno?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="nombres" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Nombres" required error={!!errors.nombres} helperText={errors.nombres?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Controller name="password" control={control} render={({ field }) => (
                    <TextField
                      {...field}
                      type={showPassword ? 'text' : 'password'}
                      fullWidth
                      label={isEdit ? 'Nueva Contraseña (opcional)' : 'Contraseña'}
                      required={!isEdit}
                      error={!!errors.password}
                      helperText={errors.password?.message ?? 'Mínimo 5 caracteres'}
                      InputProps={{
                        endAdornment: (
                          <InputAdornment position="end">
                            <IconButton
                              onClick={() => setShowPassword((s) => !s)}
                              onMouseDown={(e) => e.preventDefault()}
                              edge="end"
                              aria-label={showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                            >
                              {showPassword ? <VisibilityOffIcon /> : <VisibilityIcon />}
                            </IconButton>
                          </InputAdornment>
                        ),
                      }}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Controller name="password_confirmation" control={control} render={({ field }) => (
                    <TextField
                      {...field}
                      type={showPasswordConfirm ? 'text' : 'password'}
                      fullWidth
                      label="Confirmar Contraseña"
                      required={!isEdit}
                      error={!!errors.password_confirmation}
                      helperText={errors.password_confirmation?.message}
                      InputProps={{
                        endAdornment: (
                          <InputAdornment position="end">
                            <IconButton
                              onClick={() => setShowPasswordConfirm((s) => !s)}
                              onMouseDown={(e) => e.preventDefault()}
                              edge="end"
                              aria-label={showPasswordConfirm ? 'Ocultar confirmación' : 'Mostrar confirmación'}
                            >
                              {showPasswordConfirm ? <VisibilityOffIcon /> : <VisibilityIcon />}
                            </IconButton>
                          </InputAdornment>
                        ),
                      }}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12 }}>
                  <Controller name="activo" control={control} render={({ field }) => (
                    <FormControlLabel control={<Switch {...field} checked={!!field.value} />} label="Activo" />
                  )} />
                </Grid>
              </Grid>
            </Box>
          )}

          {step === 1 && (
            <Box>
              <Typography variant="h6" mb={2}>Identidad</Typography>
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Controller name="documento_identidad_id" control={control} render={({ field }) => (
                    <Autocomplete<DocumentoIdentidad>
                      options={documentosIdentidad ?? []}
                      getOptionLabel={(o) => o ? `${o.codigo} - ${o.nombre}` : ''}
                      isOptionEqualToValue={(o, v) => o?.id === v?.id}
                      value={documentosIdentidad?.find((d) => d.id === field.value) ?? null}
                      onChange={(_, v) => field.onChange(v?.id ?? null)}
                      renderInput={(params) => (
                        <TextField
                          {...params}
                          label="Tipo de Documento"
                          error={!!errors.documento_identidad_id}
                          helperText={errors.documento_identidad_id?.message ?? 'Catálogo oficial SUNAT/INEI'}
                          placeholder="DNI, CE, Pasaporte, RUC..."
                        />
                      )}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Controller name="numero_documento" control={control} render={({ field }) => (
                    <TextField
                      {...field}
                      fullWidth
                      label="Número de Documento"
                      error={!!errors.numero_documento}
                      helperText={errors.numero_documento?.message ?? (selectedDocIdentidad ? `Longitud: ${docMaxLength} caracteres` : '')}
                      inputProps={{ maxLength: docMaxLength }}
                      disabled={!selectedDocIdentidad}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="sexo_id" control={control} render={({ field }) => (
                    <Autocomplete<Sexo>
                      options={sexos ?? []}
                      getOptionLabel={(o) => o?.nombre ?? ''}
                      isOptionEqualToValue={(o, v) => o?.id === v?.id}
                      value={sexos?.find((s) => s.id === field.value) ?? null}
                      onChange={(_, v) => field.onChange(v?.id ?? null)}
                      renderInput={(params) => <TextField {...params} label="Sexo" />}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="estado_civil_id" control={control} render={({ field }) => (
                    <Autocomplete<EstadoCivil>
                      options={estadosCiviles ?? []}
                      getOptionLabel={(o) => o?.nombre ?? ''}
                      isOptionEqualToValue={(o, v) => o?.id === v?.id}
                      value={estadosCiviles?.find((s) => s.id === field.value) ?? null}
                      onChange={(_, v) => field.onChange(v?.id ?? null)}
                      renderInput={(params) => (
                        <TextField
                          {...params}
                          label="Estado Civil"
                          helperText="Catálogo oficial SUNAT"
                        />
                      )}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="fecha_nacimiento" control={control} render={({ field }) => (
                    <TextField {...field} type="date" fullWidth label="Fecha de Nacimiento" InputLabelProps={{ shrink: true }} />
                  )} />
                </Grid>
              </Grid>
            </Box>
          )}

          {step === 2 && (
            <Box>
              <Typography variant="h6" mb={2}>Contacto y Ubicación</Typography>
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="email" control={control} render={({ field }) => (
                    <TextField {...field} type="email" fullWidth label="Email" error={!!errors.email} helperText={errors.email?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="telefono" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Teléfono" error={!!errors.telefono} helperText={errors.telefono?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="telefono_celular" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Celular" error={!!errors.telefono_celular} helperText={errors.telefono_celular?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="pais_id" control={control} render={({ field }) => (
                    <Autocomplete<{ id: number; nombre: string }>
                      options={paises ?? []}
                      getOptionLabel={(o) => o?.nombre ?? ''}
                      isOptionEqualToValue={(o, v) => o?.id === v?.id}
                      value={paises?.find((p) => p.id === field.value) ?? null}
                      onChange={(_, v) => field.onChange(v?.id ?? null)}
                      renderInput={(params) => <TextField {...params} label="País" />}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Autocomplete
                    options={departamentos ?? []}
                    getOptionLabel={(o) => o?.nombre ?? ''}
                    isOptionEqualToValue={(o, v) => o?.id === v?.id}
                    value={departamentos?.find((d) => d.id === departamentoId) ?? null}
                    onChange={(_, v) => { setDepartamentoId(v?.id ?? null); setValue('departamento_id', v?.id ?? null); setValue('provincia_id', null); setValue('ubigeo_id', null); setProvinciaId(null); }}
                    renderInput={(params) => <TextField {...params} label="Departamento" />}
                  />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Autocomplete
                    options={(provincias as { id: number; nombre: string }[] | undefined) ?? []}
                    getOptionLabel={(o) => o?.nombre ?? ''}
                    isOptionEqualToValue={(o, v) => o?.id === v?.id}
                    value={(provincias as { id: number; nombre: string }[] | undefined)?.find((p) => p.id === provinciaId) ?? null}
                    onChange={(_, v) => { setProvinciaId(v?.id ?? null); setValue('provincia_id', v?.id ?? null); setValue('ubigeo_id', null); }}
                    disabled={!departamentoId}
                    renderInput={(params) => <TextField {...params} label="Provincia" />}
                  />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="ubigeo_id" control={control} render={({ field }) => (
                    <Autocomplete<{ id: number; nombre: string }>
                      options={(ubigeos as { id: number; nombre: string }[] | undefined) ?? []}
                      getOptionLabel={(o) => o?.nombre ?? ''}
                      isOptionEqualToValue={(o, v) => o?.id === v?.id}
                      value={(ubigeos as { id: number; nombre: string }[] | undefined)?.find((u) => u.id === field.value) ?? null}
                      onChange={(_, v) => field.onChange(v?.id ?? null)}
                      disabled={!provinciaId}
                      renderInput={(params) => <TextField {...params} label="Distrito" />}
                    />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 8 }}>
                  <Controller name="direccion" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Dirección" error={!!errors.direccion} helperText={errors.direccion?.message} />
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Controller name="referencia" control={control} render={({ field }) => (
                    <TextField {...field} fullWidth label="Referencia" error={!!errors.referencia} helperText={errors.referencia?.message} />
                  )} />
                </Grid>
              </Grid>
            </Box>
          )}

          {step === 3 && (
            <Box>
              <Typography variant="h6" mb={2}>Permisos y Accesos</Typography>
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Controller name="roles" control={control} render={({ field }) => (
                    <FormControl error={!!errors.roles} fullWidth>
                      <Typography variant="subtitle1" gutterBottom>Rol (uno solo)</Typography>
                      <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.5, border: '1px solid', borderColor: 'divider', p: 1, borderRadius: 1, maxHeight: 220, overflow: 'auto' }}>
                        {(rolesDisponibles ?? []).map((rol) => {
                          const checked = (field.value ?? []).includes(rol.name);
                          return (
                            <Box
                              key={rol.id}
                              onClick={() => field.onChange(checked ? [] : [rol.name])}
                              sx={{ display: 'flex', alignItems: 'center', gap: 1, p: 0.75, cursor: 'pointer', borderRadius: 1, '&:hover': { bgcolor: 'action.hover' }, bgcolor: checked ? 'primary.50' : 'transparent' }}
                            >
                              {checked ? <RadioButtonCheckedIcon color="primary" fontSize="small" /> : <RadioButtonUncheckedIcon fontSize="small" />}
                              <Typography variant="body2">{rol.descripcion || rol.name}</Typography>
                            </Box>
                          );
                        })}
                        {(rolesDisponibles ?? []).length === 0 && (
                          <Typography variant="body2" color="text.secondary" sx={{ p: 1 }}>No hay roles disponibles.</Typography>
                        )}
                      </Box>
                      {errors.roles && <FormHelperText>{(errors.roles as { message?: string })?.message}</FormHelperText>}
                      <FormHelperText>Seleccione un único rol para el usuario.</FormHelperText>
                    </FormControl>
                  )} />
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Typography variant="subtitle1" gutterBottom>Listas de Precios</Typography>
                  <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, border: '1px solid', borderColor: 'divider', p: 2, borderRadius: 1 }}>
                    {(listaPrecios ?? []).map((lp) => (
                      <Chip
                        key={lp.id}
                        label={lp.nombre}
                        color={selectedListas.includes(lp.id) ? 'secondary' : 'default'}
                        onClick={() => toggleLista(lp.id)}
                        variant={selectedListas.includes(lp.id) ? 'filled' : 'outlined'}
                      />
                    ))}
                    {(listaPrecios ?? []).length === 0 && (
                      <Typography variant="body2" color="text.secondary">Sin listas configuradas.</Typography>
                    )}
                  </Box>
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Typography variant="subtitle1" gutterBottom>Almacenes</Typography>
                  <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, border: '1px solid', borderColor: 'divider', p: 2, borderRadius: 1 }}>
                    {(almacenes ?? []).map((al) => (
                      <Chip
                        key={al.id}
                        label={al.nombre}
                        color={selectedAlmacenes.includes(al.id) ? 'success' : 'default'}
                        onClick={() => toggleAlmacen(al.id)}
                        variant={selectedAlmacenes.includes(al.id) ? 'filled' : 'outlined'}
                      />
                    ))}
                    {(almacenes ?? []).length === 0 && (
                      <Typography variant="body2" color="text.secondary">Sin almacenes configurados.</Typography>
                    )}
                  </Box>
                </Grid>
                <Grid size={{ xs: 12 }}>
                  <Divider sx={{ my: 1 }} />
                  <Typography variant="subtitle1" gutterBottom>Permisos Directos (adicionales al rol)</Typography>
                  {(permisosAgrupados ?? []).map((grupo) => (
                    <Box key={grupo.modulo} sx={{ mb: 2, border: '1px solid', borderColor: 'divider', p: 2, borderRadius: 1 }}>
                      <Typography variant="subtitle2" gutterBottom>{grupo.modulo}</Typography>
                      <FormGroup row>
                        {grupo.permisos.map((permiso) => (
                          <FormControlLabel
                            key={permiso.id}
                            control={
                              <Checkbox
                                checked={selectedPermisos.includes(permiso.id)}
                                onChange={() => togglePermiso(permiso.id)}
                              />
                            }
                            label={permiso.descripcion || permiso.name}
                          />
                        ))}
                      </FormGroup>
                    </Box>
                  ))}
                </Grid>
              </Grid>
            </Box>
          )}

          {step === 4 && (
            <Box>
              <Typography variant="h6" mb={2}>Fotografía</Typography>
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 4 }}>
                  <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', border: '2px dashed', borderColor: 'divider', p: 3, borderRadius: 2 }}>
                    {fotoPreview ? (
                      <Box sx={{ position: 'relative', mb: 2 }}>
                        <img src={fotoPreview} alt="Foto" style={{ maxWidth: '100%', maxHeight: 200, borderRadius: 8 }} />
                      </Box>
                    ) : (
                      <Box sx={{ width: 200, height: 200, bgcolor: '#f5f5f5', display: 'flex', alignItems: 'center', justifyContent: 'center', mb: 2, borderRadius: '50%' }}>
                        <Typography color="text.secondary">Sin foto</Typography>
                      </Box>
                    )}
                    <Button component="label" startIcon={<CloudUploadIcon />} variant="outlined">
                      {fotoPreview ? 'Cambiar foto' : 'Subir foto'}
                      <input type="file" hidden accept="image/png,image/jpeg" onChange={onFotoChange} />
                    </Button>
                    {fotoPreview && (
                      <Button startIcon={<DeleteIcon />} color="error" sx={{ mt: 1 }} onClick={onRemoveFoto}>
                        Quitar foto
                      </Button>
                    )}
                    <Typography variant="caption" sx={{ mt: 1, color: 'text.secondary' }}>
                      Formatos: PNG, JPG, JPEG. Tamaño máx.: 2MB.
                    </Typography>
                  </Box>
                </Grid>
              </Grid>
            </Box>
          )}

          {step === 5 && (
            <Box>
              <Typography variant="h6" mb={2}>Confirmación</Typography>
              <Grid container spacing={2}>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Paper variant="outlined" sx={{ p: 2 }}>
                    <Typography variant="subtitle1" gutterBottom>Datos Personales</Typography>
                    <Typography variant="body2"><b>Username:</b> {watched.username || '—'}</Typography>
                    <Typography variant="body2"><b>Apellido Paterno:</b> {watched.apellido_paterno || '—'}</Typography>
                    <Typography variant="body2"><b>Apellido Materno:</b> {watched.apellido_materno || '—'}</Typography>
                    <Typography variant="body2"><b>Nombres:</b> {watched.nombres || '—'}</Typography>
                    <Typography variant="body2"><b>Nombre Completo:</b> {computedNombreCompleto || '—'}</Typography>
                    <Typography variant="body2"><b>Email:</b> {watched.email || '—'}</Typography>
                    <Typography variant="body2"><b>Activo:</b> {watched.activo ? 'Sí' : 'No'}</Typography>
                  </Paper>
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Paper variant="outlined" sx={{ p: 2 }}>
                    <Typography variant="subtitle1" gutterBottom>Identidad</Typography>
                    <Typography variant="body2"><b>Tipo de Documento:</b> {selectedDocIdentidad ? `${selectedDocIdentidad.codigo} - ${selectedDocIdentidad.nombre}` : '—'}</Typography>
                    <Typography variant="body2"><b>Número:</b> {watched.numero_documento || '—'}</Typography>
                    <Typography variant="body2"><b>Sexo:</b> {sexos?.find((s) => s.id === watched.sexo_id)?.nombre ?? '—'}</Typography>
                    <Typography variant="body2"><b>Estado Civil:</b> {estadosCiviles?.find((s) => s.id === watched.estado_civil_id)?.nombre ?? '—'}</Typography>
                    <Typography variant="body2"><b>Fecha de Nacimiento:</b> {watched.fecha_nacimiento || '—'}</Typography>
                  </Paper>
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Paper variant="outlined" sx={{ p: 2 }}>
                    <Typography variant="subtitle1" gutterBottom>Contacto</Typography>
                    <Typography variant="body2"><b>Celular:</b> {watched.telefono_celular || '—'}</Typography>
                    <Typography variant="body2"><b>Dirección:</b> {watched.direccion || '—'}</Typography>
                  </Paper>
                </Grid>
                <Grid size={{ xs: 12, md: 6 }}>
                  <Paper variant="outlined" sx={{ p: 2 }}>
                    <Typography variant="subtitle1" gutterBottom>Permisos</Typography>
                    <Typography variant="body2"><b>Rol:</b> {selectedRoles.length > 0 ? (rolesDisponibles?.find((r) => r.name === selectedRoles[0])?.descripcion || selectedRoles[0]) : '—'}</Typography>
                    <Typography variant="body2"><b>Permisos directos:</b> {selectedPermisos.length}</Typography>
                    <Typography variant="body2"><b>Listas de precios:</b> {selectedListas.length}</Typography>
                    <Typography variant="body2"><b>Almacenes:</b> {selectedAlmacenes.length}</Typography>
                  </Paper>
                </Grid>
              </Grid>
            </Box>
          )}

          <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 3 }}>
            <Button disabled={step === 0} onClick={handleBack}>Atrás</Button>
            <Box>
              {step < STEPS.length - 1 ? (
                <Button variant="contained" onClick={handleNext}>Siguiente</Button>
              ) : (
                <Button type="submit" variant="contained" color="success" disabled={createMutation.isPending}>
                  {createMutation.isPending ? <CircularProgress size={20} /> : 'Guardar'}
                </Button>
              )}
            </Box>
          </Box>
        </Box>
      </Paper>
    </Box>
  );
}
