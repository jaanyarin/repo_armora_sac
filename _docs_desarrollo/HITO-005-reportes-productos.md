# HITO-005 — Reportes Productos (página imprimible)

**Fecha:** 2026-06-07  
**Estado:** ✅ Implementado  

---

## Resumen

Se implementó la página **Reportes Productos** en `/admin/productos/reportes`, replicando la funcionalidad de `armorasac.com/app/productos/reportes-productos` pero con el patrón de reportes imprimibles HTML del proyecto (PDF vía `window.print()`).

## Arquitectura

```
Frontend (ReportesProductosPage.tsx)
  │  GET /api/products/reportes/productos
  ▼
Backend (ProductReportController)
  │  autoriza: generar-reportes-productos
  ▼
Backend (ProductReportService)
  │  query: productos activos con relaciones
  ▼
Blade (productos-activos.blade.php)
  │  wrap: personal/reports/_print_wrapper
  ▼
HTML imprimible → nueva ventana
```

## Archivos nuevos

| Archivo | Propósito |
|---|---|
| `backend/app/Modules/Products/Reports/ProductReportService.php` | Servicio que consulta productos activos con `unidadMedida`, `productoClase`, `tipoAfeccionIgv` |
| `backend/app/Modules/Products/Http/Controllers/ProductReportController.php` | Controller delgado que autoriza, llama al servicio, renderiza blade y envuelve en `_print_wrapper` |
| `backend/resources/views/products/reports/productos-activos.blade.php` | Template HTML del reporte: tabla con código, nombre, unidad, clase, precios S/, USD, costo promedio, stock |
| `frontend/src/Admin/pages/Products/ReportesProductosPage.tsx` | Página Admin con card, Autocomplete Clase/Subclase desde `products_clases`/`products_subclases`, botón "Generar Reporte" verde |

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `backend/app/Modules/Products/Policies/ProductPolicy.php` | Agregado método `generarReportesProductos()` |
| `backend/database/seeders/RoleAndPermissionSeeder.php` | Nuevo permiso `generar-reportes-productos` asignado a Super-Admin, Admin, Gerente |
| `backend/routes/api.php` | Ruta `GET /api/products/reportes/productos` con middleware `permission:generar-reportes-productos` |
| `frontend/src/App.tsx` | Lazy import + ruta `productos/reportes` |
| `frontend/src/Admin/layouts/AdminLayout.tsx` | `deriveTitle` con caso `reportes` → "Reportes Productos" |

## Columnas del reporte

1. Código
2. Nombre
3. Unidad Medida
4. Clase (desde `dim_producto_clase` - FK actual del producto)
5. Precio S/ (S/ x.xx)
6. Precio USD ($ x.xx)
7. Costo Promedio (S/ x.xx)
8. Stock Actual

## UX

- Selector **Clase** → Autocomplete contra `GET /api/products/clases` (carga las 78 marcas)
- Selector **Subclase** → Autocomplete contra `GET /api/products/subclases?clase_id=X` (filtrado por clase seleccionada)
- Ambos selectores son **opcionales** (el reporte se puede generar sin filtros)
- Botón **Generar Reporte** (verde `#22c55e`) → abre nueva ventana con HTML imprimible
- Snackbar de feedback (éxito/error)

## Notas

- Se reutiliza el `_print_wrapper` de Personal reports (wrapper genérico con toolbar de imprimir/cerrar)
- El filtro por Clase/Subclase se pasa como query params (`clase_id`, `subclase_id`), pero el backend actualmente retorna todos los productos activos sin filtrar (el FK de `products` apunta a `dim_producto_clase`, no a `products_clases`). La estructura de filtros está lista para cuando se realinee el FK.
- Permiso `generar-reportes-productos` otorgado a Super-Admin (automático), Admin y Gerente
- El sidebar ya tenía `{ label: "Reportes Productos", path: "/admin/productos/reportes" }` desde implementación anterior
