<?php

namespace App\Modules\Purchases\Http\Controllers;

use App\Modules\Purchases\Http\Requests\StoreCompraRequest;
use App\Modules\Purchases\Http\Requests\UpdateCompraRequest;
use App\Modules\Purchases\Http\Resources\CompraResource;
use App\Modules\Purchases\Models\Compra;
use App\Modules\Purchases\Services\CompraService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CompraController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CompraService $compraService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Compra::class);
        $compras = $this->compraService->paginate(request()->only([
            'search', 'estado', 'proveedor_id', 'fecha_desde', 'fecha_hasta', 'per_page',
        ]));
        return CompraResource::collection($compras)->response();
    }

    public function show(string $id): JsonResponse
    {
        $compra = $this->compraService->findById($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada.'], 404);
        }
        $this->authorize('view', $compra);
        return response()->json(new CompraResource($compra));
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $compra = $this->compraService->create($request->validated());
        return response()->json(new CompraResource($compra), 201);
    }

    public function update(UpdateCompraRequest $request, string $id): JsonResponse
    {
        $compra = $this->compraService->findById($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada.'], 404);
        }
        $this->authorize('update', $compra);
        $updated = $this->compraService->update($compra, $request->validated());
        return response()->json(new CompraResource($updated));
    }

    public function confirmar(string $id): JsonResponse
    {
        $compra = $this->compraService->findById($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada.'], 404);
        }
        $this->authorize('confirmar', $compra);
        $confirmed = $this->compraService->confirmar($compra);
        return response()->json(new CompraResource($confirmed));
    }

    public function anular(string $id): JsonResponse
    {
        $compra = $this->compraService->findById($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada.'], 404);
        }
        $this->authorize('anular', $compra);
        $anulada = $this->compraService->anular($compra);
        return response()->json(new CompraResource($anulada));
    }

    public function destroy(string $id): JsonResponse
    {
        $compra = $this->compraService->findById($id);
        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada.'], 404);
        }
        $this->authorize('delete', $compra);
        $this->compraService->delete($compra);
        return response()->json(['message' => 'Compra eliminada.']);
    }
}
