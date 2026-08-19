@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('title', 'Kasir Dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link href="{{ asset('css/kasir.css') }}" rel="stylesheet">
@endpush

@section('content')
<div
    class="dashboard-content"
    data-total-sales="{{ $summary['total_sales'] }}"
>

    {{-- NAVBAR --}}
    @include('components.navbar')

    {{-- HEADER --}}
    <div class="header-section">

        <h1>Dashboard Penjualan</h1>

        <div class="filters">

            <form method="GET">

                <div class="date-range-picker">

                    <i class="fas fa-calendar-alt"></i>

                    <div class="date-inputs">

                        <div class="date-input-group">
                            <label>Dari:</label>

                            <input
                                type="date"
                                name="start_date"
                                value="{{ $dateRange['start'] }}"
                                max="{{ $currentDate }}"
                            >
                        </div>

                        <div class="date-input-group">
                            <label>Sampai:</label>

                            <input
                                type="date"
                                name="end_date"
                                value="{{ $dateRange['end'] }}"
                                max="{{ $currentDate }}"
                            >
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>

                </div>

            </form>

        </div>

    </div>

    {{-- SUMMARY --}}
    <div class="stats-grid">

        {{-- SALES --}}
        <div class="card">

            <div class="card-header">
                <span>Total Penjualan</span>
                <i class="fas fa-chart-line"></i>
            </div>

            <div class="card-value">
                Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}
            </div>

            <div class="card-label">
                {{ $dateRange['start_display'] }}
                -
                {{ $dateRange['end_display'] }}
            </div>

        </div>

        {{-- TRANSAKSI --}}
        <div class="card">

            <div class="card-header">
                <span>Total Transaksi</span>
                <i class="fas fa-shopping-cart"></i>
            </div>

            <div class="card-value">
                {{ number_format($summary['total_transactions'], 0, ',', '.') }}
            </div>

            <div class="card-label">
                {{ $dateRange['start_display'] }}
                -
                {{ $dateRange['end_display'] }}
            </div>

        </div>

        {{-- TOTAL CUP --}}
        <div class="card">

            <div class="card-header">
                <span>Total Cups</span>
                <i class="fas fa-mug-hot"></i>
            </div>

            <div class="card-value">
                {{ number_format($summary['total_cups'], 0, ',', '.') }}
            </div>

            <div class="card-label">
                {{ $dateRange['start_display'] }}
                -
                {{ $dateRange['end_display'] }}
            </div>

        </div>

        {{-- AVG CHECK --}}
        <div class="card">

            <div class="card-header">
                <span>Avg Check</span>
                <i class="fas fa-wallet"></i>
            </div>

            <div class="card-value">
                Rp {{ number_format($summary['avg_check'], 2, ',', '.') }}
            </div>

            <div class="card-label">
                {{ $dateRange['start_display'] }}
                -
                {{ $dateRange['end_display'] }}
            </div>

        </div>

        {{-- CUP SIZE --}}
        <div class="card cup-size-card">

            <div class="card-header">
                <span>Ukuran Cup</span>
                <i class="fas fa-glass-martini-alt"></i>
            </div>

            <div class="cup-size-stats">

                <div class="size-item">
                    <span class="size-label">Extra Large : </span>

                    <span class="size-value">
                        {{ number_format($summary['extra_large'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="size-item">
                    <span class="size-label">Large : </span>

                    <span class="size-value">
                        {{ number_format($summary['large'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="size-item">
                    <span class="size-label">Regular : </span>

                    <span class="size-value">
                        {{ number_format($summary['regular'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="size-item">
                    <span class="size-label">Small : </span>

                    <span class="size-value">
                        {{ number_format($summary['small'], 0, ',', '.') }}
                    </span>
                </div>

            </div>

            <div class="card-label">
                {{ $dateRange['start_display'] }}
                -
                {{ $dateRange['end_display'] }}
            </div>

        </div>

    </div>

    {{-- =========================
        GRAFIK + PAYMENT
    ========================= --}}
    <div class="charts-grid mt-4">

        {{-- GRAFIK PENJUALAN --}}
<div class="card chart-card">

    <div class="card-title-area">

        <h3>Grafik Penjualan</h3>

        <p>
            Tren penjualan harian
            {{ $dateRange['start_display'] }}
            -
            {{ $dateRange['end_display'] }}
        </p>

    </div>

    <div class="chart-container sales-chart">

        {{-- Y AXIS --}}
        <div class="y-axis">

            @php
                $maxSales = collect($chart)->max('sales') ?: 1;

                $yAxisLabels = [
                    $maxSales,
                    $maxSales * 0.75,
                    $maxSales * 0.5,
                    $maxSales * 0.25,
                    0
                ];
            @endphp

            @foreach($yAxisLabels as $label)

                <span>
                    {{ number_format($label, 0, ',', '.') }}
                </span>

            @endforeach

        </div>

        {{-- CHART --}}
        <div class="chart-area">

            {{-- SVG LINE --}}
            <svg
                class="line-chart-svg"
                viewBox="0 0 1000 300"
                preserveAspectRatio="none"
            >

                @php
                    $points = [];

                    foreach($chart as $index => $item){

                        $x = count($chart) > 1
                            ? ($index / (count($chart)-1)) * 1000
                            : 0;

                        $y = $maxSales > 0
                            ? 300 - (($item['sales'] / $maxSales) * 260)
                            : 300;

                        $points[] = "$x,$y";
                    }
                @endphp

                <polyline
                    fill="none"
                    stroke="#f59e0b"
                    stroke-width="4"
                    points="{{ implode(' ', $points) }}"
                />

            </svg>

            {{-- GRID --}}
            <div class="grid-line"></div>
            <div class="grid-line"></div>
            <div class="grid-line"></div>
            <div class="grid-line"></div>

            {{-- DATA POINT --}}
            @foreach($chart as $index => $item)

                @php
                    $position = ($index / max(count($chart)-1,1)) * 100;

                    $height = $maxSales > 0
                        ? ($item['sales'] / $maxSales) * 85
                        : 0;
                @endphp

                <div
                    class="data-point"
                    style="
                        left: {{ $position }}%;
                        bottom: {{ $height }}%;
                    "
                    title="
                        {{ $item['date'] }}
                        :
                        Rp {{ number_format($item['sales'],0,',','.') }}
                    "
                ></div>

            @endforeach

        </div>

        {{-- X AXIS --}}
        <div class="x-axis">

            @if(count($chart) > 0)

                {{ $chart->first()['date'] }}
                -
                {{ $chart->last()['date'] }}

            @else

                No Data

            @endif

        </div>

    </div>

    <div class="chart-legend">
        <span class="dot orange"></span>
        Penjualan Harian
    </div>

</div>
        {{-- METODE PEMBAYARAN --}}
        <div class="card chart-card">

            <div class="card-title-area">

                <h3>Metode Pembayaran</h3>

                <p>
                    Distribusi metode pembayaran
                    {{ $dateRange['start_display'] }}
                    -
                    {{ $dateRange['end_display'] }}
                </p>

            </div>

            @php
                $ojol = $summary['ojol'];
                $instore = $summary['instore'];
                $total = $summary['total_sales'] ?: 1;
                $instorePercent = round(($instore / $total) * 100, 1);
                $ojolPercent = round(($ojol / $total) * 100, 1);
            @endphp

            <div class="payment-content">

                {{-- PIE --}}
                <div class="pie-container">

                    <div
                        class="pie-chart"
                        style="
                            background:
                            conic-gradient(
                                #10b981 0deg {{ $instorePercent * 3.6 }}deg,
                                #f97316 {{ $instorePercent * 3.6 }}deg 360deg
                            );
                        "
                    ></div>

                    <div class="pie-label instore-text">
                        Instore {{ $instorePercent }}%
                    </div>

                    <div class="pie-label ojol-text">
                        Ojol {{ $ojolPercent }}%
                    </div>

                </div>

                {{-- DETAIL --}}
                <div class="payment-details">

                    <div class="payment-item">

                        <div class="item-header">
                            <i class="fas fa-store"></i>
                            <strong>Instore</strong>
                        </div>

                        <div class="price">
                            Rp {{ number_format($instore, 0, ',', '.') }}
                        </div>

                        <div class="percentage">
                            {{ $instorePercent }}%
                        </div>

                    </div>

                    <div class="payment-item">

                        <div class="item-header">
                            <i class="fas fa-mobile-alt"></i>
                            <strong>OJOL</strong>
                        </div>

                        <div class="price">
                            Rp {{ number_format($ojol, 0, ',', '.') }}
                        </div>

                        <div class="percentage">
                            {{ $ojolPercent }}%
                        </div>

                        <div class="sub-details">

                            <div class="sub-item">
                                <span>ShopeeFood</span>

                                <span>
                                    Rp {{ number_format($summary['shopee'], 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="sub-item">
                                <span>GoFood</span>

                                <span>
                                    Rp {{ number_format($summary['gofood'], 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="sub-item">
                                <span>GrabFood</span>

                                <span>
                                    Rp {{ number_format($summary['grab'], 0, ',', '.') }}
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

   {{-- =========================
TOP MENU ANALYTICS
========================= --}}
<div class="card top-menu-card mt-4">

    {{-- HEADER --}}
    <div class="top-menu-header">

        <div>
            <h3>Top Menu Analytics</h3>
            <p>Analisa performa menu terlaris (Gunakan data 1 dan 7 hari kebelakang untuk analisa menu terlaris)</p>
        </div>

    </div>

    {{-- FILTER AREA --}}
    <div class="top-menu-filter-area">

        {{-- SELECT BOX --}}
        <div class="menu-select-container">

            <div
                class="menu-select-box"
                onclick="toggleMenuDropdown(event)"
            >

                <span id="selectedMenuCount">
                    0 menu dipilih
                </span>

                <i class="fas fa-chevron-down"></i>

            </div>

            {{-- DROPDOWN --}}
            <div
                class="menu-dropdown"
                id="menuDropdown"
            >

                {{-- SEARCH --}}
                <div class="dropdown-search">

                    <input
                        type="text"
                        id="menuSearch"
                        placeholder="Cari menu..."
                        onkeyup="searchMenus(this.value)"
                    >
                </div>

                {{-- ACTION --}}
                <div class="dropdown-actions">

                    <button
                        type="button"
                        onclick="selectAllMenus()"
                    >
                        Pilih Semua
                    </button>

                    <button
                        type="button"
                        onclick="deselectAllMenus()"
                    >
                        Reset
                    </button>

                </div>

                {{-- TAGS --}}
                <div
                    class="selected-tags"
                    id="selectedTags"
                ></div>

                {{-- LIST --}}
                <div
                    class="menu-checkboxes"
                    id="menuCheckboxes"
                >

                    @forelse($topProducts as $product)

                        <div class="menu-item">

                            <label>

                                <input
                                    type="checkbox"
                                    class="menu-checkbox"

                                    value="{{ $product->article_name }}"

                                    data-quantity="{{ $product->total_quantity }}"
                                    data-sales="{{ $product->total_sales }}"
                                    data-type="{{ $product->type }}"
                                    data-series="{{ $product->series }}"
                                    data-article-code="{{ $product->article_code }}"

                                    onchange="updateSelectedMenus()"
                                >

                                <span class="menu-name">
                                    {{ $product->article_name }}
                                </span>

                                <span class="menu-qty">
                                    ({{ number_format($product->total_quantity) }})
                                </span>

                            </label>

                        </div>

                    @empty

                        <div class="empty-state">
                            Tidak ada data
                        </div>

                    @endforelse

                </div> {{-- end menu-checkboxes --}}

            </div> {{-- end dropdown --}}

        </div> {{-- end menu-select-container --}}

    </div> {{-- end top-menu-filter-area --}}

    {{-- SUMMARY --}}
    <div class="top-menu-summary">

        <div class="summary-box">

            <span>Total Menu Dipilih</span>

            <h2 id="totalSoldCups">
                0 Cups
            </h2>

        </div>

    </div>

    {{-- CHART --}}
    <div class="comparison-chart-wrapper">

        <div
            class="comparison-chart"
            id="comparisonChart"
        >

            <div class="empty-chart">
                Pilih menu untuk melihat grafik
            </div>

        </div>

    </div>

    {{-- DETAIL --}}
    <div
        class="menu-stats-container"
        id="menuStatsContainer"
    ></div>

</div>
@endsection

@push('scripts')

<script src="{{ asset('js/kasir.js') }}"></script>

@endpush