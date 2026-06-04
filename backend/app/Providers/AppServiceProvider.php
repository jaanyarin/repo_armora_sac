<?php

namespace App\Providers;

use App\Modules\Customers\Models\Customer;
use App\Modules\Customers\Policies\CustomerPolicy;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Policies\ProductPolicy;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Policies\SalePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);

        // Nota: el listener DescontarStock de SaleConfirmed ya NO se registra
        // automáticamente. SaleService::confirmar()/update() invoca directamente
        // InventoryService::descontarPorVenta() DENTRO de la misma transacción
        // (cumple A-04). El evento SaleConfirmed se sigue disparando desde el
        // Service para observabilidad (Notification, Webhook, AuditLog externo).

        // A-05: rate limiter 'login' — 5 intentos/min por (IP + login) para /api/auth/login
        RateLimiter::for('login', function (Request $request) {
            $key = strtolower((string) $request->input('login', '')) . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Demasiados intentos de inicio de sesión. Intente de nuevo en 1 minuto.',
                ], 429);
            });
        });

        // Limiter default 'api' requerido por throttleApi(): 60 req/min por user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
