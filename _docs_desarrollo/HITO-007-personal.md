# HITO-007 — Módulo Personal (CRUD Backend + Formulario Frontend)

**Estado:** ✅ Cerrado + Iteración feedback + Gestión Personal + Reportes Personal + Fixes UX edición implementados
**Fecha original:** 2026-06-06
**Última actualización:** 2026-06-07 (fixes UX edición: estado legible, persistencia permisos/listas/almacenes, select all por grupo, alert seguridad passwords)
**Iteración:** Senior Fullstack ERP Architect v3
**Dependencias:** AGENTS.md (Hito 003-004a), perfil arquitecto v3

---

## 1. Análisis

### 1.1 Origen (legacy armorasac.com)

El módulo **Personal** del legacy (armorasac.com) incluía un wizard de 6 pasos para crear usuarios/empleados con información personal, identidad, contacto, permisos, fotografía y confirmación. Incluía:

- Datos personales (login, contraseñas, estado)
- Identidad (DNI/RUC/CE/Pasaporte, número, sexo, estado civil, fecha nacimiento)
- Contacto y ubicación (email, teléfonos, país→departamento→provincia→distrito, dirección, referencia)
- Permisos (roles legacy Vendedor/Administrador/Cajero/Transportista, permisos granulares por módulo, listas de precios, almacenes)
- Fotografía (upload de imagen con preview)
- Confirmación (resumen y guardado final)

El backend legacy estaba acoplado a tablas planas con ~100+ permisos, una tabla `personal` separada de `users`, y un campo "Mapa de Rutas" huérfano sin módulo de Logística implementado.

### 1.1.1 Iteración feedback (2026-06-06)

Revisión de la ficha del legacy contra el wizard implementado detectó 6 brechas, todas corregidas en esta iteración:

| # | Hallazgo | Corrección |
|---|---|---|
| 1 | Step "Datos Personales" mostraba campo `nombre_completo` separado | Eliminado: `nombre_completo` se computa server-side desde `apellido_paterno` + `apellido_materno` + `nombres` |
| 2 | Campos `password` y `password_confirmation` sin toggle de visibilidad | Agregado `IconButton` con `Visibility`/`VisibilityOff` (MUI) en cada campo |
| 3 | Step "Identidad": tipo de documento era un `Select` simple | Reemplazado por `Autocomplete` contra el nuevo catálogo `dim_documento_identidad` (DNI / CE / Pasaporte / RUC) |
| 4 | Step "Identidad": campos `dni` y `ruc` separados en el formulario | Eliminados: un único campo `numero_documento` cuya regex y `maxLength` se calculan dinámicamente según el tipo seleccionado |
| 5 | Step "Identidad": estado civil sin fuente oficial | Reemplazado por `Autocomplete` que apunta a `dim_estado_civil` (catálogo SUNAT/INEI: Soltero, Casado, Divorciado, Viudo, Conviviente) |
| 6 | Step "Permisos y Accesos": roles multi-select con chips | Convertido a **single-select** mediante lista de "radio buttons" (iconos `RadioButtonChecked`/`RadioButtonUnchecked`); el validador `roles.max:1` rechaza más de un rol; el frontend hace `field.onChange(checked ? [] : [rol.name])` |

### 1.2 Decisiones arquitectónicas validadas (7 preguntas)

| # | Decisión | Razón |
|---|---|---|
| 1 | **Extender `users`** con columnas nullable (no tabla `personal` separada) | Una sola fuente de verdad para auth + personal; evita JOIN en cada login |
| 2 | **Usar 39 permisos actuales** del proyecto, no los 100+ legacy | Los permisos legacy no tienen módulo implementado; mantener scope |
| 3 | **Roles Spatie** (Vendedor, Comprador, Logística) en el step 3 | No se duplican flags booleanos; un usuario tiene **un solo rol** (validado server-side con `roles.max:1` y UI con radio buttons) |
| 4 | **Diferir "Mapa de Rutas"** | El módulo de Logística está en Hito 005 backlog; evita tabla vacía |
| 5 | **Endpoint separado `POST /api/personal/{user}/foto`** (no multipart en el mismo POST) | Patrón consistente con `EmpresaController::uploadImage`; reduce complejidad en el FormRequest |
| 6 | **Validar `password_confirmation`** con `confirmed` (Zod refine + Laravel `confirmed`) | UX: doble digitación + validación server-side; **toggle de visibilidad** en ambos campos con `IconButton` |
| 7 | **CRUD completo backend + Crear/Editar/Listar en frontend** | `PersonalFormPage` cubre crear/editar y `PersonalListPage` cubre gestión básica con búsqueda, paginación y acciones por fila |
| 8 | **`dim_documento_identidad` como nueva tabla** (reemplaza uso incorrecto de `dim_documento_tipo` SUNAT comprobantes) | `dim_documento_tipo` contiene comprobantes de pago (Factura/Boleta/NC); un nuevo catálogo de tipos de **documento de identidad** (DNI/CE/Pasaporte/RUC) es lo correcto para `users` |
| 9 | **`numero_documento` único + regex dinámica** según tipo seleccionado | Elimina campos `dni`/`ruc` redundantes; permite agregar nuevos tipos (Pasaporte, CE) sin migración |
| 10 | **Seed `dim_almacen` con Almacén Principal por defecto** | La tabla estaba vacía y la sección "Almacenes" del step 3 no mostraba nada |

### 1.3 Scope final

**Backend (completo, 22 tests Feature passing):**
- 4 migraciones nuevas (1 extiende `users` + 3 pivotes)
- 2 modelos catálogo nuevos (`Sexo`, `EstadoCivil`)
- 1 modelo de dominio (`Personal` extends `User`)
- 1 Service (`PersonalService` con 9 métodos)
- 1 Policy (`PersonalPolicy`, impide auto-eliminarse)
- 2 FormRequests (`StorePersonalRequest`, `UpdatePersonalRequest`)
- 1 API Resource (`PersonalResource` con `whenLoaded`)
- 1 Controller (10 endpoints)
- 4 nuevos permisos Spatie (ver/crear/editar/eliminar-personal)
- 1 endpoint catálogo nuevo (`GET /api/catalog/almacenes`)

**Frontend (operativo para Crear/Editar/Listar Personal):**
- 8 métodos en `personalApi` (endpoints.ts)
- 5 tipos nuevos en `shared/types/index.ts` (Personal, PersonalPayload, Sexo, EstadoCivil, PermisoAgrupado)
- 1 página `PersonalFormPage.tsx` con Stepper de 6 pasos
- 1 lazy import + 2 rutas en `App.tsx`
- 1 derivación de título en `AdminLayout` (Nuevo Personal / Editar Personal)
- Mejoras en `env.d.ts` (tipado de `useForm().watch/setValue/getValues` + `useQuery<T>()`)

