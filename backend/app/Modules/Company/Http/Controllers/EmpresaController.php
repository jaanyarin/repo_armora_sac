<?php

namespace App\Modules\Company\Http\Controllers;

use App\Modules\Company\Http\Requests\UpdateEmpresaRequest;
use App\Modules\Company\Http\Resources\EmpresaResource;
use App\Modules\Company\Models\EmpresaConfig;
use App\Modules\Company\Services\EmpresaService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmpresaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly EmpresaService $empresaService,
    ) {}

    public function show(): JsonResponse
    {
        $this->authorize('view', EmpresaConfig::class);
        return response()->json(new EmpresaResource($this->empresaService->get()));
    }

    public function update(UpdateEmpresaRequest $request): JsonResponse
    {
        $config = $this->empresaService->update($request->validated());
        return response()->json(new EmpresaResource($config));
    }

    public function uploadImage(Request $request, string $tipo): JsonResponse
    {
        $this->authorize('update', EmpresaConfig::class);

        $allowed = ['login', 'home', 'reporte', 'firma'];
        if (!in_array($tipo, $allowed, true)) {
            return response()->json(['message' => 'Tipo de imagen no válido.'], 422);
        }

        $validated = $request->validate([
            'imagen' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'imagen.required' => 'Debe seleccionar una imagen.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'Solo se permiten formatos: png, jpg, jpeg.',
            'imagen.max' => 'La imagen no debe superar los 2MB.',
        ]);

        $config = $this->empresaService->uploadImage($tipo, $validated['imagen']);
        return response()->json(new EmpresaResource($config));
    }

    public function resetImage(string $tipo): JsonResponse
    {
        $this->authorize('update', EmpresaConfig::class);

        $allowed = ['login', 'home', 'reporte', 'firma'];
        if (!in_array($tipo, $allowed, true)) {
            return response()->json(['message' => 'Tipo de imagen no válido.'], 422);
        }

        $config = $this->empresaService->resetImage($tipo);
        return response()->json(new EmpresaResource($config));
    }

    public function getImage(string $tipo)
    {
        $allowed = ['login', 'home', 'reporte', 'firma'];
        if (!in_array($tipo, $allowed, true)) {
            abort(404);
        }

        $path = $this->empresaService->getImagePath($tipo);

        if (!$path) {
            return response()->json(['message' => 'Imagen no encontrada.'], 404);
        }

        return response()->file($path);
    }

    public function actualizarDecimalesSunat(): JsonResponse
    {
        $this->authorize('update', EmpresaConfig::class);

        $result = $this->empresaService->actualizarDecimalesSunat();

        return response()->json([
            'message' => 'Decimales actualizados correctamente.',
            'products_actualizados' => $result['products'],
            'stocks_actualizados' => $result['stocks'],
        ]);
    }
}
