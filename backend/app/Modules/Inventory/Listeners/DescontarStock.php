<?php

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Sales\Events\SaleConfirmed;
use Illuminate\Support\Facades\Log;

class DescontarStock
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    public function handle(SaleConfirmed $event): void
    {
        try {
            $this->inventoryService->descontarPorVenta($event->sale);
        } catch (\Throwable $e) {
            Log::error('Error descontando stock para venta', [
                'venta_id' => $event->sale->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
