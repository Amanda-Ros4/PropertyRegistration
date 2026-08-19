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
        @page { margin: 34pt 30pt 48pt; }

        body {
            font-family: Helvetica, "DejaVu Sans", sans-serif;
            font-size: 11pt;
            line-height: 13.2pt;
            color: #262626;
            margin: 0;
        }

        .brand { text-align: center; }
        .brand-logo { height: 59pt; margin-bottom: 10pt; }
        .brand-name,
        .brand-state { line-height: 13.2pt; color: #000; }

        .report-title {
            margin: 25pt 0 27pt;
            line-height: 20.7pt;
            font-weight: bold;
            color: #404040;
            text-align: center;
        }

        table {
            width: 100%;
            table-layout: fixed;
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
            color: #404040;
            background: #fff;
        }

        tbody tr:nth-child(odd) td { background: #f2f2f2; }

        .num { text-align: right; }

        .section-title {
            margin: 27pt 0 8pt;
            font-weight: bold;
            color: #404040;
        }

        table.details th { width: 32%; }
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
