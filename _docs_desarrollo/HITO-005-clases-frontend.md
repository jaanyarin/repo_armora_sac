# Hito 005 (frontend) — Productos Clases y Subclases

**Fecha**: 2026-06-07
**Estado**: ✅ Implementado
**Módulo**: Products (frontend Admin)
**Página**: `/admin/productos/clases` → `ProductosClasesPage.tsx`

---

## Resumen ejecutivo

Se implementó la pantalla **Gestión de Clases y Subclases** del Admin de ARMORA NextGen, partiendo del listado legacy de `armorasac.com/admin_producto_clases.php` y aplicando mejoras de UX/UI con MUI 7 + React 19 + TanStack Query 5 + RHF 7 + Zod. La página reutiliza el backend de Products (Hito 005 productos_clases) implementado en este mismo hito, validado por `ProductoClaseTest` (18/18) y `ProductoSubclaseTest` (19/19).

## Alcance

- ✅ Vista master-detail con Grid responsivo (stacks en `<md`).
- ✅ Listado paginado de Clases con búsqueda en vivo (debounce 300 ms) + filtros `activo` y `licor`.
- ✅ Detalle de Subclases paginadas, en panel derecho, con búsqueda local.
- ✅ Formularios RHF + Zod con validaciones inline y modo `crear` / `editar`.
- ✅ Acciones: crear, editar, eliminar (con confirm dialog) y soft-delete de subclases.
- ✅ Bloqueo visual de clases inactivas con Chip "Inactivo" y feedback por Snackbar.
- ✅ Integración con `productoClasesApi` y `productoSubclasesApi`.
- ⏸ Fuera de alcance: drag&drop reorder (campo `orden` y método `reorder()` del backend quedan disponibles para v2).

## Análisis del form legacy

URL legacy: `armorasac.com/intranet/admin_producto_clases.php` (página de configuración con dos DataTables lado a lado: Clases y Subclases).

Observaciones del legacy y decisiones de migración:

| Legacy (Semantic UI) | NextGen (MUI 7) | Justificación |
|---|---|---|
| 2 tablas lado a lado en una sola pantalla | Master-detail con Grid responsivo | UI se rompe en < 1200 px. El layout master-detail es estándar para jerarquías padre-hijo (estilo Gmail/Linear). |
| `flag licor` visible como texto | `Chip` con ícono (LocalBar) y color semántico | Distinguir visualmente el flag en < 100 ms sin leer. |
| Sin búsqueda en DataTable | `TextField` con debounce 300 ms sobre `search` param | Backend ya soporta `search` (Hito 005 backend). |
| Filtros como dropdowns | `Switch` + `Switch` + `Tooltip` "Mostrar solo activos"/"Mostrar solo licor" | Inmediatez: 1 click vs 2. Reduce fricción para el operario. |
| Sin estado de selección vacío | `EmptyState` con botón "Crear primera clase" | Patrón ya usado en `PersonalListPage`. |
| Botón "+" pequeño en cada fila | Botón flotante con `SpeedDial` (futuro) | Se optó por `Button` "Nueva Clase" en AppBar para v1. |
| Sin confirmación al eliminar | `ConfirmDialog` MUI con advertencias (`subclases_eliminadas` count) | Eliminar clase cascada a subclases; se debe mostrar cuántas se eliminarán. |

## Decisiones técnicas

### Stack y patrones

- **MUI 7 Grid v2** con `size={{ xs: 12, md: 5 }}` para master-detail responsivo.
- **TanStack Query 5** con keys `['producto-clases', filters]` y `['producto-subclases', { clase_id, per_page: 100 }]`. `invalidateQueries` al éxito de mutations.
- **RHF 7 + Zod** en formularios (alineado con `PersonalFormPage` y `ClaseFormDialog`).
- **`useDebouncedValue` inline** (~5 líneas) para evitar hook nuevo.
- **Snackbar MUI** para feedback; `Alert` top-of-form para errores de validación.

### Layout

