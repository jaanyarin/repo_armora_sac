<?php

namespace App\Modules\Products\Reports;

use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductReportService
{
    public function getProductosActivos(): Collection
    {
        return Product::query()
            ->where('activo', true)
            ->with(['unidadMedida', 'productoClase', 'tipoAfeccionIgv'])
            ->orderBy('nombre')
            ->get();
    }
}
