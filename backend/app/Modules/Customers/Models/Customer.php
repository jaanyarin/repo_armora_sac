<?php

namespace App\Modules\Customers\Models;

use App\Models\Catalog\ListaPrecio;
use App\Models\Catalog\SegmentoCliente;
use App\Models\Catalog\TipoCliente;
use App\Models\Catalog\Ubigeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'tipo_documento',
        'numero_documento',
        'nombre_completo',
        'nombre_comercial',
        'direccion',
        'ubigeo_id',
        'email',
        'telefono',
        'tipo_cliente_id',
        'segmento_id',
        'lista_precio_id',
        'limite_credito',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'limite_credito' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function ubigeo(): BelongsTo
    {
        return $this->belongsTo(Ubigeo::class, 'ubigeo_id');
    }

    public function tipoCliente(): BelongsTo
    {
        return $this->belongsTo(TipoCliente::class, 'tipo_cliente_id');
    }

    public function segmento(): BelongsTo
    {
        return $this->belongsTo(SegmentoCliente::class, 'segmento_id');
    }

    public function listaPrecio(): BelongsTo
    {
        return $this->belongsTo(ListaPrecio::class, 'lista_precio_id');
    }
}
