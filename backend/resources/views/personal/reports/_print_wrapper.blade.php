<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.2cm 1.4cm 1.5cm 1.4cm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, h4 { margin: 0; }

        .print-toolbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            background: #1f2937;
            color: #f9fafb;
            padding: 10px 16px;
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            z-index: 999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .print-toolbar button {
            background: #f97316;
            color: #fff;
            border: 0;
            padding: 8px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }

        .print-toolbar button:hover { background: #ea580c; }

        .print-toolbar .close {
            background: transparent;
            color: #f9fafb;
            border: 1px solid #4b5563;
        }

        .print-toolbar .close:hover { background: #374151; }

        .report-content {
            margin-top: 60px;
        }

        @media print {
            .print-toolbar { display: none !important; }
            .report-content { margin-top: 0; }
            body { font-size: 10px; }
        }

        .report-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .report-header .company { flex: 1; }
        .report-header h1 {
            font-size: 18px;
            color: #1f2937;
            margin: 0 0 4px 0;
        }
        .report-header .ruc {
            font-size: 11px;
            color: #6b7280;
        }
        .report-header .meta {
            text-align: right;
            font-size: 10px;
            color: #4b5563;
            line-height: 1.6;
        }
        .report-header .meta strong { color: #1f2937; }

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 14px 0 18px 0;
            color: #1f2937;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.report thead th {
            background: #f3f4f6;
            color: #1f2937;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px 6px;
            border-bottom: 1.5px solid #1f2937;
        }

        table.report tbody td {
            padding: 7px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        table.report tbody tr:nth-child(even) { background: #fafafa; }
        table.report tbody tr:hover { background: #f3f4f6; }

        table.report tfoot td {
            padding: 8px 6px;
            font-weight: 700;
            border-top: 1.5px solid #1f2937;
            background: #f3f4f6;
        }

        .ficha-section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .ficha-section h2 {
            font-size: 12px;
            color: #f97316;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f97316;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .ficha-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 18px;
        }

        .ficha-grid.single { grid-template-columns: 1fr; }

        .ficha-item .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.4px;
            display: block;
        }

        .ficha-item .value {
            font-size: 11px;
            color: #1f2937;
            font-weight: 500;
        }

        .ficha-foto {
            float: right;
            width: 110px;
            height: 130px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            margin-left: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .ficha-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .ficha-foto .placeholder {
            color: #9ca3af;
            font-size: 9px;
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge.green { background: #d1fae5; color: #065f46; }
        .badge.gray { background: #e5e7eb; color: #374151; }

        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }

        .empty-state {
            text-align: center;
            padding: 30px 10px;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()">Imprimir / Guardar PDF</button>
        <button class="close" onclick="window.close()">Cerrar</button>
    </div>
    <div class="report-content">
        {!! $content !!}
    </div>
</body>
</html>