- **2 columnas principales** (Grid v2):
  - **Izquierda (md=5)**: lista de clases en `Card` apilados + buscador + 2 Switches + botón "Nueva clase".
  - **Derecha (md=7)**: header con nombre de la clase seleccionada + tabla de subclases + botón "Nueva subclase" (deshabilitado si no hay clase seleccionada).
- En `<md` se apila verticalmente: primero la lista, después el detalle.
- **Sticky top header** con el nombre de la clase seleccionada para que el usuario nunca pierda el contexto al scrollear subclases.

### Selección de clase

- `selectedClaseId: string | null` en estado local.
- Click en una `ClaseCard` actualiza el ID y dispara refetch de subclases.
- El ID se persiste solo en memoria (no en URL en v1) — el patrón "deep link" se puede agregar después con `useSearchParams`.

### Formularios

- **ClaseFormDialog**:
  - RHF + Zod (`{ nombre, codigo?, descripcion?, licor, activo }`).
  - En modo edición, muestra `codigo` (auto-generado en create) y `slug` (read-only).
  - Validación `nombre` min 2 max 100, `codigo` opcional regex `CLS-\d{5}` en update.
- **SubclaseFormDialog**:
  - RHF + Zod (`{ nombre, descripcion?, activo }`).
  - `clase_id` se inyecta desde la clase seleccionada, no editable.
  - Validación: nombre único por clase (validación server-side + UI).

### Eliminación

- **Clase**: `ConfirmDialog` MUI mostrando cantidad de subclases que se eliminarán (backend devuelve `subclases_eliminadas`).
- **Subclase**: `ConfirmDialog` simple con el nombre de la subclase.

## Contrato API usado

| Endpoint | Uso | Permiso requerido |
|---|---|---|
| `GET /api/products/clases?search=&activo=&licor=&sort_by=&sort_dir=&per_page=` | Listado paginado | `ver-clases` |
| `GET /api/products/clases/{id}` | Detalle de clase | `ver-clases` |
| `POST /api/products/clases` | Crear clase | `crear-clases` |
| `PUT /api/products/clases/{id}` | Actualizar clase | `editar-clases` |
| `DELETE /api/products/clases/{id}` | Eliminar clase (cascade) | `eliminar-clases` |
| `GET /api/products/subclases?clase_id=&per_page=` | Listado paginado | `ver-subclases` |
| `GET /api/products/subclases/{id}` | Detalle | `ver-subclases` |
| `POST /api/products/subclases` | Crear subclase | `crear-subclases` |
| `PUT /api/products/subclases/{id}` | Actualizar subclase | `editar-subclases` |
| `DELETE /api/products/subclases/{id}` | Eliminar subclase | `eliminar-subclases` |

### Payloads

**`POST /api/products/clases`**:
```json
{
  "nombre": "Vinos",
  "descripcion": "Vinos tintos, blancos y rosados",
  "licor": true,
  "activo": true
}
```

**`POST /api/products/subclases`**:
```json
{
  "clase_id": "01HXY...ULID",
  "nombre": "Vino tinto",
  "descripcion": null,
  "activo": true
}
```

Errores 422 se mapean a `form.setError(field, { message })` y muestran `HelperText` rojo bajo cada campo.

## Archivos tocados

