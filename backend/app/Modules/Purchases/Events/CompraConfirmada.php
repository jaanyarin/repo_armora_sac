<?php

namespace App\Modules\Purchases\Events;

use App\Modules\Purchases\Models\Compra;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompraConfirmada
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Compra $compra) {}
}
