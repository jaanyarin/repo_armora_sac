@php
    $empresaNombre = $empresa?->nombre_comercial ?: $empresa?->razon_social ?: 'ARMORA SAC';
    $empresaRuc = $empresa?->ruc;
@endphp
<div class="report-header">
    <div class="company">
        <h1>{{ $empresaNombre }}</h1>
        @if($empresaRuc)
            <div class="ruc">RUC: {{ $empresaRuc }}</div>
        @endif
    </div>
    <div class="meta">
        <div><strong>Fecha de emisión:</strong> {{ $generadoEn }}</div>
        <div><strong>Generado por:</strong> {{ $generadoPor }}</div>
    </div>
</div>

<div class="report-title">Reporte de Productos Activos</div>

@if($productos->isEmpty())
    <div class="empty-state">No hay productos activos registrados en el sistema.</div>
@else
    <table class="report">
        <thead>
            <tr>
                <th style="width: 8%;">Código</th>
                <th style="width: 28%;">Nombre</th>
                <th style="width: 10%;">Unidad</th>
                <th style="width: 14%;">Clase</th>
                <th style="width: 10%;">Precio S/</th>
                <th style="width: 10%;">Precio USD</th>
                <th style="width: 10%;">Costo Prom.</th>
                <th style="width: 10%;">Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $p)
                <tr>
                    <td><strong>{{ $p->codigo }}</strong></td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ $p->unidadMedida?->nombre ?: '—' }}</td>
                    <td>{{ $p->productoClase?->nombre ?: '—' }}</td>
                    <td style="text-align: right;">S/ {{ number_format($p->precio_venta, 2) }}</td>
                    <td style="text-align: right;">$ {{ number_format($p->precio_venta_usd, 2) }}</td>
                    <td style="text-align: right;">S/ {{ number_format($p->costo_promedio, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($p->stock_actual, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">
                    Total de productos activos: <strong>{{ $productos->count() }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
@endif

<div class="footer">
    {{ $empresaNombre }} — Reporte generado automáticamente por el sistema ARMORA ERP.
</div>
