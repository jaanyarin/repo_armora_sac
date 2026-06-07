import { useState, useEffect, useMemo, useCallback } from 'react';
import {
  Box, Typography, Button, TextField, IconButton, Tooltip, Chip,
  Dialog, DialogTitle, DialogContent, DialogActions, Alert, InputAdornment,
  Menu, MenuItem, ListItemIcon, ListItemText, Paper, Switch, FormControlLabel,
  Skeleton, Divider, Stack, Card, CardContent, CardActionArea, Grid,
} from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import SearchIcon from '@mui/icons-material/Search';
import MoreVertIcon from '@mui/icons-material/MoreVert';
import DragIndicatorIcon from '@mui/icons-material/DragIndicator';
import LocalBarIcon from '@mui/icons-material/LocalBar';
import NoDrinksIcon from '@mui/icons-material/NoDrinks';
import CategoryIcon from '@mui/icons-material/Category';
import SubdirectoryArrowRightIcon from '@mui/icons-material/SubdirectoryArrowRight';
import CloseIcon from '@mui/icons-material/Close';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '../../../../shared/lib/zodResolver';
import { z } from 'zod';
import {
  productoClasesApi, productoSubclasesApi,
} from '../../../../shared/api/endpoints';
import type {
  ProductoClase, ProductoSubclase,
  ProductoClasePayload, ProductoSubclasePayload,
} from '../../../../shared/types';

const claseSchema = z.object({
  nombre: z.string().min(2, 'Mínimo 2 caracteres').max(100, 'Máximo 100 caracteres'),
  descripcion: z.string().max(1000, 'Máximo 1000 caracteres').optional().nullable(),
  licor: z.boolean().optional().default(false),
  activo: z.boolean().optional().default(true),
  orden: z.coerce.number().int().min(0).optional(),
});

const subclaseSchema = z.object({
  clase_id: z.string().min(1, 'Seleccione una clase'),
  nombre: z.string().min(2, 'Mínimo 2 caracteres').max(100, 'Máximo 100 caracteres'),
  descripcion: z.string().max(1000, 'Máximo 100 caracteres').optional().nullable(),
  activo: z.boolean().optional().default(true),
  orden: z.coerce.number().int().min(0).optional(),
});

type ClaseForm = z.infer<typeof claseSchema>;
type SubclaseForm = z.infer<typeof subclaseSchema>;

interface ActionMenuState<T> {
  anchorEl: HTMLElement | null;
  row: T | null;
}

