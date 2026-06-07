# Hito 005 (frontend) — PurchaseFormPage

**Fecha**: 2026-06-07
**Estado**: ✅ Implementado
**Módulo**: Purchases (frontend Admin)
**Página**: `/admin/compras/nueva` → `PurchaseFormPage.tsx`

---

## Resumen ejecutivo

Se implementó la pantalla **Crear Compra** del Admin de ARMORA NextGen, partiendo del form legacy de `armorasac.com` y aplicando mejoras de UX/UI con MUI 7 + React 19 + TanStack Query 5. La página reutiliza el backend de Hito 004 (Purchases Ola A+B) que ya estaba completo, validado por `CompraTest` (13/13) y `ProveedorTest` (6/6).

## Alcance

- ✅ Formulario Admin para crear una compra
- ✅ Integración con `purchasesApi` (POST) y `proveedoresApi` (búsqueda)
- ✅ Cálculo IGV 18% en vivo, fuente única: `shared/utils/igvCalculator.ts`
- ✅ Bloqueo proactivo cuando `empresa.compras_bloqueadas = true`
- ✅ Snapshot de `costo_promedio` del producto al elegir ítem
- ✅ Acciones duales: "Guardar borrador" y "Crear y confirmar"
- ✅ Estados UI: loading skeletons, empty state, error de validación, snackbar feedback
- ⏸ Fuera de alcance (mantener iteración): diálogo quick-create proveedor; selector de serie/número; edición de borrador (ruta `/admin/compras/:id/editar` queda pendiente hasta definir si compras en borrador son editables).

## Análisis del form legacy

URL legacy: `armorasac.com/intranet/agregar_compra.php`

Observaciones del legacy y decisiones de migración:

| Legacy (Semantic UI) | NextGen (MUI 7) | Justificación |
|---|---|---|
| `Dropdown` proveedor cargado completo al inicio | `Autocomplete` server-side con `filterOptions={(x) => x}` | Backend ya tiene `index` con parámetro `search` (ADR-005 mantiene `DB::table` con paginación). Reduce payload inicial en catálogos grandes. |
| Stock + precio unitario en columnas separadas del proveedor | Tabla de ítems con columna "Stock actual" (Chip) + "Costo sincronizado" (Chip + SyncIcon) | Da contexto inmediato al operador sin navegar a la ficha de producto. |
| Subtotal/IGV/Total siempre visibles, sin distinción de fuente única | Cálculo con `calcularTotalesIgv` (mismo helper que backend) | Una sola fuente matemática; elimina drift entre UI y backend. |
| Guardar y Confirmar en el mismo botón | 2 CTAs: "Guardar borrador" (outlined) + "Crear y confirmar" (contained) | Refleja el modelo de estado backend (`borrador` / `confirmada`); permite que Comprador capture la OC y luego la confirme tras validar factura. |
| Sin validación visible de bloqueo de empresa | `Alert severity="warning"` + deshabilita campos + CTAs bloqueados | La opción de Empresa "Bloquear compras" ya estaba implementada en backend; el form debe respetarla antes de hacer POST. |
| Búsqueda de productos = `Dropdown` con todos | `Autocomplete` carga inicial con `per_page=200 activo=true` (suficiente para MVP) | Catálogo de productos típicamente ≤ 200 activos. Migrar a server-side cuando se supere. |
| Sin acceso directo a crear proveedor | Link inline "Crear nuevo proveedor" → `/admin/proveedores/nuevo` | Mantiene scope (no requiere modal); preserva navegación. |

## Decisiones técnicas

### Stack y patrones
- **MUI 7 + plain `useState`** (no RHF/Zod) — alineado con `SaleFormPage` (análogo más cercano).
- **TanStack Query 5** con `invalidateQueries` de `'compras'`, `'products'`, `'stock'`, `'kardex'` al éxito (preparado para integración futura con páginas list/kardex).
- **Snackbar MUI** para feedback; **Alert top-of-form** para errores de validación.
- **Sin nuevo hook util** (`useDebouncedValue` se implementó inline en el archivo; ~5 líneas).

### Cálculo de totales
- Fuente única: `frontend/src/shared/utils/igvCalculator.ts` (existente).
- Línea: `subtotal = cantidad * precio_unitario - descuento_linea`.
- IGV se calcula **sobre la suma de subtotales** (no línea por línea) — mismo comportamiento que `SaleService::calcularLineaIgv` (Hito 003 fix: `igv = total - gravada` post-rounding).

### Estado
- `items: ItemRow[]` con `key: string` (`crypto.randomUUID()`) — única clave estable para reconciliar filas.
- `precio_unitario` y `costSynced` se acoplan: editar el campo en mano desactiva el Chip "Costo sincronizado" (evita confusión sobre el origen del valor).

### Bloqueo
- Hook `useQuery(['empresa-config'])` reusa `empresaApi.get()`.
- Si `empresa.compras_bloqueadas`:
  - Banner naranja con `BlockIcon` y referencia a la ruta de desbloqueo.
  - **Todos los campos quedan `disabled`** (formDisabled gate), incluyendo los 2 CTAs.
  - No se hace POST; se evita golpear la API cuando el resultado es 422 garantizado.

### Layout
- **2 columnas principales**: "Datos generales" (Paper) + "Productos" (Paper).
- **Sticky bottom bar** con subtotal, IGV, total y los 2 CTAs. Visible al scrollear tablas largas.
- **Responsive** (xs/md breakpoints MUI Grid V2): 1 columna en móvil, 2 en desktop.

## Contrato API usado

