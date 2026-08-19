@php
    $user = auth()->user();

    /**
     * 🔥 Satu sumber untuk daftar menu semua role — dulu ada 2 file
     * navbar terpisah (satu buat kasir/store_manager, satu lagi buat
     * area_manager) yang strukturnya mirip tapi harus di-maintain dobel.
     * Sekarang cukup tambah 1 entry array kalau ada role/menu baru,
     * nggak perlu bikin file navbar baru lagi.
     */
    $menus = [
        'kasir' => [
            ['route' => 'dashboard.kasir', 'label' => 'Dashboard', 'icon' => 'fa-th-large'],
            ['route' => 'dashboard.upload-data', 'label' => 'Upload Data', 'icon' => 'fa-upload'],
            ['route' => 'dashboard.daftar-laporan', 'label' => 'Daftar Laporan', 'icon' => 'fa-bars'],
        ],
        'store_manager' => [
            ['route' => 'dashboard.store-manager', 'label' => 'Dashboard', 'icon' => 'fa-th-large'],
            ['route' => 'dashboard.daftar-laporan', 'label' => 'Daftar Laporan', 'icon' => 'fa-bars'],
        ],
        'area_manager' => [
            ['route' => 'dashboard.area-manager', 'label' => 'Dashboard', 'icon' => 'fa-th-large'],
            ['route' => 'area-manager.upload-master', 'label' => 'Upload Master', 'icon' => 'fa-database'],
            ['route' => 'area-manager.daftar-laporan', 'label' => 'Daftar Laporan', 'icon' => 'fa-file-alt'],
        ],
    ];

    $roleLabels = [
        'kasir'          => 'Kasir',
        'store_manager'  => 'Store Manager',
        'area_manager'   => 'Area Manager',
    ];

    // Fallback ke menu kasir kalau ada role yang belum terdaftar di atas
    // (lebih aman daripada navbar kosong / error route not found).
    $items     = $menus[$user->role] ?? $menus['kasir'];
    $roleLabel = $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role));
@endphp

<nav class="navbar">
    <div class="navbar-menu">
        @foreach($items as $item)
            <a href="{{ route($item['route']) }}"
               class="nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <i class="fas {{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Info user — biar navbar lebih informatif: kelihatan sedang login
         sebagai siapa & scope toko mana. Area manager biasanya nggak
         terikat 1 site_code, jadi ditampilkan beda dari kasir/SM. --}}
    <div class="navbar-user">
        <span class="navbar-user-badge navbar-user-badge--{{ $user->role }}">
            {{ $roleLabel }}
        </span>
        @if($user->role !== 'area_manager' && !empty($user->site_code))
            <span class="navbar-user-site">
                <i class="fas fa-store"></i> {{ $user->site_code }}
            </span>
        @endif
    </div>
</nav>