export default function ProductosClasesPage() {
  const queryClient = useQueryClient();
  const [search, setSearch] = useState('');
  const [searchInput, setSearchInput] = useState('');
  const [filterLicor, setFilterLicor] = useState<'all' | 'si' | 'no'>('all');
  const [filterActivo, setFilterActivo] = useState<'all' | 'si' | 'no'>('all');
  const [selectedClaseId, setSelectedClaseId] = useState<string | null>(null);
  const [subclaseDialog, setSubclaseDialog] = useState<{ open: boolean; claseId: string; subclase: ProductoSubclase | null }>({
    open: false, claseId: '', subclase: null,
  });
  const [claseDialog, setClaseDialog] = useState<{ open: boolean; clase: ProductoClase | null }>({
    open: false, clase: null,
  });
  const [deleteDialog, setDeleteDialog] = useState<{ open: boolean; clase: ProductoClase | null; subclasesCount: number }>({
    open: false, clase: null, subclasesCount: 0,
  });
  const [deleteSubclaseDialog, setDeleteSubclaseDialog] = useState<{ open: boolean; subclase: ProductoSubclase | null }>({
    open: false, subclase: null,
  });
  const [snackbar, setSnackbar] = useState<{ open: boolean; message: string; severity: 'success' | 'error' }>({
    open: false, message: '', severity: 'success',
  });
  const [claseMenu, setClaseMenu] = useState<ActionMenuState<ProductoClase>>({ anchorEl: null, row: null });
  const [subclaseMenu, setSubclaseMenu] = useState<ActionMenuState<ProductoSubclase>>({ anchorEl: null, row: null });

  useEffect(() => {
    const t = setTimeout(() => setSearch(searchInput.trim()), 300);
    return () => clearTimeout(t);
  }, [searchInput]);

  const clasesParams = useMemo(() => {
    const p: Record<string, string | number | boolean> = { per_page: 100, sort_by: 'orden' };
    if (search) p.search = search;
    if (filterActivo !== 'all') p.activo = filterActivo === 'si';
    if (filterLicor !== 'all') p.licor = filterLicor === 'si';
    return p;
  }, [search, filterActivo, filterLicor]);

  const { data: clasesData, isLoading: loadingClases, isError: errorClases } = useQuery({
    queryKey: ['producto-clases', clasesParams],
    queryFn: () => productoClasesApi.list(clasesParams).then((r) => r.data),
  });

  const clases: ProductoClase[] = clasesData?.data ?? [];
  const selectedClase: ProductoClase | null = clases.find((c) => c.id === selectedClaseId) || null;

  const { data: subclasesData, isLoading: loadingSubclases } = useQuery({
    queryKey: ['producto-subclases', { clase_id: selectedClaseId, per_page: 100 }],
    queryFn: () => productoSubclasesApi.list({ clase_id: selectedClaseId as string, per_page: 100 }).then((r) => r.data),
    enabled: !!selectedClaseId,
  });

  const subclases: ProductoSubclase[] = subclasesData?.data ?? [];

  const invalidateClases = () => {
    queryClient.invalidateQueries({ queryKey: ['producto-clases'] });
  };
  const invalidateSubclases = () => {
    queryClient.invalidateQueries({ queryKey: ['producto-subclases'] });
  };

  const showSnack = useCallback((message: string, severity: 'success' | 'error') => {
    setSnackbar({ open: true, message, severity });
  }, []);

  const deleteClaseMut = useMutation({
    mutationFn: (id: string) => productoClasesApi.delete(id),
    onSuccess: (res: { data?: { subclases_eliminadas?: number } }) => {
      invalidateClases();
      const count = res.data?.subclases_eliminadas ?? 0;
      showSnack(count > 0 ? `Clase eliminada (${count} subclase(s) también).` : 'Clase eliminada.', 'success');
      setDeleteDialog({ open: false, clase: null, subclasesCount: 0 });
      const deletedId = deleteDialog.clase?.id;
      if (deletedId && selectedClaseId === deletedId) setSelectedClaseId(null);
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      showSnack(err.response?.data?.message || 'Error al eliminar la clase.', 'error');
    },
  });

  const deleteSubclaseMut = useMutation({
    mutationFn: (id: string) => productoSubclasesApi.delete(id),
    onSuccess: () => {
      invalidateSubclases();
      invalidateClases();
      showSnack('Subclase eliminada.', 'success');
      setDeleteSubclaseDialog({ open: false, subclase: null });
    },
    onError: () => showSnack('Error al eliminar la subclase.', 'error'),
  });

  const saveClaseMut = useMutation({
    mutationFn: (vars: { id: string | null; payload: Record<string, unknown> }) =>
      vars.id
        ? productoClasesApi.update(vars.id, vars.payload)
        : productoClasesApi.create(vars.payload),
    onSuccess: (_res: unknown, vars: { id: string | null }) => {
      invalidateClases();
      setClaseDialog({ open: false, clase: null });
      showSnack(vars.id ? 'Clase actualizada.' : 'Clase creada.', 'success');
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      showSnack(err.response?.data?.message || 'Error al guardar la clase.', 'error');
    },
  });

  const saveSubclaseMut = useMutation({
    mutationFn: (vars: { id: string | null; payload: Record<string, unknown> }) =>
      vars.id
        ? productoSubclasesApi.update(vars.id, vars.payload)
        : productoSubclasesApi.create(vars.payload),
    onSuccess: (_res: unknown, vars: { id: string | null }) => {
      invalidateSubclases();
      invalidateClases();
      setSubclaseDialog({ open: false, claseId: '', subclase: null });
      showSnack(vars.id ? 'Subclase actualizada.' : 'Subclase creada.', 'success');
    },
    onError: (err: { response?: { data?: { message?: string } } }) => {
      showSnack(err.response?.data?.message || 'Error al guardar la subclase.', 'error');
    },
  });

  const handleSelectClase = (id: string) => setSelectedClaseId(id);
  const handleClearClase = () => setSelectedClaseId(null);

  const openClaseMenu = (e: React.MouseEvent<HTMLElement>, row: ProductoClase) => {
    e.stopPropagation();
    setClaseMenu({ anchorEl: e.currentTarget, row });
  };
  const closeClaseMenu = () => setClaseMenu({ anchorEl: null, row: null });

  const openSubclaseMenu = (e: React.MouseEvent<HTMLElement>, row: ProductoSubclase) => {
    e.stopPropagation();
    setSubclaseMenu({ anchorEl: e.currentTarget, row });
  };
  const closeSubclaseMenu = () => setSubclaseMenu({ anchorEl: null, row: null });

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3, flexWrap: 'wrap', gap: 2 }}>
        <Box>
          <Typography variant="h5" fontWeight={700}>
            Gestión de Clases y Subclases
          </Typography>
          <Typography variant="body2" color="text.secondary" sx={{ mt: 0.5 }}>
            Organiza tu catálogo por líneas y subcategorías. Selecciona una clase para ver y editar sus subclases.
          </Typography>
        </Box>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => setClaseDialog({ open: true, clase: null })}
        >
          Nueva Clase
        </Button>
      </Box>

      <Paper sx={{ p: 2, mb: 2 }}>
        <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap', alignItems: 'center' }}>
          <TextField
            size="small"
            placeholder="Buscar clase..."
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            sx={{ minWidth: 280, flex: 1 }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <SearchIcon fontSize="small" />
                </InputAdornment>
              ),
              endAdornment: searchInput ? (
                <InputAdornment position="end">
                  <IconButton size="small" onClick={() => setSearchInput('')}>
                    <CloseIcon fontSize="small" />
                  </IconButton>
                </InputAdornment>
              ) : null,
            }}
          />
          <FormControlLabel
            control={
              <Switch
                size="small"
                checked={filterLicor === 'si'}
                onChange={(_, v) => setFilterLicor(v ? 'si' : 'all')}
                color="warning"
              />
            }
            label={
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <LocalBarIcon fontSize="small" sx={{ color: filterLicor === 'si' ? 'warning.main' : 'text.secondary' }} />
                <Typography variant="body2">Solo Licor</Typography>
              </Box>
            }
          />
          <FormControlLabel
            control={
              <Switch
                size="small"
                checked={filterActivo === 'si'}
                onChange={(_, v) => setFilterActivo(v ? 'si' : 'all')}
                color="success"
              />
            }
            label={
              <Typography variant="body2">
                {filterActivo === 'si' ? 'Solo activos' : 'Activos e inactivos'}
              </Typography>
            }
          />
        </Box>
      </Paper>

      <Grid container spacing={2}>
        <Grid item xs={12} md={selectedClaseId ? 6 : 12}>
          <Paper sx={{ p: 2 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 2 }}>
              <CategoryIcon color="primary" />
              <Typography variant="h6" fontWeight={600}>
                Clases
              </Typography>
              <Chip
                size="small"
                label={clases.length}
                color="primary"
                variant="outlined"
                sx={{ ml: 'auto' }}
              />
            </Box>
            <Divider sx={{ mb: 1 }} />

            {loadingClases ? (
              <Stack spacing={1}>
                {[1, 2, 3, 4, 5].map((i) => (
                  <Skeleton key={i} variant="rounded" height={72} />
                ))}
              </Stack>
            ) : errorClases ? (
              <Alert severity="error" sx={{ my: 2 }}>
                No se pudieron cargar las clases. Verifica tu conexión o permisos.
              </Alert>
            ) : clases.length === 0 ? (
              <Box sx={{ textAlign: 'center', py: 6 }}>
                <CategoryIcon sx={{ fontSize: 56, color: 'text.disabled', mb: 1 }} />
                <Typography color="text.secondary" gutterBottom>
                  {search ? 'No se encontraron clases con ese criterio.' : 'Aún no has creado ninguna clase.'}
                </Typography>
                {!search && (
                  <Button
                    variant="outlined"
                    startIcon={<AddIcon />}
                    sx={{ mt: 1 }}
                    onClick={() => setClaseDialog({ open: true, clase: null })}
                  >
                    Crear primera clase
                  </Button>
                )}
              </Box>
            ) : (
              <Stack spacing={1}>
                {clases.map((c) => (
                  <ClaseCard
                    key={c.id}
                    clase={c}
                    selected={selectedClaseId === c.id}
                    onSelect={() => handleSelectClase(c.id)}
                    onMenu={(e) => openClaseMenu(e, c)}
                  />
                ))}
              </Stack>
            )}
          </Paper>
        </Grid>

        {selectedClaseId && (
          <Grid item xs={12} md={6}>
            <Paper sx={{ p: 2 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 2 }}>
                <SubdirectoryArrowRightIcon color="secondary" />
                <Typography variant="h6" fontWeight={600}>
                  Subclases de <span style={{ color: '#1976d2' }}>{selectedClase?.nombre || '...'}</span>
                </Typography>
                <Chip
                  size="small"
                  label={subclases.length}
                  color="secondary"
                  variant="outlined"
                  sx={{ ml: 'auto' }}
                />
                <Tooltip title="Cerrar">
                  <IconButton size="small" onClick={handleClearClase}>
                    <CloseIcon fontSize="small" />
                  </IconButton>
                </Tooltip>
                <Button
                  size="small"
                  variant="contained"
                  startIcon={<AddIcon />}
                  onClick={() => setSubclaseDialog({ open: true, claseId: selectedClaseId, subclase: null })}
                >
                  Nueva Subclase
                </Button>
              </Box>
              <Divider sx={{ mb: 1 }} />

              {loadingSubclases ? (
                <Stack spacing={1}>
                  {[1, 2, 3].map((i) => (
                    <Skeleton key={i} variant="rounded" height={60} />
                  ))}
                </Stack>
              ) : subclases.length === 0 ? (
                <Box sx={{ textAlign: 'center', py: 6 }}>
                  <SubdirectoryArrowRightIcon sx={{ fontSize: 56, color: 'text.disabled', mb: 1 }} />
                  <Typography color="text.secondary" gutterBottom>
                    Esta clase aún no tiene subclases.
                  </Typography>
                  <Button
                    variant="outlined"
                    startIcon={<AddIcon />}
                    sx={{ mt: 1 }}
                    onClick={() => setSubclaseDialog({ open: true, claseId: selectedClaseId, subclase: null })}
                  >
                    Crear primera subclase
                  </Button>
                </Box>
              ) : (
                <Stack spacing={1}>
                  {subclases.map((s) => (
                    <SubclaseRow
                      key={s.id}
                      subclase={s}
                      onMenu={(e) => openSubclaseMenu(e, s)}
                    />
                  ))}
                </Stack>
              )}
            </Paper>
          </Grid>
        )}
      </Grid>

      <ClaseFormDialog
        open={claseDialog.open}
        clase={claseDialog.clase}
        isSaving={saveClaseMut.isPending}
        onSubmit={(payload) =>
          saveClaseMut.mutate({ id: claseDialog.clase?.id ?? null, payload })
        }
        onClose={() => {
          if (!saveClaseMut.isPending) setClaseDialog({ open: false, clase: null });
        }}
      />

      <SubclaseFormDialog
        open={subclaseDialog.open}
        claseId={subclaseDialog.claseId}
        subclase={subclaseDialog.subclase}
        isSaving={saveSubclaseMut.isPending}
        onSubmit={(payload) =>
          saveSubclaseMut.mutate({ id: subclaseDialog.subclase?.id ?? null, payload })
        }
        onClose={() => {
          if (!saveSubclaseMut.isPending) setSubclaseDialog({ open: false, claseId: '', subclase: null });
        }}
      />

      <Dialog open={deleteDialog.open} onClose={() => setDeleteDialog({ open: false, clase: null, subclasesCount: 0 })}>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogContent>
          <Typography sx={{ mb: 1 }}>
            ¿Está seguro de eliminar la clase <strong>{deleteDialog.clase?.nombre}</strong>?
          </Typography>
          {deleteDialog.subclasesCount > 0 && (
            <Alert severity="warning" sx={{ mt: 1 }}>
              Esta clase tiene <strong>{deleteDialog.subclasesCount}</strong> subclase(s) que también
              serán eliminadas. Esta acción no se puede deshacer.
            </Alert>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setDeleteDialog({ open: false, clase: null, subclasesCount: 0 })}>Cancelar</Button>
          <Button
            variant="contained"
            color="error"
            onClick={() => deleteDialog.clase && deleteClaseMut.mutate(deleteDialog.clase.id)}
            disabled={deleteClaseMut.isPending}
          >
            Eliminar
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog open={deleteSubclaseDialog.open} onClose={() => setDeleteSubclaseDialog({ open: false, subclase: null })}>
        <DialogTitle>Confirmar eliminación</DialogTitle>
        <DialogContent>
          <Typography>
            ¿Está seguro de eliminar la subclase <strong>{deleteSubclaseDialog.subclase?.nombre}</strong>?
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setDeleteSubclaseDialog({ open: false, subclase: null })}>Cancelar</Button>
          <Button
            variant="contained"
            color="error"
            onClick={() => deleteSubclaseDialog.subclase && deleteSubclaseMut.mutate(deleteSubclaseDialog.subclase.id)}
            disabled={deleteSubclaseMut.isPending}
          >
            Eliminar
          </Button>
        </DialogActions>
      </Dialog>

      <Menu anchorEl={claseMenu.anchorEl} open={!!claseMenu.anchorEl} onClose={closeClaseMenu}>
        <MenuItem
          onClick={() => {
            if (claseMenu.row) {
              setClaseDialog({ open: true, clase: claseMenu.row });
              setSelectedClaseId(claseMenu.row.id);
            }
            closeClaseMenu();
          }}
        >
          <ListItemIcon><EditIcon fontSize="small" /></ListItemIcon>
          <ListItemText>Editar</ListItemText>
        </MenuItem>
        <MenuItem
          onClick={() => {
            if (claseMenu.row) {
              setDeleteDialog({
                open: true,
                clase: claseMenu.row,
                subclasesCount: claseMenu.row.subclases_count ?? 0,
              });
            }
            closeClaseMenu();
          }}
        >
          <ListItemIcon><DeleteIcon fontSize="small" color="error" /></ListItemIcon>
          <ListItemText>Eliminar</ListItemText>
        </MenuItem>
      </Menu>

      <Menu anchorEl={subclaseMenu.anchorEl} open={!!subclaseMenu.anchorEl} onClose={closeSubclaseMenu}>
        <MenuItem
          onClick={() => {
            if (subclaseMenu.row) {
              setSubclaseDialog({
                open: true,
                claseId: subclaseMenu.row.clase_id,
                subclase: subclaseMenu.row,
              });
            }
            closeSubclaseMenu();
          }}
        >
          <ListItemIcon><EditIcon fontSize="small" /></ListItemIcon>
          <ListItemText>Editar</ListItemText>
        </MenuItem>
        <MenuItem
          onClick={() => {
            if (subclaseMenu.row) {
              setDeleteSubclaseDialog({ open: true, subclase: subclaseMenu.row });
            }
            closeSubclaseMenu();
          }}
        >
          <ListItemIcon><DeleteIcon fontSize="small" color="error" /></ListItemIcon>
          <ListItemText>Eliminar</ListItemText>
        </MenuItem>
      </Menu>

      {snackbar.open && (
        <Alert
          severity={snackbar.severity}
          variant="filled"
          onClose={() => setSnackbar((s) => ({ ...s, open: false }))}
          sx={{ position: 'fixed', bottom: 24, right: 24, zIndex: 2000, minWidth: 280 }}
        >
          {snackbar.message}
        </Alert>
      )}
    </Box>
  );
}

