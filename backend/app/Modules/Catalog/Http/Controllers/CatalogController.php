<?php

namespace App\Modules\Catalog\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function monedas()
    {
        return response()->json(DB::table('dim_moneda')->where('activo', true)->orderBy('codigo')->get());
    }

    public function unidadesMedida()
    {
        return response()->json(DB::table('dim_unidad_medida')->where('activo', true)->orderBy('codigo_sunat')->get());
    }

    public function paises()
    {
        return response()->json(DB::table('dim_pais')->where('activo', true)->orderBy('codigo')->get());
    }

    public function documentos()
    {
        return response()->json(DB::table('dim_documento_simbolo')->where('activo', true)->orderBy('codigo_sunat')->get());
    }

    public function departamentos()
    {
        return response()->json(DB::table('dim_departamento')->where('activo', true)->orderBy('codigo_ubigeo')->get());
    }

    public function provincias(?int $departamentoId = null)
    {
        $query = DB::table('dim_provincia')->where('activo', true);
        if ($departamentoId) {
            $query->where('departamento_id', $departamentoId);
        }
        return response()->json($query->orderBy('codigo_ubigeo')->get());
    }

    public function ubigeos(?int $provinciaId = null)
    {
        $query = DB::table('dim_ubigeo')->where('activo', true);
        if ($provinciaId) {
            $query->where('provincia_id', $provinciaId);
        }
        return response()->json($query->orderBy('codigo_ubigeo')->get());
    }

    public function tipoAfeccionIgv()
    {
        return response()->json(DB::table('dim_tipo_afeccion_igv')->where('activo', true)->orderBy('codigo_sunat')->get());
    }

    public function tipoCalculoIsc()
    {
        return response()->json(DB::table('dim_tipo_calculo_isc')->where('activo', true)->orderBy('codigo_sunat')->get());
    }

    public function notaCreditoTipos()
    {
        return response()->json(DB::table('dim_nota_credito_tipo')->where('activo', true)->orderBy('codigo_sunat')->get());
    }

    public function segmentos()
    {
        return response()->json(DB::table('dim_segmento_sunat')->where('activo', true)->orderBy('codigo')->get());
    }

    public function tiposCliente()
    {
        return response()->json(DB::table('dim_tipo_cliente')->where('activo', true)->orderBy('nombre')->get());
    }

    public function familias()
    {
        return response()->json(DB::table('dim_familia_sunat')->where('activo', true)->orderBy('codigo')->get());
    }

    public function clases()
    {
        return response()->json(DB::table('dim_clase_sunat')->where('activo', true)->orderBy('codigo')->get());
    }

    public function productoClases()
    {
        return response()->json(DB::table('dim_producto_clase')->where('activo', true)->orderBy('nombre')->get());
    }

    public function productoSubclases(?int $claseId = null)
    {
        $query = DB::table('dim_producto_subclase')->where('activo', true);
        if ($claseId) {
            $query->where('clase_id', $claseId);
        }
        return response()->json($query->orderBy('nombre')->get());
    }

    public function tiposVenta()
    {
        return response()->json(DB::table('dim_tipo_venta')->orderBy('nombre')->get());
    }

    public function tiposCompra()
    {
        return response()->json(DB::table('dim_tipo_compra')->orderBy('nombre')->get());
    }

    public function documentoTipos()
    {
        return response()->json(DB::table('dim_documento_tipo')
            ->join('dim_documento_simbolo', 'dim_documento_tipo.simbolo_id', '=', 'dim_documento_simbolo.id')
            ->where('dim_documento_tipo.activo', true)
            ->select('dim_documento_tipo.*', 'dim_documento_simbolo.simbolo', 'dim_documento_simbolo.nombre as simbolo_nombre')
            ->orderBy('dim_documento_tipo.codigo_sunat')
            ->get());
    }

    public function tipoCambio()
    {
        return response()->json(DB::table('dim_tipo_cambio')
            ->join('dim_moneda as mo', 'dim_tipo_cambio.moneda_origen_id', '=', 'mo.id')
            ->join('dim_moneda as md', 'dim_tipo_cambio.moneda_destino_id', '=', 'md.id')
            ->where('dim_tipo_cambio.fecha', now()->toDateString())
            ->select('dim_tipo_cambio.*', 'mo.codigo as moneda_origen', 'md.codigo as moneda_destino')
            ->orderBy('mo.codigo')
            ->get());
    }

    public function listaPrecios()
    {
        return response()->json(DB::table('dim_lista_precios')
            ->join('dim_moneda', 'dim_lista_precios.moneda_id', '=', 'dim_moneda.id')
            ->where('dim_lista_precios.activa', true)
            ->select('dim_lista_precios.*', 'dim_moneda.codigo as moneda_codigo', 'dim_moneda.simbolo as moneda_simbolo')
            ->orderBy('dim_lista_precios.nombre')
            ->get());
    }

    public function estadoCivil()
    {
        return response()->json(DB::table('dim_estado_civil')->orderBy('nombre')->get());
    }

    public function sexos()
    {
        return response()->json(DB::table('dim_sexo')->orderBy('nombre')->get());
    }

    public function roles()
    {
        return response()->json(DB::table('roles')->orderBy('name')->get());
    }

    public function permisos()
    {
        return response()->json(DB::table('permissions')->orderBy('name')->get());
    }
}