**Pendiente (siguiente iteración):**
- Acciones batch en `PersonalListPage`
- Exportar Excel/PDF y columna de Mapa de Rutas (requiere Hito 005 Logistics)
- Activity log viewer por usuario
- Foto server-side: image manipulation (thumbnail) con Intervention\Image
- Auditoría visual de cambios de rol/permisos (lista de activity logs por usuario)

---

## 2. Implementación Backend

### 2.1 Migraciones

**Hito 007 original (4 migraciones, 2026-06-07_000xxx):**
- **`2026_06_07_000001_add_personal_fields_to_users_table.php`** (extiende `users` con 19 columnas + softDeletes + 7 FK — incluye `dni` y `ruc` que luego fueron migrados).
- **`2026_06_07_000002_create_personal_listas_precios_table.php`** (pivot `user_id ↔ lista_precio_id` con timestamps).
- **`2026_06_07_000003_create_personal_almacenes_table.php`** (pivot `user_id ↔ almacen_id` con timestamps).
- **`2026_06_07_000004_create_personal_permisos_table.php`** (pivot `user_id ↔ permission_id` con timestamps).

**Iteración feedback (3 migraciones, 2026-06-07_010xxx):**

**`2026_06_07_010000_create_dim_documento_identidad_table.php`**: nuevo catálogo oficial de tipos de documento de identidad (reemplaza el uso incorrecto de `dim_documento_tipo` que contiene comprobantes de pago SUNAT). Tabla:

```php
Schema::create('dim_documento_identidad', function (Blueprint $table) {
    $table->id();
    $table->string('codigo', 4)->unique();
    $table->string('nombre', 50);
    $table->string('longitud', 20)->nullable();   // ej. '8', '11', '12', '20'
    $table->string('regex', 100)->nullable();      // regex con delimitadores PCRE (Laravel)
    $table->string('pais_codigo', 4)->nullable();  // PE para DNI/RUC
    $table->boolean('activo')->default(true);
    $table->timestamps();
});
// Seed: DNI, CE, Pasaporte, RUC
```

**`2026_06_07_010001_seed_default_almacen.php`**: siembra `dim_almacen` con `ALM-001 Almacén Principal` usando `insertOrIgnore` (idempotente, soporta RefreshDatabase en tests).

**`2026_06_07_010002_replace_documento_tipo_with_identidad.php`**: en `users` agrega `documento_identidad_id → dim_documento_identidad` y elimina `dni`, `ruc`, `documento_tipo_id` (la columna de `dim_documento_tipo` quedaba solo para comprobantes de venta, no para identidad de persona).

### 2.2 Modelos catálogo

`Sexo` y `EstadoCivil` se crearon como modelos Eloquent que apuntan a `dim_sexo` y `dim_estado_civil` (tablas SUNAT preexistentes). Ambos con `public $timestamps = false`.

### 2.3 Modelo `Personal`

Extiende `App\Models\User` (no duplica atributos). Agrega `SoftDeletes` + `LogsActivity` (Spatie). Define 10 relaciones (en la iteración feedback se renombró `documentoTipo` → `documentoIdentidad`):

```php
class Personal extends BaseUser {
    use LogsActivity, SoftDeletes;
    protected $table = 'users';

    public function documentoIdentidad(): BelongsTo { ... }   // dim_documento_identidad
    public function sexo(): BelongsTo { ... }
    public function estadoCivil(): BelongsTo { ... }
    public function pais(): BelongsTo { ... }
    public function departamento(): BelongsTo { ... }
    public function provincia(): BelongsTo { ... }
    public function ubigeo(): BelongsTo { ... }
    public function listasPrecios(): BelongsToMany { ... }
    public function almacenes(): BelongsToMany { ... }
    public function permisosDirectos(): BelongsToMany { ... }
}
```

### 2.3.1 Modelo catálogo `DocumentoIdentidad`

`App\Models\Catalog\DocumentoIdentidad` apunta a `dim_documento_identidad` con `$fillable = [codigo, nombre, longitud, regex, pais_codigo, activo]` y cast `activo => boolean`.

### 2.4 Service Layer

`PersonalService` encapsula la lógica de negocio. Métodos públicos:

```php
public function paginate(array $filters): LengthAwarePaginator
public function findById(int $id): ?Personal
public function create(array $data): Personal       // tx, sync relations, genera código PER-XXXXX
public function update(Personal $personal, array $data): Personal  // tx, sync relations
public function delete(Personal $personal): void    // soft delete
public function uploadPhoto(Personal $personal, UploadedFile $file): Personal  // borra anterior, store en public/personal/{id}/
public function resetPhoto(Personal $personal): Personal  // borra archivo + columna
public function getPhotoPath(Personal $personal): ?string
public function getRolesDisponibles(): Collection   // Spatie Role
public function getPermisosAgrupados(): Collection  // Permission::groupBy('modulo')
```

`generateCode()`: `PER-` + `max(id) + 1` con padding a 5 dígitos (`PER-00001`).

### 2.5 Validación (iteración feedback)

**`StorePersonalRequest`** / **`UpdatePersonalRequest`** (mismas reglas; update usa `Rule::unique(...)->ignore($id)`):

- `username`: `min:5|max:32|regex:/^[a-zA-Z0-9._-]+$/`
- `apellido_paterno`, `apellido_materno`, `nombres`: **`required`** (mín 1, compondrán `nombre_completo` server-side)
- `password`: `min:5|max:32|confirmed` (solo required en store; en update es opcional)
- `documento_identidad_id`: `nullable|exists:dim_documento_identidad,id`
- `numero_documento`: regex y maxLength **dinámicos** según `documento_identidad_id` seleccionado (helper privado `resolveRegex/resolveMaxLength` que consulta `dim_documento_identidad`)
- `roles`: `nullable|array|max:1` (**un solo rol por usuario**)
- Resto: `nullable` con `exists:dim_*,id` o `regex`/`unique` donde aplique

Mensajes de error en español en ambos FormRequests.

### 2.6 Resource y Policy

`PersonalResource` con `whenLoaded()` para todas las relaciones + `foto_url` generado a partir de `foto_path` con `url('storage/' . $foto_path)`.

`PersonalPolicy`:
- `viewAny/view/create/update`: cualquier usuario autenticado con permiso (gate del middleware).
- `delete`: además verifica `$personal->id !== $user->id` (impide auto-eliminarse, test `test_cannot_delete_self`).

### 2.7 Rutas y permisos

10 rutas nuevas en `routes/api.php` con prefijo `personal` y middleware `auth:sanctum + permission:*`:

