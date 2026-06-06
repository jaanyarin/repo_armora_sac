<?php

namespace App\Modules\Personal\Http\Controllers;

use App\Modules\Personal\Http\Requests\StorePersonalRequest;
use App\Modules\Personal\Http\Requests\UpdatePersonalRequest;
use App\Modules\Personal\Http\Resources\PersonalResource;
use App\Modules\Personal\Models\Personal;
use App\Modules\Personal\Services\PersonalService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PersonalController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PersonalService $personalService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Personal::class);
        $personal = $this->personalService->paginate(request()->only(['search', 'activo', 'per_page']));
        return response()->json($personal);
    }

    public function show(int $id)
    {
        $personal = $this->personalService->findById($id);
        if (!$personal) {
            return response()->json(['message' => 'Personal no encontrado.'], 404);
        }
        $this->authorize('view', $personal);
        return new PersonalResource($personal);
    }

    public function store(StorePersonalRequest $request)
    {
        $this->authorize('create', Personal::class);
        $personal = $this->personalService->create($request->validated());
        return (new PersonalResource($personal))->response()->setStatusCode(201);
    }

    public function update(UpdatePersonalRequest $request, int $id)
    {
        $personal = Personal::find($id);
        if (!$personal) {
            return response()->json(['message' => 'Personal no encontrado.'], 404);
        }
        $this->authorize('update', $personal);
        $personal = $this->personalService->update($personal, $request->validated());
        return new PersonalResource($personal);
    }

    public function destroy(int $id): JsonResponse
    {
        $personal = Personal::find($id);
        if (!$personal) {
            return response()->json(['message' => 'Personal no encontrado.'], 404);
        }
        $this->authorize('delete', $personal);
        $this->personalService->delete($personal);
        return response()->json(['message' => 'Personal eliminado.']);
    }

    public function uploadPhoto(Request $request, int $id)
    {
        $personal = Personal::find($id);
        if (!$personal) {
            return response()->json(['message' => 'Personal no encontrado.'], 404);
        }
        $this->authorize('update', $personal);

        $validated = $request->validate([
            'foto' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'foto.required' => 'Debe seleccionar una imagen.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'Solo se permiten formatos: png, jpg, jpeg.',
            'foto.max' => 'La imagen no debe superar los 2MB.',
        ]);

        $personal = $this->personalService->uploadPhoto($personal, $validated['foto']);
        return new PersonalResource($personal);
    }

    public function resetPhoto(int $id)
    {
        $personal = Personal::find($id);
        if (!$personal) {
            return response()->json(['message' => 'Personal no encontrado.'], 404);
        }
        $this->authorize('update', $personal);

        $personal = $this->personalService->resetPhoto($personal);
        return new PersonalResource($personal);
    }

    public function getPhoto(int $id)
    {
        $personal = Personal::withTrashed()->find($id);
        if (!$personal) {
            abort(404);
        }

        $path = $this->personalService->getPhotoPath($personal);
        if (!$path) {
            abort(404);
        }

        return response()->file($path);
    }

    public function rolesDisponibles(): JsonResponse
    {
        $this->authorize('viewAny', Personal::class);
        return response()->json($this->personalService->getRolesDisponibles());
    }

    public function permisosAgrupados(): JsonResponse
    {
        $this->authorize('viewAny', Personal::class);
        $permisos = $this->personalService->getPermisosAgrupados();
        $payload = $permisos->map(fn($items, $modulo) => [
            'modulo' => $modulo,
            'permisos' => $items->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'descripcion' => $p->descripcion,
            ])->values(),
        ])->values();

        return response()->json($payload);
    }
}
