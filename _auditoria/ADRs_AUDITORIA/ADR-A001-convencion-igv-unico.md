# ADR-A001: Convención única de cálculo de IGV

**Fecha:** 2026-06-04  
**Estado:** ✅ Propuesto (pendiente de aprobación por el arquitecto)  
**Contexto:** Hallazgo C-03 de la auditoría HITO 003.  
**Severidad:** 🔴 Crítico

---

## Contexto

Durante la auditoría de HITO 003 (Sales + Inventory), se identificó que el cálculo del IGV en `SaleService` se realiza con **dos fórmulas distintas según el path de ejecución**:

1. **En `create()` línea 60**: `$lineIgv = round($lineTotal * 0.18, 2)`  
   — donde `$lineTotal = cantidad × precio_unitario` (y `precio_unitario` ya incluye IGV).  
   Esto produce un IGV efectivo de **~16.27%** (no 18%).

2. **En `calcularTotales()` línea 174**: `$lineSubtotal = round($lineTotal / 1.18, 2)`  
   — esta es la fórmula correcta, que extrae IGV del precio que ya lo incluye (usando `/ 1.18`).

**Impacto**: El subtotal, IGV y total guardados en BD son diferentes según si el código pasa por `create()` o por `calcularTotales()`. Esto es un error contable que SUNAT rechazaría al validar el XML.

---

## Decisión

**Implementar una única función `calcularLineaIgv()` que sea la fuente de verdad para TODO cálculo de IGV en el backend.**


### Convención

```
Precio unitario (ya incluye IGV):    100.00
Valor unitario sin IGV (gravada):     100.00 / 1.18 = 84.75
IGV unitario:                          84.75 × 0.18 = 15.25

Fórmulas (una sola fuente):
  gravada = round(totalLinea / 1.18, 2)
  igv     = round(gravada × 0.18, 2)
  total   = gravada + igv   (= totalLinea original, por definición)
```

### Implementación

```php
// Método estático/helper en SaleService o en una clase Calculator
public static function calcularLineaIgv(float $cantidad, float $precioUnitario, float $descuentoLinea = 0): array
{
    $totalLinea = round($cantidad * $precioUnitario - $descuentoLinea, 2);
    $gravada    = round($totalLinea / 1.18, 2);
    $igv        = round($gravada * 0.18, 2);

    return [
        'total_linea' => $totalLinea,
        'gravada'     => $gravada,
        'igv'         => $igv,
        'total'       => $gravada + $igv,
    ];
}
```

---

## Consecuencias

### Positivas
- **Consistencia fiscal**: todos los paths de creación/actualización usan la misma fórmula.
- **Testable**: la función es pura (sin side effects, sin dependencias de Laravel).
- **Auditable**: si SUNAT cambia la tasa (18% → 17% en algunas operaciones), solo se modifica la constante en un lugar.
- **Extensible**: cuando llegue ISC, detracciones o percepciones, se agregan parámetros a la misma función.

### Negativas / Trade-offs
- **Cambio retroactivo**: las ventas existentes en BD que se crearon con la fórmula incorrecta tienen valores inconsistentes. Se necesita un migration para recalcular `subtotal`, `igv`, `total` de ventas afectadas.
- **Coordinación con frontend**: el frontend también calcula IGV en vivo (IGV calculation client-side en `SaleFormPage` y `OrderCreatePage`). Debe actualizarse para usar exactamente la misma fórmula y redondeo.

### Mitigaciones
- Migration SQL para recalcular montos de ventas existentes: `UPDATE sales_venta_items SET subtotal = ROUND(total / 1.18, 2), igv = ROUND(ROUND(total / 1.18, 2) * 0.18, 2) WHERE ...`
- Frontend: extraer a `shared/utils/igvCalculator.ts` la misma función exacta para que backend y frontend compartan lógica.

---

## Archivos afectados

| Archivo | Cambio |
|---|---|
| `backend/app/Modules/Sales/Services/SaleService.php:60,103,174` | Reemplazar cálculo inline por llamada a `calcularLineaIgv()` |
| `backend/app/Modules/Sales/Services/SaleService.php` | Agregar método privado/estático `calcularLineaIgv()` |
| `frontend/src/shared/utils/igvCalculator.ts` | Crear con la misma función (nuevo archivo) |
| `frontend/src/Admin/pages/Sales/SaleFormPage.tsx` | Usar igvCalculator |
| `frontend/src/Portal/pages/OrderCreatePage.tsx` | Usar igvCalculator |
| Migración nueva | Recalcular montos de ventas existentes |

---

## Referencias

- Hallazgo C-03 en `_auditoria/MATRIZ_RIESGOS.md`
- SaleService.php línea 60 y línea 174
- `AGENTS.md`: "IGV calculation client-side (18% sobre subtotal gravada, idéntico al backend)"

---

*ADR-A001 — Versión 1.0 — 2026-06-04*