interface ClaseCardProps {
  clase: ProductoClase;
  selected: boolean;
  onSelect: () => void;
  onMenu: (e: React.MouseEvent<HTMLElement>) => void;
}

function ClaseCard({ clase, selected, onSelect, onMenu }: ClaseCardProps) {
  return (
    <Card
      variant="outlined"
      sx={{
        borderColor: selected ? 'primary.main' : 'divider',
        borderWidth: selected ? 2 : 1,
        bgcolor: selected ? 'action.selected' : 'background.paper',
        transition: 'all 0.15s',
        '&:hover': { borderColor: 'primary.light', boxShadow: 1 },
      }}
    >
      <CardActionArea onClick={onSelect}>
        <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 1, py: 1.5, '&:last-child': { pb: 1.5 } }}>
          <DragIndicatorIcon sx={{ color: 'text.disabled' }} />
          <Box sx={{ flex: 1, minWidth: 0 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, flexWrap: 'wrap' }}>
              <Typography variant="body1" fontWeight={600} noWrap>
                {clase.nombre}
              </Typography>
              {clase.licor ? (
                <Chip
                  size="small"
                  label="LICOR"
                  color="warning"
                  variant="outlined"
                  icon={<LocalBarIcon sx={{ fontSize: 14 }} />}
                  sx={{ height: 20, fontSize: 10, fontWeight: 700 }}
                />
              ) : (
                <Chip
                  size="small"
                  label="NO LICOR"
                  variant="outlined"
                  icon={<NoDrinksIcon sx={{ fontSize: 14 }} />}
                  sx={{ height: 20, fontSize: 10, fontWeight: 600 }}
                />
              )}
              {!clase.activo && (
                <Chip
                  size="small"
                  label="INACTIVO"
                  color="error"
                  sx={{ height: 20, fontSize: 10, fontWeight: 700 }}
                />
              )}
            </Box>
            <Typography variant="caption" color="text.secondary" sx={{ display: 'flex', alignItems: 'center', gap: 1, mt: 0.5 }}>
              <span>Orden: {clase.orden}</span>
              <span>•</span>
              <span>{clase.subclases_count ?? 0} subclase{(clase.subclases_count ?? 0) === 1 ? '' : 's'}</span>
              {clase.codigo && (
                <>
                  <span>•</span>
                  <span style={{ fontFamily: 'monospace' }}>{clase.codigo}</span>
                </>
              )}
            </Typography>
          </Box>
          <IconButton size="small" onClick={onMenu}>
            <MoreVertIcon fontSize="small" />
          </IconButton>
        </CardContent>
      </CardActionArea>
    </Card>
  );
}

