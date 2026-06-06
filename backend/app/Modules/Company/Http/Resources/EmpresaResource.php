<?php

namespace App\Modules\Company\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'razon_social' => $this->razon_social,
            'nombre_comercial' => $this->nombre_comercial,
            'ruc' => $this->ruc,
            'email' => $this->email,
            'pais_id' => $this->pais_id,
            'telefono_fijo' => $this->telefono_fijo,
            'telefono_celular' => $this->telefono_celular,
            'departamento_id' => $this->departamento_id,
            'provincia_id' => $this->provincia_id,
            'ubigeo_id' => $this->ubigeo_id,
            'direccion' => $this->direccion,
            'referencia' => $this->referencia,

            'porcentaje_igv' => $this->porcentaje_igv,
            'boleta_monto_dni' => $this->boleta_monto_dni,
            'envio_auto_sunat' => $this->envio_auto_sunat,
            'consolidado_requerimientos' => $this->consolidado_requerimientos,
            'consolidado_liquidaciones' => $this->consolidado_liquidaciones,
            'resumen_liquidacion' => $this->resumen_liquidacion,
            'preventa_nota_pedido' => $this->preventa_nota_pedido,
            'periodo_fecha_inicio' => $this->periodo_fecha_inicio?->format('Y-m-d'),
            'periodo_fecha_fin' => $this->periodo_fecha_fin?->format('Y-m-d'),
            'comision_defecto' => $this->comision_defecto,
            'hora_cierre' => $this->hora_cierre,
            'comision_neto' => $this->comision_neto,
            'preview_fecha_inicio' => $this->preview_fecha_inicio?->format('Y-m-d'),
            'preview_fecha_fin' => $this->preview_fecha_fin?->format('Y-m-d'),
            'preview' => $this->preview,

            'imagen_login' => $this->imagen_login ? url("storage/{$this->imagen_login}") : null,
            'imagen_home' => $this->imagen_home ? url("storage/{$this->imagen_home}") : null,
            'imagen_reporte' => $this->imagen_reporte ? url("storage/{$this->imagen_reporte}") : null,
            'imagen_firma' => $this->imagen_firma ? url("storage/{$this->imagen_firma}") : null,

            'ventas_bloqueadas' => $this->ventas_bloqueadas,
            'compras_bloqueadas' => $this->compras_bloqueadas,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
