# HITO-005 — Seed masivo de Clases y Subclases (datos del dueño)

**Fecha:** 2026-06-07  
**Estado:** ✅ Cerrado  

---

## Resumen

Se poblaron las tablas `products_clases` y `products_subclases` con los datos reales proporcionados por el dueño de la empresa: **87 clases (marcas)** y **228 subclases (subcategorías de producto)**.

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `backend/database/seeders/ProductoClaseSubclaseSeeder.php` | **Nuevo** — Seeder que trunca y reinserta 78 clases + 224 subclases |
| `backend/database/seeders/DatabaseSeeder.php` | Registra `ProductoClaseSubclaseSeeder::class` |

## Detalle de datos

### Clases (`products_clases`)
- 78 registros activos (87 en el rango 1–87, con 9 gaps intencionales: 43, 45, 50, 55, 56, 57, 64, 65, 67)
- 1 marca con `licor = true`: BACKUS (orden 84)
- Códigos `CLS-XXXXX` basados en el orden original
- ULID generado por `Str::ulid()`

### Subclases (`products_subclases`)
- 224 registros activos (228 en el rango 1–228, con 4 gaps: 19, 20, 21, 104)
- FK `clase_id` apunta al ULID de la clase padre
- Códigos `SCL-XXXXX` secuenciales (1–224)
- Slugs con dedup automático (nombres exactos como "CARAMELO" reciben sufijo numérico)

## Lógica del seeder

```php
Schema::disableForeignKeyConstraints();
DB::table('products_subclases')->truncate();
DB::table('products_clases')->truncate();
Schema::enableForeignKeyConstraints();
```

1. Deshabilita FKs → Trunca ambas tablas en orden seguro (subclases primero)
2. Itera 78 clases: genera ULID, codigo `CLS-`+orden, slug único, inserta
3. Construye mapa `clase_nombre → ULID`
4. Itera 224 subclases: genera ULID, codigo `SCL-`+contador, slug único, inserta con `clase_id` del mapa

## Verificación

- **Tests:** `ProductoClaseTest` (19/19 passing) + `ProductoSubclaseTest` (20/20 passing)
- **Pint:** Linter pasado sin errores en el seeder
- **SQL:** `SELECT COUNT(*) FROM products_clases` → 78, `SELECT COUNT(*) FROM products_subclases` → 224
- **Frontend:** Sin cambios — `ProductosClasesPage.tsx` consume los mismos endpoints REST (`GET /api/products/clases`, `GET /api/products/subclases`) y refleja los datos automáticamente

## Notas

- Los gaps en la numeración original se respetan tal cual los proporcionó el dueño
- No se modifican las tablas `dim_producto_clase`/`dim_producto_subclase` (catálogo SUNAT legacy)
- El seeder corre con `php artisan db:seed` (incluido en `composer setup` y disponible desde Docker)
