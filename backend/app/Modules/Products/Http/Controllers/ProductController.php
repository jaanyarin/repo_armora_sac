<?php

namespace App\Modules\Products\Http\Controllers;

use App\Modules\Products\Http\Requests\StoreProductRequest;
use App\Modules\Products\Http\Requests\UpdateProductRequest;
use App\Modules\Products\Http\Resources\ProductResource;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        $products = $this->productService->paginate(request()->only(['search', 'activo', 'per_page', 'unidad_medida_id', 'producto_clase_id']));
        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }
        return response()->json(new ProductResource($product));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());
        return response()->json(new ProductResource($product), 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());
        return response()->json(new ProductResource($product));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);
        return response()->json(['message' => 'Producto eliminado.']);
    }
}