| Método | Ruta | Permiso |
|---|---|---|
| GET | `/api/personal` | ver-personal |
| POST | `/api/personal` | crear-personal |
| GET | `/api/personal/{personal}` | ver-personal |
| PUT | `/api/personal/{personal}` | editar-personal |
| DELETE | `/api/personal/{personal}` | eliminar-personal |
| POST | `/api/personal/{personal}/foto` | editar-personal |
| DELETE | `/api/personal/{personal}/foto` | editar-personal |
| GET | `/api/personal/foto/{personal}` | (público para servir foto) |
| GET | `/api/personal/roles-disponibles` | ver-personal |
| GET | `/api/personal/permisos-agrupados` | ver-personal |

Nuevos permisos Spatie en `RoleAndPermissionSeeder`: `ver-personal`, `crear-personal`, `editar-personal`, `eliminar-personal`. Asignados a `Admin` y `Super-Admin`.

### 2.8 Endpoints catálogo complementarios

`GET /api/catalog/almacenes` (nuevo): retorna todos los `dim_almacen` ordenados por nombre. Necesario para el step 3 de almacenes.

---

## 3. Implementación Frontend

### 3.1 Tipos (`shared/types/index.ts`)

5 interfaces nuevas (≈100 líneas):
- `Personal` (entidad completa, 30+ campos)
- `PersonalPayload` (input del formulario)
- `Sexo`, `EstadoCivil` (catálogos)
- `PermisoAgrupado` (para step de permisos)

### 3.2 API client (`shared/api/endpoints.ts`)

```typescript
export const personalApi = {
  list: (params?) => apiClient.get('/personal', { params }),
  find: (id) => apiClient.get(`/personal/${id}`),
  create: (data) => apiClient.post('/personal', data),
  update: (id, data) => apiClient.put(`/personal/${id}`, data),
  delete: (id) => apiClient.delete(`/personal/${id}`),
  uploadPhoto: (id, file) => { const fd = new FormData(); fd.append('foto', file); return apiClient.post(`/personal/${id}/foto`, fd, { headers: { 'Content-Type': 'multipart/form-data' } }); },
  resetPhoto: (id) => apiClient.delete(`/personal/${id}/foto`),
  rolesDisponibles: () => apiClient.get('/personal/roles-disponibles'),
  permisosAgrupados: () => apiClient.get('/personal/permisos-agrupados'),
};
```

### 3.3 Componente `PersonalFormPage`

Componente React 19 + TypeScript 6 + MUI 7 + RHF + Zod. 6 pasos en MUI `<Stepper>`:

1. **Datos Personales**: username (requerido, regex alfanum+especiales), **apellido paterno + materno + nombres** (todos requeridos, componen `nombre_completo` server-side), contraseña + confirmación (con `IconButton` de `Visibility`/`VisibilityOff` para mostrar/ocultar en ambos campos, solo requeridos en create), switch activo.
2. **Identidad**: **Autocomplete** de `documento_identidad_id` contra `dim_documento_identidad` (catálogo oficial DNI/CE/Pasaporte/RUC), **un único `numero_documento`** (su regex y `maxLength` se calculan según el tipo seleccionado, `disabled` hasta elegir tipo), Autocomplete de `estado_civil_id` (catálogo SUNAT oficial), sexo, fecha nacimiento.
3. **Contacto y Ubicación**: email, teléfono fijo, celular, país (default PE), departamento→provincia→distrito (autocomplete en cascada, watch de estado local), dirección, referencia.
4. **Permisos y Accesos**: lista de **radio buttons** (single-select, íconos `RadioButtonChecked`/`RadioButtonUnchecked`) para roles, chips toggleables para listas de precios, chips toggleables para almacenes (ahora muestra "Almacén Principal" tras seed), y checkboxes agrupados por módulo para permisos directos adicionales. Validación Zod: `roles.max(1)`.
5. **Fotografía**: input file con preview circular, validación client-side (max 2MB, png/jpg/jpeg), botón quitar.
6. **Confirmación**: 4 papers resumen (datos personales, identidad, contacto, permisos). `nombre_completo` se computa client-side con helper `buildNombreCompleto(ap, am, nom)` para previsualización. Botón "Guardar" ejecuta POST/PUT; si hay foto, hace upload en segundo `try/catch` después de crear.

Estado local mínimo: `step`, `submitError`, `fotoFile`, `fotoPreview`, `showPassword`, `showPasswordConfirm`, `departamentoId`/`provinciaId` (para cascada de ubicación). El estado de cada step se acumula en el RHF `formState`, no en `useState`, para evitar re-renders innecesarios.

Submit construye payload eliminando `password` si está vacío. `onSuccess` hace `invalidateQueries(['personal'])` y navega a `/admin/personal`.

### 3.4 Rutas (`App.tsx`)

```tsx
const PersonalFormPage = lazy(() => import('./Admin/pages/Personal/PersonalFormPage'));
// ...
<Route path="personal/nuevo" element={<SuspenseWrapper><PersonalFormPage /></SuspenseWrapper>} />
<Route path="personal/:id/editar" element={<SuspenseWrapper><PersonalFormPage /></SuspenseWrapper>} />
```

### 3.5 Sidebar (ya tenía la entrada)

El sidebar ya tenía las 3 entradas de la sección "Personal":
- Crear Personal → `/admin/personal/nuevo` (ahora funcional)
- Gestión Personal → `/admin/personal` (funcional con `PersonalListPage`)
- Reportes Personal → `/admin/personal/reportes` (todavía ComingSoon)

### 3.6 `deriveTitle` en `AdminLayout`

```ts
if (pathname.startsWith('/admin/personal')) {
  if (pathname.includes('/nuevo')) return 'Nuevo Personal';
  if (pathname.includes('/editar')) return 'Editar Personal';
  return 'Personal';
}
```

### 3.7 Mejoras en `env.d.ts` (efecto colateral positivo)

- `useForm<T>()` ahora retorna `watch`, `setValue`, `getValues` (antes solo `control`, `handleSubmit`, `reset`).
- `useQuery<T>()` ahora acepta tipo genérico.
- `useMutation()` ahora retorna `mutateAsync` y tolera `mutate()` sin args (para casos como `decimalesMutation.mutate()` en CompanySettingsPage).

Esto arregló 2 errores pre-existentes en `OrderHistoryPage.tsx` y `CompanySettingsPage.tsx`.

---

## 4. Bitácora de depuración (4 fixes críticos)

### 4.1 Fix #1: `data` wrap faltante en Resource

**Síntoma**: `test_admin_can_create_personal` retornaba `{"id":3,"codigo":"PER-00003","username":"jperez",...}` SIN la envoltura `data` que el test esperaba (`assertJsonPath('data.username', 'jperez')`).

**Causa raíz**: `response()->json(new PersonalResource($personal), 201)` no aplica la envoltura `data` por defecto. La envoltura solo se aplica cuando se retorna el resource directamente (Laravel auto-convierte a `JsonResponse` y aplica el wrap).