| Endpoint | Uso | Permiso requerido |
|---|---|---|
| `GET /api/empresa` | Detectar `compras_bloqueadas` | `ver-configuracion` |
| `GET /api/purchases/proveedores?search=&per_page=20&activo=true` | Autocomplete proveedores (debounce 350 ms) | `ver-proveedores` |
| `GET /api/purchases/proveedores` | (crear nuevo vía link, no API) | — |
| `GET /api/products?per_page=200&activo=true` | Autocomplete productos | `ver-productos` |
| `GET /api/catalog/almacenes` | Selector de almacén destino | (catálogo público) |
| `GET /api/catalog/monedas` | Selector de moneda (default PEN) | (catálogo público) |
| `GET /api/catalog/unidades-medida` | Unidad por ítem | (catálogo público) |
| `POST /api/purchases/compras` | Crear compra (`estado: 'borrador'` o `'confirmada'`) | `crear-compras` |

### Payload (POST /api/purchases/compras)

```json
{
  "proveedor_id": "01HXY...ULID",
  "almacen_id": 1,
  "moneda_id": 1,
  "fecha_emision": "2026-06-07",
  "observaciones": "OC-2026-001 ref. factura F001-123",
  "origen": "admin",
  "estado": "borrador",
  "items": [
    {
      "producto_id": 42,
      "unidad_medida_id": 1,
      "cantidad": 10,
      "precio_unitario": 15.25,
      "descuento_linea": 0
    }
  ]
}
```

Errores 422 se mapean 1:1 a `fieldErrors` y muestran `Alert` arriba del formulario con la primera key (p.ej. `items.0.producto_id`).

## Archivos tocados

| Archivo | Cambio |
|---|---|
| `frontend/src/shared/types/index.ts` | + `Proveedor`, `Almacen`, `CompraItem`, `CompraEstado`, `Compra`, `CompraItemPayload`, `CompraPayload` |
| `frontend/src/shared/api/endpoints.ts` | + `purchasesApi` (list/find/create/update/confirmar/anular/delete), `proveedoresApi` (list/find/create/update/delete) |
| `frontend/src/Admin/pages/Purchases/PurchaseFormPage.tsx` | **Reescrito** (era placeholder). 680 líneas, 1 solo archivo, sin archivos auxiliares. |

## Validación

- ✅ `npm run lint` — 0 errores (warning preexistente en `PersonalFormPage.tsx` no relacionado).
- ✅ `npx tsc --noEmit` — 0 errores.
- ✅ `npm run build` — bundle emitido `PurchaseFormPage-SYI1GhYL.js` (16.24 kB / 5.89 kB gzip).
- ⏳ Pruebas manuales pendientes (ver plan abajo) — se ejecutan en sesión E2E separada con backend levantado.

## Plan de pruebas manual

Con backend en `http://localhost:8005` y seeders aplicados:

1. **Happy path — borrador**:
   - Login como usuario con rol `Comprador` o `Admin`.
   - Navegar a `/admin/compras/nueva`.
   - Buscar proveedor "armora", seleccionar.
   - Agregar 2 productos (cantidad 5, precio 100 c/u).
   - Verificar subtotal = 1000.00, IGV = 180.00, total = 1180.00.
   - Click "Guardar borrador" → snackbar verde → redirect a `/admin/compras` (futuro).

2. **Happy path — confirmar**:
   - Mismo flow + click "Crear y confirmar" → stock se incrementa en `inventory_stock` y se genera `inventory_movimientos` con `tipo=ingreso-compra`.
   - Verificar Kardex: saldo anterior = stock previo, cantidad = ítem, saldo nuevo = anterior + cantidad.

3. **Bloqueo de empresa**:
   - Activar flag en Configuración → Empresa → Bloqueo.
   - Refrescar `/admin/compras/nueva` → banner naranja, campos `disabled`, CTAs `disabled`.
   - Intentar POST directo via DevTools → backend rechaza con 422.

4. **Validaciones**:
   - Sin proveedor seleccionado + click confirmar → Alert "Selecciona un proveedor".
   - Ítems sin producto → items filtrados, Alert "Agrega al menos un ítem…".
   - Cantidad 0 → backend 422 con `items.0.cantidad` message.

5. **Edge cases**:
   - Proveedor con RUC inactivo: search no lo muestra (filtro `activo=true`).
   - Almacén vacío: usa primer almacén (defensa en profundidad).
   - Moneda vacía: usa PEN como default.

## Trabajo futuro

1. **Página de listado** (`PurchaseListPage`) con DataGrid server-side — actual placeholder.
2. **Vista de detalle** de compra (`/admin/compras/:id`) con datos del proveedor, ítems, totales, generación de NC.
3. **Diálogo quick-create proveedor** (modal) en lugar de link externo — reduce fricción pero requiere más state management.
4. **Conversión a React Hook Form + Zod** si el form crece (Hito 005 ya consolidó este patrón en Personal).
5. **Server-side search para productos** cuando el catálogo supere los 200 activos — patrón ya disponible en `proveedoresApi`.
6. **Soporte para edición de borradores** (PATCH) — requiere decisión: ¿se edita cualquier campo en borrador o solo observaciones? Decidir con Comprador real.
7. **Importar OC desde XML/PDF** (futuras integraciones con proveedores).

## Notas operativas

- **Permisos**: la página es accesible para cualquier usuario con `crear-compras`; el sidebar ya la muestra solo a roles con `ver-compras` (entry declarada en `sidebarMenu.ts`).
- **Multi-almacén**: la selección de almacén es obligatoria para que la lógica de `aumentarStock` (Hito 004) ubique el movimiento correctamente. Backend la hace opcional (`nullable`) pero la UI la requiere para no perder trazabilidad.
- **Moneda**: el backend no exige moneda, pero se muestra en UI para claridad de importes (futuro soporte multi-moneda).
