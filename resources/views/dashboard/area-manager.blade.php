@extends('layouts.app')

@section('title', 'Area Manager Dashboard')

@section('content')

@include('components.navbar')

@push('styles')
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link href="{{ asset('css/area-manager.css') }}" rel="stylesheet">
@endpush

<div class="dashboard-container">
    {{-- =========================================
         HEADER SECTION
    ========================================= --}}
    <header class="dashboard-header">
        <div class="header-title">
            <h1>Area Manager Dashboard</h1>
            <p>
                Monitoring performa semua cabang 
                @if(isset($startDate) && isset($endDate))
                    ({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})
                @else
                    (All Time)
                @endif
            </p>
        </div>
    
        {{-- FORM FILTER --}}
        <form method="GET" action="" class="store-selector">
            
            <div class="filter-group">
                <label>Dari:</label>
                <input type="date" name="start_date"
                    value="{{ $startDate ?? request('start_date') }}"
                    onchange="this.form.submit()">
    
                <span class="divider">-</span>
    
                <label>Sampai:</label>
                <input type="date" name="end_date"
                    value="{{ $endDate ?? request('end_date') }}"
                    onchange="this.form.submit()">
            </div>
    
            <div class="filter-group">
                <label>Pilih Toko:</label>
                <select name="store" onchange="this.form.submit()">
                    <option value="all" {{ ($store == 'all') ? 'selected' : '' }}>
                        All Store
                    </option>
                    
                    @foreach($storeOptions as $s)
                        <option value="{{ $s->code }}"
                            {{ ($store == $s->code) ? 'selected' : '' }}>
                            {{ $s->code }} - {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
    
        </form>
    </header>

    {{-- =========================================
         SECTION VISIBILITY TOGGLE PANEL
    ========================================= --}}
    <section class="section-toggle-panel" id="sectionTogglePanel">
        <div class="toggle-panel-header" onclick="toggleSectionPanel()">
            <span><i class="fas fa-sliders-h"></i> Atur Tampilan Dashboard</span>
            <span id="togglePanelArrow">▾</span>
        </div>
        <div class="toggle-panel-body" id="togglePanelBody">
            <label class="toggle-chip">
                <input type="checkbox" data-section="section-performance" class="section-toggle-checkbox" checked>
                <span>Performance per Toko</span>
            </label>
            <label class="toggle-chip">
                <input type="checkbox" data-section="section-chart-sales" class="section-toggle-checkbox" checked>
                <span>Chart Sales per Toko</span>
            </label>
            <label class="toggle-chip">
                <input type="checkbox" data-section="section-time-progress" class="section-toggle-checkbox" checked>
                <span>Time Progress (MTD)</span>
            </label>
            <label class="toggle-chip">
                <input type="checkbox" data-section="section-top-menu" class="section-toggle-checkbox" checked>
                <span>Top Menu & Sold Cups</span>
            </label>
            <label class="toggle-chip">
                <input type="checkbox" data-section="section-snack" class="section-toggle-checkbox" checked>
                <span>Penjualan Snack</span>
            </label>
            <label class="toggle-chip">
                <input type="checkbox" data-section="section-promotion" class="section-toggle-checkbox" checked>
                <span>Top Promotion</span>
            </label>
        </div>
    </section>

    {{-- =========================================
         STATS GRID SECTION
    ========================================= --}}
    <section class="stats-grid">
        <div class="card stat-card">
            <div class="card-top">
                <span class="card-title">Total Net Sales</span>
                <i class="fas fa-chart-line card-icon"></i>
            </div>
            <div class="card-body">
                <div class="card-value">Rp {{ number_format($totals['total_sales'] ?? 0, 0, ',', '.') }}</div>
                <div class="card-subtext">{{ count($stores) }} cabang</div>
            </div>
        </div>

        <div class="card stat-card">
            <div class="card-top">
                <span class="card-title">Total Transaksi</span>
                <i class="fas fa-shopping-cart card-icon"></i>
            </div>
            <div class="card-body">
                <div class="card-value">{{ number_format($totals['total_transactions'] ?? 0, 0, ',', '.') }}</div>
                <div class="card-subtext">{{ count($stores) }} cabang</div>
            </div>
        </div>

        <div class="card stat-card">
            <div class="card-top">
                <span class="card-title">Total Sold Cups</span>
                <i class="fas fa-mug-hot card-icon"></i>
            </div>
            <div class="card-body">
                <div class="card-value">{{ number_format($totals['total_quantity'] ?? 0, 0, ',', '.') }}</div>
                <div class="card-subtext">{{ count($stores) }} cabang</div>
            </div>
        </div>

        <div class="card stat-card">
            <div class="card-top">
                <span class="card-title">Total Cabang</span>
                <i class="far fa-building card-icon"></i>
            </div>
            <div class="card-body">
                <div class="card-value">{{ count($stores) }}</div>
                <div class="card-subtext">Total Cabang Aktif</div>
            </div>
        </div>
    </section>

    {{-- =========================================
         PERFORMANCE TABLE SECTION
    ========================================= --}}
    <section class="performance-section" id="section-performance">
        <div class="card">
            <div class="card-header">
                <h2>Performance per Toko (All Time)</h2>
                <p>Ringkasan Sales, Transaksi, dan Sold Cups per cabang</p>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Toko</th>
                            <th>Kode</th>
                            <th>Net Sales</th>
                            <th>Transaksi</th>
                            <th>Sold Cups</th>
                            <th>AC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($stores) > 0)
                            {{-- FIX: rename loop var $store -> $storeRow agar tidak menimpa $store dari controller --}}
                            @foreach($stores as $storeRow)
                            <tr>
                                <td>{{ $storeRow['name'] ?? 'Unknown' }}</td>
                                <td>{{ $storeRow['code'] ?? 'N/A' }}</td>
                                <td>Rp {{ number_format($storeRow['total_sales'] ?? 0, 0, ',', '.') }}</td>
                                <td>{{ number_format($storeRow['total_transactions'] ?? 0, 0, ',', '.') }}</td>
                                <td>{{ number_format($storeRow['total_quantity'] ?? 0, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($storeRow['avg_check'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #888;">
                                    Tidak ada data tersedia
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2">TOTAL</td>
                            <td>Rp {{ number_format($totals['total_sales'], 0, ',', '.') }}</td>
                            <td>{{ number_format($totals['total_transactions'], 0, ',', '.') }}</td>
                            <td>{{ number_format($totals['total_quantity'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($totals['avg_check'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>

{{-- =========================================
        MAIN CHART SECTION (SALES PER STORE)
   ========================================= --}}
   <section class="chart-section" id="section-chart-sales">
       <div class="card">
           <div class="card-header-chart">
               <h2>Perbandingan Sales per Toko</h2>
               <p class="subtitle">All Time - Sales, Transaksi, dan Sold Cups</p>
           </div>
       
           @php
               $maxSales = 0;
               $maxTransactions = 0;
               $maxQuantity = 0;
               
               foreach($stores as $storeRow) {
                   $maxSales = max($maxSales, $storeRow['total_sales']);
                   $maxTransactions = max($maxTransactions, $storeRow['total_transactions']);
                   $maxQuantity = max($maxQuantity, $storeRow['total_quantity']);
               }
   
               // 🔥 FIX: kasih headroom 15% di atas nilai max supaya bar
               // tertinggi tidak "mentok" nyentuh garis atas chart —
               // membuat perbandingan antar toko lebih mudah dibaca.
               $chartMaxSales        = $maxSales * 1.15;
               $chartMaxTransactions = $maxTransactions * 1.15;
               $chartMaxQuantity     = $maxQuantity * 1.15;
               
               $topStores = array_slice($stores->toArray(), 0, 5);
           @endphp
       
           <div class="chart-container">
               <div class="y-axis">
                   <span>{{ number_format($chartMaxTransactions, 0, ',', '.') }}</span>
                   <span>{{ number_format($chartMaxTransactions * 0.75, 0, ',', '.') }}</span>
                   <span>{{ number_format($chartMaxTransactions * 0.5, 0, ',', '.') }}</span>
                   <span>{{ number_format($chartMaxTransactions * 0.25, 0, ',', '.') }}</span>
                   <span>0</span>
               </div>
       
               <div class="plot-area">
                   <div class="grid-lines">
                       <div class="grid-line"></div> 
                       <div class="grid-line"></div> 
                       <div class="grid-line"></div> 
                       <div class="grid-line"></div> 
                       <div class="grid-line" style="border-top: none;"></div> 
                   </div>
       
                   @if(count($topStores) > 1)
                       <div class="separator" style="left: {{ (100 / count($topStores)) }}%;"></div>
                   @endif
                   @if(count($topStores) > 2)
                       <div class="separator" style="left: {{ (200 / count($topStores)) }}%;"></div>
                   @endif
       
                   @foreach($topStores as $index => $storeRow)
                       <div class="bar-group">
   
                           {{-- TRANSAKSI --}}
                           <div class="bar-wrapper">
                               <span class="bar-value">{{ number_format($storeRow['total_transactions'], 0, ',', '.') }}</span>
                               <div class="bar blue" 
                                    style="height: {{ $chartMaxTransactions > 0 ? ($storeRow['total_transactions'] / $chartMaxTransactions) * 100 : 0 }}%;" 
                                    title="Transaksi: {{ number_format($storeRow['total_transactions'], 0, ',', '.') }}"></div>
                           </div>
   
                           {{-- SOLD CUPS --}}
                           <div class="bar-wrapper">
                               <span class="bar-value">{{ number_format($storeRow['total_quantity'], 0, ',', '.') }}</span>
                               <div class="bar green" 
                                    style="height: {{ $chartMaxQuantity > 0 ? ($storeRow['total_quantity'] / $chartMaxQuantity) * 100 : 0 }}%;" 
                                    title="Sold Cups: {{ number_format($storeRow['total_quantity'], 0, ',', '.') }}"></div>
                           </div>
   
                           {{-- SALES --}}
                           <div class="bar-wrapper">
                               <span class="bar-value">{{ number_format($storeRow['total_sales'] / 1000000, 1) }}jt</span>
                               <div class="bar orange" 
                                    style="height: {{ $chartMaxSales > 0 ? ($storeRow['total_sales'] / $chartMaxSales) * 100 : 0 }}%;" 
                                    title="Sales: Rp {{ number_format($storeRow['total_sales'], 0, ',', '.') }}"></div>
                           </div>
   
                       </div>
                   @endforeach
               </div>
       
               <div class="y-axis right">
                   <span>{{ number_format($chartMaxSales / 1000000, 1) }}jt</span>
                   <span>{{ number_format($chartMaxSales * 0.75 / 1000000, 1) }}jt</span>
                   <span>{{ number_format($chartMaxSales * 0.5 / 1000000, 1) }}jt</span>
                   <span>{{ number_format($chartMaxSales * 0.25 / 1000000, 1) }}jt</span>
                   <span>0.0jt</span>
               </div>
           </div>
       
           <div class="x-axis">
               <div class="x-labels-container">
                   
                   @foreach($topStores as $storeRow)
                       <span class="x-label">{{ Str::limit($storeRow['name'], 15) }}</span>
                   @endforeach
               </div>
           </div>
       
           <div class="legend">
               <div class="legend-item">
                   <div class="legend-box" style="background-color: var(--orange-bar);"></div>
                   <span>Sales (Rp)</span>
               </div>
               <div class="legend-item">
                   <div class="legend-box" style="background-color: var(--green-bar);"></div>
                   <span>Sold Cups</span>
               </div>
               <div class="legend-item">
                   <div class="legend-box" style="background-color: var(--blue-bar);"></div>
                   <span>Transaksi</span>
               </div>
           </div>
       </div>
   </section>

    {{-- =========================================
         [NEW] TIME PROGRESS (MTD vs TARGET)
    ========================================= --}}
    <section class="time-progress-section" id="section-time-progress" style="margin-bottom: 30px;">
        <div class="card tp-card">
            <div class="tp-header">
                <div>
                    <h2>Time Progress &mdash; {{ $timeProgress['month_label'] }}</h2>
                    <p class="subtitle">MTD Sales vs Target &mdash; {{ $timeProgress['scope_label'] }}</p>
                </div>
                <div class="tp-acv-badge {{ $timeProgress['acv_percentage'] >= $timeProgress['time_progress'] ? 'tp-on-track' : 'tp-behind' }}">
                    {{ number_format($timeProgress['acv_percentage'], 1) }}% ACV
                </div>
            </div>

            <div class="tp-progress-track">
                <div class="tp-progress-fill" style="width: {{ min($timeProgress['acv_percentage'], 100) }}%;"></div>
                <div class="tp-progress-marker" style="left: {{ min($timeProgress['time_progress'], 100) }}%;" title="Waktu berjalan: {{ $timeProgress['time_progress'] }}%"></div>
            </div>
            <div class="tp-progress-caption">
                <span>Hari ke-{{ $timeProgress['days_passed'] }} dari {{ $timeProgress['days_in_month'] }} hari ({{ $timeProgress['time_progress'] }}% waktu berjalan)</span>
            </div>

            <div class="tp-stats-grid">
                <div class="tp-stat-box">
                    <span class="tp-stat-label">MTD Sales</span>
                    <span class="tp-stat-value">Rp {{ number_format($timeProgress['mtd_sales'], 0, ',', '.') }}</span>
                </div>
                <div class="tp-stat-box">
                    <span class="tp-stat-label">Target Bulan Ini</span>
                    <span class="tp-stat-value">Rp {{ number_format($timeProgress['monthly_target'], 0, ',', '.') }}</span>
                </div>
                <div class="tp-stat-box">
                    <span class="tp-stat-label">Sisa Target</span>
                    <span class="tp-stat-value">Rp {{ number_format($timeProgress['remaining_target'], 0, ',', '.') }}</span>
                </div>
                <div class="tp-stat-box">
                    <span class="tp-stat-label">Rata-rata/Hari (Aktual)</span>
                    <span class="tp-stat-value">Rp {{ number_format($timeProgress['daily_avg_actual'], 0, ',', '.') }}</span>
                </div>
                <div class="tp-stat-box">
                    <span class="tp-stat-label">Rata-rata/Hari (Dibutuhkan)</span>
                    <span class="tp-stat-value {{ $timeProgress['daily_avg_needed'] > $timeProgress['daily_avg_actual'] ? 'tp-text-alert' : 'tp-text-good' }}">
                        Rp {{ number_format($timeProgress['daily_avg_needed'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="tp-stat-box">
                    <span class="tp-stat-label">Sisa Hari</span>
                    <span class="tp-stat-value">{{ $timeProgress['remaining_days'] }} hari</span>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================
         [NEW] TOP MENU & SOLD CUPS COMPARISON
    ========================================= --}}
    <section class="top-menu-section" id="section-top-menu" style="margin-bottom: 30px;">
        <div class="card tm-container-card">
            <div class="tm-card-header">
                <div class="tm-title-section">
                    <h2>Top Menu & Sold Cups</h2>
                    <p>
                        Bandingkan penjualan menu dengan sold cup (Menu vs Target)
                    </p>
                </div>

                <div class="tm-dropdown-filter">
                    <span style="font-size: 14px; font-weight: 500;">Pilih Menu:</span>
                    <div class="tm-select-box" onclick="toggleMenuDropdown()">
                        <span id="selectedMenuCount">0 menu dipilih</span> <span>▼</span>
                        
                        <div class="tm-dropdown-content" id="menuDropdown">
                            <div class="tm-dropdown-header">
                                <input type="text" id="menuSearch" placeholder="Cari menu..." onkeyup="filterMenuCheckboxes()">
                            </div>
                            <div class="tm-dropdown-scroll" id="menuCheckboxes">
                                @if(isset($topProducts))
                                    @foreach($topProducts as $index => $product)
                                        <div class="tm-menu-item">
                                            <input type="checkbox"
                                                id="menu_{{ $index }}"
                                                value="{{ $product->article_name }}"
                                                data-quantity="{{ $product->total_quantity }}"
                                                onchange="updateSelectedMenus()"
                                                class="menu-checkbox">
                                            <label for="menu_{{ $index }}">
                                                {{ Str::limit($product->article_name, 30) }} ({{ $product->total_quantity }})
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <div style="padding:10px; font-size:12px;">Data menu tidak tersedia</div>
                                @endif
                            </div>
                            <div class="tm-dropdown-footer">
                                <button type="button" onclick="selectAllMenus()">Pilih Semua</button>
                                <button type="button" onclick="deselectAllMenus()">Hapus Semua</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tm-content-grid">
                <div class="tm-chart-wrapper">
                    <div class="tm-chart-visual">
                        <div class="tm-y-axis-labels">
                            <span>0</span><span>100</span><span>200</span><span>300</span><span>400</span><span>500</span>
                        </div>
                        
                        <div class="tm-chart-area">
                            <div class="tm-grid-line"></div>
                            <div class="tm-grid-line"></div>
                            <div class="tm-grid-line"></div>
                            <div class="tm-grid-line"></div>
                            <div class="tm-grid-line"></div>
                            <div class="tm-grid-line"></div>
                            
                            <div id="comparisonChart" style="position: relative; height: 100%; width: 100%; z-index: 2;">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #999;">
                                    <p>Silakan pilih menu untuk melihat grafik</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tm-legend">
                        <span><b style="color:var(--primary-green)">■</b> Sold Cups (Real Data)</span>
                        <span><b style="color:var(--target-gray)">■</b> Target (100)</span>
                    </div>
                </div>

                <div class="tm-details-container">
                    <div class="tm-tags-area">
                        <p style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Menu yang Dipilih:</p>
                        <div id="selectedTags" class="tm-selected-tags">
                        </div>
                    </div>
                    
                    <div class="card-container">
                        <div class="summary-header">
                            <h2 class="title">Total Sold Cups:</h2>
                            <span class="total-badge" id="totalSoldCups">0 cups</span>
                        </div>

                        <div id="menuStatsContainer">
                            <!-- Menu statistics will appear here when user selects menus -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================
         SNACK SECTION 
    ========================================= --}}
    <section class="snack-section" id="section-snack">
        <div class="card widget-card">
            <div class="header">
                <h3>Penjualan Snack per Toko</h3>
                <span>Quantity dan Sales (Korean Street Food + Topping Berbayar)</span>
            </div>
    
            @php
                $maxSnackQty   = max($snackData->max('total_quantity') ?? 0, 1);
                $maxSnackSales = max($snackData->max('total_sales') ?? 0, 1);
                $chartSnackStores = $snackData->take(5);
    
                // 🔥 FIX: headroom 15% supaya bar tertinggi tidak mentok
                // ke atas — sama seperti fix di "Perbandingan Sales per Toko"
                $chartMaxSnackQty   = $maxSnackQty * 1.15;
                $chartMaxSnackSales = $maxSnackSales * 1.15;
            @endphp
    
            <div class="chart-container snack-chart-container">
                <span class="axis-label-rotate-left">Quantity (pcs)</span>
                <div class="y-axis left">
                    <span>{{ number_format($chartMaxSnackQty, 0, ',', '.') }}</span>
                    <span>{{ number_format($chartMaxSnackQty * 0.75, 0, ',', '.') }}</span>
                    <span>{{ number_format($chartMaxSnackQty * 0.5, 0, ',', '.') }}</span>
                    <span>{{ number_format($chartMaxSnackQty * 0.25, 0, ',', '.') }}</span>
                    <span>0</span>
                </div>
    
                <div class="plot-area">
                    @forelse($chartSnackStores as $storeRow)
                        @php
                            $qty    = $storeRow['total_quantity'] ?? 0;
                            $sales  = $storeRow['total_sales'] ?? 0;
                            $hQty   = ($chartMaxSnackQty > 0) ? ($qty / $chartMaxSnackQty) * 100 : 0;
                            $hSales = ($chartMaxSnackSales > 0) ? ($sales / $chartMaxSnackSales) * 100 : 0;
                        @endphp
                        <div class="bar-group">
                            <div class="bar snack-bar orange" style="height: {{ $hQty }}%;" title="Quantity: {{ number_format($qty) }}"></div>
                            <div class="bar snack-bar green" style="height: {{ $hSales }}%;" title="Sales: Rp {{ number_format($sales) }}"></div>
                            <div class="x-label" title="{{ $storeRow['store_name'] ?? 'Toko' }}">
                                {{ Str::limit($storeRow['store_name'] ?? 'Toko', 22) }}
                            </div>
                        </div>
                    @empty
                        <div style="padding: 30px; text-align: center; color: #999;">Tidak ada data snack pada periode ini.</div>
                    @endforelse
                </div>
    
                <span class="axis-label-rotate-right">Sales (Ribu)</span>
                <div class="y-axis right">
                    <span>{{ number_format($chartMaxSnackSales/1000, 0, ',', '.') }}k</span>
                    <span>{{ number_format(($chartMaxSnackSales * 0.75)/1000, 0, ',', '.') }}k</span>
                    <span>{{ number_format(($chartMaxSnackSales * 0.5)/1000, 0, ',', '.') }}k</span>
                    <span>{{ number_format(($chartMaxSnackSales * 0.25)/1000, 0, ',', '.') }}k</span>
                    <span>0</span>
                </div>
            </div>
    
            <div class="legend snack-legend">
                <div class="legend-item l-orange">
                    <div class="legend-box l-orange"></div>
                    <span>Quantity (pcs)</span>
                </div>
                <div class="legend-item l-green">
                    <div class="legend-box l-green"></div>
                    <span>Sales (IDR)</span>
                </div>
            </div>
    
            <div class="table-responsive">
                <table class="snack-table">
                    <thead>
                        <tr>
                            <th>Nama Toko</th>
                            <th style="text-align: right;">Quantity (pcs)</th>
                            <th style="text-align: right;">Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- FIX: rename loop var $store -> $storeRow --}}
                        @forelse($snackData as $storeRow)
                        <tr>
                            <td>{{ $storeRow['store_name'] ?? 'Unknown Store' }}</td>
                            <td style="text-align: right;">{{ number_format($storeRow['total_quantity'] ?? 0, 0, ',', '.') }} pcs</td>
                            <td style="text-align: right;">Rp {{ number_format($storeRow['total_sales'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted" style="text-align: center;">Tidak ada data snack.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="total-row snack-total">
                            <td>TOTAL ALL STORES</td>
                            <td style="text-align: right;">{{ number_format($snackTotals['total_quantity'], 0, ',', '.') }} pcs</td>
                            <td style="text-align: right;">Rp {{ number_format($snackTotals['total_sales'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>

    {{-- =========================================
         [NEW] TOP PROMOTION SECTION
    ========================================= --}}
    <section class="promotion-section" id="section-promotion" style="margin-top: 30px; margin-bottom: 30px;">
        <div class="card widget-card">
            <div class="header">
                <h3>Top Promotion</h3>
                <span>Promo paling sering dipakai (berdasarkan jumlah transaksi) &mdash; {{ $store === 'all' ? 'Semua Toko' : ($storeOptions->firstWhere('code', $store)->name ?? $store) }}</span>
            </div>

            <div class="table-responsive">
                <table class="promotion-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Promo</th>
                            <th>Kode</th>
                            <th style="text-align: right;">Dipakai</th>
                            <th style="text-align: right;">Qty Terjual</th>
                            <th style="text-align: right;">Total Diskon</th>
                            <th style="text-align: right;">Net Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPromotions as $i => $promo)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $promo->promotion_name }}</td>
                                <td>{{ $promo->promotion_code ?? '-' }}</td>
                                <td style="text-align: right;">{{ number_format($promo->usage_count, 0, ',', '.') }}x</td>
                                <td style="text-align: right;">{{ number_format($promo->total_quantity, 0, ',', '.') }}</td>
                                <td style="text-align: right;">Rp {{ number_format($promo->total_discount, 0, ',', '.') }}</td>
                                <td style="text-align: right;">Rp {{ number_format($promo->total_sales, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: #888;">
                                    Tidak ada data promosi pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="promotion-search-box" style="margin-top: 20px;">
                <label for="promotionSearchInput" style="font-weight: 600; font-size: 14px; display: block; margin-bottom: 8px;">
                    Cari Promo:
                </label>
                <input
                    type="text"
                    id="promotionSearchInput"
                    placeholder="Ketik nama promo... (misal: snacktime, tiktok, jajan nikmat)"
                    oninput="searchPromotion(this.value)"
                    style="width: 100%; max-width: 420px; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
                >
                <div id="promotionSearchResults" style="margin-top: 14px;"></div>
            </div>
        </div>
    </section>

</div>
@endsection
@push('styles')
<link href="{{ asset('css/area-manager.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/area-manager.js') }}"></script>
@endpush