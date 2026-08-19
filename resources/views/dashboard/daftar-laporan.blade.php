@extends('layouts.app')

@section('title', 'Daftar Laporan Penjualan')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="{{ asset('css/navbar.css') }}" rel="stylesheet">
<link href="{{ asset('css/daftar-laporan.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/daftar-laporan.js') }}"></script>
@endpush

@section('content')

<div class="dashboard-content">

    @include('components.navbar')

    <div class="dashboard-card">

        {{-- HEADER --}}
<div class="laporan-header">

    <div>
        <h2>Daftar Laporan Penjualan</h2>
        <p class="subtitle">
            Riwayat laporan penjualan seluruh cabang
        </p>
    </div>

    <button type="button" class="toggle-filter-btn" onclick="toggleDateFilter()">
        <span id="filterToggleIcon">🔽</span> Filter Tanggal
    </button>

</div>

{{-- =========================
     FILTER TANGGAL / BULAN / TAHUN (hidden by default)
========================= --}}
<form method="GET" class="date-filter-bar" id="dateFilterBar" style="display: none;">

    <div class="date-filter-group">
        <label for="filterTanggal">Tanggal Spesifik:</label>
        <input
            type="date"
            name="tanggal"
            id="filterTanggal"
            value="{{ $filters['tanggal'] ?? '' }}"
        >
    </div>

    <span class="date-filter-divider">atau</span>

    <div class="date-filter-group">
        <label for="filterBulan">Bulan:</label>
        <select name="bulan" id="filterBulan">
            <option value="">Semua Bulan</option>
            @php
                $namaBulan = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
            @endphp
            @foreach($namaBulan as $num => $label)
                <option value="{{ $num }}" {{ (string)($filters['bulan'] ?? '') === (string)$num ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="date-filter-group">
        <label for="filterTahun">Tahun:</label>
        <select name="tahun" id="filterTahun">
            <option value="">Semua Tahun</option>
            @foreach($availableYears as $year)
                <option value="{{ $year }}" {{ (string)($filters['tahun'] ?? '') === (string)$year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="date-filter-group">
        <label for="filterStatus">Status:</label>
        <select name="status" id="filterStatus">
            <option value="">Semua Status</option>
            <option value="submitted" {{ ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' }}>
                Submitted
            </option>
            <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>
                Approved
            </option>
            <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>
                Rejected
            </option>
            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>
                Draft
            </option>
        </select>
    </div>

    <div class="date-filter-actions">
        <button type="submit" class="date-filter-submit">
            Terapkan
        </button>

        @if(!empty($filters['tanggal']) || !empty($filters['bulan']) || !empty($filters['tahun']) || !empty($filters['status']))
            <a href="{{ url()->current() }}" class="date-filter-reset">
                Reset
            </a>
        @endif
    </div>

</form>

{{-- ALERT --}}
@if(session('success'))
    <div class="alert-success-custom">
        {{ session('success') }}
    </div>
@endif
        @if(session('error'))
            <div class="alert-danger-custom">
                {{ session('error') }}
            </div>
        @endif

        {{-- TABLE --}}
        <div class="laporan-table-wrapper">

            <table class="laporan-table">

                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Cabang</th>
                        <th class="text-center">Transaksi</th>
                        <th class="text-center">Sold Cups</th>
                        <th class="text-right">Net Sales</th>
                        <th class="text-center">Void Count</th>
                        <th class="text-center">Status</th>

                        @if(auth()->user()->role == 'kasir')
                            <th class="text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody id="reportsTableBody">

                    @forelse($data as $report)

                    <tr class="table-row">

                        {{-- TANGGAL --}}
                        <td onclick="window.location.href='{{ route('laporan.show', $report['tanggal']) }}'" class="clickable">
                            {{ \Carbon\Carbon::parse($report['tanggal'])->format('d F Y') }}
                        </td>

                        {{-- CABANG --}}
                        <td onclick="window.location.href='{{ route('laporan.show', $report['tanggal']) }}'" class="clickable">
                            {{ $report['site_description'] }}
                        </td>

                        {{-- TRANSAKSI --}}
                        <td class="text-center clickable"
                            onclick="window.location.href='{{ route('laporan.show', $report['tanggal']) }}'">

                            {{ number_format($report['total_transactions'], 0, ',', '.') }}

                        </td>

                        {{-- SOLD CUP --}}
                        <td class="text-center clickable"
                            onclick="window.location.href='{{ route('laporan.show', $report['tanggal']) }}'">

                            {{ number_format($report['total_cup'], 0, ',', '.') }}

                        </td>

                        {{-- NET SALES --}}
                        <td class="text-right clickable"
                            onclick="window.location.href='{{ route('laporan.show', $report['tanggal']) }}'">

                            Rp {{ number_format($report['net_sales'], 0, ',', '.') }}

                        </td>

                        {{-- VOID --}}
                        <td class="text-center clickable"
                            onclick="window.location.href='{{ route('laporan.show', $report['tanggal']) }}'">

                            {{ number_format($report['void_count'], 0, ',', '.') }}

                        </td>

                        {{-- STATUS --}}
                        <td class="text-center">

                            @if($report['status'] == 'submitted')

                                <span class="status-badge warning">
                                    Submitted
                                </span>

                            @elseif($report['status'] == 'approved')

                                <span class="status-badge success">
                                    Approved
                                </span>

                            @elseif($report['status'] == 'rejected')

                                <span class="status-badge danger">
                                    Rejected
                                </span>

                            @else

                                <span class="status-badge">
                                    Draft
                                </span>

                            @endif

                        </td>

                      
                       {{-- AKSI --}}
                    @if(auth()->user()->role == 'kasir')

                    <td class="text-center">

                        <form
                            action="{{ route('daftar-laporan.destroy', $report['tanggal']) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus laporan ini beserta data penjualan dan pembayaran?')"
                        >

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn-delete">
                                Hapus
                            </button>

                        </form>

                    </td>

                    @endif

                    </tr>

                    @empty

                    <tr>

                        <td
                            colspan="{{ auth()->user()->role == 'kasir' ? '8' : '7' }}"
                            class="empty-data"
                        >
                            Tidak ada data laporan
                        </td>

                    </tr>

                    @endforelse
                    

                </tbody>

            </table>
            

        </div>

    </div>

</div>

@endsection