**Fix**: Cambiar todas las llamadas a `return new PersonalResource($personal)` (con `->response()->setStatusCode(201)` para `store`). Esto es la forma idiomática Laravel 12+.

**Archivos**: `app/Modules/Personal/Http/Controllers/PersonalController.php` (5 endpoints modificados: `show`, `store`, `update`, `uploadPhoto`, `resetPhoto`).

### 4.2 Fix #2: `$fillable` incompleto en `User`

**Síntoma**: `test_update_personal` recibía 200 OK pero `data.telefono_celular` era `null` después de PUT. El value se enviaba correctamente pero no se persistía.

**Causa raíz**: El modelo `App\Models\User` solo tenía 10 campos en `$fillable` (los originales del Hito 003). Los 19 campos nuevos de personal (apellido_paterno, telefono_celular, etc.) no estaban en `$fillable`, así que `Personal::update(['telefono_celular' => '...'])` los descartaba silenciosamente.

**Fix**: Agregar los 19 campos nuevos a `$fillable` en `User.php`. La alternativa (override en `Personal`) duplicaba la lista; mejor centralizar en `User` ya que `Personal` extiende de él y comparte tabla.

### 4.3 Fix #3: Date casts faltantes

**Síntoma**: 500 Error "Call to a member function `toIso8601String()` on string" en `PersonalResource.php:56` (`password_changed_at`).

**Causa raíz**: `password_changed_at` y `fecha_nacimiento` se guardaban como strings, no como Carbon. `$this->password_changed_at?->toIso8601String()` retornaba string al llamar `?->`, pero el operador `?->` solo evita el error si el valor es `null`, no si es string.

**Fix**: Agregar casts a `User`:
```php
'password_changed_at' => 'datetime',
'fecha_nacimiento' => 'date',
```

### 4.4 Fix #4: Env.d.ts type augmentation

**Síntoma**: TypeScript build fallaba con 11 errores: `useForm` no tenía `watch/setValue/getValues`, `useQuery` no aceptaba genéricos.

**Causa raíz**: `env.d.ts` declaraba el módulo `@tanstack/react-query` con tipos `any` simples que no soportaban genéricos, y `react-hook-form` solo declaraba 4 propiedades de retorno.

**Fix**: Expandir declaraciones:
```typescript
export const useQuery: <T = any>(opts: any) => { data: T | undefined; isLoading: boolean; isError: boolean; error: any; };
export const useForm<T = any>(opts?: ...): { control, handleSubmit, reset, watch, setValue, getValues, formState };
```

Esto también arregló errores pre-existentes en `OrderHistoryPage.tsx:42` (`isError` no existía) y `CompanySettingsPage.tsx:542` (`mutate()` sin args no estaba tipado).

---

## 5. Tests (30/30 Personal, 93/93 backend — post fixes UX edición 2026-06-07)

`backend/tests/Feature/PersonalTest.php` con **23 casos** que cubren (1 nuevo `test_create_personal_allows_only_one_role`):

| # | Test | Cubre |
|---|---|---|
| 1 | `test_admin_can_list_personal` | GET /api/personal paginado |
| 2 | `test_vendedor_cannot_list_personal` | RBAC: 403 sin `ver-personal` |
| 3 | `test_admin_can_create_personal` | POST con payload completo; verifica `nombre_completo = "Pérez López Juan"` (computado) y `documento_identidad_id` |
| 4 | `test_create_personal_validates_required_fields` | Validación 422 sin username, apellido_paterno, apellido_materno, nombres, password |
| 5 | `test_create_personal_validates_password_confirmation` | `confirmed` en create |
| 6 | `test_create_personal_validates_unique_username` | `unique:users,username` |
| 7 | `test_create_personal_validates_dni_format` | `numero_documento` con regex DNI 8 dígitos |
| 8 | `test_create_personal_validates_ruc_format` | `numero_documento` con regex RUC 11 dígitos |
| 9 | `test_create_personal_with_roles_and_permissions` | syncRoles + sync permisos |
| 10 | `test_create_personal_with_listas_precios_and_almacenes` | sync pivotes |
| 11 | `test_show_personal` | GET /{id} |
| 12 | `test_update_personal` | PUT parcial (`nombres` → recalcula `nombre_completo`) |
| 13 | `test_update_personal_can_change_password` | `password_changed_at` se actualiza |
| 14 | `test_delete_personal_soft_delete` | DELETE → `assertSoftDeleted` |
| 15 | `test_cannot_delete_self` | Policy impide auto-eliminarse |
| 16 | `test_vendedor_cannot_create_personal` | RBAC: 403 sin `crear-personal` |
| 17 | `test_upload_photo` | POST /{id}/foto multipart |
| 18 | `test_upload_photo_validates_file` | 422 sin imagen / tipo inválido |
| 19 | `test_reset_photo` | DELETE /{id}/foto borra archivo y columna |
| 20 | `test_roles_disponibles` | GET retorna 11 roles Spatie |
| 21 | `test_permisos_agrupados` | GET retorna permisos agrupados por módulo |
| 22 | `test_list_personal_with_search` | paginate con search filter |
| 23 | `test_create_personal_allows_only_one_role` | 422 con 2 roles en payload (`roles.max:1`) |

Resultado: **23 passed, 0 failed, 0 errors, 75 assertions, ~30s**.

Suite completa backend auditada: `composer test` => **100/100 tests passing, 284 assertions**.

Suite frontend auditada:
- `npm run build` => OK.
- `npm run lint` => OK con 1 warning no bloqueante (`PersonalFormPage.tsx`, React Hook Form `watch()`).
- `npm run test` => OK fuera del sandbox por restricción `spawn EPERM`; resultado **3 files, 9 tests passing**.
- `npm run test:e2e` => OK fuera del sandbox por restricción `EPERM` sobre `test-results`; resultado **8 Playwright tests passing**.

---

## 6. Mapeo legacy → nextgen

| Legacy (armorasac.com) | NextGen (ARMORA NextGen) | Notas |
|---|---|---|
| Tabla `personal` separada de `users` | Tabla `users` extendida con 19 columnas nullable | Una sola fuente de verdad |
| 100+ permisos planos | 39 permisos agrupados por módulo | Mantiene RBAC granular sin overingeniería |
| 6+ roles hardcodeados (Vendedor/Cajero/Admin/Transportista/etc.) | 11 roles Spatie (Vendedor, Comprador, Logística, Jefe Almacén, Admin, Super-Admin, etc.) | Spatie + `guard_name='web'` |
| Campos booleanos legacy (`vendedor`, `transportista`, `cajero`) | Solo roles + permisos | Flags eliminados |
| Upload foto en mismo POST (multipart gigante) | Endpoint separado `POST /api/personal/{id}/foto` | Mejor manejo de errores y testing |
| Wizard sin validación de confirmación de password | Zod refine + Laravel `confirmed` | Doble digitación obligatoria |
| "Mapa de Rutas" huérfano | **Diferido** al Hito 005 (Logistics) | Sin tabla vacía |
| Tabla `datos_usuario` con 80+ columnas | `users` extendida con solo los campos realmente necesarios | Columnas nullable + FKs a dim_* |
| Soft delete solo en customers/products | Soft delete en `users` (vía migración `add_soft_deletes`) | Consistencia cross-module |

