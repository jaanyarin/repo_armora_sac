<?php

namespace App\Modules\Personal\Http\Controllers;

use App\Modules\Personal\Http\Requests\PersonalReportRequest;
use App\Modules\Personal\Models\Personal;
use App\Modules\Personal\Reports\PersonalReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class PersonalReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PersonalReportService $reportService,
    ) {}

    public function personalActivo(Request $request): Response
    {
        $this->authorize('generarReportesPersonal', Personal::class);

        $personal = $this->reportService->getPersonalActivo();
        $empresa = $this->getEmpresaConfig();

        $html = view('personal.reports.personal-activo', [
            'personal' => $personal,
            'empresa' => $empresa,
            'generadoEn' => now()->format('d/m/Y H:i:s'),
            'generadoPor' => auth()->user()?->nombre_completo ?? auth()->user()?->name ?? 'Sistema',
        ])->render();

        return $this->respondPrintable($html, 'reporte-personal-activo');
    }

    public function fichaPersonal(PersonalReportRequest $request): Response
    {
        $this->authorize('generarReportesPersonal', Personal::class);

        $personal = $this->reportService->findForFicha($request->integer('pid'));
        if (! $personal) {
            abort(404, 'Personal no encontrado.');
        }

        $empresa = $this->getEmpresaConfig();

        $html = view('personal.reports.ficha-personal', [
            'p' => $personal,
            'empresa' => $empresa,
            'generadoEn' => now()->format('d/m/Y H:i:s'),
            'generadoPor' => auth()->user()?->nombre_completo ?? auth()->user()?->name ?? 'Sistema',
        ])->render();

        $slug = $personal->codigo ?: ('PER-' . $personal->id);
        return $this->respondPrintable($html, 'ficha-personal-' . $slug);
    }

    private function respondPrintable(string $html, string $filename): Response
    {
        $wrapped = view('personal.reports._print_wrapper', ['content' => $html, 'title' => $filename])->render();
        return response($wrapped, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function getEmpresaConfig(): ?object
    {
        try {
            return \App\Modules\Company\Models\EmpresaConfig::query()->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
