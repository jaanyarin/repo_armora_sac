<?php

namespace App\Modules\Products\Http\Controllers;

use App\Modules\Products\Http\Requests\StoreProductoSubclaseRequest;
use App\Modules\Products\Http\Requests\UpdateProductoSubclaseRequest;
use App\Modules\Products\Http\Resources\ProductoSubclaseResource;
use App\Modules\Products\Models\ProductoSubclase;
use App\Modules\Products\Services\ProductoSubclaseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductoSubclaseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProductoSubclaseService $subclaseService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ProductoSubclase::class);
        $subclases = $this->subclaseService->paginate(request()->only([
            'search', 'activo', 'clase_id', 'per_page', 'sort_by', 'sort_dir',
        ]));
        return ProductoSubclaseResource::collection($subclases)->response();
    }

    public function show(string $id): JsonResponse
    {
        $subclase = $this->subclaseService->findById($id);
        if (!$subclase) {
            return response()->json(['message' => 'Subclase no encontrada.'], 404);
        }
        $this->authorize('view', $subclase);
        return response()->json(new ProductoSubclaseResource($subclase));
    }

    public function store(StoreProductoSubclaseRequest $request): JsonResponse
    {
        $subclase = $this->subclaseService->create($request->validated());
        return response()->json(new ProductoSubclaseResource($subclase), 201);
    }

    public function update(UpdateProductoSubclaseRequest $request, string $id): JsonResponse
    {
        $subclase = $this->subclaseService->findById($id);
        if (!$subclase) {
            return response()->json(['message' => 'Subclase no encontrada.'], 404);
        }
        $this->authorize('update', $subclase);
        $updated = $this->subclaseService->update($subclase, $request->validated());
        return response()->json(new ProductoSubclaseResource($updated));
    }

    public function destroy(string $id): JsonResponse
    {
        $subclase = $this->subclaseService->findById($id);
        if (!$subclase) {
            return response()->json(['message' => 'Subclase no encontrada.'], 404);
        }
        $this->authorize('delete', $subclase);
        $this->subclaseService->delete($subclase);
        return response()->json(['message' => 'Subclase eliminada correctamente.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('reorder', ProductoSubclase::class);
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'exists:products_subclases,id'],
        ], [
            'ids.required' => 'Debe enviar el nuevo orden.',
            'ids.*.exists' => 'Una de las subclases no existe.',
        ]);

        $this->subclaseService->reorder($validated['ids']);
        return response()->json(['message' => 'Orden actualizado correctamente.']);
    }
}
