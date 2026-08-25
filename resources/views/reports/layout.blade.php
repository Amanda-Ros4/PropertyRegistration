@php
    $logoPath = public_path('images/report-logo.png');
    $hasLogo = is_file($logoPath);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28pt 22pt 42pt; }

        body {
            font-family: Helvetica, "DejaVu Sans", sans-serif;
            font-size: 11pt;
            line-height: 13.2pt;
            color: #262626;
            margin: 0;
        }

        .brand { text-align: center; }
        .brand-logo { height: 59pt; margin-bottom: 10pt; }
        .brand-name {
            line-height: 13.2pt;
            color: #000;
            font-weight: bold;
        }

        .brand-state {
            line-height: 13.2pt;
            color: #000;
        }

        .report-title {
            margin: 20pt 0 22pt;
            line-height: 20.7pt;
            font-weight: bold;
            color: #000;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 0.5pt solid #bfbfbf;
            padding: 11pt 5.4pt;
            text-align: left;
            vertical-align: top;
        }

        thead { display: table-header-group; }

        th {
            font-weight: bold;
            color: #000;
            background: #fff;
        }

        table.report-table {
            table-layout: fixed;
            font-size: 9pt;
            line-height: 11pt;
        }

        table.report-table th,
        table.report-table td {
            padding: 6pt 4pt;
            white-space: normal;
            overflow-wrap: break-word;
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }

        table.report-table thead th {
            background: #f2f2f2;
            font-size: 8.5pt;
            line-height: 10pt;
            vertical-align: middle;
            font-weight: bold;
            color: #000;
        }

        table.report-table tbody tr:nth-child(even) td {
            background: #f2f2f2;
        }

        table.report-table tbody tr:nth-child(odd) td {
            background: #fff;
        }

        .num { text-align: right; }

        .section-title {
            margin: 27pt 0 8pt;
            font-weight: bold;
            color: #000;
        }

        table.details th {
            width: 32%;
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="brand">
        @if ($hasLogo)
            <img class="brand-logo" src="{{ $logoPath }}" alt="">
        @endif
        <div class="brand-name">{{ __('reports.municipality') }}</div>
        <div class="brand-state">{{ __('reports.state') }}</div>
    </div>

    <div class="report-title">{{ Str::upper($title) }}</div>

    @yield('content')
</body>
</html>
