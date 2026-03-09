@extends('layouts.admin.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" />
<style>
    .jvm-tooltip {
        background-color: rgba(0, 0, 0, 0.8);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.875rem;
        max-width: 200px;
        word-wrap: break-word;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .map-tooltip { text-align: center; }
    .map-tooltip strong { color: #fff; display: block; margin-bottom: 4px; font-size: 1rem; }
    .map-tooltip .text-info { color: #7dd3fc !important; }
</style>
<style>
    .dashboard-chart-container {
        position: relative;
        height: 280px;
        width: 100%;
    }
    .dashboard-table .table th,
    .dashboard-table .table td {
        text-align: center;
        vertical-align: middle;
    }
    .dashboard-table .table th {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #475569;
    }
    .dashboard-table .table td {
        font-size: 0.875rem;
    }
    .dashboard-table .table td .text-muted {
        font-size: 0.8125rem;
    }
</style>
@endpush

@section('content')
    <!-- Summary stats -->
    @php
        $totalParticipants = ($genderDistribution['male'] ?? 0) + ($genderDistribution['female'] ?? 0);
        $totalOrdersWithCategory = array_sum($buyerCategoryDistribution ?? []);
    @endphp
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value">{{ number_format($totalParticipants) }}</div>
                <div class="stat-label">Total Participants (all events)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value">RM {{ number_format(($totalRevenueCents ?? 0) / 100, 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-value">{{ number_format($totalOrdersCount ?? 0) }}</div>
                <div class="stat-label">Total orders (all time)</div>
            </div>
        </div>
    </div>

    <!-- Charts row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title">Gender distribution (participants)</h3>
                </div>
                <div class="card-body">
                    @if($totalParticipants > 0)
                        <div class="dashboard-chart-container">
                            <canvas id="genderChart" aria-label="Gender distribution of event participants"></canvas>
                        </div>
                    @else
                        <p class="text-muted mb-0">No participant data yet. Gender is collected from ticket holders of paid orders.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title">Buyer category distribution (orders)</h3>
                </div>
                <div class="card-body">
                    @if($totalOrdersWithCategory > 0)
                        <div class="dashboard-chart-container">
                            <canvas id="buyerCategoryChart" aria-label="Buyer category distribution"></canvas>
                        </div>
                    @else
                        <p class="text-muted mb-0">No order data yet. Categories: Individual, Academician, Organization.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Client locations (world map) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Client Locations</h3>
                </div>
                <div class="card-body">
                    @php $totalByCountry = array_sum($buyerCountryDistribution ?? []); @endphp
                    @if($totalByCountry > 0)
                        <h5 class="mb-3">Global Distribution</h5>
                        <div id="worldMap" class="dashboard-chart-container" style="height: 400px;"></div>
                    @else
                        <p class="text-muted mb-0">No country data yet. Country is collected from the buyer at checkout.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent orders table -->
    <div class="row g-4">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header">
                    <h3 class="card-title">Recent orders</h3>
                    <a href="{{ route('admin.orders') }}" class="btn-admin btn-admin-primary">
                        <i class="bi bi-list-ul"></i>
                        View all orders
                    </a>
                </div>
                <div class="card-body dashboard-table">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reference</th>
                                    <th>Buyer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $index => $order)
                                    @php
                                        $buyer = $order->buyer_snapshot ?? [];
                                        $buyerName = $buyer['buyer_name'] ?? $order->user->name ?? '-';
                                        $buyerEmail = $buyer['buyer_email'] ?? $order->user->email ?? '-';
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="text-nowrap">{{ $order->stripe_payment_intent_id ?? $order->id }}</span></td>
                                        <td>
                                            <div>{{ $buyerName }}</div>
                                            <small class="text-muted">{{ $buyerEmail }}</small>
                                        </td>
                                        <td>RM {{ number_format($order->total_amount_cents / 100, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $order->status === 'paid' ? 'bg-success' : ($order->status === 'cancelled' ? 'bg-secondary' : ($order->status === 'refunded' ? 'bg-info text-dark' : 'bg-warning text-dark')) }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</td>
                                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No orders yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
    <script src="https://unpkg.com/jsvectormap@1.5.3/dist/maps/world.js"></script>
    <script>
(function() {
    var genderData = @json($genderDistribution);
    var categoryData = @json($buyerCategoryDistribution);
    var countryData = @json($buyerCountryDistribution ?? []);

    if (document.getElementById('genderChart') && (genderData.male + genderData.female) > 0) {
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [genderData.male || 0, genderData.female || 0],
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    if (document.getElementById('buyerCategoryChart') && Object.keys(categoryData).length > 0) {
        var labels = Object.keys(categoryData).map(function(k) {
            return k.charAt(0).toUpperCase() + k.slice(1);
        });
        var values = Object.values(categoryData);
        var colors = ['#10b981', '#8b5cf6', '#f59e0b', '#64748b'];
        var backgroundColors = labels.map(function(_, i) { return colors[i % colors.length]; });

        new Chart(document.getElementById('buyerCategoryChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: values,
                    backgroundColor: backgroundColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    var worldMapEl = document.getElementById('worldMap');
    if (worldMapEl && Object.keys(countryData).length > 0) {
        var countryMapping = {
            'Afghanistan': 'AF', 'Albania': 'AL', 'Algeria': 'DZ', 'Andorra': 'AD', 'Angola': 'AO',
            'Antigua and Barbuda': 'AG', 'Argentina': 'AR', 'Armenia': 'AM', 'Australia': 'AU', 'Austria': 'AT',
            'Azerbaijan': 'AZ', 'Bahamas': 'BS', 'Bahrain': 'BH', 'Bangladesh': 'BD', 'Barbados': 'BB',
            'Belarus': 'BY', 'Belgium': 'BE', 'Belize': 'BZ', 'Benin': 'BJ', 'Bhutan': 'BT', 'Bolivia': 'BO',
            'Bosnia and Herzegovina': 'BA', 'Botswana': 'BW', 'Brazil': 'BR', 'Brunei': 'BN', 'Bulgaria': 'BG',
            'Burkina Faso': 'BF', 'Burundi': 'BI', 'Cabo Verde': 'CV', 'Cambodia': 'KH', 'Cameroon': 'CM',
            'Canada': 'CA', 'Central African Republic': 'CF', 'Chad': 'TD', 'Chile': 'CL', 'China': 'CN',
            'Colombia': 'CO', 'Comoros': 'KM', 'Congo': 'CG', 'Costa Rica': 'CR', 'Croatia': 'HR',
            'Cuba': 'CU', 'Cyprus': 'CY', 'Czech Republic': 'CZ', 'Democratic Republic of the Congo': 'CD',
            'Denmark': 'DK', 'Djibouti': 'DJ', 'Dominica': 'DM', 'Dominican Republic': 'DO',
            'East Timor': 'TL', 'Ecuador': 'EC', 'Egypt': 'EG', 'El Salvador': 'SV', 'Equatorial Guinea': 'GQ',
            'Eritrea': 'ER', 'Estonia': 'EE', 'Eswatini': 'SZ', 'Ethiopia': 'ET', 'Fiji': 'FJ',
            'Finland': 'FI', 'France': 'FR', 'Gabon': 'GA', 'Gambia': 'GM', 'Georgia': 'GE', 'Germany': 'DE',
            'Ghana': 'GH', 'Greece': 'GR', 'Grenada': 'GD', 'Guatemala': 'GT', 'Guinea': 'GN',
            'Guinea-Bissau': 'GW', 'Guyana': 'GY', 'Haiti': 'HT', 'Honduras': 'HN', 'Hong Kong': 'HK',
            'Hungary': 'HU', 'Iceland': 'IS', 'India': 'IN', 'Indonesia': 'ID', 'Iran': 'IR', 'Iraq': 'IQ',
            'Ireland': 'IE', 'Israel': 'IL', 'Italy': 'IT', 'Ivory Coast': 'CI', 'Jamaica': 'JM', 'Japan': 'JP',
            'Jordan': 'JO', 'Kazakhstan': 'KZ', 'Kenya': 'KE', 'Kiribati': 'KI', 'Kosovo': 'XK', 'Kuwait': 'KW',
            'Kyrgyzstan': 'KG', 'Laos': 'LA', 'Latvia': 'LV', 'Lebanon': 'LB', 'Lesotho': 'LS', 'Liberia': 'LR',
            'Libya': 'LY', 'Liechtenstein': 'LI', 'Lithuania': 'LT', 'Luxembourg': 'LU', 'Macau': 'MO',
            'Madagascar': 'MG', 'Malawi': 'MW', 'Malaysia': 'MY', 'Maldives': 'MV', 'Mali': 'ML', 'Malta': 'MT',
            'Marshall Islands': 'MH', 'Mauritania': 'MR', 'Mauritius': 'MU', 'Mexico': 'MX', 'Micronesia': 'FM',
            'Moldova': 'MD', 'Monaco': 'MC', 'Mongolia': 'MN', 'Montenegro': 'ME', 'Morocco': 'MA',
            'Mozambique': 'MZ', 'Myanmar': 'MM', 'Namibia': 'NA', 'Nauru': 'NR', 'Nepal': 'NP',
            'Netherlands': 'NL', 'New Zealand': 'NZ', 'Nicaragua': 'NI', 'Niger': 'NE', 'Nigeria': 'NG',
            'North Korea': 'KP', 'North Macedonia': 'MK', 'Norway': 'NO', 'Oman': 'OM', 'Pakistan': 'PK',
            'Palau': 'PW', 'Palestine': 'PS', 'Panama': 'PA', 'Papua New Guinea': 'PG', 'Paraguay': 'PY',
            'Peru': 'PE', 'Philippines': 'PH', 'Poland': 'PL', 'Portugal': 'PT', 'Qatar': 'QA',
            'Romania': 'RO', 'Russia': 'RU', 'Rwanda': 'RW', 'Saint Kitts and Nevis': 'KN', 'Saint Lucia': 'LC',
            'Saint Vincent and the Grenadines': 'VC', 'Samoa': 'WS', 'San Marino': 'SM', 'Sao Tome and Principe': 'ST',
            'Saudi Arabia': 'SA', 'Senegal': 'SN', 'Serbia': 'RS', 'Seychelles': 'SC', 'Sierra Leone': 'SL',
            'Singapore': 'SG', 'Slovakia': 'SK', 'Slovenia': 'SI', 'Solomon Islands': 'SB', 'Somalia': 'SO',
            'South Africa': 'ZA', 'South Korea': 'KR', 'South Sudan': 'SS', 'Spain': 'ES', 'Sri Lanka': 'LK',
            'Sudan': 'SD', 'Suriname': 'SR', 'Sweden': 'SE', 'Switzerland': 'CH', 'Syria': 'SY', 'Taiwan': 'TW',
            'Tajikistan': 'TJ', 'Tanzania': 'TZ', 'Thailand': 'TH', 'Togo': 'TG', 'Tonga': 'TO',
            'Trinidad and Tobago': 'TT', 'Tunisia': 'TN', 'Turkey': 'TR', 'Turkmenistan': 'TM', 'Tuvalu': 'TV',
            'Uganda': 'UG', 'Ukraine': 'UA', 'United Arab Emirates': 'AE', 'United Kingdom': 'GB',
            'United States': 'US', 'Uruguay': 'UY', 'Uzbekistan': 'UZ', 'Vanuatu': 'VU', 'Vatican City': 'VA',
            'Venezuela': 'VE', 'Vietnam': 'VN', 'Yemen': 'YE', 'Zambia': 'ZM', 'Zimbabwe': 'ZW', 'Other': 'XX', 'Unknown': 'XX'
        };
        var mapData = {};
        Object.keys(countryData).forEach(function(name) {
            var code = countryMapping[name] || (function() {
                var lower = (name || '').toLowerCase();
                var key = Object.keys(countryMapping).find(function(k) { return k.toLowerCase() === lower; });
                return key ? countryMapping[key] : null;
            })();
            if (code && code !== 'XX') {
                mapData[code] = (mapData[code] || 0) + countryData[name];
            }
        });
        if (typeof jsVectorMap !== 'undefined') {
            function setRegionTooltip(event, tooltipOrLabel, code) {
                var total = mapData[code] || 0;
                var name = (function() {
                    var n = Object.keys(countryMapping).find(function(k) { return countryMapping[k] === code; });
                    return n || code;
                })();
                var html = '<div class="map-tooltip"><strong>' + name + '</strong><br><span class="text-info">Total Participants: ' + total.toLocaleString() + '</span></div>';
                if (tooltipOrLabel && typeof tooltipOrLabel.text === 'function') {
                    tooltipOrLabel.text(html, true);
                } else if (tooltipOrLabel && typeof tooltipOrLabel.html === 'function') {
                    tooltipOrLabel.html(html);
                } else if (tooltipOrLabel) {
                    var el = tooltipOrLabel[0] || tooltipOrLabel;
                    if (el && el.innerHTML !== undefined) el.innerHTML = html;
                }
            }
            var map = new jsVectorMap({
                selector: '#worldMap',
                map: 'world',
                zoomOnScroll: true,
                zoomButtons: true,
                markers: null,
                markerStyle: {
                    initial: { r: 6, fill: '#1e88e5', stroke: '#fff', strokeWidth: 2 }
                },
                series: {
                    regions: [{
                        values: mapData,
                        scale: ['#c8e6ff', '#0d47a1'],
                        normalizeFunction: 'polynomial',
                        legend: false
                    }]
                },
                regionStyle: {
                    initial: { fill: '#e9ecef', stroke: '#fff', strokeWidth: 0.5 },
                    hover: { fill: '#2563eb', cursor: 'pointer' }
                },
                onRegionTooltipShow: setRegionTooltip,
                onRegionTipShow: function(e, label, code) {
                    setRegionTooltip(e, label, code);
                },
                backgroundColor: 'transparent'
            });
        } else {
            worldMapEl.innerHTML = '<p class="text-muted py-4 text-center">Map library loading…</p>';
        }
    }
})();
    </script>
@endpush
