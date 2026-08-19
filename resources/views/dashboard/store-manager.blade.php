@extends('layouts.app')

    @section('title', 'Store Manager Dashboard - Chatime')

    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/store-manager.css') }}" rel="stylesheet">
    @endpush

    @section('content')
    <div class="dashboard-content">
        @include('components.navbar')
        <div class="container">
            <header class="header-section">
                <h1>Store Manager Dashboard</h1>
                <div class="filters">
                    <div class="date-range-picker">
                        <i class="fas fa-calendar-alt"></i>
                        <div class="date-inputs">
                            <div class="date-input-group">
                                <label>Dari:</label>
                                <input type="date" id="startDate" value="{{ $dateRange['start'] }}"
                                    max="{{ now()->format('Y-m-d') }}" onchange="updateDateRange()">
                            </div>
                            <div class="date-input-group">
                                <label>Sampai:</label>
                                <input type="date" id="endDate" value="{{ $dateRange['end'] }}"
                                    max="{{ now()->format('Y-m-d') }}" onchange="updateDateRange()">
                            </div>
                        </div>
                        <span id="dateRangeDisplay">{{ \Carbon\Carbon::parse($dateRange['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($dateRange['end'])->format('d M Y') }}</span>
                    </div>
                </div>
            </header>

            <main class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Net Sales</span>
                        <span class="material-icons-outlined">trending_up</span>
                    </div>
                    <div class="value-large">Rp {{ number_format($netSales, 0, ',', '.') }}</div>
                    <div class="sub-label">This Week</div>
                    <hr>
                    <div class="stat-line"><span>vs Last Week:</span> <span>{{ number_format($vsLastWeekSales, 1) }}%</span></div>
                    <div class="stat-line"><span>vs Last Month:</span> <span>{{ number_format($vsLastMonthSales, 1) }}%</span></div>
                    <div class="footer-stats">
                        <div class="stat-line"><span>Last Week:</span> <b>Rp {{ number_format($lastWeekData['total_revenue'], 0, ',', '.') }}</b></div>
                        <div class="stat-line"><span>Last Month:</span> <b>Rp {{ number_format($lastMonthData['total_revenue'], 0, ',', '.') }}</b></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Transaksi</span>
                        <span class="material-icons-outlined">trending_up</span>
                    </div>
                    <div class="value-large">{{ number_format($totalTransactions, 0, ',', '.') }}</div>
                    <div class="sub-label">This Week</div>
                    <div class="spacer"></div> <hr>
                    <div class="stat-line"><span>vs Last Week:</span> <span>{{ number_format($vsLastWeekTransactions, 1) }}%</span></div>
                    <div class="stat-line"><span>vs Last Month:</span> <span>{{ number_format($vsLastMonthTransactions, 1) }}%</span></div>
                    <div class="footer-stats">
                        <div class="stat-line"><span>Last Week:</span> <b>{{ number_format($lastWeekData['total_transactions'], 0, ',', '.') }}</b></div>
                        <div class="stat-line"><span>Last Month:</span> <b>{{ number_format($lastMonthData['total_transactions'], 0, ',', '.') }}</b></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">AC (Average Check)</span>
                        <span class="material-icons-outlined">trending_up</span>
                    </div>
                    <div class="value-large">Rp {{ number_format($averageCheck, 2, ',', '.') }}</div>
                    <div class="sub-label">Average per transaksi</div>
                    <div class="spacer"></div><hr>
                    <div class="stat-line"><span>vs Last Week:</span> <span>{{ number_format($vsLastWeekAC, 1) }}%</span></div>
                    <div class="stat-line"><span>vs Last Month:</span> <span>{{ number_format($vsLastMonthAC, 1) }}%</span></div>
                    <div class="footer-stats">
                        <div class="stat-line"><span>Last Week:</span> <b>Rp {{ number_format($lastWeekData['total_transactions'] > 0 ? $lastWeekData['total_revenue'] / $lastWeekData['total_transactions'] : 0, 2, ',', '.') }}</b></div>
                        <div class="stat-line"><span>Last Month:</span> <b>Rp {{ number_format($lastMonthData['total_transactions'] > 0 ? $lastMonthData['total_revenue'] / $lastMonthData['total_transactions'] : 0, 2, ',', '.') }}</b></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Transaksi Ojol</span>
                        <span class="material-icons-outlined">delivery_dining</span>
                    </div>
                    <div class="value-large">{{ number_format($thisWeekPayments['ojol_transactions'], 0, ',', '.') }}</div>
                    <div class="sub-label">This Week</div>
                    <div class="target-row">
                        <span class="target-label">% vs Total:</span>
                        <span class="percentage-orange">{{ number_format($thisWeekPayments['ojol_transaction_percentage'], 1) }}%</span>
                    </div>
                    <hr>
                    <div class="stat-line"><span>vs Last Week:</span> <span>{{ number_format($lastWeekPayments['ojol_transaction_percentage'], 1) }}%</span></div>
                    <div class="stat-line"><span>vs Last Month:</span> <span>{{ number_format($lastMonthPayments['ojol_transaction_percentage'], 1) }}%</span></div>
                    <div class="footer-stats">
                        <div class="stat-line"><span>Last Week:</span> <b>{{ number_format($lastWeekPayments['ojol_transactions'], 0, ',', '.') }} trans</b></div>
                        <div class="stat-line"><span>Last Month:</span> <b>{{ number_format($lastMonthPayments['ojol_transactions'], 0, ',', '.') }} trans</b></div>
                    </div>
                </div>
            </main>

            <div class="chart-card" style="margin-top: 40px;">
                <div class="chart-header">
                    <h2>Perbandingan Net Sales</h2>
                    <p>This Week vs Last Week vs Last Month (Same Days)</p>
                </div>
                <div class="chart-wrapper">
                    <div class="chart-body">
                        @php
                            $netSalesThisWeek = $netSales;
                            $netSalesLastWeek = $lastWeekData['total_revenue'] ?? 0;
                            $netSalesLastMonth = $lastMonthData['total_revenue'] ?? 0;
                            $maxNetSales = max($netSalesThisWeek, $netSalesLastWeek, $netSalesLastMonth);
                        @endphp
                        <div class="y-axis">
                            <span>{{ number_format($maxNetSales / 1000000, 1) }}jt</span>
                            <span>{{ number_format($maxNetSales * 0.75 / 1000000, 1) }}jt</span>
                            <span>{{ number_format($maxNetSales * 0.5 / 1000000, 1) }}jt</span>
                            <span>{{ number_format($maxNetSales * 0.25 / 1000000, 1) }}jt</span>
                            <span>0.0jt</span>
                        </div>
                        <div class="plot-area">
                            <div class="grid-lines">
                                <div class="grid-line"></div><div class="grid-line"></div>
                                <div class="grid-line"></div><div class="grid-line"></div>
                                <div class="grid-line" style="border-color: transparent;"></div>
                            </div>
                            <div class="bar-item">
                                @php $thisWeekHeight = $maxNetSales > 0 ? ($netSalesThisWeek / $maxNetSales) * 100 : 0; @endphp
                                <div class="bar" style="height: {{ $thisWeekHeight }}%;" title="This Week: Rp {{ number_format($netSalesThisWeek, 0, ',', '.') }}"></div>
                            </div>
                            <div class="bar-item">
                                @php $lastWeekHeight = $maxNetSales > 0 ? ($netSalesLastWeek / $maxNetSales) * 100 : 0; @endphp
                                <div class="bar" style="height: {{ $lastWeekHeight }}%; background-color: #3b82f6;" title="Last Week: Rp {{ number_format($netSalesLastWeek, 0, ',', '.') }}"></div>
                            </div>
                            <div class="bar-item">
                                @php $lastMonthHeight = $maxNetSales > 0 && $netSalesLastMonth > 0 ? ($netSalesLastMonth / $maxNetSales) * 100 : 0; @endphp
                                <div class="bar" style="height: {{ $lastMonthHeight }}%; background-color: #f59e0b;" title="Last Month: Rp {{ number_format($netSalesLastMonth, 0, ',', '.') }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="legend">
                    <div class="legend-item"><div class="legend-box" style="background-color: #10b981;"></div><span class="legend-text">This Week</span></div>
                    <div class="legend-item"><div class="legend-box" style="background-color: #3b82f6;"></div><span class="legend-text">Last Week</span></div>
                    <div class="legend-item"><div class="legend-box" style="background-color: #f59e0b;"></div><span class="legend-text">Last Month</span></div>
                </div>
            </div>

            <div class="dashboard-card mtd-card" style="margin-top: 40px;">
                <div class="mtd-header">
                    <div>
                        <h2>Time Progress vs Achievement (MTD)</h2>
                        <p class="subtitle">{{ $mtdProgress['month_name'] }} — mengikuti tanggal akhir ({{ \Carbon\Carbon::parse($dateRange['end'])->format('d M Y') }}) yang dipilih di filter</p>
                    </div>
                    <span class="mtd-status-badge {{ $mtdProgress['on_track'] ? 'status-on-track' : 'status-behind' }}">
                        {{ $mtdProgress['on_track'] ? 'ON TRACK' : 'BEHIND' }}
                    </span>
                </div>

                <div class="mtd-numbers">
                    <div class="mtd-number-box">
                        <span class="mtd-number-label">MTD Sales</span>
                        <span class="mtd-number-value">Rp {{ number_format($mtdProgress['mtd_sales'], 0, ',', '.') }}</span>
                    </div>
                    <div class="mtd-number-box">
                        <span class="mtd-number-label">Target Bulan Ini</span>
                        <span class="mtd-number-value">Rp {{ number_format($mtdProgress['target'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mtd-progress-group">
                    <div class="mtd-progress-row">
                        <div class="mtd-progress-label">
                            <span>Waktu Berjalan</span>
                            <span class="mtd-progress-percent text-blue">{{ number_format($mtdProgress['time_progress'], 1) }}%</span>
                        </div>
                        <div class="mtd-progress-track">
                            <div class="mtd-progress-fill fill-blue" style="width: {{ min($mtdProgress['time_progress'], 100) }}%;"></div>
                        </div>
                        <span class="mtd-progress-caption">Hari ke-{{ $mtdProgress['current_day'] }} dari {{ $mtdProgress['days_in_month'] }} hari bulan ini</span>
                    </div>

                    <div class="mtd-progress-row">
                        <div class="mtd-progress-label">
                            <span>Pencapaian Target</span>
                            <span class="mtd-progress-percent {{ $mtdProgress['on_track'] ? 'text-green' : 'text-red' }}">{{ number_format($mtdProgress['achievement'], 1) }}%</span>
                        </div>
                        <div class="mtd-progress-track">
                            <div class="mtd-progress-fill {{ $mtdProgress['on_track'] ? 'fill-green' : 'fill-red' }}" style="width: {{ min($mtdProgress['achievement'], 100) }}%;"></div>
                        </div>
                        <span class="mtd-progress-caption">
                            @if($mtdProgress['on_track'])
                                Melampaui waktu berjalan sebesar {{ number_format($mtdProgress['achievement'] - $mtdProgress['time_progress'], 1) }}%
                            @else
                                Tertinggal {{ number_format($mtdProgress['time_progress'] - $mtdProgress['achievement'], 1) }}% dari waktu berjalan
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="dashboard-card" style="margin-top: 40px;">
                <header class="header">
                    <h2>Perbandingan Transaksi Instore & Ojol</h2>
                    <p>Distribusi pembayaran per periode</p>
                </header>
                <section class="chart-section">
                    <div class="chart-body">
                        <div class="y-axis">
                            @php $maxPayment = max($thisWeekPayments['total'], $lastWeekPayments['total'], $lastMonthPayments['total']); @endphp
                            <span>{{ number_format($maxPayment / 1000000, 1) }}jt</span>
                            <span>{{ number_format($maxPayment * 0.75 / 1000000, 1) }}jt</span>
                            <span>{{ number_format($maxPayment * 0.5 / 1000000, 1) }}jt</span>
                            <span>{{ number_format($maxPayment * 0.25 / 1000000, 1) }}jt</span>
                            <span>0.0jt</span>
                        </div>
                        <div class="plot-area">
                            <div class="grid-lines"><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div></div>
                            <div class="bar-group">
                                <div class="bar-stack" style="height: {{ $maxPayment > 0 ? ($thisWeekPayments['total'] / $maxPayment) * 100 : 0 }}%;">
                                    @php
                                        $thisWeekOjolHeight = $thisWeekPayments['total'] > 0 ? ($thisWeekPayments['ojol'] / $thisWeekPayments['total']) * 100 : 0;
                                        $thisWeekInstoreHeight = 100 - $thisWeekOjolHeight;
                                    @endphp
                                    <div class="bar ojol" style="height: {{ $thisWeekOjolHeight }}%;" title="This Week Ojol: Rp {{ number_format($thisWeekPayments['ojol'], 0, ',', '.') }}"></div>
                                    <div class="bar instore" style="height: {{ $thisWeekInstoreHeight }}%;" title="This Week Instore: Rp {{ number_format($thisWeekPayments['instore'], 0, ',', '.') }}"></div>
                                </div>
                            </div>
                            <div class="bar-group">
                                <div class="bar-stack" style="height: {{ $maxPayment > 0 ? ($lastWeekPayments['total'] / $maxPayment) * 100 : 0 }}%;">
                                    @php
                                        $lastWeekOjolHeight = $lastWeekPayments['total'] > 0 ? ($lastWeekPayments['ojol'] / $lastWeekPayments['total']) * 100 : 0;
                                        $lastWeekInstoreHeight = 100 - $lastWeekOjolHeight;
                                    @endphp
                                    <div class="bar ojol" style="height: {{ $lastWeekOjolHeight }}%;" title="Last Week Ojol: Rp {{ number_format($lastWeekPayments['ojol'], 0, ',', '.') }}"></div>
                                    <div class="bar instore" style="height: {{ $lastWeekInstoreHeight }}%;" title="Last Week Instore: Rp {{ number_format($lastWeekPayments['instore'], 0, ',', '.') }}"></div>
                                </div>
                            </div>
                            <div class="bar-group">
                                <div class="bar-stack" style="height: {{ $maxPayment > 0 ? ($lastMonthPayments['total'] / $maxPayment) * 100 : 0 }}%;">
                                    @php
                                        $lastMonthOjolHeight = $lastMonthPayments['total'] > 0 ? ($lastMonthPayments['ojol'] / $lastMonthPayments['total']) * 100 : 0;
                                        $lastMonthInstoreHeight = 100 - $lastMonthOjolHeight;
                                    @endphp
                                    <div class="bar ojol" style="height: {{ $lastMonthOjolHeight }}%;" title="Last Month Ojol: Rp {{ number_format($lastMonthPayments['ojol'], 0, ',', '.') }}"></div>
                                    <div class="bar instore" style="height: {{ $lastMonthInstoreHeight }}%;" title="Last Month Instore: Rp {{ number_format($lastMonthPayments['instore'], 0, ',', '.') }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="x-axis"><span>This Week</span><span>Last Week</span><span>Last Month</span></div>
                    <div class="legend">
                        <div class="legend-item"><div class="dot bg-blue"></div> Instore</div>
                        <div class="legend-item"><div class="dot bg-orange"></div> Ojol</div>
                    </div>
                </section>
                <footer class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title"><span class="material-icons-outlined text-blue">storefront</span> This Week</div>
                        <div class="data-row"><span class="label">Instore:</span><span class="value">Rp {{ number_format($thisWeekPayments['instore'], 0, ',', '.') }}</span></div>
                        <div class="data-row"><span class="label">Ojol:</span><span class="value">Rp {{ number_format($thisWeekPayments['ojol'], 0, ',', '.') }}</span></div>
                        <div class="divider"></div>
                        <div class="data-row"><span class="label">Ratio:</span><span class="value">{{ number_format($thisWeekPayments['instore_percentage'], 0) }}% / {{ number_format($thisWeekPayments['ojol_percentage'], 0) }}%</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title"><span class="material-icons-outlined text-orange">smartphone</span> Last Week</div>
                        <div class="data-row"><span class="label">Instore:</span><span class="value">Rp {{ number_format($lastWeekPayments['instore'], 0, ',', '.') }}</span></div>
                        <div class="data-row"><span class="label">Ojol:</span><span class="value">Rp {{ number_format($lastWeekPayments['ojol'], 0, ',', '.') }}</span></div>
                        <div class="divider"></div>
                        <div class="data-row"><span class="label">Ratio:</span><span class="value">{{ number_format($lastWeekPayments['instore_percentage'], 0) }}% / {{ number_format($lastWeekPayments['ojol_percentage'], 0) }}%</span></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title"><span class="material-icons-outlined text-green">trending_up</span> Last Month</div>
                        <div class="data-row"><span class="label">Instore:</span><span class="value">Rp {{ number_format($lastMonthPayments['instore'], 0, ',', '.') }}</span></div>
                        <div class="data-row"><span class="label">Ojol:</span><span class="value">Rp {{ number_format($lastMonthPayments['ojol'], 0, ',', '.') }}</span></div>
                        <div class="divider"></div>
                        <div class="data-row"><span class="label">Ratio:</span><span class="value">{{ number_format($lastMonthPayments['instore_percentage'], 0) }}% / {{ number_format($lastMonthPayments['ojol_percentage'], 0) }}%</span></div>
                    </div>
                </footer>
            </div>

            <div class="dashboard-card" style="margin-top: 40px;">
                <div class="card-header">
                    <h2>Perbandingan Penjualan Snack</h2>
                    <p class="subtitle">Quantity, Sales, dan Achievement vs Target (30% dari Net Sales)</p>
                </div>
                <div class="chart-wrapper">
                    <div class="y-axis left">
                        @php
                            $maxSnackQuantity = max($thisWeekSnacks['quantity'], $lastWeekSnacks['quantity'], $lastMonthSnacks['quantity']);
                            $maxSnackSales = max(
                                $thisWeekSnacks['sales'], $lastWeekSnacks['sales'], $lastMonthSnacks['sales'],
                                $thisWeekSnacks['target_sales'], $lastWeekSnacks['target_sales'], $lastMonthSnacks['target_sales']
                            );
                        @endphp
                        <span>{{ max($maxSnackQuantity, 400) }}</span>
                        <span>{{ max($maxSnackQuantity, 400) * 0.75 }}</span>
                        <span>{{ max($maxSnackQuantity, 400) * 0.5 }}</span>
                        <span>{{ max($maxSnackQuantity, 400) * 0.25 }}</span>
                        <span>0</span>
                        <div class="axis-label-vert">Quantity (pcs)</div>
                    </div>
                    <div class="graph-area">
                        <div class="grid-lines"><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div><div class="line bottom"></div></div>
                        <div class="bars-container">
                            <div class="bar-group">
                                <div class="bars-wrapper">
                                    @php
                                        $thisWeekQuantityHeight = max($maxSnackQuantity, 400) > 0 ? ($thisWeekSnacks['quantity'] / max($maxSnackQuantity, 400)) * 100 : 0;
                                        $thisWeekSalesHeight = $maxSnackSales > 0 ? ($thisWeekSnacks['sales'] / $maxSnackSales) * 100 : 0;
                                    @endphp
                                    <div class="bar orange" style="height: {{ $thisWeekQuantityHeight }}%;" title="This Week Quantity: {{ number_format($thisWeekSnacks['quantity'], 0) }} pcs"></div>
                                    <div class="bar green" style="height: {{ $thisWeekSalesHeight }}%;" title="This Week Sales: Rp {{ number_format($thisWeekSnacks['sales'], 0, ',', '.') }}"></div>
                                </div>
                                <span class="x-label">This Week</span>
                            </div>
                            <div class="bar-group">
                                <div class="bars-wrapper">
                                    @php
                                        $lastWeekQuantityHeight = max($maxSnackQuantity, 400) > 0 ? ($lastWeekSnacks['quantity'] / max($maxSnackQuantity, 400)) * 100 : 0;
                                        $lastWeekSalesHeight = $maxSnackSales > 0 ? ($lastWeekSnacks['sales'] / $maxSnackSales) * 100 : 0;
                                    @endphp
                                    <div class="bar orange" style="height: {{ $lastWeekQuantityHeight }}%;" title="Last Week Quantity: {{ number_format($lastWeekSnacks['quantity'], 0) }} pcs"></div>
                                    <div class="bar green" style="height: {{ $lastWeekSalesHeight }}%;" title="Last Week Sales: Rp {{ number_format($lastWeekSnacks['sales'], 0, ',', '.') }}"></div>
                                </div>
                                <span class="x-label">Last Week</span>
                            </div>
                            <div class="bar-group">
                                <div class="bars-wrapper">
                                    @php
                                        $lastMonthQuantityHeight = max($maxSnackQuantity, 400) > 0 ? ($lastMonthSnacks['quantity'] / max($maxSnackQuantity, 400)) * 100 : 0;
                                        $lastMonthSalesHeight = $maxSnackSales > 0 ? ($lastMonthSnacks['sales'] / $maxSnackSales) * 100 : 0;
                                    @endphp
                                    <div class="bar orange" style="height: {{ $lastMonthQuantityHeight }}%;" title="Last Month Quantity: {{ number_format($lastMonthSnacks['quantity'], 0) }} pcs"></div>
                                    <div class="bar green" style="height: {{ $lastMonthSalesHeight }}%;" title="Last Month Sales: Rp {{ number_format($lastMonthSnacks['sales'], 0, ',', '.') }}"></div>
                                </div>
                                <span class="x-label">Last Month</span>
                            </div>
                        </div>
                    </div>
                    <div class="y-axis right">
                        <span>{{ number_format($maxSnackSales / 1000000, 1) }}jt</span>
                        <span>{{ number_format($maxSnackSales * 0.75 / 1000000, 1) }}jt</span>
                        <span>{{ number_format($maxSnackSales * 0.5 / 1000000, 1) }}jt</span>
                        <span>{{ number_format($maxSnackSales * 0.25 / 1000000, 1) }}jt</span>
                        <span>0</span>
                        <div class="axis-label-vert right-label">Sales (Ribu)</div>
                    </div>
                </div>
                <div class="legend">
                    <div class="legend-item"><span class="dot orange"></span> Quantity (pcs)</div>
                    <div class="legend-item"><span class="dot green"></span> Sales (000)</div>
                </div>
                <div class="data-cards">
                    <div class="summary-card">
                        <div class="card-title"><span class="material-icons-outlined">bakery_dining</span> This Week</div>
                        <div class="card-row"><span class="label">Quantity:</span><span class="value">{{ number_format($thisWeekSnacks['quantity'], 0) }} pcs</span></div>
                        <div class="card-row"><span class="label">Sales:</span><span class="value">Rp {{ number_format($thisWeekSnacks['sales'], 0, ',', '.') }}</span></div>
                        <div class="card-row"><span class="label">Target (30%) dari All Sales:</span><span class="value">Rp {{ number_format($thisWeekSnacks['target_sales'], 0, ',', '.') }}</span></div>
                        <div class="card-row"><span class="label">Kontribusi ke Net Sales:</span><span class="value">{{ number_format($thisWeekSnacks['contribution_percentage'], 1) }}%</span></div>
                        <div class="card-divider"></div>
                        <div class="card-row acv"><span class="label">% ACV:</span><span class="value {{ $thisWeekSnacks['sales_acv'] >= 85 ? 'highlight' : 'alert' }}">{{ number_format($thisWeekSnacks['sales_acv'], 1) }}%</span></div>
                    </div>


                    <div class="summary-card">
                        <div class="card-title"><span class="material-icons-outlined">bakery_dining</span> Last Week</div>
                        <div class="card-row"><span class="label">Quantity:</span><span class="value">{{ number_format($lastWeekSnacks['quantity'], 0) }} pcs</span></div>
                        <div class="card-row"><span class="label">Sales:</span><span class="value">Rp {{ number_format($lastWeekSnacks['sales'], 0, ',', '.') }}</span></div>
                        <div class="card-row"><span class="label">Target (30%) dari All Sales:</span><span class="value">Rp {{ number_format($lastWeekSnacks['target_sales'], 0, ',', '.') }}</span></div>
                        <div class="card-row"><span class="label">Kontribusi ke Net Sales:</span><span class="value">{{ number_format($lastWeekSnacks['contribution_percentage'], 1) }}%</span></div>
                        <div class="card-divider"></div>
                        <div class="card-row acv"><span class="label">% ACV:</span><span class="value {{ $lastWeekSnacks['sales_acv'] >= 85 ? 'highlight' : 'alert' }}">{{ number_format($lastWeekSnacks['sales_acv'], 1) }}%</span></div>
                    </div>

                    <div class="summary-card">
                        <div class="card-title"><span class="material-icons-outlined">bakery_dining</span> Last Month</div>
                        <div class="card-row"><span class="label">Quantity:</span><span class="value">{{ number_format($lastMonthSnacks['quantity'], 0) }} pcs</span></div>
                        <div class="card-row"><span class="label">Sales:</span><span class="value">Rp {{ number_format($lastMonthSnacks['sales'], 0, ',', '.') }}</span></div>
                        <div class="card-row"><span class="label">Target (30%) dari All Sales:</span><span class="value">Rp {{ number_format($lastMonthSnacks['target_sales'], 0, ',', '.') }}</span></div>
                        <div class="card-row"><span class="label">Kontribusi ke Net Sales:</span><span class="value">{{ number_format($lastMonthSnacks['contribution_percentage'], 1) }}%</span></div>
                        <div class="card-divider"></div>
                        <div class="card-row acv"><span class="value {{ $lastMonthSnacks['sales_acv'] >= 85 ? 'highlight' : 'alert' }}">{{ number_format($lastMonthSnacks['sales_acv'], 1) }}%</span></div>
                    </div>
                </div>

                <div class="snack-top-search" style="margin-top: 30px;">
                    <h3 class="snack-section-title">Top 5 Produk Terlaris (This Week)</h3>
                    <table class="top-products-table">
                        <thead><tr><th>#</th><th>Produk</th><th>Qty</th><th>Sales</th></tr></thead>
                        <tbody>
                            @forelse ($thisWeekSnacks['top_products'] as $i => $product)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $product->article_name }}</td>
                                    <td>{{ number_format($product->qty, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($product->sales, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="no-result">Belum ada data penjualan snack di periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="snack-search-box">
                        <label for="snackSearchInput">Cari Penjualan Snack:</label>
                        <input type="text" id="snackSearchInput" placeholder="Ketik nama produk... (misal: jinja, k-pop, sokkochi)" oninput="searchSnack(this.value)">
                        <div id="snackSearchResults"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script src="{{ asset('js/store-manager.js') }}"></script>
    @endpush