<?php

namespace App\Modules\Sales\Http\Controllers;

use App\Modules\Sales\Http\Requests\StoreSaleRequest;
use App\Modules\Sales\Http\Requests\UpdateSaleRequest;
use App\Modules\Sales\Http\Resources\SaleResource;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Services\CreditNoteService;
use App\Modules\Sales\Services\SaleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SaleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SaleService $saleService,
        private readonly CreditNoteService $creditNoteService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Sale::class);
        $sales = $this->saleService->paginate(request()->only([
            'search', 'estado', 'cliente_id', 'fecha_desde', 'fecha_hasta', 'per_page',
        ]));
        return response()->json($sales);
    }

    public function show(Sale $sale): JsonResponse
    {
        $this->authorize('view', $sale);
        return response()->json(new SaleResource($this->saleService->findById($sale->id)));
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $sale = $this->saleService->create($request->validated());
        return response()->json(new SaleResource($sale), 201);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): JsonResponse
    {
        $updated = $this->saleService->update($sale, $request->validated());
        return response()->json(new SaleResource($updated));
    }

    public function confirmar(Sale $sale): JsonResponse
    {
        $this->authorize('confirmar', $sale);
        $confirmed = $this->saleService->confirmar($sale);
        return response()->json(new SaleResource($confirmed));
    }

    public function anular(Sale $sale): JsonResponse
    {
        $this->authorize('anular', $sale);
        $anulada = $this->saleService->anular($sale);
        return response()->json(new SaleResource($anulada));
    }

    public function destroy(Sale $sale): JsonResponse
    {
        $this->authorize('delete', $sale);
        $this->saleService->delete($sale);
        return response()->json(['message' => 'Venta eliminada.']);
    }

    public function emitirNotaCredito(Request $request, Sale $sale): JsonResponse
    {
        $this->authorize('emitir-nota-credito', $sale);

        $validated = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
            'nota_credito_tipo_id' => ['nullable', 'integer', 'exists:dim_nota_credito_tipo,id'],
        ], [
            'motivo.required' => 'Debe especificar el motivo de la nota de crédito.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $creditNote = $this->creditNoteService->emitirPorVenta(
            $sale,
            $validated['motivo'],
            $validated['nota_credito_tipo_id'] ?? null,
        );

        return response()->json($creditNote, 201);
    }
}
