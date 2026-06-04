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
        $this->inventoryService->descontarPorVenta($event->sale);
    }
}
