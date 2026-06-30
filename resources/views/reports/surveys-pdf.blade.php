<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Surveys Report') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            margin: 20px;
        }
        h1 {
            font-size: 16px;
            color: #166534;
            margin-bottom: 4px;
        }
        .subtitle {
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #166534;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
        }
        td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-green {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-yellow {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #999;
        }
        .header-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <h1>{{ $companyName }}</h1>
    <div class="subtitle">{{ __('Surveys Report') }}</div>

    <div class="header-info">
        <span><strong>{{ __('Period') }}:</strong> {{ $period }}</span>
        <span><strong>{{ __('From') }}:</strong> {{ $startDate }} <strong>{{ __('To') }}:</strong> {{ $endDate }}</span>
        <span><strong>{{ __('Generated') }}:</strong> {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Patient') }}</th>
                <th>{{ __('Document') }}</th>
                <th>{{ __('Insurer') }}</th>
                <th>{{ __('Template') }}</th>
                <th>{{ __('Rating') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($surveys as $index => $survey)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $survey->patient?->name ?? __('Anonymous') }}</td>
                    <td>{{ $survey->patient?->dni ?? '—' }}</td>
                    <td>{{ $survey->patient?->insurer?->name ?? '—' }}</td>
                    <td>{{ $survey->template?->title ?? __('Deleted Template') }}</td>
                    <td>
                        @if (!is_null($survey->rating))
                            <span class="badge
                                {{ $survey->rating >= 4.5 ? 'badge-green' : ($survey->rating >= 3.0 ? 'badge-yellow' : 'badge-red') }}">
                                {{ number_format($survey->rating, 2) }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $survey->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #999;">
                        {{ __('No surveys found for the selected period.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $companyName }} — {{ __('Generated on') }} {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