function SubclaseRow({ subclase, onMenu }: { subclase: ProductoSubclase; onMenu: (e: React.MouseEvent<HTMLElement>) => void }) {
  return (
    <Box
      sx={{
        display: 'flex', alignItems: 'center', gap: 1, p: 1.5,
        border: 1, borderColor: 'divider', borderRadius: 1,
        bgcolor: 'background.paper',
        '&:hover': { borderColor: 'primary.light', bgcolor: 'action.hover' },
        opacity: subclase.activo ? 1 : 0.6,
      }}
    >
      <DragIndicatorIcon sx={{ color: 'text.disabled' }} />
      <Box sx={{ flex: 1, minWidth: 0 }}>
        <Typography variant="body2" fontWeight={500} noWrap>
          {subclase.nombre}
        </Typography>
        <Typography variant="caption" color="text.secondary">
          Orden: {subclase.orden} {subclase.codigo && `• ${subclase.codigo}`}
          {!subclase.activo && ' • INACTIVO'}
        </Typography>
      </Box>
      <IconButton size="small" onClick={onMenu}>
        <MoreVertIcon fontSize="small" />
      </IconButton>
    </Box>
  );
}

interface ClaseFormDialogProps {
  open: boolean;
  clase: ProductoClase | null;
  isSaving: boolean;
  onClose: () => void;
  onSubmit: (payload: Record<string, unknown>) => void;
}

