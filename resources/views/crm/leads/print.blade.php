<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead - {{ $lead->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: letter;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .header-left {
            display: table-cell;
            width: 20%;
            vertical-align: top;
        }

        .header-left img {
            max-width: 150px;
            max-height: 80px;
            display: block;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: left;
            padding-left: 10px;
            padding-top: 5px;
        }

        .header-info {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
            padding-top: 5px;
        }

        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
            color: #007bff;
        }

        .company-info {
            font-size: 9pt;
            line-height: 1.3;
            color: #666;
        }

        .lead-id {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .print-date {
            font-size: 8pt;
            color: #666;
        }

        .title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
            border: 2px solid #007bff;
            padding: 8px;
            background-color: #f8f9fa;
            color: #007bff;
        }

        .section {
            margin-bottom: 12px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #007bff;
            color: white;
            padding: 6px 10px;
            font-size: 11pt;
            font-weight: bold;
        }

        .section-content {
            padding: 8px 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        table td.label {
            font-weight: bold;
            width: 35%;
            color: #555;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }

        .notes-list, .tasks-list, .activities-list {
            list-style: none;
        }

        .notes-list li, .tasks-list li, .activities-list li {
            padding: 6px;
            margin-bottom: 5px;
            border-left: 3px solid #007bff;
            background-color: #f8f9fa;
        }

        .note-date, .task-date, .activity-date {
            font-size: 8pt;
            color: #666;
            display: block;
            margin-bottom: 3px;
        }

        .note-content {
            font-size: 9pt;
        }

        .task-title {
            font-weight: bold;
            font-size: 9pt;
        }

        .task-desc {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
        }

        .activity-type {
            display: inline-block;
            padding: 2px 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            font-size: 8pt;
            margin-right: 5px;
        }

        .activity-desc {
            font-size: 9pt;
        }

        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }

        .two-column {
            display: flex;
            gap: 10px;
        }

        .column {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <div class="header-left">
            @php
                // Obtener el conjunto del lead o del usuario actual
                $conjuntoCode = $lead->conjunto_id ?? \App\Helpers\SchemaHelper::getSchema();

                // Buscar el conjunto en la tabla ERPADMIN.CONJUNTO
                $conjunto = \App\Models\Conjunto::where('CONJUNTO', $conjuntoCode)->first();

                $logoPath = null;

                // El logo viene de ERPADMIN.CONJUNTO campo LOGO
                // Ruta: C:\wamp64\www\hyplast\public\imagen\conjunto
                // Si no hay logo configurado, no se muestra ninguno (no usar fallback de otra empresa)
                if ($conjunto && $conjunto->LOGO) {
                    $logoFile = public_path('imagen/conjunto/' . $conjunto->LOGO);
                    if (file_exists($logoFile)) {
                        $logoPath = asset('imagen/conjunto/' . $conjunto->LOGO);
                    }
                }
            @endphp
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="{{ $conjunto->NOMBRE ?? 'Logo' }}">
            @endif
        </div>
        <div class="header-right">
            <div class="company-name">{{ $conjunto->NOMBRE ?? 'HYPLAST S.R.L.' }}</div>
            <div class="company-info">
                @if($conjunto)
                    {{ $conjunto->DIREC1 }}<br>
                    {{ $conjunto->DIREC2 }}<br>
                    Tel: {{ $conjunto->TELEFONO }}
                @endif
            </div>
        </div>
        <div class="header-info">
            <div class="lead-id">Lead ID: #{{ $lead->id }}</div>
            <div class="print-date">Impreso: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <!-- Título -->
    <div class="title">FICHA DE PROSPECTO - {{ strtoupper($lead->company ?: $lead->name) }}</div>

    <!-- Sección: Datos del Contacto -->
    <div class="section">
        <div class="section-title">DATOS DEL CONTACTO</div>
        <div class="section-content">
            <div class="two-column">
                <div class="column">
                    <table>
                        @if($lead->company)
                        <tr>
                            <td class="label">Empresa:</td>
                            <td>{{ $lead->company }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Nombre:</td>
                            <td>{{ $lead->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Teléfono:</td>
                            <td>{{ $lead->phone }}</td>
                        </tr>
                        @if($lead->email)
                        <tr>
                            <td class="label">Email:</td>
                            <td>{{ $lead->email }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">País:</td>
                            <td>{{ $lead->countryProduction ? $lead->countryProduction->name : 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Estado:</td>
                            <td>{{ $lead->state ? $lead->state->name : 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Ciudad:</td>
                            <td>{{ $lead->city ? $lead->city->name : 'No especificado' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <td class="label">Estado:</td>
                            <td><span class="badge badge-{{ $lead->status->color ?? 'secondary' }}">{{ $lead->status->name ?? 'Sin estado' }}</span></td>
                        </tr>
                        <tr>
                            <td class="label">Prioridad:</td>
                            <td>
                                <span class="badge
                                    @if($lead->priority == 1) badge-danger
                                    @elseif($lead->priority == 2) badge-warning
                                    @else badge-info
                                    @endif">
                                    @if($lead->priority == 1) Alta
                                    @elseif($lead->priority == 2) Media
                                    @else Baja
                                    @endif
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Sitio Web:</td>
                            <td>{{ $lead->website ?? 'No especificado' }}</td>
                        </tr>
                        @if($lead->preferred_channel)
                        <tr>
                            <td class="label">Canal Preferido:</td>
                            <td>{{ ucfirst($lead->preferred_channel) }}</td>
                        </tr>
                        @endif
                        @if($lead->assignedTo)
                        <tr>
                            <td class="label">Asignado a:</td>
                            <td>{{ $lead->assignedTo->name }}</td>
                        </tr>
                        @endif
                        @if($lead->source)
                        <tr>
                            <td class="label">Fuente:</td>
                            <td>{{ ucfirst($lead->source) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Fecha creación:</td>
                            <td>{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Notas -->
    <div class="section">
        <div class="section-title">NOTAS ({{ $leadNotes->count() }})</div>
        <div class="section-content">
            @if($leadNotes->count() > 0)
                <ul class="notes-list">
                    @foreach($leadNotes as $note)
                    <li>
                        <span class="note-date">
                            {{ $note->created_at->format('d/m/Y H:i') }} - {{ $note->user->name ?? 'Desconocido' }}
                        </span>
                        <div class="note-content">{!! nl2br(strip_tags($note->content)) !!}</div>
                    </li>
                    @endforeach
                </ul>
            @else
                <p style="color: #999; font-style: italic;">No hay notas registradas</p>
            @endif
        </div>
    </div>

    <!-- Sección: Tareas -->
    <div class="section">
        <div class="section-title">TAREAS ({{ $leadTasks->count() }})</div>
        <div class="section-content">
            @if($leadTasks->count() > 0)
                <ul class="tasks-list">
                    @foreach($leadTasks as $task)
                    <li style="border-left-color: {{ $task->is_completed ? '#28a745' : '#dc3545' }}">
                        <span class="task-date">
                            <strong>Vencimiento:</strong> {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sin fecha' }}
                            @if($task->is_completed)
                                | <span style="color: #28a745;">✓ Completada</span>
                            @else
                                | <span style="color: #dc3545;">Pendiente</span>
                            @endif
                        </span>
                        <div class="task-title">{{ $task->title }}</div>
                        @if($task->description)
                        <div class="task-desc">{{ $task->description }}</div>
                        @endif
                        @if($task->assignedTo)
                        <div style="font-size: 8pt; color: #666; margin-top: 2px;">
                            Asignado a: {{ $task->assignedTo->name }}
                        </div>
                        @endif
                    </li>
                    @endforeach
                </ul>
            @else
                <p style="color: #999; font-style: italic;">No hay tareas registradas</p>
            @endif
        </div>
    </div>

    <!-- Sección: Actividades -->
    <div class="section">
        <div class="section-title">ACTIVIDADES ({{ $leadActivities->count() }})</div>
        <div class="section-content">
            @if($leadActivities->count() > 0)
                <ul class="activities-list">
                    @foreach($leadActivities->take(20) as $activity)
                    <li>
                        <span class="activity-date">
                            {{ $activity->created_at->format('d/m/Y H:i') }} - {{ $activity->user->name ?? 'Sistema' }}
                        </span>
                        <span class="activity-type">
                            @if($activity->type == 'call') Llamada
                            @elseif($activity->type == 'meeting') Reunión
                            @elseif($activity->type == 'email') Email
                            @elseif($activity->type == 'note') Nota
                            @elseif($activity->type == 'status_change') Cambio de Estado
                            @else {{ ucfirst($activity->type) }}
                            @endif
                        </span>
                        <span class="activity-desc">{{ $activity->description }}</span>
                    </li>
                    @endforeach
                    @if($leadActivities->count() > 20)
                    <li style="background-color: #fff; border-left: none; font-style: italic; color: #666;">
                        ... y {{ $leadActivities->count() - 20 }} actividades más
                    </li>
                    @endif
                </ul>
            @else
                <p style="color: #999; font-style: italic;">No hay actividades registradas</p>
            @endif
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        Hyplast - Sistema de Gestión de Leads | Impreso el {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <!-- Botón para imprimir (solo visible en pantalla) -->
    <div class="no-print" style="position: fixed; top: 10px; right: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12pt;">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12pt; margin-left: 5px;">
            Cerrar
        </button>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
