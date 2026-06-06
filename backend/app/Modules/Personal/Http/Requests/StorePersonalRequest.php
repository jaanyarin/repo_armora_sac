<?php

namespace App\Modules\Personal\Http\Requests;

use App\Models\Catalog\DocumentoIdentidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $docId = $this->input('documento_identidad_id');
        $docRegex = $this->resolveRegex($docId);
        $docMax = $this->resolveMaxLength($docId);

        return [
            'codigo' => ['nullable', 'string', 'max:20', 'unique:users,codigo'],
            'username' => ['required', 'string', 'min:5', 'max:32', 'unique:users,username', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'apellido_paterno' => ['required', 'string', 'min:1', 'max:100'],
            'apellido_materno' => ['required', 'string', 'min:1', 'max:100'],
            'nombres' => ['required', 'string', 'min:1', 'max:150'],
            'password' => ['required', 'string', 'min:5', 'max:32', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:5', 'max:32'],

            'documento_identidad_id' => ['nullable', 'integer', 'exists:dim_documento_identidad,id'],
            'numero_documento' => array_filter([
                'nullable',
                'string',
                $docMax ? "max:{$docMax}" : null,
                $docRegex ? "regex:{$docRegex}" : null,
                'unique:users,numero_documento',
            ]),

            'sexo_id' => ['nullable', 'integer', 'exists:dim_sexo,id'],
            'estado_civil_id' => ['nullable', 'integer', 'exists:dim_estado_civil,id'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],

            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
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
            'username.required' => 'El login de usuario es obligatorio.',
            'username.min' => 'El login debe tener al menos 5 caracteres.',
            'username.max' => 'El login no puede superar los 32 caracteres.',
            'username.unique' => 'Este login ya está en uso.',
            'username.regex' => 'El login solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_materno.required' => 'El apellido materno es obligatorio.',
            'nombres.required' => 'El nombre es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 5 caracteres.',
            'password.max' => 'La contraseña no puede superar los 32 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password_confirmation.required' => 'Debe confirmar la contraseña.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
            'numero_documento.regex' => 'El número de documento no cumple con el formato del tipo seleccionado.',
            'numero_documento.max' => 'El número de documento excede la longitud permitida.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'documento_identidad_id.exists' => 'El tipo de documento seleccionado no es válido.',
            'roles.max' => 'Solo se permite asignar un rol por usuario.',
            'roles.*.exists' => 'Uno de los roles seleccionados no es válido.',
            'permisos.*.exists' => 'Uno de los permisos seleccionados no es válido.',
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
