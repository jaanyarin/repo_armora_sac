<?php

namespace App\Providers;

use App\Modules\Customers\Models\Customer;
use App\Modules\Customers\Policies\CustomerPolicy;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Policies\ProductPolicy;
use App\Modules\Sales\Models\Sale;
use App\Modules\Sales\Policies\SalePolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\Inventory\Listeners\DescontarStock;
use App\Modules\Sales\Events\SaleConfirmed;

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

        Event::listen(SaleConfirmed::class, DescontarStock::class);
    }
}
