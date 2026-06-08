<?php

namespace App\Modules\Products\Http\Controllers;

use App\Modules\Company\Models\EmpresaConfig;
use App\Modules\Products\Reports\ProductReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProductReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ProductReportService $reportService,
    ) {}

    public function productosActivos(Request $request): Response
    {
        $this->authorize('generarReportesProductos', Product::class);

        $productos = $this->reportService->getProductosActivos();
        $empresa = $this->getEmpresaConfig();

        $html = view('products.reports.productos-activos', [
            'productos' => $productos,
            'empresa' => $empresa,
            'generadoEn' => now()->format('d/m/Y H:i:s'),
            'generadoPor' => auth()->user()?->nombre_completo ?? auth()->user()?->name ?? 'Sistema',
        ])->render();

        return $this->respondPrintable($html, 'reporte-productos-activos');
    }

    private function respondPrintable(string $html, string $filename): Response
    {
        $wrapped = view('personal.reports._print_wrapper', [
            'content' => $html,
            'title' => $filename,
        ])->render();

        return response($wrapped, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function getEmpresaConfig(): ?object
    {
        try {
            return EmpresaConfig::query()->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