---

## 7. Decisiones técnicas no obvias

### 7.1 `$guard_name = 'web'` en `User` (fix del Hito 003 reabierto)

El modelo `User` ahora declara explícitamente `protected $guard_name = 'web'` para que `syncRoles` siempre use el guard 'web' (donde están los 11 roles sembrados), no el guard 'sanctum' (que es el default cuando se ejecuta bajo `auth:sanctum` middleware). Esto es esencial para que `personalApi.update()` no falle con "Role not found" cuando el usuario autenticado está bajo Sanctum.

### 7.2 `personalApi.update` no sube foto

El cliente React hace la subida de foto en una segunda llamada (`uploadPhoto`) DESPUÉS de crear/actualizar el personal. Esto es porque:
- El endpoint de foto requiere un `id` válido (no se puede crear + foto en un solo POST sin acrobacias de FormData con arrays anidados).
- En el legacy, el upload se hacía en el mismo formulario, pero en backend generaba errores 500 frecuentes.

Si el upload falla, el personal ya está guardado (no se hace rollback); se muestra el error general y el usuario puede re-subir la foto desde la pantalla de edición.

### 7.3 `permissions` adicional a roles

La pivot `personal_permisos` permite asignar permisos DIRECTOS a un usuario, además de los que hereda por sus roles. Esto replica el patrón legacy de "permisos especiales" sin agregar complejidad a `Spatie\Permission` (que ya soporta `givePermissionTo` por usuario, pero al usar `attach` directo sobre la pivot se evita el flush de caché que se gatilla en cada `givePermissionTo`).

### 7.4 Foto con `Storage::disk('public')`

Reutiliza el patrón del Hito 004a (Company). Requiere ejecutar `php artisan storage:link` una vez. La estructura es `storage/app/public/personal/{userId}/foto-{hash}.{ext}`.

---

## 8. Pendiente (siguiente iteración)

1. **Acciones batch en `PersonalListPage`** (habilitar/inhabilitar/eliminar masivo con checkbox).
2. **Exportar Personal** a Excel (no PDF — los reportes ya cubren el caso de uso).
3. **Mapa de Rutas** en la grilla (requiere Hito 005 Logistics).
4. **Avatar fallback**: si no hay foto, mostrar iniciales en círculo coloreado (estilo Google).
5. **Image optimization** (Intervention\Image: thumbnail 200x200 al subir).
6. **Activity log viewer**: vista por usuario con timeline de cambios (Spatie Activitylog ya guarda).
7. **Reportes adicionales**: Personal Inactivo, Cambios de Personal (audit log), Personal por Rol/Almacén (requiere params).
8. **Migrar reportes a PDF nativo** (`spatie/laravel-pdf`) si el cliente pide descarga directa sin pasar por el dialog de impresión.

---

## 9. Comandos de verificación

```bash
# Backend tests
cd backend && php artisan test --filter=PersonalTest
# → 30 passed, 0 failed (5 reportes + 2 nuevos: IDs permisos/listas/almacenes en resource)

# Backend tests (suite completa filtrada)
php artisan test --filter='PersonalTest|CompanyTest|EmpresaTest|InventoryTest|SaleTest|CompraTest|ProveedorTest|AuthTest'
# → 93 passed, 0 failed, 302 assertions

# Frontend build
cd frontend && npm run build
# → ✓ built in ~3s, sin errores TS

# Lint
cd frontend && npm run lint
# → 0 errores, 1 warning Personal (watch RHF API, pre-existente)

# Manual: probar wizard + reportes en browser
cd backend && php artisan serve --port=8005   # en una terminal
cd frontend && npm run dev                      # en otra terminal (puerto 5175)
# → http://localhost:5175/admin/personal (estado "Activo"/"No activo")
# → http://localhost:5175/admin/personal/{id}/editar (alert password + step 4 persistente)
# → http://localhost:5175/admin/personal/reportes
```

---

## 10. Archivos tocados

**Backend (25 archivos, +Reportes Personal 2026-06-07):**
- `app/Models/User.php` (fillable + casts ampliados; reemplazado `dni`/`ruc`/`documento_tipo_id` por `documento_identidad_id`)
- `app/Models/Catalog/Sexo.php` (nuevo)
- `app/Models/Catalog/EstadoCivil.php` (nuevo)
- `app/Models/Catalog/DocumentoIdentidad.php` (**nuevo en iteración**)
- `app/Modules/Personal/Models/Personal.php` (renombrada relación `documentoTipo` → `documentoIdentidad`)
- `app/Modules/Personal/Services/PersonalService.php` (+ helper `resolveNombreCompleto`; búsqueda por dni/ruc eliminada)
- `app/Modules/Personal/Policies/PersonalPolicy.php` (nuevo + **método `generarReportesPersonal`**)
- `app/Modules/Personal/Http/Controllers/PersonalController.php` (nuevo)
- `app/Modules/Personal/Http/Controllers/PersonalReportController.php` (**nuevo Reportes Personal**)
- `app/Modules/Personal/Http/Requests/StorePersonalRequest.php` (refactor: regex/longitud dinámicas)
- `app/Modules/Personal/Http/Requests/UpdatePersonalRequest.php` (refactor: regex/longitud dinámicas)
- `app/Modules/Personal/Http/Requests/PersonalReportRequest.php` (**nuevo Reportes Personal**)
- `app/Modules/Personal/Http/Resources/PersonalResource.php` (reemplazado `dni/ruc/documento_tipo` por `numero_documento/documento_identidad`)
- `app/Modules/Personal/Reports/PersonalReportService.php` (**nuevo Reportes Personal**)
- `app/Modules/Catalog/Http/Controllers/CatalogController.php` (+ `almacenes`, **+ `documentosIdentidad`**)
- `app/Modules/Auth/Services/AuthService.php` (login multi-campo: `dni`/`ruc` → `numero_documento`)
- `app/Modules/Auth/Http/Resources/UserResource.php` (reemplazado `dni`/`ruc` por `numero_documento`)
- `database/seeders/RoleAndPermissionSeeder.php` (4 permisos + **1 permiso Reportes Personal**)
- `database/factories/UserFactory.php` (reemplazado `dni`/`ruc` por `documento_identidad_id` + `numero_documento`)
- `database/migrations/2026_06_07_000001_add_personal_fields_to_users_table.php` (Hito 007)
- `database/migrations/2026_06_07_000002_create_personal_listas_precios_table.php` (Hito 007)
- `database/migrations/2026_06_07_000003_create_personal_almacenes_table.php` (Hito 007)
- `database/migrations/2026_06_07_000004_create_personal_permisos_table.php` (Hito 007)
- `database/migrations/2026_06_07_010000_create_dim_documento_identidad_table.php` (**nuevo en iteración**)
- `database/migrations/2026_06_07_010001_seed_default_almacen.php` (**nuevo en iteración**)
- `database/migrations/2026_06_07_010002_replace_documento_tipo_with_identidad.php` (**nuevo en iteración**)
- `routes/api.php` (10 rutas personal + 1 ruta catalog + **2 rutas reportes personal**)
- `resources/views/personal/reports/_print_wrapper.blade.php` (**nuevo Reportes Personal**)
- `resources/views/personal/reports/personal-activo.blade.php` (**nuevo Reportes Personal**)
- `resources/views/personal/reports/ficha-personal.blade.php` (**nuevo Reportes Personal**)
- `tests/Feature/PersonalTest.php` (nuevo, 28 tests post-Reportes, 5 nuevos para reportes)
- `tests/Feature/InventoryTest.php` (idempotente: usa `where(...)->value(...) ?? insertGetId` para ALM-001)

