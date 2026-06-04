# Hito 003 — Sales + Inventory

**Fecha**: 2026-06-04
**Estado**: ✅ Implementado (parcial, sin SUNAT ni Finance)
**Módulos nuevos**: Sales, Inventory
**Módulos actualizados**: Auth (Permisos), Customers (Policy), Products (Policy), Admin/Portal (rutas y páginas)

---

## Resumen ejecutivo

Se implementó el flujo transaccional de ventas con descuento automático de inventario al confirmar, y se habilitaron los pedidos desde el Portal Cliente con carrito persistente. Backend completo con RBAC granular, eventos y policies; frontend con dos páginas Admin (lista + formulario) y dos páginas Portal (checkout + historial).

## Backend

### Migraciones nuevas (6 archivos)

| Tabla | Prefijo | ULID | Soft Delete | Observaciones |
|---|---|---|---|---|
| `dim_almacen` | `dim_` | ❌ | ❌ | Catálogo de almacenes |
| `sales_ventas` | `sales_` | ✅ | ✅ | Cabecera de venta |
| `sales_venta_items` | `sales_` | ✅ | ❌ | Líneas de venta |
| `sales_notas_credito` | `sales_` | ✅ | ✅ | Notas de crédito |
| `inventory_stock` | `inventory_` | ❌ | ❌ | Stock por producto/almacén |
| `inventory_movimientos` | `inventory_` | ✅ | ❌ | Kardex con saldo anterior/nuevo |

### Modelos (5 Eloquent)

- **`Sale`** — `HasUlids`, `SoftDeletes`, `LogsActivity`. Relaciones: `cliente`, `usuario`, `items`, `notasCredito`.
- **`SaleItem`** — `HasUlids`. Relaciones: `producto`, `unidadMedida`.
- **`CreditNote`** — `HasUlids`, `SoftDeletes`.
- **`Stock`** — autoincrement. Único por `(producto_id, almacen_id)`.
- **`InventoryMovement`** — `HasUlids`, `LogsActivity`. Kardex completo.

### Services (3)

| Service | Métodos clave | Decisiones |
|---|---|---|
| `SaleService` | `paginate`, `findById`, `create`, `update`, `confirmar`, `anular`, `delete` | Cálculo IGV 18% sobre subtotal gravada. Evento `SaleConfirmed` disparado al confirmar/crear (si estado confirmado). `anular` re-ingresa stock. |
| `InventoryService` | `descontarPorVenta`, `reingresarPorVenta` | `DB::transaction` + `lockForUpdate`. Inicializa `Stock` desde `Product.stock_actual` si no existe. Crea `InventoryMovement` con saldo anterior/nuevo. |
| `CreditNoteService` | `emitirPorVenta` | Anula venta original + re-ingresa stock. |

### Event-Driven

```
SaleService::confirmar()
        │
        ▼
  event(new SaleConfirmed($sale))
        │
        ▼
  DescontarStock::handle()
        │
        ▼
  InventoryService::descontarPorVenta()
        │
        ▼
  DB::transaction + lockForUpdate
        │
        ▼
  Product.stock_actual -= cantidad
  InventoryMovement::create()
  Stock::updateOrCreate()
```

### Policies de Laravel (3)

- `SalePolicy` — `viewAny`, `view`, `create`, `update`, `delete`, `anular`, `emitirNotaCredito`
- `CustomerPolicy` — mapea permisos CRUD a gates
- `ProductPolicy` — mapea permisos CRUD a gates

Registradas en `AppServiceProvider::boot()`:
```php
Gate::policy(Customer::class, CustomerPolicy::class);
Gate::policy(Product::class, ProductPolicy::class);
Gate::policy(Sale::class, SalePolicy::class);
Event::listen(SaleConfirmed::class, DescontarStock::class);
```

### Rutas API (11 nuevas)

| Método | URI | Permiso |
|---|---|---|
| GET | `/api/sales` | `ver-ventas` |
| GET | `/api/sales/{sale}` | `ver-ventas` |
| POST | `/api/sales` | `crear-ventas` |
| PUT | `/api/sales/{sale}` | `ver-ventas` |
| POST | `/api/sales/{sale}/confirmar` | `crear-ventas` |
| POST | `/api/sales/{sale}/anular` | `anular-ventas` |
| DELETE | `/api/sales/{sale}` | `ver-ventas` |
| POST | `/api/sales/{sale}/nota-credito` | `nota-credito` |
| GET | `/api/inventory/stock` | `ver-stock` |
| GET | `/api/inventory/stock/{id}` | `ver-stock` |
| GET | `/api/inventory/kardex` | `kardex` |

### Permisos nuevos (8)

```
ver-ventas       crear-ventas     editar-ventas   anular-ventas
nota-credito     ver-stock        ajustar-stock   kardex
```

Asignados en `RoleAndPermissionSeeder`:
- **Vendedor**: `ver-ventas`, `crear-ventas`, `editar-ventas`, `nota-credito`
- **Admin/Super-Admin**: todos
- **Jefe-Almacen**: `ver-stock`, `ajustar-stock`, `kardex`

### Tests (10 nuevos)

- `tests/Feature/SaleTest.php` — 8 tests (list, create, validación cliente/ítems, confirmar descuenta stock, anular revierte stock, nota crédito, no autorizado)
- `tests/Feature/InventoryTest.php` — 2 tests (list stock, kardex)

**Refactor de credenciales (ADR-008)**: los tests usan `User::factory()` + `actingAs($user, 'sanctum')` + helpers por rol (`asVendedor()`, `asAdmin()`, `asLogistica()`, `asJefeAlmacen()`). Cero contraseñas literales.

