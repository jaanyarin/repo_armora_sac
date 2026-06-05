<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Models\Stock;
use App\Modules\Products\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function stock(Request $request): JsonResponse
    {
        $this->authorize('viewStock');
        $query = Stock::with(['producto.unidadMedida']);

        if ($search = $request->query('search')) {
            $query->whereHas('producto', fn($q) => $q->where('nombre', 'ilike', "%{$search}%")
                                                       ->orWhere('codigo', 'ilike', "%{$search}%"));
        }

        $stock = $query->paginate($request->query('per_page', 25));

        return response()->json($stock);
    }

    public function kardex(Request $request): JsonResponse
    {
        $this->authorize('viewKardex');
        $query = InventoryMovement::with(['producto.unidadMedida', 'usuario'])
            ->orderBy('fecha_movimiento', 'desc');

        if ($productId = $request->query('producto_id')) {
            $query->where('producto_id', $productId);
        }

        if ($fechaDesde = $request->query('fecha_desde')) {
            $query->where('fecha_movimiento', '>=', $fechaDesde);
        }

        if ($fechaHasta = $request->query('fecha_hasta')) {
            $query->where('fecha_movimiento', '<=', $fechaHasta);
        }

        return response()->json($query->paginate($request->query('per_page', 50)));
    }

    public function stockByProduct(int $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        $stock = Stock::where('producto_id', $productId)->get();
        return response()->json([
            'producto' => [
                'id' => $product->id,
                'codigo' => $product->codigo,
                'nombre' => $product->nombre,
            ],
            'stock_actual' => (float) $product->stock_actual,
            'stock_minimo' => (float) $product->stock_minimo,
            'almacenes' => $stock,
        ]);
    }
}
