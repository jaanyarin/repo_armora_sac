# Log de Desarrollo — ARMORA NextGen

**Proyecto:** Re-implementación de ARMORA bajo Arquitectura Laravel 12 + React 19
**Fecha de inicio:** Junio 2026
**Repositorio:** `armora-sac`
**Arquitecto:** Senior Fullstack ERP Architect
**Estado general:** 🟡 En Desarrollo (Fase 1A — Clientes + Productos)

---

## 📋 Hito 002: Módulos Customers + Products

**Fecha:** 2026-06-03
**Estado:** ✅ Completo

### Alcance

Implementación completa de los módulos de Clientes (Customers) y Productos (Products) con backend Laravel (API REST) y frontend React (MUI Data Grid + Formularios). Autenticación multi-campo (username/email/DNI/RUC).

### Entregables

#### Backend — Migraciones

| Migración | Archivo | Estado |
|---|---|---|
| Agregar dni/ruc a tabla users | `2026_06_03_070000_add_dni_ruc_to_users_table.php` | ✅ |
| Crear tabla customers | `2026_06_03_071000_create_customers_table.php` | ✅ |
| Crear tabla products | `2026_06_03_072000_create_products_table.php` | ✅ |

#### Backend — Models Eloquent (Tablas Dimensionales)

| Modelo | Tabla | Archivo |
|---|---|---|
| Moneda | `dim_moneda` | `app/Models/Catalog/Moneda.php` |
| UnidadMedida | `dim_unidad_medida` | `app/Models/Catalog/UnidadMedida.php` |
| Pais | `dim_pais` | `app/Models/Catalog/Pais.php` |
| Departamento | `dim_departamento` | `app/Models/Catalog/Departamento.php` |
| Provincia | `dim_provincia` | `app/Models/Catalog/Provincia.php` |
| Ubigeo | `dim_ubigeo` | `app/Models/Catalog/Ubigeo.php` |
| TipoAfeccionIgv | `dim_tipo_afeccion_igv` | `app/Models/Catalog/TipoAfeccionIgv.php` |
| TipoCalculoIsc | `dim_tipo_calculo_isc` | `app/Models/Catalog/TipoCalculoIsc.php` |
| NotaCreditoTipo | `dim_nota_credito_tipo` | `app/Models/Catalog/NotaCreditoTipo.php` |
| SegmentoCliente | `dim_segmento_sunat` | `app/Models/Catalog/SegmentoCliente.php` |
| TipoCliente | `dim_tipo_cliente` | `app/Models/Catalog/TipoCliente.php` |
| FamiliaSunat | `dim_familia_sunat` | `app/Models/Catalog/FamiliaSunat.php` |
| ClaseSunat | `dim_clase_sunat` | `app/Models/Catalog/ClaseSunat.php` |
| ProductoClase | `dim_producto_clase` | `app/Models/Catalog/ProductoClase.php` |
| ProductoSubclase | `dim_producto_subclase` | `app/Models/Catalog/ProductoSubclase.php` |
| ListaPrecio | `dim_lista_precios` | `app/Models/Catalog/ListaPrecio.php` |
| DocumentoTipo | `dim_documento_tipo` | `app/Models/Catalog/DocumentoTipo.php` |
| DocumentoSimbolo | `dim_documento_simbolo` | `app/Models/Catalog/DocumentoSimbolo.php` |

#### Backend — Módulo Customers

| Componente | Archivo | Estado |
|---|---|---|
| Modelo | `app/Modules/Customers/Models/Customer.php` | ✅ |
| Service | `app/Modules/Customers/Services/CustomerService.php` | ✅ |
| Controller | `app/Modules/Customers/Http/Controllers/CustomerController.php` | ✅ |
| Store Request | `app/Modules/Customers/Http/Requests/StoreCustomerRequest.php` | ✅ |
| Update Request | `app/Modules/Customers/Http/Requests/UpdateCustomerRequest.php` | ✅ |
| Resource | `app/Modules/Customers/Http/Resources/CustomerResource.php` | ✅ |

**Endpoints API:**
- `GET /api/customers` — Listar (paginado, búsqueda por nombre/documento/código)
- `GET /api/customers/{id}` — Ver detalle
- `POST /api/customers` — Crear
- `PUT /api/customers/{id}` — Actualizar
- `DELETE /api/customers/{id}` — Eliminar (soft delete)

**Validaciones:**
- Tipo documento: DNI, RUC, CE, PASAPORTE
- N° documento único por cliente
- Nombre/razón social obligatorio
- Email válido si se proporciona

#### Backend — Módulo Products

| Componente | Archivo | Estado |
|---|---|---|
| Modelo | `app/Modules/Products/Models/Product.php` | ✅ |
| Service | `app/Modules/Products/Services/ProductService.php` | ✅ |
| Controller | `app/Modules/Products/Http/Controllers/ProductController.php` | ✅ |
| Store Request | `app/Modules/Products/Http/Requests/StoreProductRequest.php` | ✅ |
| Update Request | `app/Modules/Products/Http/Requests/UpdateProductRequest.php` | ✅ |
| Resource | `app/Modules/Products/Http/Resources/ProductResource.php` | ✅ |

