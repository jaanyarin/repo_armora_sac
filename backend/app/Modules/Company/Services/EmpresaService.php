<?php

namespace App\Modules\Company\Services;

use App\Modules\Company\Models\EmpresaConfig;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmpresaService
{
    public function get(): EmpresaConfig
    {
        return EmpresaConfig::firstOrCreate(['id' => 1]);
    }

    public function update(array $data): EmpresaConfig
    {
        $config = $this->get();
        $config->update($data);
        return $config->fresh();
    }

    public function uploadImage(string $tipo, UploadedFile $file): EmpresaConfig
    {
        $config = $this->get();

        $path = $file->store("empresa/{$tipo}", 'public');
        $config->update(["imagen_{$tipo}" => $path]);

        return $config->fresh();
    }

    public function resetImage(string $tipo): EmpresaConfig
    {
        $config = $this->get();
        $config->update(["imagen_{$tipo}" => null]);
        return $config->fresh();
    }

    public function getImagePath(string $tipo): ?string
    {
        $config = $this->get();
        $path = $config->{"imagen_{$tipo}"};

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        return null;
    }

    public function bloquearVentas(): EmpresaConfig
    {
        return $this->update(['ventas_bloqueadas' => true]);
    }

    public function activarVentas(): EmpresaConfig
    {
        return $this->update(['ventas_bloqueadas' => false]);
    }

    public function bloquearCompras(): EmpresaConfig
    {
        return $this->update(['compras_bloqueadas' => true]);
    }

    public function activarCompras(): EmpresaConfig
    {
        return $this->update(['compras_bloqueadas' => false]);
    }

    public function actualizarDecimalesSunat(): array
    {
        $updated = [
            'products' => 0,
            'stocks' => 0,
        ];

        $updated['products'] = \App\Modules\Products\Models\Product::whereNotNull('precio_venta')
            ->whereRaw('precio_venta != ROUND(precio_venta::numeric, 2)')
            ->orWhereRaw('costo_promedio != ROUND(costo_promedio::numeric, 2)')
            ->update([
                'precio_venta' => \Illuminate\Support\Facades\DB::raw('ROUND(precio_venta::numeric, 2)'),
                'precio_venta_usd' => \Illuminate\Support\Facades\DB::raw('ROUND(precio_venta_usd::numeric, 2)'),
                'costo_promedio' => \Illuminate\Support\Facades\DB::raw('ROUND(costo_promedio::numeric, 2)'),
            ]);

        $updated['stocks'] = \App\Modules\Inventory\Models\Stock::whereRaw('cantidad_disponible != ROUND(cantidad_disponible::numeric, 2)')
            ->update([
                'cantidad_disponible' => \Illuminate\Support\Facades\DB::raw('ROUND(cantidad_disponible::numeric, 2)'),
            ]);

        return $updated;
    }
}
