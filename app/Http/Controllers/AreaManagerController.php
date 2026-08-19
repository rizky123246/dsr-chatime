<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\LaporanHarian;
use App\Models\Store;
use App\Services\SalesReportService;
use Carbon\Carbon;

class AreaManagerController extends Controller
{
    public function __construct(private SalesReportService $reportService)
    {
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate   = $request->get('end_date', date('Y-m-d'));
        $store     = $request->get('store', 'all'); // default ALL

        // Defensif: kalau ada query string aneh (mis. store[]=xxx) yang
        // membuat $store jadi array, paksa ambil elemen pertama saja.
        if (is_array($store)) {
            $store = $store[0] ?? 'all';
        }

        // Ambil semua store aktif
        $storeOptions = Store::where('is_active', true)->orderBy('name')->get();

        // 🔥 Semua angka sekarang dari SalesReportService — sumber yang
        // sama dengan Kasir & Store Manager.
        $siteCodeFilter = $store !== 'all' ? $store : null;

        $stores    = $this->getAllStoresData($startDate, $endDate, $store, $storeOptions);
        $rangeSales = $this->reportService->getSalesData($startDate, $endDate, $siteCodeFilter);
        $totals    = $this->calculateTotals($stores, $rangeSales);

        $topProducts   = $this->reportService->getTopBeverages($startDate, $endDate, $siteCodeFilter, 20);
        $snackData     = $this->getSnackPerStoreData($startDate, $endDate, $store);
        $snackTotals   = $this->calculateSnackTotals($snackData);
        $topPromotions = $this->reportService->getTopPromotions($startDate, $endDate, $siteCodeFilter, 5);
        $timeProgress  = $this->getAreaTimeProgress($store, $storeOptions, $endDate);

        // Set session nama store (dipakai di tempat lain / header)
        if ($store !== 'all') {
            $selectedStore = $storeOptions->firstWhere('code', $store);
            session(['selected_store_name' => $selectedStore->name ?? 'Store Tidak Diketahui']);
        } else {
            session(['selected_store_name' => 'Semua Store']);
        }

        return view('dashboard.area-manager', compact(
            'stores',
            'storeOptions',
            'totals',
            'topProducts',
            'snackData',
            'snackTotals',
            'topPromotions',
            'timeProgress',
            'startDate',
            'endDate',
            'store'
        ));
    }

    
    private function getAllStoresData($startDate, $endDate, $store, $storeOptions)
    {
        $siteCodes = $store !== 'all' ? [$store] : null;

        $salesByStore = $this->reportService->getSalesDataByStore($startDate, $endDate, $siteCodes);

        $laporanQuery = LaporanHarian::with('metrics')
            ->whereBetween('trans_date', [$startDate, $endDate]);

        if ($store !== 'all') {
            $laporanQuery->where('site', $store);
        }

        $cupsBySite = $laporanQuery->get()->groupBy('site')->map(function ($items) {
            $totalCups = 0;
            foreach ($items as $item) {
                $totalCups += (int) $item->getValue('ALL', 'CURRENT', 'TOTAL_CUP');
            }
            return $totalCups;
        });

        $allSiteCodes = $salesByStore->keys()->merge($cupsBySite->keys())->unique();

        return $allSiteCodes->map(function ($code) use ($salesByStore, $cupsBySite, $storeOptions) {
            $sales = $salesByStore->get($code, [
                'total_transactions' => 0,
                'total_revenue'      => 0,
                'net_revenue'        => 0,
            ]);
            $totalCups = $cupsBySite->get($code, 0);
            $storeName = optional($storeOptions->firstWhere('code', $code))->name ?? $code;

            return [
                'code'               => $code,
                'name'               => $storeName,
                'total_sales'        => $sales['net_revenue'],
                'total_quantity'     => $totalCups,
                'total_transactions' => $sales['total_transactions'],
                'avg_check'          => $sales['total_transactions'] > 0
                                            ? round($sales['net_revenue'] / $sales['total_transactions'])
                                            : 0,
            ];
        })->values();
    }

    /**
     * 🔥 total_sales/total_transactions/avg_check dari $rangeSales
     * (SalesReportService::getSalesData() — satu query untuk SELURUH
     * scope), bukan dijumlah dari breakdown per toko lagi. Ini konsisten
     * dengan pola Kasir/StoreManager: angka headline selalu dari query
     * langsung, bukan sum-of-parts.
     */
    private function calculateTotals($stores, array $rangeSales)
    {
        return [
            'total_sales'        => $rangeSales['net_revenue'],
            'total_transactions' => $rangeSales['total_transactions'],
            'total_quantity'     => $stores->sum('total_quantity'),
            'avg_check'          => $rangeSales['total_transactions'] > 0
                ? round($rangeSales['net_revenue'] / $rangeSales['total_transactions'])
                : 0,
            'total_stores'       => $stores->count(),
        ];
    }

