<?php

namespace App\Modules\Company\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaConfig extends Model
{
    protected $table = 'config_empresa';

    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'ruc',
        'email',
        'pais_id',
        'telefono_fijo',
        'telefono_celular',
        'departamento_id',
        'provincia_id',
        'ubigeo_id',
        'direccion',
        'referencia',
        'porcentaje_igv',
        'boleta_monto_dni',
        'envio_auto_sunat',
        'consolidado_requerimientos',
        'consolidado_liquidaciones',
        'resumen_liquidacion',
        'preventa_nota_pedido',
        'periodo_fecha_inicio',
        'periodo_fecha_fin',
        'comision_defecto',
        'hora_cierre',
        'comision_neto',
        'preview_fecha_inicio',
        'preview_fecha_fin',
        'preview',
        'imagen_login',
        'imagen_home',
        'imagen_reporte',
        'imagen_firma',
        'ventas_bloqueadas',
        'compras_bloqueadas',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_igv' => 'decimal:2',
            'boleta_monto_dni' => 'decimal:2',
            'comision_defecto' => 'decimal:2',
            'envio_auto_sunat' => 'boolean',
            'consolidado_requerimientos' => 'boolean',
            'consolidado_liquidaciones' => 'boolean',
            'resumen_liquidacion' => 'boolean',
            'preventa_nota_pedido' => 'boolean',
            'comision_neto' => 'boolean',
            'preview' => 'boolean',
            'ventas_bloqueadas' => 'boolean',
            'compras_bloqueadas' => 'boolean',
            'periodo_fecha_inicio' => 'date',
            'periodo_fecha_fin' => 'date',
            'preview_fecha_inicio' => 'date',
            'preview_fecha_fin' => 'date',
            'hora_cierre' => 'string',
        ];
    }
}