**Frontend (8 archivos, + ReportesPersonalPage):**
- `src/shared/types/index.ts` (+6 interfaces: Personal, PersonalPayload, Sexo, EstadoCivil, PermisoAgrupado, **DocumentoIdentidad**)
- `src/shared/api/endpoints.ts` (+ `personalApi`, + `catalogApi.almacenes`, **+ `catalogApi.documentosIdentidad`**, **+ `personalApi.reportePersonalActivo` y `personalApi.reporteFichaPersonal`**)
- `src/Admin/pages/Personal/PersonalFormPage.tsx` (reescrito, ~620 líneas: step 1 sin nombre_completo + eye toggle; step 2 Autocomplete; step 3 roles single-select)
- `src/Admin/pages/Personal/PersonalListPage.tsx` (nuevo, ~280 líneas: DataGrid server-side con búsqueda, filtros, paginación, acciones: editar/reset password/habilitar/eliminar)
- `src/Admin/pages/Personal/ReportesPersonalPage.tsx` (**nuevo Reportes Personal**, ~190 líneas: 2 Cards MUI 7 + Autocomplete + Snackbar de feedback)
- `src/App.tsx` (lazy import + 4 rutas: list, nuevo, editar, **reportes**)
- `src/Admin/layouts/AdminLayout.tsx` (deriveTitle para Personal, **+ reportes**)
- `src/env.d.ts` (tipado de `useForm`/`useQuery` con genéricos)

**Total:** 35 archivos (30 nuevos, 5 modificados) en iteración feedback + Reportes Personal.

---

### 3.5 PersonalListPage (DataGrid Gestión Personal)

Implementado basado en https://armorasac.com/app/personal/gestion-personal (legacy Semantic UI DataTable).

**Funcionalidades:**
- Header "Gestión del Personal" con botón "Crear Personal"
- Checkbox column (master checkbox + per-row, sin funcionalidad batch por ahora)
- Columnas: Código, Login, Nombre Completo, Documento, Estado, Acciones
- Documento se muestra como "DNI: 42920008" (formato `{codigo}: {numero_documento}`)
- Estado: Chip verde "HABI" (activo) / rojo "INHA" (inactivo)
- Per-page selector (10, 25, 50, 100, 250, 500) + paginación server-side
- Búsqueda con debounce implícito (onChange → setSearch → setPage(0))
- Paginación estilo "1-15 de 23"

**Acciones por fila (IconButtons con Tooltip):**
- Editar → navega a `/admin/personal/{id}/editar`
- Cambiar Contraseña → Dialog modal con password + confirm + validación coincidencia
- Habilitar/Inhabilitar → toggle directo (POST /personal/{id}/toggle-activo) con Snackbar confirmación
- Eliminar → Dialog de confirmación con soft delete (DELETE /personal/{id})

**Backend añadido:**
- `PersonalService::toggleActivo(Personal)` — alterna `activo` boolean
- `PersonalService::resetPassword(Personal, string)` — actualiza password + `password_changed_at`
- `PersonalController::toggleActivo()` — endpoint POST
- `PersonalController::resetPassword()` — endpoint POST con validación `confirmed`
- Rutas: `POST /personal/{personal}/toggle-activo`, `POST /personal/{personal}/reset-password`
- `personalApi.toggleActivo(id)` y `personalApi.resetPassword(id, password, confirm)` en frontend endpoints.ts

**Ruta en App.tsx:** `/admin/personal` → `PersonalListPage` (lazy import)

**Fix de carga de grilla (`/admin/personal`):**
- `GET /api/personal` ahora responde con `PersonalResource::collection($paginator)`, manteniendo contrato `data` + `meta.total` y filas con `documento_identidad`.
- La grilla lee `meta.total` y conserva compatibilidad temporal con paginador crudo (`total`) para no romper entornos intermedios.
- La UI muestra errores HTTP en la tabla. Si aparece 403, ejecutar `cd backend && php artisan db:seed --class=RoleAndPermissionSeeder` y luego `php artisan permission:cache-reset` para sincronizar permisos `ver/crear/editar/eliminar-personal` en una BD Docker antigua.
- `DatabaseSeeder` usa `documento_identidad_id` + `numero_documento` y `updateOrCreate` para los usuarios demo `admin` y `vendedor`.

### 3.6 ReportesPersonalPage (Reportes Personal)

Implementado basado en https://armorasac.com/app/personal/reportes-personal (legacy Semantic UI stackable two cards).

**Funcionalidades:**
- Header "Reportes de Personal" con descripción de uso
- 2 Cards MUI 7 en Grid responsive (`xs=12, md=6`)

**Card 1 — Personal Activo:**
- Ícono `GroupsIcon` azul
- Botón naranja (`#f97316`) "Generar Reporte" fullWidth
- Al click: abre nueva pestaña con HTML printable que lista TODO el personal activo (`activo=true`), ordenado por apellido paterno/materno/nombres
- Tabla con: Código, Nombre Completo, Documento (DNI: 12345678), Email, Roles (badges), Almacenes (códigos), Listas de Precios
- Footer con conteo total

**Card 2 — Ficha Personal:**
- Ícono `PersonIcon` azul
- `Autocomplete` MUI con búsqueda en vivo contra `GET /api/personal?per_page=500` (lazy)
- Formato: `{codigo} — {nombre_completo}`
- Botón naranja "Generar Reporte" deshabilitado hasta seleccionar personal
- Al click: abre nueva pestaña con ficha completa del personal