function ClaseFormDialog({ open, clase, isSaving, onClose, onSubmit }: ClaseFormDialogProps) {
  const { control, handleSubmit, formState: { errors }, reset } = useForm<ClaseForm>({
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    resolver: zodResolver(claseSchema) as any,
    defaultValues: {
      nombre: clase?.nombre ?? '',
      descripcion: clase?.descripcion ?? '',
      licor: clase?.licor ?? false,
      activo: clase?.activo ?? true,
    },
  });

  useEffect(() => {
    if (open) {
      reset({
        nombre: clase?.nombre ?? '',
        descripcion: clase?.descripcion ?? '',
        licor: clase?.licor ?? false,
        activo: clase?.activo ?? true,
      });
    }
  }, [open, clase, reset]);

  const submit = (data: ClaseForm) => {
    const payload: ProductoClasePayload = {
      nombre: data.nombre,
      descripcion: data.descripcion || null,
      licor: data.licor ?? false,
      activo: data.activo ?? true,
    };
    onSubmit(payload as unknown as Record<string, unknown>);
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <form onSubmit={handleSubmit(submit)}>
        <DialogTitle>{clase ? 'Editar Clase' : 'Nueva Clase'}</DialogTitle>
        <DialogContent>
          <Stack spacing={2} sx={{ mt: 1 }}>
            <Controller
              name="nombre"
              control={control}
              render={({ field }) => (
                <TextField
                  {...field}
                  label="Nombre de la clase"
                  fullWidth
                  required
                  autoFocus
                  error={!!errors.nombre}
                  helperText={errors.nombre?.message}
                />
              )}
            />
            <Controller
              name="descripcion"
              control={control}
              render={({ field }) => (
                <TextField
                  {...field}
                  value={field.value ?? ''}
                  label="Descripción (opcional)"
                  fullWidth
                  multiline
                  rows={2}
                  error={!!errors.descripcion}
                  helperText={errors.descripcion?.message}
                />
              )}
            />
            <Controller
              name="licor"
              control={control}
              render={({ field }) => (
                <FormControlLabel
                  control={
                    <Switch
                      checked={!!field.value}
                      onChange={field.onChange}
                      color="warning"
                    />
                  }
                  label={
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                      <LocalBarIcon fontSize="small" />
                      <Typography>Es una clase de licor / bebidas alcohólicas</Typography>
                    </Box>
                  }
                />
              )}
            />
            <Controller
              name="activo"
              control={control}
              render={({ field }) => (
                <FormControlLabel
                  control={
                    <Switch
                      checked={!!field.value}
                      onChange={field.onChange}
                      color="success"
                    />
                  }
                  label="Activo (visible en formularios)"
                />
              )}
            />
          </Stack>
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} disabled={isSaving}>Cancelar</Button>
          <Button type="submit" variant="contained" disabled={isSaving}>
            {isSaving ? 'Guardando…' : clase ? 'Guardar cambios' : 'Crear clase'}
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}

