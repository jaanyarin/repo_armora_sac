<?php

namespace App\Modules\Products\Http\Controllers;

use App\Modules\Products\Http\Requests\StoreProductoClaseRequest;
use App\Modules\Products\Http\Requests\UpdateProductoClaseRequest;
use App\Modules\Products\Http\Resources\ProductoClaseResource;
use App\Modules\Products\Models\ProductoClase;
use App\Modules\Products\Services\ProductoClaseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductoClaseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProductoClaseService $claseService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ProductoClase::class);
        $clases = $this->claseService->paginate(request()->only([
            'search', 'activo', 'licor', 'per_page', 'sort_by', 'sort_dir',
        ]));
        return ProductoClaseResource::collection($clases)->response();
    }

    public function show(string $id): JsonResponse
    {
        $clase = $this->claseService->findById($id);
        if (!$clase) {
            return response()->json(['message' => 'Clase no encontrada.'], 404);
        }
        $this->authorize('view', $clase);
        return response()->json(new ProductoClaseResource($clase));
    }

    public function store(StoreProductoClaseRequest $request): JsonResponse
    {
        $clase = $this->claseService->create($request->validated());
        return response()->json(new ProductoClaseResource($clase), 201);
    }

    public function update(UpdateProductoClaseRequest $request, string $id): JsonResponse
    {
        $clase = $this->claseService->findById($id);
        if (!$clase) {
            return response()->json(['message' => 'Clase no encontrada.'], 404);
        }
        $this->authorize('update', $clase);
        $updated = $this->claseService->update($clase, $request->validated());
        return response()->json(new ProductoClaseResource($updated));
    }

    public function destroy(string $id): JsonResponse
    {
        $clase = $this->claseService->findById($id);
        if (!$clase) {
            return response()->json(['message' => 'Clase no encontrada.'], 404);
        }
        $this->authorize('delete', $clase);
        $subclasesCount = $this->claseService->countSubclases($clase, false);
        $this->claseService->delete($clase);
        return response()->json([
            'message' => 'Clase eliminada correctamente.',
            'subclases_eliminadas' => $subclasesCount,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('reorder', ProductoClase::class);
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'exists:products_clases,id'],
        ], [
            'ids.required' => 'Debe enviar el nuevo orden.',
            'ids.*.exists' => 'Una de las clases no existe.',
        ]);

        $this->claseService->reorder($validated['ids']);
        return response()->json(['message' => 'Orden actualizado correctamente.']);
    }
}
