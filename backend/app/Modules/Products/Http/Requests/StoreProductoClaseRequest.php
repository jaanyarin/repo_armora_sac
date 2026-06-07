<?php

namespace App\Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoClaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-clases');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:20', 'unique:products_clases,codigo'],
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:products_clases,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'licor' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la clase es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'codigo.unique' => 'Este código ya está en uso.',
            'slug.unique' => 'Este slug ya está en uso.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
        ];
    }
}
