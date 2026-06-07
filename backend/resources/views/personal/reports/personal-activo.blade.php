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

<div class="report-title">Reporte de Personal Activo</div>

@if($personal->isEmpty())
    <div class="empty-state">No hay personal activo registrado en el sistema.</div>
@else
    <table class="report">
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 25%;">Nombre Completo</th>
                <th style="width: 10%;">Documento</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 12%;">Roles</th>
                <th style="width: 14%;">Almacenes</th>
                <th style="width: 14%;">Listas de Precios</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personal as $p)
                <tr>
                    <td><strong>{{ $p->codigo ?: 'PER-' . $p->id }}</strong></td>
                    <td>
                        {{ trim(($p->apellido_paterno ?? '') . ' ' . ($p->apellido_materno ?? '') . ' ' . ($p->nombres ?? '')) ?: $p->name }}
                    </td>
                    <td>
                        @if($p->documentoIdentidad && $p->numero_documento)
                            {{ $p->documentoIdentidad->codigo }}: {{ $p->numero_documento }}
                        @else
                            <span style="color: #9ca3af;">—</span>
                        @endif
                    </td>
                    <td>{{ $p->email ?: '—' }}</td>
                    <td>
                        @forelse($p->roles as $rol)
                            <span class="badge green">{{ $rol->name }}</span>
                        @empty
                            <span class="badge gray">Sin rol</span>
                        @endforelse
                    </td>
                    <td>
                        @forelse($p->almacenes as $alm)
                            <span class="badge gray">{{ $alm->codigo }}</span>
                        @empty
                            <span style="color: #9ca3af;">—</span>
                        @endforelse
                    </td>
                    <td>
                        @forelse($p->listasPrecios as $lp)
                            <span class="badge gray">{{ $lp->nombre }}</span>
                        @empty
                            <span style="color: #9ca3af;">—</span>
                        @endforelse
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">
                    Total de personal activo: <strong>{{ $personal->count() }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
@endif

<div class="footer">
    {{ $empresaNombre }} — Reporte generado automáticamente por el sistema ARMORA ERP.
</div>
