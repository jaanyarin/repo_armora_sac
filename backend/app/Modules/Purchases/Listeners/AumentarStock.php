<?php

namespace App\Modules\Purchases\Listeners;

use App\Modules\Purchases\Events\CompraConfirmada;

/**
 * Listener placeholder. La lógica crítica de aumento de stock se ejecuta
 * INLINE desde CompraService::confirmar() (mismo patrón A-04 que DescontarStock).
 * Este listener existe solo para futura integración con Notification/Logs
 * cuando se implemente la cola Redis en Hito 006.
 */
class AumentarStock
{
    public function handle(CompraConfirmada $event): void
    {
        // No-op: CompraService::confirmar ya invoca la lógica de stock inline.
    }
}