| Archivo | Cambio |
|---|---|
| `backend/database/migrations/2026_06_07_030000_create_products_clases_table.php` | **Nuevo** (ULID, SoftDeletes, LogsActivity, índice único slug) |
| `backend/database/migrations/2026_06_07_030001_create_products_subclases_table.php` | **Nuevo** (ULID, SoftDeletes, FK a `products_clases`, índice único `(clase_id, nombre)`) |
| `backend/app/Modules/Products/Models/ProductoClase.php` | **Nuevo** (HasUlids, SoftDeletes, LogsActivity, relaciones) |
| `backend/app/Modules/Products/Models/ProductoSubclase.php` | **Nuevo** (HasUlids, SoftDeletes, LogsActivity, relaciones) |
| `backend/app/Modules/Products/Services/ProductoClaseService.php` | **Nuevo** (CRUD + reorder + count) |
| `backend/app/Modules/Products/Services/ProductoSubclaseService.php` | **Nuevo** (CRUD) |
| `backend/app/Modules/Products/Policies/ProductoClasePolicy.php` | **Nuevo** |
| `backend/app/Modules/Products/Policies/ProductoSubclasePolicy.php` | **Nuevo** |
| `backend/app/Modules/Products/Http/Requests/StoreProductoClaseRequest.php` | **Nuevo** (validación + mensajes en español) |
| `backend/app/Modules/Products/Http/Requests/UpdateProductoClaseRequest.php` | **Nuevo** |
| `backend/app/Modules/Products/Http/Requests/StoreProductoSubclaseRequest.php` | **Nuevo** (validación composite unique) |
| `backend/app/Modules/Products/Http/Requests/UpdateProductoSubclaseRequest.php` | **Nuevo** |
| `backend/app/Modules/Products/Http/Resources/ProductoClaseResource.php` | **Nuevo** (con `whenLoaded` y `subclases_count`) |
| `backend/app/Modules/Products/Http/Resources/ProductoSubclaseResource.php` | **Nuevo** |
| `backend/app/Modules/Products/Http/Controllers/ProductoClaseController.php` | **Nuevo** (CRUD + reorder + cascade) |
| `backend/app/Modules/Products/Http/Controllers/ProductoSubclaseController.php` | **Nuevo** |
| `backend/routes/api.php` | + 12 rutas (6 clases + 6 subclases) + constraint `where('clase', '[0-9A-Za-z]{26}')` |
| `backend/database/seeders/RoleAndPermissionSeeder.php` | + 8 permisos y asignación a 8 roles |
| `backend/database/factories/Modules/Products/Models/ProductoClaseFactory.php` | **Nuevo** |
| `backend/database/factories/Modules/Products/Models/ProductoSubclaseFactory.php` | **Nuevo** |
| `backend/tests/Feature/ProductoClaseTest.php` | **Nuevo** (18 tests) |
| `backend/tests/Feature/ProductoSubclaseTest.php` | **Nuevo** (19 tests) |
| `frontend/src/shared/types/index.ts` | + `ProductoClase`, `ProductoSubclase`, `ProductoClasePayload`, `ProductoSubclasePayload`, renombrar legacy `ProductoClase` → `DimProductoClase` |
| `frontend/src/shared/api/endpoints.ts` | + `productoClasesApi`, `productoSubclasesApi` |
| `frontend/src/Admin/pages/Products/Clases/ProductosClasesPage.tsx` | **Nuevo** (~860 líneas, master-detail con RHF + Zod) |
| `frontend/src/App.tsx` | + ruta lazy `/admin/productos/clases` (ANTES de `/admin/productos`) |
| `frontend/src/Admin/layouts/AdminLayout.tsx` | + `deriveTitle` para `/admin/productos/clases` → "Clases y Subclases" |

## Validación

- ✅ `composer test` — **142/142 tests passing** (37 nuevos), 411 assertions.
- ✅ `npm run lint` — 0 errores (warning preexistente en `PersonalFormPage.tsx` no relacionado).
- ✅ `npx tsc --noEmit` — 0 errores (tras renombrar `ProductoClase` legacy → `DimProductoClase` para resolver conflicto de tipo ULID vs number).
- ✅ `npm run build` — bundle emitido `ProductosClasesPage-DVkTBYvt.js` (19.03 kB / 5.65 kB gzip).
- ⏳ Pruebas manuales pendientes (ver plan abajo) — se ejecutan en sesión E2E separada con backend levantado.

- âœ… `npm run test` â€” 9/9 tests passing (fuera del sandbox por `spawn EPERM` de Vite/Rolldown).
- âœ… `npm run test:e2e` â€” 8/8 Playwright passing (fuera del sandbox por `EPERM` en `test-results`).

## Plan de pruebas manual

Con backend en `http://localhost:8005` y seeders aplicados:

