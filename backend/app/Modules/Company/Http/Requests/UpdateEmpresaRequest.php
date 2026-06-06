<?php

namespace App\Modules\Company\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('configurar-empresa');
    }

    public function rules(): array
    {
        $required = $this->has('__seccion') && $this->input('__seccion') === 'empresa'
            ? 'required'
            : 'nullable';

        return [
            'razon_social' => [$required, 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'ruc' => [$required, 'string', 'max:11', 'regex:/^\d{11}$/'],
            'email' => [$required, 'email', 'max:255'],
            'pais_id' => ['nullable', 'integer', 'exists:dim_pais,id'],
            'telefono_fijo' => ['nullable', 'string', 'max:20'],
            'telefono_celular' => [$required, 'string', 'max:20'],
            'departamento_id' => [$required, 'integer', 'exists:dim_departamento,id'],
            'provincia_id' => [$required, 'integer', 'exists:dim_provincia,id'],
            'ubigeo_id' => [$required, 'integer', 'exists:dim_ubigeo,id'],
            'direccion' => [$required, 'string', 'max:255'],
            'referencia' => ['nullable', 'string', 'max:255'],

            'porcentaje_igv' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'boleta_monto_dni' => ['nullable', 'numeric', 'min:0'],
            'envio_auto_sunat' => ['nullable', 'boolean'],
            'consolidado_requerimientos' => ['nullable', 'boolean'],
            'consolidado_liquidaciones' => ['nullable', 'boolean'],
            'resumen_liquidacion' => ['nullable', 'boolean'],
            'preventa_nota_pedido' => ['nullable', 'boolean'],
            'periodo_fecha_inicio' => ['nullable', 'date'],
            'periodo_fecha_fin' => ['nullable', 'date', 'after_or_equal:periodo_fecha_inicio'],
            'comision_defecto' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hora_cierre' => ['nullable', 'date_format:H:i'],
            'comision_neto' => ['nullable', 'boolean'],
            'preview_fecha_inicio' => ['nullable', 'date'],
            'preview_fecha_fin' => ['nullable', 'date', 'after_or_equal:preview_fecha_inicio'],
            'preview' => ['nullable', 'boolean'],

            'ventas_bloqueadas' => ['nullable', 'boolean'],
            'compras_bloqueadas' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'razon_social.required' => 'La razón social es obligatoria.',
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.regex' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.max' => 'El RUC debe tener exactamente 11 dígitos.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email no tiene un formato válido.',
            'telefono_celular.required' => 'El teléfono celular es obligatorio.',
            'departamento_id.required' => 'El departamento es obligatorio.',
            'departamento_id.exists' => 'El departamento seleccionado no existe.',
            'provincia_id.required' => 'La provincia es obligatoria.',
            'provincia_id.exists' => 'La provincia seleccionada no existe.',
            'ubigeo_id.required' => 'El distrito es obligatorio.',
            'ubigeo_id.exists' => 'El ubigeo seleccionado no existe.',
            'direccion.required' => 'La dirección es obligatoria.',
            'pais_id.exists' => 'El país seleccionado no existe.',
            'periodo_fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'preview_fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