**Endpoints API:**
- `GET /api/products` — Listar (paginado, búsqueda, filtros por UM/clase)
- `GET /api/products/{id}` — Ver detalle (con relaciones)
- `POST /api/products` — Crear
- `PUT /api/products/{id}` — Actualizar
- `DELETE /api/products/{id}` — Eliminar (soft delete)

**Relaciones:**
- Unidad de Medida (obligatorio)
- Clase/Subclase de Producto
- Familia/Clase SUNAT
- Afección IGV / Cálculo ISC
- Precios en S/ y USD, costo promedio

#### Backend — Auth Multi-Campo

| Cambio | Detalle |
|---|---|
| LoginRequest | Campo `username` → `login` (acepta username, email, DNI o RUC) |
| AuthService | Búsqueda por `username`, `email`, `dni` o `ruc` |
| UserResource | Expone campos `dni` y `ruc` |
| User model | Fillable incluye `dni`, `ruc` |
| UserFactory | Incluye `dni`, `ruc` en definición |

#### Frontend — Customers

| Componente | Archivo | Estado |
|---|---|---|
| Lista (DataGrid) | `src/Admin/pages/Customers/CustomerListPage.tsx` | ✅ |
| Formulario (Crear/Editar) | `src/Admin/pages/Customers/CustomerFormPage.tsx` | ✅ |

**Funcionalidades:**
- DataGrid con paginación server-side, búsqueda por texto
- Formulario con validación Zod + React Hook Form
- Selectores de catálogo: tipo cliente, segmento, lista precio (desde API)
- Crear, editar, eliminar con confirmación

#### Frontend — Products

| Componente | Archivo | Estado |
|---|---|---|
| Lista (DataGrid) | `src/Admin/pages/Products/ProductListPage.tsx` | ✅ |
| Formulario (Crear/Editar) | `src/Admin/pages/Products/ProductFormPage.tsx` | ✅ |

**Funcionalidades:**
- DataGrid con precios S/, stock, estado
- Formulario con selectores: UM, clase, familia SUNAT, afección IGV
- Precios, costos, stock mínimo/actual

#### Frontend — Actualizaciones

| Archivo | Cambio |
|---|---|
| `src/App.tsx` | Rutas lazy-loaded: clientes, productos, clientes/nuevo, productos/nuevo, clientes/:id/editar, productos/:id/editar |
| `src/shared/api/endpoints.ts` | Nuevos endpoints `customersApi` y `productsApi` |
| `src/shared/types/index.ts` | Interfaces `Customer`, `Product`, `ProductoClase`, `ProductoSubclase`, `FamiliaSunat`, `ClaseSunat`, `ListaPrecio` |
| `src/shared/hooks/useAuth.ts` | Login acepta `login` genérico |

#### Tests

| Archivo | Tipo | Tests |
|---|---|---|
| `tests/Feature/AuthTest.php` | Feature | Login (username, email, DNI), invalid, me, logout |
| `tests/Feature/CustomerTest.php` | Feature | CRUD completo, validación duplicado |
| `tests/Feature/ProductTest.php` | Feature | CRUD completo, validación UM requerida |

**Total de tests:** 16 casos

### Decisiones Técnicas (ADRs)

#### ADR-004: Login Multi-Campo

- **Contexto:** Clientes y proveedores necesitan login con RUC/DNI, no solo username.
- **Decisión:** El campo `login` acepta username, email, DNI o RUC. La búsqueda es OR.
- **Consecuencia:** El formulario frontend muestra "Usuario, Correo, DNI o RUC" como placeholder.

#### ADR-005: Modelos Eloquent para Tablas Dimensionales

- **Contexto:** CatalogController usaba `DB::table()` directo. Se necesitan relaciones Eloquent para Customers y Products.
- **Decisión:** Crear modelos en `App\Models\Catalog\*` con `$table` apuntando a `dim_*`. CatalogController se mantiene con `DB::table()` para simplicidad.
- **Consecuencia:** Los modelos existen para relaciones; CatalogController puede migrarse gradualmente.

### Pendientes para Siguiente Hito

| Tarea | Prioridad | Dependencia |
|---|---|---|
| Módulo Sales (ventas + items + envío SUNAT Greenter) | 🔴 Alta | Customers + Products |
| Módulo Inventory (kardex + ajustes + stock) | 🔴 Alta | Products |
| Portal Cliente (catálogo público + pedidos) | 🟡 Media | Sales |
| Módulo Purchases (compras + proveedores) | 🟡 Media | Products |
| Módulo Finance (libro contable, IGV, detracciones) | 🟡 Media | Sales + Purchases |
| Tests E2E con Playwright | 🟢 Baja | Frontend completo |

---

*Documento generado: 2026-06-03*
*Próxima revisión: Al completar Hito 003 (Sales + Inventory)*