1. **Happy path — crear clase**:
   - Login como usuario con rol `Admin` o `Inventario`.
   - Navegar a `/admin/productos/clases`.
   - Click "Nueva clase" → llenar nombre "Vinos", marcar `Licor` → guardar.
   - Verificar que aparece en la lista con Chip verde "LICOR" + Snackbar verde.
   - Verificar que la URL sigue siendo `/admin/productos/clases` (no deep-link en v1).

2. **Happy path — crear subclase**:
   - Seleccionar la clase recién creada.
   - Click "Nueva subclase" en el panel derecho.
   - Llenar nombre "Vino tinto", guardar.
   - Verificar que aparece en la tabla del panel derecho + Snackbar verde.

3. **Búsqueda y filtros**:
   - Crear 5 clases (3 con `licor=true`, 2 con `activo=false`).
   - Buscar "vin" → solo aparece "Vinos".
   - Toggle "Mostrar solo licor" → solo 3 clases visibles.
   - Toggle "Mostrar solo activos" → solo las activas.
   - Combinar ambos → intersección.

4. **Edición**:
   - Click en clase "Vinos" → "Editar" en menú de acción (o doble click en la card).
   - Cambiar descripción, desmarcar licor → guardar.
   - Verificar que el Chip "LICOR" desaparece y la descripción se actualiza.

5. **Eliminación con cascade**:
   - Eliminar la clase "Vinos" (que tiene 2 subclases).
   - Confirm dialog debe decir "Se eliminarán también 2 subclase(s)".
   - Aceptar → Snackbar "Clase eliminada (2 subclase(s) también).".
   - Verificar que las subclases ya no aparecen en la BD (`soft delete`).

6. **Validaciones**:
   - Crear clase con nombre "X" (1 char) → validación inline "Mínimo 2 caracteres".
   - Crear subclase con nombre duplicado en la misma clase → error 422 con mensaje del backend.
   - Editar subclase con `clase_id` distinto → error de autorización.

7. **Edge cases**:
   - Eliminar clase sin subclases → Snackbar "Clase eliminada." (sin mencionar subclases).
   - Sin clases → EmptyState con botón "Crear primera clase".
   - Backend caído → Snackbar de error con mensaje del backend.

## Trabajo futuro

1. **Drag&drop reorder** — `ProductoClaseService::reorder()` y campo `orden` ya están en BD; solo falta UI (e.g. `@dnd-kit/core`).
2. **Deep-link con URL** — `?claseId=...` para compartir/permalinks.
3. **Vista de detalle expandida** — mostrar productos asociados a cada clase/subclase (cuando `products` referencie estas tablas).
4. **Bulk actions** — selección múltiple de clases/subclases con toolbar (eliminar varios, activar/desactivar varios).
5. **Importar/Exportar CSV** — para migrar desde el legacy armorasac.com.
6. **Paginación server-side en subclases** — actualmente `per_page: 100` hardcoded; pasar a DataGrid server-side si se supera.
7. **Paginación cursor-based** — para > 10k clases (PostgreSQL ya está listo con ULID).

## Notas operativas

- **Permisos**: 8 nuevos permisos (4 clases + 4 subclases) asignados a 8 roles en `RoleAndPermissionSeeder`. La sidebar entry debe agregarse en próxima iteración (no incluida en este commit para mantener el scope).
- **Conflict de tipos resuelto**: el legacy `ProductoClase` (de `dim_producto_clase`, `id: number`) se renombró a `DimProductoClase` en `shared/types/index.ts` para evitar colisión con el nuevo `ProductoClase` (de `products_clases`, `id: string`/ULID). Solo afecta a código que use el tipo legacy (revisión manual recomienda grep por `ProductoClase[^G]` y `ProductoSubclase[^G]`).
- **Backend vs frontend diff**: el backend fue implementado en este mismo sprint (no en Hito anterior); ver commit "Hito 005 — Productos Clases y Subclases (backend + frontend)" para el alcance completo.
- **Sin `php artisan storage:link` requerido** (no hay imágenes en este módulo).
- **Multi-tenant safe**: las tablas usan ULID + SoftDeletes, patrón consistente con `users`, `purchases_*`, `inventory_*`.
