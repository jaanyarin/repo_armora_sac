<?php

namespace App\Modules\Personal\Http\Requests;

use App\Models\Catalog\DocumentoIdentidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('personal')?->id ?? $this->route('personal');
        $docId = $this->input('documento_identidad_id');
        $docRegex = $this->resolveRegex($docId);
        $docMax = $this->resolveMaxLength($docId);

        return [
            'codigo' => ['nullable', 'string', 'max:20', Rule::unique('users', 'codigo')->ignore($id)],
            'username' => ['sometimes', 'string', 'min:5', 'max:32', Rule::unique('users', 'username')->ignore($id), 'regex:/^[a-zA-Z0-9._-]+$/'],
            'apellido_paterno' => ['sometimes', 'string', 'min:1', 'max:100'],
            'apellido_materno' => ['sometimes', 'string', 'min:1', 'max:100'],
            'nombres' => ['sometimes', 'string', 'min:1', 'max:150'],
            'password' => ['nullable', 'string', 'min:5', 'max:32', 'confirmed'],
            'password_confirmation' => ['nullable', 'string', 'min:5', 'max:32'],

            'documento_identidad_id' => ['nullable', 'integer', 'exists:dim_documento_identidad,id'],
            'numero_documento' => array_filter([
                'nullable',
                'string',
                $docMax ? "max:{$docMax}" : null,
                $docRegex ? "regex:{$docRegex}" : null,
                Rule::unique('users', 'numero_documento')->ignore($id),
            ]),

            'sexo_id' => ['nullable', 'integer', 'exists:dim_sexo,id'],
            'estado_civil_id' => ['nullable', 'integer', 'exists:dim_estado_civil,id'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],

            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'pais_id' => ['nullable', 'integer', 'exists:dim_pais,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'telefono_fijo' => ['nullable', 'string', 'max:20'],
            'telefono_celular' => ['nullable', 'string', 'max:20'],

            'departamento_id' => ['nullable', 'integer', 'exists:dim_departamento,id'],
            'provincia_id' => ['nullable', 'integer', 'exists:dim_provincia,id'],
            'ubigeo_id' => ['nullable', 'integer', 'exists:dim_ubigeo,id'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'referencia' => ['nullable', 'string', 'max:500'],

            'activo' => ['boolean'],

            'roles' => ['nullable', 'array', 'max:1'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'exists:permissions,id'],
            'listas_precios' => ['nullable', 'array'],
            'listas_precios.*' => ['integer', 'exists:dim_lista_precios,id'],
            'almacenes' => ['nullable', 'array'],
            'almacenes.*' => ['integer', 'exists:dim_almacen,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Este login ya está en uso.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
            'numero_documento.regex' => 'El número de documento no cumple con el formato del tipo seleccionado.',
            'numero_documento.max' => 'El número de documento excede la longitud permitida.',
            'roles.max' => 'Solo se permite asignar un rol por usuario.',
        ];
    }

    private function resolveRegex(?int $docId): ?string
    {
        if (!$docId) return null;
        $doc = DocumentoIdentidad::find($docId);
        return $doc?->regex;
    }

    private function resolveMaxLength(?int $docId): ?int
    {
        if (!$docId) return 20;
        $doc = DocumentoIdentidad::find($docId);
        if (!$doc || !$doc->longitud) return 20;
        $parts = explode(',', $doc->longitud);
        $max = isset($parts[1]) ? (int) $parts[1] : (int) $parts[0];
        return $max;
    }
}