### Bug pre-existente corregido

- `InventoryController` faltaba `use Illuminate\Http\Request;` → corregido

## Frontend

### Tipos TypeScript (7 nuevos)

`Sale`, `SaleItem`, `CreditNote`, `Stock`, `InventoryMovement`, `SalePayload`, `SaleItemPayload`, `CartItem` — todos snake_case para coincidir con el Resource del backend.

### APIs (2 nuevos)

- `salesApi` — `list`, `find`, `create`, `update`, `confirmar`, `anular`, `emitirNotaCredito`
- `inventoryApi` — `stock`, `stockByProduct`, `kardex`

### Cart store (Zustand persist)

`src/shared/hooks/useCart.ts` — carrito persistente en `localStorage` con clave `armora-portal-cart`:
- `add`, `remove`, `updateCantidad`, `clear`
- Selectores: `total()`, `itemCount()`

### Páginas Admin (2)

- **`SaleListPage`** (`/admin/ventas`) — DataGrid server-side con paginación, búsqueda, filtro por estado. Acciones: confirmar, anular, ver detalle. Diálogos de confirmación con mensajes claros.
- **`SaleFormPage`** (`/admin/ventas/nueva`) — Formulario con autocomplete de clientes y productos, tabla dinámica de ítems con cantidad/precio/subtotal, cálculo IGV en tiempo real. Botones "Guardar borrador" y "Confirmar y registrar".

### Páginas Portal (2)

- **`OrderCreatePage`** (`/portal/pedidos/nuevo`) — Vista del carrito con incremento/decremento de cantidad, búsqueda de cliente, observaciones, resumen con IGV. Carrito vacío muestra CTA al catálogo.
- **`OrderHistoryPage`** (`/portal/pedidos`) — Tabs por estado (Todos/Borrador/Confirmado/Pagado/Anulado), búsqueda por código, paginación, diálogo de detalle con desglose de ítems y totales.

### Rutas (4 nuevas en `App.tsx`)

```tsx
/admin/ventas                  → SaleListPage
/admin/ventas/nueva            → SaleFormPage
/admin/ventas/:id              → SaleListPage (filtro)
/portal/pedidos                → OrderHistoryPage
/portal/pedidos/nuevo          → OrderCreatePage
```

### Validación

- `npm run lint` → 0 errores ✅
- `npm run build` → 1350 módulos transformados, 0 errores ✅
- `npm run test` → 6/6 tests pasan ✅

## Pendiente

### Hito 003 (no completado)

- [ ] **Greenter 5.x** + envío SOAP a SUNAT
- [ ] Job asíncrono `SendInvoiceToSunat` con Redis Queue
- [ ] `InventoryPage` Admin completa (stock + kardex con filtros)
- [ ] Evento `Finance` para generar asiento contable
- [ ] Tests Playwright E2E del flujo Admin (crear venta → confirmar) y Portal (catálogo → carrito → pedido)

### Hito 004 — Purchases + Finance

- Compras y proveedores
- Libro contable electrónico (PLE)
- Cálculo IGV/ISC/detracciones

## Decisiones de diseño

| Decisión | Razón |
|---|---|
| `Stock` se inicializa desde `Product.stock_actual` si no existe | El campo `Product.stock_actual` es la fuente de verdad histórica; `Stock` es vista por almacén |
| `Sales.saldo_pendiente` siempre = `total` al crear | No hay módulo de pagos aún; se asume "contado" en Hito 003 |
| `Sales.origen` (`admin`/`portal`) | Permite filtrar pedidos del portal vs ventas manuales |
| `InventoryMovement.saldo_anterior`/`saldo_nuevo` | Kardex auditable sin necesidad de recalcular |
| `SoftDeletes` en Sale y CreditNote | Conservar historial para auditoría fiscal |
| Carrito persiste en `localStorage` | El portal puede perder sesión; el carrito se mantiene |
| IGV se calcula desde el `precio_unitario` (que ya incluye IGV) | Coincide con la lógica backend (`total / 1.18` para obtener gravada) |
| `actingAs($user, 'sanctum')` en tests (no `withToken`) | Evita cache de Sanctum entre requests en testing |

## Métricas

| Métrica | Antes | Después |
|---|---|---|
| Módulos backend | 4 | 6 (+Sales, +Inventory) |
| Migraciones | 12 | 18 (+6) |
| Models | 8 | 13 (+5) |
| Services | 5 | 8 (+3) |
| Policies | 0 | 3 |
| Eventos | 0 | 1 |
| Listeners | 0 | 1 |
| Rutas API | ~30 | ~41 |
| Permisos RBAC | 23 | 31 (+8) |
| Tests backend | 16 | 26 (+10) |
| Tests frontend | 6 | 6 (sin nuevos, scope backend) |
| Páginas Admin | 4 | 6 (+SaleListPage, +SaleFormPage) |
| Páginas Portal | 3 | 5 (+OrderCreatePage, +OrderHistoryPage) |
| Líneas de código (frontend) | ~2500 | ~3500 |
| Tipos TypeScript | 233 líneas | 320+ líneas |

## Referencias

- `app/Modules/Sales/` — código backend
- `app/Modules/Inventory/` — código backend
- `frontend/src/Admin/pages/Sales/` — páginas Admin
- `frontend/src/Portal/pages/OrderCreatePage.tsx`, `OrderHistoryPage.tsx` — páginas Portal
- `frontend/src/shared/hooks/useCart.ts` — carrito persistente
- `_docs_desarrollo/ADR-008-no-hardcode-credenciales-tests.md` — refactor de credenciales
