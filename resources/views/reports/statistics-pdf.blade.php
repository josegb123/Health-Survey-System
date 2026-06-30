<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Statistics Report') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
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
        .header-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .stats-grid {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .stat-card .value {
            font-size: 22px;
            font-weight: bold;
            color: #166534;
        }
        .stat-card .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-top: 4px;
        }
        h2 {
            font-size: 12px;
            color: #374151;
            border-bottom: 2px solid #166534;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #166534;
            color: white;
            padding: 5px 8px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
        }
        td {
            padding: 4px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .bar-container {
            background-color: #e5e7eb;
            border-radius: 3px;
            height: 12px;
            width: 100%;
            overflow: hidden;
        }
        .bar-fill {
            background-color: #166534;
            height: 100%;
            border-radius: 3px;
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
    </style>
</head>
<body>
    <h1>{{ $companyName }}</h1>
    <div class="subtitle">{{ __('Statistics Report') }}</div>

    <div class="header-info">
        <span><strong>{{ __('Period') }}:</strong> {{ $period }}</span>
        <span><strong>{{ __('From') }}:</strong> {{ $startDate }} <strong>{{ __('To') }}:</strong> {{ $endDate }}</span>
        <span><strong>{{ __('Generated') }}:</strong> {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="value">{{ number_format($totalSurveys) }}</div>
            <div class="label">{{ __('Total Surveys') }}</div>
        </div>
        <div class="stat-card">
            <div class="value">{{ number_format($averageRating, 1) }}</div>
            <div class="label">{{ __('Average Rating') }}</div>
        </div>
    </div>

    @if (!empty($templateBreakdown))
        <h2>{{ __('By Template') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Template') }}</th>
                    <th>{{ __('Count') }}</th>
                    <th>{{ __('Percentage') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($templateBreakdown as $item)
                    @php $pct = $totalSurveys > 0 ? round(($item['total'] / $totalSurveys) * 100, 1) : 0; @endphp
                    <tr>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ $item['total'] }}</td>
                        <td>{{ $pct }}%</td>
                        <td>
                            <div class="bar-container">
                                <div class="bar-fill" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (!empty($insurerBreakdown))
        <h2>{{ __('By Insurer') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Insurer') }}</th>
                    <th>{{ __('Count') }}</th>
                    <th>{{ __('Percentage') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($insurerBreakdown as $item)
                    @php $pct = $totalSurveys > 0 ? round(($item['total'] / $totalSurveys) * 100, 1) : 0; @endphp
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['total'] }}</td>
                        <td>{{ $pct }}%</td>
                        <td>
                            <div class="bar-container">
                                <div class="bar-fill" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (!empty($dailyTrend))
        <h2>{{ __('Daily Trend') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Count') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dailyTrend as $date => $count)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                        <td>{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        {{ $companyName }} — {{ __('Generated on') }} {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
