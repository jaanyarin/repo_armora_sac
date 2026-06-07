@php
    $empresaNombre = $empresa?->nombre_comercial ?: $empresa?->razon_social ?: 'ARMORA SAC';
    $empresaRuc = $empresa?->ruc;
    $fullName = trim(($p->apellido_paterno ?? '') . ' ' . ($p->apellido_materno ?? '') . ' ' . ($p->nombres ?? '')) ?: $p->name;
    $fotoUrl = $p->foto_path ? url('storage/' . $p->foto_path) : null;
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

<div class="report-title">Ficha de Personal</div>

<div class="ficha-section">
    <div class="ficha-foto">
        @if($fotoUrl)
            <img src="{{ $fotoUrl }}" alt="Foto de {{ $fullName }}">
        @else
            <div class="placeholder">Sin foto</div>
        @endif
    </div>
    <h2 style="margin-top: 0;">Identificación</h2>
    <div class="ficha-grid">
        <div class="ficha-item">
            <span class="label">Código</span>
            <span class="value">{{ $p->codigo ?: 'PER-' . $p->id }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Estado</span>
            <span class="value">
                @if($p->activo)
                    <span class="badge green">Activo</span>
                @else
                    <span class="badge gray">Inactivo</span>
                @endif
                @if($p->trashed())
                    <span class="badge gray">Eliminado</span>
                @endif
            </span>
        </div>
        <div class="ficha-item">
            <span class="label">Nombre Completo</span>
            <span class="value">{{ $fullName }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Username</span>
            <span class="value">{{ $p->username }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">{{ $p->documentoIdentidad?->nombre ?? 'Documento' }}</span>
            <span class="value">{{ $p->numero_documento ?: '—' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Sexo</span>
            <span class="value">{{ $p->sexo?->nombre ?? '—' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Estado Civil</span>
            <span class="value">{{ $p->estadoCivil?->nombre ?? '—' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Fecha de Nacimiento</span>
            <span class="value">{{ $p->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</span>
        </div>
    </div>
</div>

<div class="ficha-section">
    <h2>Contacto y Ubicación</h2>
    <div class="ficha-grid">
        <div class="ficha-item">
            <span class="label">Email</span>
            <span class="value">{{ $p->email ?: '—' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Teléfono</span>
            <span class="value">{{ $p->telefono ?: $p->telefono_celular ?: '—' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Celular</span>
            <span class="value">{{ $p->telefono_celular ?: $p->telefono ?: '—' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">País</span>
            <span class="value">{{ $p->pais?->nombre ?? '—' }}</span>
        </div>
        <div class="ficha-item" style="grid-column: 1 / -1;">
            <span class="label">Dirección</span>
            <span class="value">
                @if($p->direccion)
                    {{ $p->direccion }}
                    @if($p->ubigeo)
                        ({{ $p->ubigeo?->departamento?->nombre ?? '' }} / {{ $p->ubigeo?->provincia?->nombre ?? '' }} / {{ $p->ubigeo?->nombre ?? '' }})
                    @endif
                    @if($p->referencia)
                        <br><small style="color: #6b7280;">Ref: {{ $p->referencia }}</small>
                    @endif
                @else
                    —
                @endif
            </span>
        </div>
    </div>
</div>

<div class="ficha-section">
    <h2>Roles Asignados</h2>
    @if($p->roles->isNotEmpty())
        @foreach($p->roles as $rol)
            <span class="badge green">{{ $rol->name }}</span>
        @endforeach
    @else
        <span style="color: #9ca3af; font-style: italic;">Sin roles asignados</span>
    @endif
</div>

@if($p->permisosDirectos->isNotEmpty())
<div class="ficha-section">
    <h2>Permisos Directos Adicionales</h2>
    @foreach($p->permisosDirectos as $perm)
        <span class="badge gray">{{ $perm->name }}</span>
    @endforeach
</div>
@endif

<div class="ficha-section">
    <h2>Almacenes Asignados</h2>
    @if($p->almacenes->isNotEmpty())
        <table class="report">
            <thead>
                <tr>
                    <th style="width: 25%;">Código</th>
                    <th>Nombre</th>
                    <th style="width: 40%;">Dirección</th>
                </tr>
            </thead>
            <tbody>
                @foreach($p->almacenes as $alm)
                    <tr>
                        <td><strong>{{ $alm->codigo }}</strong></td>
                        <td>{{ $alm->nombre }}</td>
                        <td>{{ $alm->direccion ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="color: #9ca3af; font-style: italic;">Sin almacenes asignados</div>
    @endif
</div>

<div class="ficha-section">
    <h2>Listas de Precios Asignadas</h2>
    @if($p->listasPrecios->isNotEmpty())
        <table class="report">
            <thead>
                <tr>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>
                @foreach($p->listasPrecios as $lp)
                    <tr>
                        <td>{{ $lp->nombre }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="color: #9ca3af; font-style: italic;">Sin listas de precios asignadas</div>
    @endif
</div>

<div class="ficha-section">
    <h2>Información del Sistema</h2>
    <div class="ficha-grid">
        <div class="ficha-item">
            <span class="label">Último Acceso</span>
            <span class="value">{{ $p->ultimo_acceso?->format('d/m/Y H:i') ?? 'Nunca' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Contraseña Cambiada</span>
            <span class="value">{{ $p->password_changed_at?->format('d/m/Y H:i') ?? '—' }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Creado</span>
            <span class="value">{{ $p->created_at?->format('d/m/Y H:i') }}</span>
        </div>
        <div class="ficha-item">
            <span class="label">Última Actualización</span>
            <span class="value">{{ $p->updated_at?->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</div>

<div class="footer">
    {{ $empresaNombre }} — Ficha de personal generada automáticamente por ARMORA ERP.
</div>
