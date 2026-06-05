<?php

namespace App\Modules\Purchases\Http\Controllers;

use App\Modules\Purchases\Http\Requests\StoreProveedorRequest;
use App\Modules\Purchases\Http\Requests\UpdateProveedorRequest;
use App\Modules\Purchases\Http\Resources\ProveedorResource;
use App\Modules\Purchases\Models\Proveedor;
use App\Modules\Purchases\Services\ProveedorService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ProveedorController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProveedorService $proveedorService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Proveedor::class);
        $proveedores = $this->proveedorService->paginate(request()->only(['search', 'activo', 'per_page']));
        return ProveedorResource::collection($proveedores)->response();
    }

    public function show(string $id): JsonResponse
    {
        $proveedor = $this->proveedorService->findById($id);
        if (!$proveedor) {
            return response()->json(['message' => 'Proveedor no encontrado.'], 404);
        }
        $this->authorize('view', $proveedor);
        return response()->json(new ProveedorResource($proveedor));
    }

    public function store(StoreProveedorRequest $request): JsonResponse
    {
        $proveedor = $this->proveedorService->create($request->validated());
        return response()->json(new ProveedorResource($proveedor), 201);
    }

    public function update(UpdateProveedorRequest $request, string $id): JsonResponse
    {
        $proveedor = $this->proveedorService->findById($id);
        if (!$proveedor) {
            return response()->json(['message' => 'Proveedor no encontrado.'], 404);
        }
        $this->authorize('update', $proveedor);
        $updated = $this->proveedorService->update($proveedor, $request->validated());
        return response()->json(new ProveedorResource($updated));
    }

    public function destroy(string $id): JsonResponse
    {
        $proveedor = $this->proveedorService->findById($id);
        if (!$proveedor) {
            return response()->json(['message' => 'Proveedor no encontrado.'], 404);
        }
        $this->authorize('delete', $proveedor);
        $this->proveedorService->delete($proveedor);
        return response()->json(['message' => 'Proveedor eliminado.']);
    }
}