    // ─── Snack per toko ───────────────────────────────────────────────────

    private function getSnackPerStoreData($startDate, $endDate, $store)
    {
        $siteCodes = $store !== 'all' ? [$store] : null;

        return $this->reportService->getSnackDataByStore($startDate, $endDate, $siteCodes);
    }

    private function calculateSnackTotals($snackData)
    {
        return [
            'total_quantity' => $snackData->sum('total_quantity'),
            'total_sales'    => $snackData->sum('total_sales'),
        ];
    }

    // ─── Time Progress (MTD vs Target, area-wide atau per toko) ───────────

     
    private function getAreaTimeProgress($store, $storeOptions, $endDate)
    {
        $siteCodes = ($store !== 'all')
            ? [$store]
            : $storeOptions->pluck('code')->toArray();

        $mtd = $this->reportService->getMtdProgress($siteCodes, $endDate);

        $remainingTarget = max($mtd['target'] - $mtd['mtd_sales'], 0);
        $remainingDays   = max($mtd['days_in_month'] - $mtd['current_day'], 1);
        $dailyAvgNeeded  = round($remainingTarget / $remainingDays);
        $dailyAvgActual  = $mtd['current_day'] > 0 ? round($mtd['mtd_sales'] / $mtd['current_day']) : 0;

        return [
            'mtd_sales'        => $mtd['mtd_sales'],
            'monthly_target'   => $mtd['target'],
            'acv_percentage'   => $mtd['achievement'],
            'time_progress'    => $mtd['time_progress'],
            'days_passed'      => $mtd['current_day'],
            'days_in_month'    => $mtd['days_in_month'],
            'remaining_target' => $remainingTarget,
            'remaining_days'   => $remainingDays,
            'daily_avg_actual' => $dailyAvgActual,
            'daily_avg_needed' => $dailyAvgNeeded,
            'month_label'      => $mtd['month_name'],
            'scope_label'      => ($store !== 'all')
                ? (optional($storeOptions->firstWhere('code', $store))->name ?? $store)
                : 'Semua Toko (' . count($siteCodes) . ' cabang)',
        ];
    }

    /**
     * Endpoint pencarian promosi by keyword (AJAX), scope ikut filter store.
     * GET /dashboard/area-manager/search-promotion?q=...&start_date=...&end_date=...&store=...
     */
    public function searchPromotion(Request $request)
    {
        try {
            $keyword   = $request->get('q', '');
            $startDate = $request->get('start_date', date('Y-m-01'));
            $endDate   = $request->get('end_date', date('Y-m-d'));
            $store     = $request->get('store', 'all');

            $siteCodeFilter = $store !== 'all' ? $store : null;

            $results = $this->reportService->searchPromotions($startDate, $endDate, $siteCodeFilter, $keyword);

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── AJAX dashboard refresh ─────────────────────────────────────────

    public function getDashboardData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $store     = $request->get('store', 'all');

        if (is_array($store)) {
            $store = $store[0] ?? 'all';
        }

        $siteCodeFilter = $store !== 'all' ? $store : null;

        $storeOptionsFromTable = Store::where('is_active', true)->orderBy('name')->get();

        $stores    = $this->getAllStoresData($startDate, $endDate, $store, $storeOptionsFromTable);
        $rangeSales = $this->reportService->getSalesData($startDate, $endDate, $siteCodeFilter);
        $totals    = $this->calculateTotals($stores, $rangeSales);

        $topProducts   = $this->reportService->getTopBeverages($startDate, $endDate, $siteCodeFilter, 20);
        $snackData     = $this->getSnackPerStoreData($startDate, $endDate, $store);
        $snackTotals   = $this->calculateSnackTotals($snackData);
        $topPromotions = $this->reportService->getTopPromotions($startDate, $endDate, $siteCodeFilter, 5);
        $storeOptions  = $stores->map(fn($s) => (object) ['code' => $s['code'], 'name' => $s['name']]);
        $timeProgress  = $this->getAreaTimeProgress($store, $storeOptionsFromTable, $endDate);

        return view('dashboard.area-manager', [
            'stores'        => $stores,
            'totals'        => $totals,
            'topProducts'   => $topProducts,
            'snackData'     => $snackData,
            'snackTotals'   => $snackTotals,
            'topPromotions' => $topPromotions,
            'timeProgress'  => $timeProgress,
            'storeOptions'  => $storeOptions,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'store'         => $store,
        ]);
    }

    // ─── Laporan Harian (tidak diubah) ───────────────────────────────────

    public function areaManager(Request $request)
    {
        $query = LaporanHarian::query();

        if ($request->filled('store')) {
            $query->where('site', $request->store);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporans = $query->with('metrics')->latest('trans_date')->get();

        $stores = LaporanHarian::select('site')->distinct()->pluck('site');

        return view('dashboard.area-manager.daftar-laporan', compact('laporans', 'stores'));
    }
}