**Ficha personal incluye 5 secciones (`ficha-section` con `page-break-inside: avoid`):**
1. **Identificación** — Código, Estado (badge), Nombre, Username, Documento, Sexo, Estado Civil, Fecha Nacimiento + Foto (110x130px con `<img>` o "Sin foto")
2. **Contacto y Ubicación** — Email, Teléfono, Celular, País, Dirección (con departamento/provincia/distrito concatenados)
3. **Roles Asignados** — badges verdes por rol
4. **Permisos Directos Adicionales** (solo si hay) — badges grises
5. **Almacenes Asignados** — tabla (Código, Nombre, Dirección)
6. **Listas de Precios Asignadas** — tabla
7. **Información del Sistema** — Último Acceso, Contraseña Cambiada, Creado, Actualizado

**Toolbar print-only:** barra fija superior con botones "Imprimir / Guardar PDF" y "Cerrar". Al imprimir (`@media print`), la toolbar se oculta vía `display: none !important`.

**Backend añadido:**
- `app/Modules/Personal/Reports/PersonalReportService.php` (nuevo, 2 métodos: `getPersonalActivo()`, `findForFicha($id)` con eager loading completo)
- `app/Modules/Personal/Http/Controllers/PersonalReportController.php` (nuevo, 2 endpoints: `personalActivo()`, `fichaPersonal(PersonalReportRequest)`)
- `app/Modules/Personal/Http/Requests/PersonalReportRequest.php` (nuevo, valida `pid` required+integer+exists:users,id)
- `app/Modules/Personal/Policies/PersonalPolicy.php` (+ método `generarReportesPersonal(User)` que valida `generar-reportes-personal`)
- `resources/views/personal/reports/_print_wrapper.blade.php` (nuevo, layout A4 con CSS print + toolbar "Imprimir/Guardar PDF")
- `resources/views/personal/reports/personal-activo.blade.php` (nuevo, tabla con conteo total)
- `resources/views/personal/reports/ficha-personal.blade.php` (nuevo, 7 secciones con `page-break-inside: avoid`)
- `database/seeders/RoleAndPermissionSeeder.php` (+ 1 permiso `generar-reportes-personal` asignado a `Admin` y `Gerente`)
- `routes/api.php` (+ 2 rutas: `GET /api/personal/reportes/personal-activo`, `GET /api/personal/reportes/ficha-personal` con `permission:generar-reportes-personal`)
- `tests/Feature/PersonalTest.php` (+ 5 tests: 200 admin/403 vendedor/personal activo/ficha + validación `pid` required/exists)

**Frontend añadido:**
- `src/Admin/pages/Personal/ReportesPersonalPage.tsx` (nuevo, ~190 líneas, 2 Cards MUI 7 + Autocomplete + Snackbar de feedback)
- `src/shared/api/endpoints.ts` (+ `personalApi.reportePersonalActivo()` y `personalApi.reporteFichaPersonal(id)` con `responseType: 'blob'`)
- `src/App.tsx` (lazy import + ruta `/admin/personal/reportes`)
- `src/Admin/layouts/AdminLayout.tsx` (`deriveTitle` extendido: `/admin/personal/reportes` → "Reportes Personal")

**Decisión clave — HTML printable vs PDF nativo:** No se instaló `barryvdh/laravel-dompdf` ni `spatie/laravel-pdf` (cero dependencias nuevas). El backend devuelve HTML con CSS `@media print` y A4 `@page` rules; el frontend abre el HTML en nueva pestaña via `window.open` + `document.write`. La barra superior del reporte ofrece el botón "Imprimir / Guardar PDF" que dispara el dialog nativo del navegador (`window.print()`), permitiendo:
- Vista previa WYSIWYG exacta antes de imprimir
- Selección de impresora o "Guardar como PDF" como destino
- Sin headless Chromium ni 200MB de dependencias

Si en el futuro se requiere PDF directo (sin pasar por la UI del navegador), se puede migrar a `spatie/laravel-pdf` (Chromium headless) o `barryvdh/laravel-dompdf` (más liviano pero menos preciso en CSS moderno).

**Trade-offs aceptados:**
- El reporte no es descargable como archivo `.pdf` directo desde la UI; el usuario debe hacer "Guardar como PDF" en el dialog de impresión
- La nueva pestaña requiere permiso de popups (en navegadores restrictivos el usuario debe permitirlo)

**Mapeo legacy 1:1:**

| Legacy armorasac.com | ARMORA NextGen |
|---|---|
| `<div class="ui stackable two cards">` | `<Grid container spacing={3}>` con 2 `<Grid size={{ xs:12, md:6 }}>` |
| `<div class="card">` con `<div class="header">` + `<div class="description">` | `<Card>` + `<CardContent>` con `<Typography variant="h5">` + `<Typography variant="body2">` |
| Botón naranja `<div class="ui bottom attached orange button">` | `<CardActions>` + `<Button fullWidth sx={{ bgcolor:'#f97316' }}>` |
| `<i class="file pdf icon"></i>` | `<PictureAsPdfIcon />` (Material Icons) |
| `<select name="pid" id="dropdown-personal">` con `<option value="1">` etc. | `<Autocomplete>` MUI con `getOptionLabel` = `${codigo} — ${nombre_completo}` |
| `id="button-personal-activo"` (jQuery click) | `onClick={handleGenerarActivo}` (React) |
| `id="button-ficha-personal"` (jQuery click) | `onClick={handleGenerarFicha}` (React) |
| POST con form a PHP que devuelve `header('Content-Type: application/pdf')` | GET con `Authorization: Bearer` a Laravel que devuelve `Content-Type: text/html; charset=UTF-8` |

## 12. Pendientes (próximos pasos)

- PersonalListPage: Implementar batch actions (habilitar/inhabilitar/eliminar masivo con checkbox)
- PersonalListPage: Columna "Mapa de Rutas" (requiere Hito 005 Logistics)
- PersonalListPage: Exportar a Excel (CSV)
- Fotografía: optimization con Intervention\\Image, avatar fallback con iniciales
- Activity log viewer
- Portal Proveedor (vista de sus órdenes)
- Reportes Personal: agregar PDF directo (sin pasar por print dialog) si el cliente lo pide
- Reportes Personal: filtros adicionales (por rol, por almacén, por fecha de ingreso)

---

## 13. Iteración UX edición (2026-06-07)

Aplicada tras feedback de auditoría visual de la pantalla `/admin/personal` → "Editar Personal".

### 13.1 Cambios solicitados

