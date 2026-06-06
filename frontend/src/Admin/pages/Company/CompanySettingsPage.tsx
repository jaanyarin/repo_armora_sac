import { useState, useEffect, useMemo } from 'react';
import {
  Box, Typography, TextField, Button, Grid, Tabs, Tab, Card, CardContent,
  Switch, FormControlLabel, CircularProgress, Alert, Snackbar, Autocomplete,
} from '@mui/material';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { empresaApi, catalogApi } from '../../../shared/api/endpoints';
import type { EmpresaConfig, Pais, Departamento, Provincia, Ubigeo } from '../../../shared/types';

const TIPO_IMAGENES = ['login', 'home', 'reporte', 'firma'] as const;

function TabPanel({ children, value, index }: { children: React.ReactNode; value: number; index: number }) {
  return value === index ? <Box sx={{ py: 2 }}>{children}</Box> : null;
}

export default function CompanySettingsPage() {
  const [tab, setTab] = useState(0);

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const [form, setForm] = useState<Record<string, any>>({});
  const [formInitialized, setFormInitialized] = useState(false);
  const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false, message: '', severity: 'success',
  });

  const [paises, setPaises] = useState<Pais[]>([]);
  const [departamentos, setDepartamentos] = useState<Departamento[]>([]);
  const [provincias, setProvincias] = useState<Provincia[]>([]);
  const [ubigeos, setUbigeos] = useState<Ubigeo[]>([]);

  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['empresa-config'],
    queryFn: () => empresaApi.get().then(r => r.data),
  });

  const updateMutation = useMutation({
    mutationFn: ({ payload, seccion }: { payload: Record<string, unknown>; seccion?: 'empresa' }) =>
      empresaApi.update(seccion ? { ...payload, __seccion: seccion } : payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['empresa-config'] });
      setSnackbar({ open: true, message: 'Configuración guardada correctamente.', severity: 'success' });
    },
    onError: (err: unknown) => {
      const ax = err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
      const msg = ax?.response?.data?.errors
        ? Object.values(ax.response.data.errors).flat().join(' ')
        : ax?.response?.data?.message ?? 'Error al guardar la configuración.';
      setSnackbar({ open: true, message: msg, severity: 'error' });
    },
  });

  const uploadMutation = useMutation({
    mutationFn: ({ tipo, file }: { tipo: string; file: File }) => empresaApi.uploadImage(tipo, file),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['empresa-config'] });
      setSnackbar({ open: true, message: 'Imagen actualizada correctamente.', severity: 'success' });
    },
    onError: () => {
      setSnackbar({ open: true, message: 'Error al subir la imagen.', severity: 'error' });
    },
  });

  const resetImageMutation = useMutation({
    mutationFn: (tipo: string) => empresaApi.resetImage(tipo),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['empresa-config'] });
      setSnackbar({ open: true, message: 'Imagen restablecida a valor por defecto.', severity: 'success' });
    },
    onError: () => {
      setSnackbar({ open: true, message: 'Error al restablecer la imagen.', severity: 'error' });
    },
  });

  useEffect(() => {
    catalogApi.paises().then(r => setPaises(r.data));
    catalogApi.departamentos().then(r => setDepartamentos(r.data));
  }, []);

  useEffect(() => {
    if (!form.departamento_id) {
      setProvincias([]);
      return;
    }
    const depId = form.departamento_id;
    const controller = new AbortController();
    catalogApi.provincias(depId, { signal: controller.signal })
      .then(r => { if (!controller.signal.aborted) setProvincias(Array.isArray(r.data) ? r.data : []); })
      .catch(err => {
        if (controller.signal.aborted) return;
        console.error('[cascada] provincias fetch error:', err);
        setProvincias([]);
      });
    return () => { controller.abort(); };
  }, [form.departamento_id]);

  useEffect(() => {
    if (!form.provincia_id) {
      setUbigeos([]);
      return;
    }
    const provId = form.provincia_id;
    const controller = new AbortController();
    catalogApi.ubigeos(provId, { signal: controller.signal })
      .then(r => { if (!controller.signal.aborted) setUbigeos(Array.isArray(r.data) ? r.data : []); })
      .catch(err => {
        if (controller.signal.aborted) return;
        console.error('[cascada] ubigeos fetch error:', err);
        setUbigeos([]);
      });
    return () => { controller.abort(); };
  }, [form.provincia_id]);

  useEffect(() => {
    if (!data || formInitialized) return;
    const config = data as EmpresaConfig;
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setForm({
      razon_social: config.razon_social ?? '',
      nombre_comercial: config.nombre_comercial ?? '',
      ruc: config.ruc ?? '',
      email: config.email ?? '',
      pais_id: config.pais_id ?? null,
      telefono_fijo: config.telefono_fijo ?? '',
      telefono_celular: config.telefono_celular ?? '',
      departamento_id: config.departamento_id ?? null,
      provincia_id: config.provincia_id ?? null,
      ubigeo_id: config.ubigeo_id ?? null,
      direccion: config.direccion ?? '',
      referencia: config.referencia ?? '',
      porcentaje_igv: config.porcentaje_igv ?? 18,
      boleta_monto_dni: config.boleta_monto_dni ?? '',
      envio_auto_sunat: config.envio_auto_sunat,
      consolidado_requerimientos: config.consolidado_requerimientos,
      consolidado_liquidaciones: config.consolidado_liquidaciones,
      resumen_liquidacion: config.resumen_liquidacion,
      preventa_nota_pedido: config.preventa_nota_pedido,
      periodo_fecha_inicio: config.periodo_fecha_inicio ?? '',
      periodo_fecha_fin: config.periodo_fecha_fin ?? '',
      comision_defecto: config.comision_defecto ?? '',
      hora_cierre: config.hora_cierre ?? '',
      comision_neto: config.comision_neto,
      preview_fecha_inicio: config.preview_fecha_inicio ?? '',
      preview_fecha_fin: config.preview_fecha_fin ?? '',
      preview: config.preview,
      ventas_bloqueadas: config.ventas_bloqueadas,
      compras_bloqueadas: config.compras_bloqueadas,
    });
    setFormInitialized(true);
  }, [data, formInitialized]);

  const decimalesMutation = useMutation({
    mutationFn: () => empresaApi.actualizarDecimales(),
    onSuccess: (res: { data: { products_actualizados: number; stocks_actualizados: number } }) => {
      const d = res.data;
      setSnackbar({
        open: true,
        message: `Decimales actualizados: ${d.products_actualizados} productos, ${d.stocks_actualizados} stocks.`,
        severity: 'success',
      });
    },
    onError: () => {
      setSnackbar({ open: true, message: 'Error al actualizar decimales.', severity: 'error' });
    },
  });

  const handleChange = (field: string, value: unknown) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const handleSave = (sections: string[], overrides?: Record<string, unknown>, seccion?: 'empresa') => {
    const payload: Record<string, unknown> = {};
    for (const key of sections) {
      payload[key] = key in form ? form[key] : null;
    }
    if (overrides) Object.assign(payload, overrides);
    updateMutation.mutate({ payload, seccion });
  };

  const paisSel = useMemo(
    () => paises.find(p => p.id === form.pais_id) ?? null,
    [paises, form.pais_id],
  );
  const departamentoSel = useMemo(
    () => departamentos.find(d => d.id === form.departamento_id) ?? null,
    [departamentos, form.departamento_id],
  );
  const provinciaSel = useMemo(
    () => provincias.find(p => p.id === form.provincia_id) ?? null,
    [provincias, form.provincia_id],
  );
  const ubigeoSel = useMemo(
    () => ubigeos.find(u => u.id === form.ubigeo_id) ?? null,
    [ubigeos, form.ubigeo_id],
  );

  const handlePaisChange = (_: unknown, value: Pais | null) => {
    setForm(prev => ({
      ...prev,
      pais_id: value?.id ?? null,
      departamento_id: null,
      provincia_id: null,
      ubigeo_id: null,
    }));
  };

  const handleDepartamentoChange = (_: unknown, value: Departamento | null) => {
    setForm(prev => ({
      ...prev,
      departamento_id: value?.id ?? null,
      provincia_id: null,
      ubigeo_id: null,
    }));
  };

  const handleProvinciaChange = (_: unknown, value: Provincia | null) => {
    setForm(prev => ({
      ...prev,
      provincia_id: value?.id ?? null,
      ubigeo_id: null,
    }));
  };

  const handleUbigeoChange = (_: unknown, value: Ubigeo | null) => {
    handleChange('ubigeo_id', value?.id ?? null);
  };

  const handleImageUpload = (tipo: string, file: File | null) => {
    if (file) uploadMutation.mutate({ tipo, file });
  };

  const config = data as EmpresaConfig | undefined;

  if (isLoading) {
    return <Box sx={{ display: 'flex', justifyContent: 'center', p: 4 }}><CircularProgress /></Box>;
  }

  return (
    <Box>
      <Typography variant="h5" sx={{ mb: 2 }}>Configuración de Empresa</Typography>

      <Tabs value={tab} onChange={(_, v) => setTab(v)} sx={{ mb: 2 }}>
        <Tab label="Empresa" />
        <Tab label="Parámetros" />
        <Tab label="Imágenes" />
        <Tab label="Bloqueo" />
      </Tabs>

      <TabPanel value={tab} index={0}>
        <Grid container spacing={2}>
          {/* Fila 1 */}
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth size="small" required label="Razón Social" value={form.razon_social ?? ''}
              onChange={e => handleChange('razon_social', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth size="small" label="Nombre Comercial" value={form.nombre_comercial ?? ''}
              onChange={e => handleChange('nombre_comercial', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth size="small" required label="RUC" value={form.ruc ?? ''} inputProps={{ maxLength: 11 }}
              onChange={e => handleChange('ruc', e.target.value)} />
          </Grid>

          {/* Fila 2 */}
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth size="small" required label="Email" type="email" value={form.email ?? ''}
              onChange={e => handleChange('email', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <Autocomplete
              size="small"
              options={paises}
              getOptionLabel={o => o.nombre}
              value={paisSel}
              onChange={handlePaisChange}
              isOptionEqualToValue={(o, v) => o.id === v.id}
              renderInput={params => <TextField {...params} label="País" />}
            />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth size="small" label="Teléfono Fijo" value={form.telefono_fijo ?? ''}
              onChange={e => handleChange('telefono_fijo', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth size="small" required label="Celular" value={form.telefono_celular ?? ''}
              onChange={e => handleChange('telefono_celular', e.target.value)} />
          </Grid>

          {/* Fila 3 */}
          <Grid size={{ xs: 12, md: 4 }}>
            <Autocomplete
              size="small"
              options={departamentos}
              getOptionLabel={o => o.nombre}
              value={departamentoSel}
              onChange={handleDepartamentoChange}
              isOptionEqualToValue={(o, v) => o.id === v.id}
              renderInput={params => <TextField {...params} required label="Departamento" />}
            />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Autocomplete
              size="small"
              options={provincias}
              getOptionLabel={o => o.nombre}
              value={provinciaSel}
              onChange={handleProvinciaChange}
              isOptionEqualToValue={(o, v) => o.id === v.id}
              disabled={!form.departamento_id}
              renderInput={params => <TextField {...params} required label="Provincia" />}
            />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Autocomplete
              size="small"
              options={ubigeos}
              getOptionLabel={o => o.nombre}
              value={ubigeoSel}
              onChange={handleUbigeoChange}
              isOptionEqualToValue={(o, v) => o.id === v.id}
              disabled={!form.provincia_id}
              renderInput={params => <TextField {...params} required label="Distrito" />}
            />
          </Grid>

          {/* Fila 4 */}
          <Grid size={{ xs: 12 }}>
            <TextField fullWidth size="small" required label="Dirección" value={form.direccion ?? ''}
              onChange={e => handleChange('direccion', e.target.value)} />
          </Grid>

          {/* Fila 5 */}
          <Grid size={{ xs: 12 }}>
            <TextField fullWidth size="small" label="Referencia" value={form.referencia ?? ''}
              onChange={e => handleChange('referencia', e.target.value)} />
          </Grid>

          {/* Fila 6 */}
          <Grid size={{ xs: 12 }} sx={{ display: 'flex', justifyContent: 'flex-end' }}>
            <Button variant="contained" onClick={() => handleSave([
              'razon_social', 'nombre_comercial', 'ruc', 'email', 'pais_id',
              'telefono_fijo', 'telefono_celular', 'departamento_id', 'provincia_id',
              'ubigeo_id', 'direccion', 'referencia',
            ], undefined, 'empresa')}>Guardar</Button>
          </Grid>
        </Grid>
      </TabPanel>

      <TabPanel value={tab} index={1}>
        <Typography variant="subtitle1" sx={{ fontWeight: 600, mb: 1 }}>Parámetros de Ventas</Typography>
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={12} sm={4}>
            <TextField fullWidth size="small" label="Valor IGV (%)" type="number" inputProps={{ min: 0, max: 100, step: 0.1 }}
              value={form.porcentaje_igv ?? ''}
              onChange={e => handleChange('porcentaje_igv', e.target.value)} />
          </Grid>
          <Grid item xs={12} sm={4}>
            <TextField fullWidth size="small" label="Monto boleta DNI" type="number" inputProps={{ min: 0, step: 0.1 }}
              value={form.boleta_monto_dni ?? ''}
              onChange={e => handleChange('boleta_monto_dni', e.target.value)} />
          </Grid>
          <Grid item xs={12} sm={4} sx={{ display: 'flex', alignItems: 'center' }}>
            <FormControlLabel control={<Switch checked={form.envio_auto_sunat ?? false}
              onChange={e => handleChange('envio_auto_sunat', e.target.checked)} />}
              label="Envío Auto SUNAT" />
          </Grid>
        </Grid>

        <Typography variant="subtitle1" sx={{ fontWeight: 600, mb: 1 }}>Parámetros Reportes</Typography>
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={12} sm={6}>
            <FormControlLabel control={<Switch checked={form.consolidado_requerimientos ?? false}
              onChange={e => handleChange('consolidado_requerimientos', e.target.checked)} />}
              label="Consolidados Requerimientos" />
          </Grid>
          <Grid item xs={12} sm={6}>
            <FormControlLabel control={<Switch checked={form.consolidado_liquidaciones ?? false}
              onChange={e => handleChange('consolidado_liquidaciones', e.target.checked)} />}
              label="Consolidados Liquidaciones" />
          </Grid>
          <Grid item xs={12} sm={6}>
            <FormControlLabel control={<Switch checked={form.resumen_liquidacion ?? true}
              onChange={e => handleChange('resumen_liquidacion', e.target.checked)} />}
              label="Liquidación sin devoluciones vacías" />
          </Grid>
          <Grid item xs={12} sm={6}>
            <FormControlLabel control={<Switch checked={form.preventa_nota_pedido ?? true}
              onChange={e => handleChange('preventa_nota_pedido', e.target.checked)} />}
              label="Habilitar Preventas Notas Pedido" />
          </Grid>
        </Grid>

        <Typography variant="subtitle1" sx={{ fontWeight: 600, mb: 1 }}>Periodo de Ventas</Typography>
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={12} sm={6}>
            <TextField fullWidth size="small" label="Inicio de Periodo" type="date" InputLabelProps={{ shrink: true }}
              value={form.periodo_fecha_inicio ?? ''}
              onChange={e => handleChange('periodo_fecha_inicio', e.target.value)} />
          </Grid>
          <Grid item xs={12} sm={6}>
            <TextField fullWidth size="small" label="Fin de Periodo" type="date" InputLabelProps={{ shrink: true }}
              value={form.periodo_fecha_fin ?? ''}
              onChange={e => handleChange('periodo_fecha_fin', e.target.value)} />
          </Grid>
        </Grid>

        <Typography variant="subtitle1" sx={{ fontWeight: 600, mb: 1 }}>Comisiones</Typography>
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={12} sm={4}>
            <TextField fullWidth size="small" label="Comisión por defecto (%)" type="number" inputProps={{ min: 0, step: 0.01 }}
              value={form.comision_defecto ?? ''}
              onChange={e => handleChange('comision_defecto', e.target.value)} />
          </Grid>
          <Grid item xs={12} sm={4}>
            <TextField fullWidth size="small" label="Hora de Cierre" type="time" InputLabelProps={{ shrink: true }}
              value={form.hora_cierre ?? ''}
              onChange={e => handleChange('hora_cierre', e.target.value)} />
          </Grid>
          <Grid item xs={12} sm={4} sx={{ display: 'flex', alignItems: 'center' }}>
            <FormControlLabel control={<Switch checked={form.comision_neto ?? false}
              onChange={e => handleChange('comision_neto', e.target.checked)} />}
              label="Comisión Neto" />
          </Grid>
        </Grid>

        <Typography variant="subtitle1" sx={{ fontWeight: 600, mb: 1 }}>Gráfico de Ventas</Typography>
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={12} sm={4}>
            <TextField fullWidth size="small" label="Fecha de Inicio" type="date" InputLabelProps={{ shrink: true }}
              value={form.preview_fecha_inicio ?? ''}
              onChange={e => handleChange('preview_fecha_inicio', e.target.value)} />
          </Grid>
          <Grid item xs={12} sm={4}>
            <TextField fullWidth size="small" label="Fecha de Fin" type="date" InputLabelProps={{ shrink: true }}
              value={form.preview_fecha_fin ?? ''}
              onChange={e => handleChange('preview_fecha_fin', e.target.value)} />
          </Grid>
          <Grid item xs={12} sm={4} sx={{ display: 'flex', alignItems: 'center' }}>
            <FormControlLabel control={<Switch checked={form.preview ?? true}
              onChange={e => handleChange('preview', e.target.checked)} />}
              label="Ver Ventas" />
          </Grid>
        </Grid>

        <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
          <Button variant="contained" onClick={() => handleSave([
            'porcentaje_igv', 'boleta_monto_dni', 'envio_auto_sunat',
            'consolidado_requerimientos', 'consolidado_liquidaciones',
            'resumen_liquidacion', 'preventa_nota_pedido',
            'periodo_fecha_inicio', 'periodo_fecha_fin',
            'comision_defecto', 'hora_cierre', 'comision_neto',
            'preview_fecha_inicio', 'preview_fecha_fin', 'preview',
          ])}>Guardar</Button>
        </Box>
      </TabPanel>

      <TabPanel value={tab} index={2}>
        <Grid container spacing={3}>
          {TIPO_IMAGENES.map(tipo => {
            const labelMap: Record<string, string> = {
              login: 'Imagen Login',
              home: 'Imagen Home',
              reporte: 'Imagen Reporte',
              firma: 'Imagen Firma',
            };
            const descMap: Record<string, string> = {
              login: 'Imagen utilizada para el login del sistema',
              home: 'Imagen utilizada para el Home del sistema',
              reporte: 'Imagen utilizada para los reportes del sistema',
              firma: 'Imagen utilizada para la firma digital',
            };
            const imgKey = `imagen_${tipo}` as keyof EmpresaConfig;
            const currentUrl = config?.[imgKey] as string | null;

            return (
              <Grid item xs={12} sm={6} key={tipo}>
                <Card>
                  <CardContent>
                    <Typography variant="subtitle1" sx={{ fontWeight: 600 }}>{labelMap[tipo]}</Typography>
                    <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>{descMap[tipo]}</Typography>
                    <Box sx={{ display: 'flex', justifyContent: 'center', mb: 2, bgcolor: '#f5f5f5', p: 2, borderRadius: 1, minHeight: 100 }}>
                      {currentUrl
                        ? <Box component="img" src={currentUrl} alt={labelMap[tipo]} sx={{ maxHeight: 100, maxWidth: '100%', objectFit: 'contain' }} />
                        : <Typography color="text.secondary" sx={{ alignSelf: 'center' }}>Sin imagen</Typography>}
                    </Box>
                    <Typography variant="caption" display="block" color="text.secondary">
                      Dimensiones Sugeridas: {tipo === 'reporte' || tipo === 'firma' ? '300x100 px' : '450x150 px'}
                    </Typography>
                    <Typography variant="caption" display="block" color="text.secondary" sx={{ mb: 1 }}>
                      Formatos Permitidos: png, jpg, jpeg
                    </Typography>
                    <Box sx={{ display: 'flex', gap: 1 }}>
                      <Button variant="outlined" size="small" component="label">
                        Cambiar Imagen
                        <input type="file" hidden accept=".png,.jpg,.jpeg"
                          onChange={e => {
                            const file = e.target.files?.[0];
                            if (file) handleImageUpload(tipo, file);
                          }} />
                      </Button>
                      <Button variant="text" size="small" color="error"
                        onClick={() => resetImageMutation.mutate(tipo)}>
                        Restablecer
                      </Button>
                    </Box>
                  </CardContent>
                </Card>
              </Grid>
            );
          })}
        </Grid>
      </TabPanel>

      <TabPanel value={tab} index={3}>
        <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2, maxWidth: 400, mx: 'auto' }}>
          {form.ventas_bloqueadas ? (
            <Button fullWidth variant="contained" color="success"
              onClick={() => handleSave(['ventas_bloqueadas'], { ventas_bloqueadas: false })}>
              Activar Ventas y Preventas
            </Button>
          ) : (
            <Button fullWidth variant="contained" color="error"
              onClick={() => handleSave(['ventas_bloqueadas'], { ventas_bloqueadas: true })}>
              Bloquear Ventas y Preventas
            </Button>
          )}

          {form.compras_bloqueadas ? (
            <Button fullWidth variant="contained" color="success"
              onClick={() => handleSave(['compras_bloqueadas'], { compras_bloqueadas: false })}>
              Activar Compras
            </Button>
          ) : (
            <Button fullWidth variant="contained" color="error"
              onClick={() => handleSave(['compras_bloqueadas'], { compras_bloqueadas: true })}>
              Bloquear Compras
            </Button>
          )}

          <Button fullWidth variant="contained" color="info" onClick={() => decimalesMutation.mutate()}>
            Actualizar decimales SUNAT
          </Button>
        </Box>
      </TabPanel>

      <Snackbar open={snackbar.open} autoHideDuration={4000} onClose={() => setSnackbar(s => ({ ...s, open: false }))}>
        <Alert severity={snackbar.severity} onClose={() => setSnackbar(s => ({ ...s, open: false }))}>
          {snackbar.message}
        </Alert>
      </Snackbar>
    </Box>
  );
}
