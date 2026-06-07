<?php

namespace App\Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoSubclaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar-subclases');
    }

    public function rules(): array
    {
        $subclaseId = $this->route('subclase');
        $claseId = $this->input('clase_id');

        return [
            'clase_id' => ['required', 'string', 'exists:products_clases,id'],
            'codigo' => ['nullable', 'string', 'max:20', Rule::unique('products_subclases', 'codigo')->ignore($subclaseId)],
            'nombre' => [
                'required', 'string', 'min:2', 'max:100',
                Rule::unique('products_subclases', 'nombre')
                    ->where('clase_id', $claseId)
                    ->whereNull('deleted_at')
                    ->ignore($subclaseId),
            ],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('products_subclases', 'slug')->ignore($subclaseId), 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'clase_id.required' => 'Debe seleccionar una clase.',
            'clase_id.exists' => 'La clase seleccionada no existe.',
            'nombre.required' => 'El nombre de la subclase es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'nombre.unique' => 'Ya existe una subclase con este nombre en la clase seleccionada.',
            'codigo.unique' => 'Este código ya está en uso.',
            'slug.unique' => 'Este slug ya está en uso.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
        ];
    }
}
