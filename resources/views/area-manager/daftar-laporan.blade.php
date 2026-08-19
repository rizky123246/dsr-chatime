@extends('layouts.app')

@section('title', 'Daftar Laporan')

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

    {{-- NAVBAR --}}
    @include('components.navbar')

    {{-- WRAPPER --}}
    <div class="laporan-full-wrapper">

        {{-- CARD --}}
        <div class="dashboard-card laporan-card">

            {{-- HEADER --}}
            <div class="laporan-header">

                <div>
                    <h2>Daftar Laporan</h2>

                    <p class="subtitle">
                        Riwayat laporan seluruh store
                    </p>
                </div>

                <button type="button" class="toggle-filter-btn" onclick="toggleDateFilter()">
                    <span id="filterToggleIcon">🔽</span> Filter
                </button>

            </div>

            {{-- =========================
                 FILTER STORE / STATUS / TANGGAL / BULAN / TAHUN
            ========================= --}}
            <form method="GET" class="date-filter-bar" id="dateFilterBar" style="display: none;">

                {{-- FILTER STORE --}}
                <div class="date-filter-group">
                    <label for="filterStore">Store:</label>
                    <select name="store" id="filterStore">
                        <option value="">Semua Store</option>
                        @foreach($stores as $store)
                            <option value="{{ $store }}" {{ ($filters['store'] ?? '') == $store ? 'selected' : '' }}>
                                {{ $store }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- FILTER STATUS --}}
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

                <span class="date-filter-divider">|</span>

                {{-- FILTER TANGGAL SPESIFIK --}}
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

                {{-- FILTER BULAN --}}
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

                {{-- FILTER TAHUN --}}
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

                <div class="date-filter-actions">
                    <button type="submit" class="date-filter-submit">
                        Terapkan
                    </button>

                    @if(!empty($filters['store']) || !empty($filters['status']) || !empty($filters['tanggal']) || !empty($filters['bulan']) || !empty($filters['tahun']))
                        <a href="{{ url()->current() }}" class="date-filter-reset">
                            Reset
                        </a>
                    @endif
                </div>

            </form>

            {{-- TABLE --}}
            <div class="laporan-table-wrapper">

                <table class="laporan-table">
            
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Store</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-center">Sold Cups</th>
                            <th class="text-right">Net Sales</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
            
                    <tbody>
            
                        @forelse($laporans as $laporan)
            
                        <tr class="table-row">

                            {{-- TANGGAL --}}
                            <td
                                class="clickable"
                                onclick="window.location.href='{{ route('laporan.show', $laporan->trans_date) }}'"
                            >
                                {{ \Carbon\Carbon::parse($laporan->trans_date)->format('d M Y') }}
                            </td>

                            {{-- STORE --}}
                            <td
                                class="clickable"
                                onclick="window.location.href='{{ route('laporan.show', $laporan->trans_date) }}'"
                            >
                                {{ $laporan->store }}
                            </td>

                            {{-- TRANSAKSI --}}
                            <td
                                class="text-center clickable"
                                onclick="window.location.href='{{ route('laporan.show', $laporan->trans_date) }}'"
                            >

                                {{
                                    number_format(
                                        optional(
                                            $laporan->metrics
                                                ->where('metric', 'TC')
                                                ->first()
                                        )->value ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            </td>

                            {{-- SOLD CUP --}}
                            <td
                                class="text-center clickable"
                                onclick="window.location.href='{{ route('laporan.show', $laporan->trans_date) }}'"
                            >

                                {{
                                    number_format(
                                        optional(
                                            $laporan->metrics
                                                ->where('metric', 'TOTAL_CUP')
                                                ->first()
                                        )->value ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            </td>

                            {{-- NET SALES --}}
                            <td
                                class="text-right clickable"
                                onclick="window.location.href='{{ route('laporan.show', $laporan->trans_date) }}'"
                            >

                                Rp
                                {{
                                    number_format(
                                        optional(
                                            $laporan->metrics
                                                ->where('metric', 'SALES')
                                                ->first()
                                        )->value ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            </td>

                            {{-- STATUS --}}
                            <td class="text-center">

                                @if($laporan->status == 'approved')

                                    <span class="status-badge success">
                                        Approved
                                    </span>

                                @elseif($laporan->status == 'rejected')

                                    <span class="status-badge danger">
                                        Rejected
                                    </span>

                                @elseif($laporan->status == 'submitted')

                                    <span class="status-badge warning">
                                        Submitted
                                    </span>

                                @else

                                    <span class="status-badge">
                                        Draft
                                    </span>

                                @endif

                            </td>

                            {{-- AKSI --}}
                            <td class="text-center">

                                @if($laporan->status == 'submitted')

                                    <div class="action-buttons">

                                        {{-- APPROVE --}}
                                        <form
                                            action="{{ route('area-manager.approve', $laporan->id) }}"
                                            method="POST"
                                        >

                                            @csrf

                                            <button
                                                type="submit"
                                                class="btn-action approve"
                                            >
                                                Approve
                                            </button>

                                        </form>

                                        {{-- REJECT --}}
                                        <form
                                            action="{{ route('area-manager.reject', $laporan->id) }}"
                                            method="POST"
                                        >

                                            @csrf

                                            <button
                                                type="submit"
                                                class="btn-action reject"
                                            >
                                                Reject
                                            </button>

                                        </form>

                                    </div>

                                @else

                                    -

                                @endif

                            </td>

                        </tr>
            
                        @empty
            
                        <tr>
            
                            <td colspan="7" class="empty-data">
                                Tidak ada laporan tersedia
                            </td>
            
                        </tr>
            
                        @endforelse
            
                    </tbody>
            
                </table>
            
            </div>

            {{-- PAGINATION --}}
            @if($laporans->hasPages())
                <div class="laporan-pagination">
                    {{ $laporans->links() }}
                </div>
            @endif

        </div>

    </div>

</div>

@endsection