1. **Estado en `PersonalListPage`**: Cambiar etiquetas `HABI` / `INHA` por `Activo` / `No activo`.
2. **Contraseñas no se previsualizan al editar**: Por seguridad, las contraseñas están hasheadas con `Hash::make()` (bcrypt) en la BD — la API **NO PUEDE** devolver el plaintext. Se agregó un `Alert` info en el step 1 del wizard explicando el motivo y cómo proceder.
3. **Step 4 Permisos Directos**:
   - Persistencia: al editar, los permisos directos guardados no aparecían seleccionados (resource retornaba nombres, form esperaba IDs).
   - UX: agregar "Seleccionar todo" / "Deseleccionar todo" global y checkbox por grupo (módulo) con estado `indeterminate`.
4. **Step 4 Listas de Precios + Almacenes**:
   - Persistencia: al editar, los IDs guardados no se pre-cargaban (resource retornaba objetos `{id, nombre}`, form esperaba `int[]`).

### 13.2 Cambios aplicados

**Backend (1 archivo):**
- `app/Modules/Personal/Http/Resources/PersonalResource.php`: 3 campos nuevos que retornan `int[]`:
  - `permisos`: IDs de `permisosDirectos` (sin prefijo `_ids` para mantener consistencia con el form)
  - `listas_precios_ids`: IDs de `listasPrecios` (con prefijo para no colisionar con el array de objetos `listas_precios`)
  - `almacenes_ids`: IDs de `almacenes` (mismo motivo)

**Tests backend (2 nuevos):**
- `test_show_personal_includes_related_ids_as_int_arrays`: valida que los 3 campos se serializan como `int[]` correctos
- `test_show_personal_includes_empty_arrays_when_no_relations`: valida caso sin relaciones (arrays vacíos)

**Frontend (2 archivos):**
- `src/shared/types/index.ts`: `Personal` interface extendida con 3 campos: `permisos: number[]`, `listas_precios_ids: number[]`, `almacenes_ids: number[]`
- `src/Admin/pages/Personal/PersonalFormPage.tsx`:
  - `reset()` mapea los 3 campos correctamente (`permisos: data.permisos`, `listas_precios: data.listas_precios_ids`, `almacenes: data.almacenes_ids`)
  - Step 1 (Datos Personales) — `Alert severity="info"` con icono `LockIcon` explicando seguridad de passwords (solo en modo edición)
  - Helper text del password en edición: "Dejar en blanco para mantener la actual"
  - Step 4 (Permisos y Accesos) — Permisos Directos:
    - Header con counter "X de Y permisos seleccionados"
    - Botón "Seleccionar todo" (selecciona todos los IDs de todos los módulos)
    - Botón "Deseleccionar todo" (limpia el array)
    - Cada grupo (módulo) tiene:
      - Checkbox maestro con `indeterminate` cuando hay selección parcial
      - Click en checkbox de grupo: si todos están marcados → desmarca ese grupo; si no → marca todo el grupo
      - Counter inline "N / M" al lado derecho del header
- `src/Admin/pages/Personal/PersonalListPage.tsx`:
  - Chip de estado: `HABI` → `Activo`, `INHA` → `No activo`

### 13.3 Decisiones técnicas

- **Mantener `listas_precios` y `listas_precios_ids` separados**: el resource sigue retornando el array de objetos para mostrar nombre en UI; el `_ids` es solo para el form. Esto evita breaking changes con `PersonalListPage` y `ReportesPersonalPage` que ya consumen `listas_precios` como objetos.
- **Permisos se llaman `permisos` (sin sufijo)**: el form ya esperaba `permisos: number[]`, así que el field name se mantiene; los `permisos_directos` (nombres como strings) se conservan para casos de uso que requieren el slug legible.
- **Group checkbox usa `setValue('permisos', merged, { shouldDirty: true })`** (no `field.onChange`): el form está en modo RHF pero el campo se gestiona manualmente con `setValue`/`getValues` (no usa `<Controller>`), por lo que `shouldDirty: true` es necesario para que RHF registre el cambio y dispare la validación/submit.
- **Alert con `icon={<LockIcon fontSize="inherit" />}`**: el icono por defecto del `Alert severity="info"` es `InfoOutlinedIcon`; lo reemplazamos por `LockIcon` para reforzar visualmente el contexto de seguridad.

### 13.4 Trade-offs aceptados

- El usuario NO puede ver la contraseña actual nunca (decisión correcta de seguridad; OWASP A02:2025 — Cryptographic Failures).
- Si el usuario olvidó la contraseña, debe usar la acción "Cambiar Contraseña" del listado (botón con `LockIcon`) o pedirle al admin que use el mismo flujo.
- El botón "Seleccionar todo" puede asignar muchos permisos de golpe. No hay confirmación. Si se hace por error, el usuario puede desmarcar manualmente. (Mejora futura: `Dialog` de confirmación si se marcan >20 permisos.)

### 13.5 Validación

| Check | Resultado |
|---|---|
| `php artisan test --filter=PersonalTest` | **30/30 passing** (122 assertions) |
| Suite filtrada completa | **93/93 passing** (302 assertions) |
| `npm run build` | ✅ built in 2.99s |
| `npm run lint` | ✅ 0 errors, 1 warning pre-existente |
| Curl `GET /api/personal/1` | 200 con `permisos:[1,2,3,4]`, `listas_precios_ids:[1]`, `almacenes_ids:[1]` |

---

## 11. Lecciones aprendidas

1. **Catálogo ≠ dominio**: `dim_documento_tipo` contiene comprobantes de pago (Factura/Boleta/NC), no documentos de identidad. Mezclar ambos en el mismo campo (`documento_tipo_id` en `users`) fue un error de diseño del Hito 003; este hito lo corrige con un catálogo dedicado (`dim_documento_identidad`).
2. **Regex con delimitadores**: Laravel `Validator::regex` requiere que el patrón venga delimitado (`/^...$/`). El seed inicial usó `^\d{8}$` sin delimitadores, lo que rompía la validación con `preg_match(): No ending delimiter '^' found`. Solución: almacenar regex con delimitadores en la columna, o wrapper con delimitadores en el FormRequest.
3. **Soft-delete + RefreshDatabase + seed idempotente**: `insertOrIgnore` es la forma idiomática de seeds en Laravel cuando se combina con `RefreshDatabase` (migraciones corren antes de cada test, evitando duplicados en `RefreshDatabase`).
4. **Radio buttons vs chips**: para single-select con pocos items, una lista vertical con íconos `RadioButtonChecked`/`RadioButtonUnchecked` ofrece UX más clara que un Autocomplete (evita tipeo, muestra todos los roles, ocupa poco espacio). La validación Zod `roles.max(1)` + `field.onChange(checked ? [] : [name])` garantiza single-select incluso con click rápido.
5. **`$fillable` centralizado en `User`**: agregar campos al modelo hijo (`Personal`) sin tocar el padre (`User`) hace que `update()` los descarte silenciosamente. Centralizar `$fillable` en `User` evita duplicación y errores sutiles.