interface SubclaseFormDialogProps {
  open: boolean;
  claseId: string;
  subclase: ProductoSubclase | null;
  isSaving: boolean;
  onClose: () => void;
  onSubmit: (payload: Record<string, unknown>) => void;
}

function SubclaseFormDialog({ open, claseId, subclase, isSaving, onClose, onSubmit }: SubclaseFormDialogProps) {
  const { control, handleSubmit, formState: { errors }, reset } = useForm<SubclaseForm>({
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    resolver: zodResolver(subclaseSchema) as any,
    defaultValues: {
      clase_id: claseId,
      nombre: subclase?.nombre ?? '',
      descripcion: subclase?.descripcion ?? '',
      activo: subclase?.activo ?? true,
    },
  });

  useEffect(() => {
    if (open) {
      reset({
        clase_id: claseId,
        nombre: subclase?.nombre ?? '',
        descripcion: subclase?.descripcion ?? '',
        activo: subclase?.activo ?? true,
      });
    }
  }, [open, claseId, subclase, reset]);

  const submit = (data: SubclaseForm) => {
    const payload: ProductoSubclasePayload = {
      clase_id: data.clase_id,
      nombre: data.nombre,
      descripcion: data.descripcion || null,
      activo: data.activo ?? true,
    };
    onSubmit(payload as unknown as Record<string, unknown>);
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <form onSubmit={handleSubmit(submit)}>
        <DialogTitle>{subclase ? 'Editar Subclase' : 'Nueva Subclase'}</DialogTitle>
        <DialogContent>
          <Stack spacing={2} sx={{ mt: 1 }}>
            <Controller
              name="nombre"
              control={control}
              render={({ field }) => (
                <TextField
                  {...field}
                  label="Nombre de la subclase"
                  fullWidth
                  required
                  autoFocus
                  error={!!errors.nombre}
                  helperText={errors.nombre?.message}
                />
              )}
            />
            <Controller
              name="descripcion"
              control={control}
              render={({ field }) => (
                <TextField
                  {...field}
                  value={field.value ?? ''}
                  label="Descripción (opcional)"
                  fullWidth
                  multiline
                  rows={2}
                  error={!!errors.descripcion}
                  helperText={errors.descripcion?.message}
                />
              )}
            />
            <Controller
              name="activo"
              control={control}
              render={({ field }) => (
                <FormControlLabel
                  control={
                    <Switch
                      checked={!!field.value}
                      onChange={field.onChange}
                      color="success"
                    />
                  }
                  label="Activo (visible en formularios)"
                />
              )}
            />
          </Stack>
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} disabled={isSaving}>Cancelar</Button>
          <Button type="submit" variant="contained" disabled={isSaving}>
            {isSaving ? 'Guardando…' : subclase ? 'Guardar cambios' : 'Crear subclase'}
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
