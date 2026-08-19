<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use App\Services\SalesReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function __construct(private SalesReportService $reportService)
    {
    }

    /**
     * Dashboard Kasir
     */
    public function index(Request $request)
    {
        $siteCode = Auth::user()->site_code;

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate   = $validated['end_date'] ?? now()->format('Y-m-d');

        $laporan = LaporanHarian::with('metrics')
            ->where('site', $siteCode)
            ->whereBetween('trans_date', [$startDate, $endDate])
            ->orderBy('trans_date', 'asc')
            ->get();

        $totalQty = 0;
        $totalCups = 0;
        $large = 0;
        $regular = 0;
        $small = 0;
        $extraLarge = 0;

        foreach ($laporan as $item) {
            $totalQty   += (int) $item->getValue('FOOD', 'CURRENT', 'QTY');
            $totalCups  += (int) $item->getValue('ALL', 'CURRENT', 'TOTAL_CUP');
            $large      += (int) $item->getValue('ALL', 'CURRENT', 'LARGE');
            $regular    += (int) $item->getValue('ALL', 'CURRENT', 'REGULER');
            $small      += (int) $item->getValue('ALL', 'CURRENT', 'SMALL');
            $extraLarge += (int) $item->getValue('ALL', 'CURRENT', 'EXTRA_LARGE');
        }

        $rangeSales    = $this->reportService->getSalesData($startDate, $endDate, $siteCode);
        $rangePayment  = $this->reportService->getPaymentMethodData($startDate, $endDate, $siteCode);
        $ojolBreakdown = $this->reportService->getOjolBreakdown($startDate, $endDate, $siteCode);

        $totalSales        = $rangeSales['net_revenue'];
        $totalTransactions = $rangeSales['total_transactions'];
        $avgCheck          = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        $shopee = $ojolBreakdown['shopee'];
        $gofood = $ojolBreakdown['gofood'];
        $grab   = $ojolBreakdown['grab'];

        $summary = [
            'total_sales'        => $totalSales,
            'total_qty'          => $totalQty,
            'total_cups'         => $totalCups,
            'total_transactions' => $totalTransactions,
            'avg_check'          => $avgCheck,

            'large'       => $large,
            'regular'     => $regular,
            'small'       => $small,
            'extra_large' => $extraLarge,

            'shopee' => $shopee,
            'gofood' => $gofood,
            'grab'   => $grab,
            'instore' => $rangePayment['instore'],
            'ojol'    => $rangePayment['ojol'],
        ];

        $salesData = [
            'total_revenue'      => $summary['total_sales'],
            'total_transactions' => $summary['total_transactions'],
            'total_cups'         => $summary['total_cups'],
            'avg_check'          => $summary['avg_check'],

            'cup_size_stats' => [
                'extra_large' => $summary['extra_large'],
                'large'       => $summary['large'],
                'regular'     => $summary['regular'],
                'small'       => $summary['small'],
            ],

            'shopee' => $summary['shopee'],
            'gofood' => $summary['gofood'],
            'grab'   => $summary['grab'],
        ];

        $chart = $laporan->map(function ($item) {
            return [
                'date'         => Carbon::parse($item->trans_date)->format('d M'),
                'sales'        => (float) $item->getValue('ALL', 'CURRENT', 'SALES'),
                'qty'          => (int) $item->getValue('FOOD', 'CURRENT', 'QTY'),
                'cups'         => (int) $item->getValue('ALL', 'CURRENT', 'TOTAL_CUP'),
                'transactions' => (int) $item->getValue('ALL', 'CURRENT', 'TC'),
            ];
        });

        $dailySalesTrend = $laporan->map(function ($item) {
            return (object) [
                'created_date'  => $item->trans_date,
                'daily_revenue' => (float) $item->getValue('ALL', 'CURRENT', 'SALES'),
            ];
        });

        $paymentMethods = collect([
            (object) ['payment_method' => 'Shopee', 'total_amount' => $summary['shopee']],
            (object) ['payment_method' => 'GoFood', 'total_amount' => $summary['gofood']],
            (object) ['payment_method' => 'Grab',   'total_amount' => $summary['grab']],
            (object) ['payment_method' => 'Instore', 'total_amount' => $rangePayment['instore']],
        ]);

        $topProducts = $this->reportService->getTopProducts($startDate, $endDate, $siteCode, 6);

        return view('dashboard.kasir', [
            'laporan'         => $laporan,
            'summary'         => $summary,
            'salesData'       => $salesData,
            'chart'           => $chart,
            'dailySalesTrend' => $dailySalesTrend,
            'paymentMethods'  => $paymentMethods,
            'topProducts'     => $topProducts,
            'snackData'       => collect(),
            'currentDate'     => now()->format('Y-m-d'),
            'dateRange' => [
                'start'         => $startDate,
                'end'           => $endDate,
                'start_display' => Carbon::parse($startDate)->format('d M Y'),
                'end_display'   => Carbon::parse($endDate)->format('d M Y'),
            ],
        ]);
    }

    /**
     * API Dashboard Data
     */
    public function getDashboardData(Request $request)
    {
        $siteCode = Auth::user()->site_code;

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate   = $validated['end_date'] ?? now()->format('Y-m-d');

        $laporan = LaporanHarian::with('metrics')
            ->where('site', $siteCode)
            ->whereBetween('trans_date', [$startDate, $endDate])
            ->orderBy('trans_date', 'asc')
            ->get();

        $totalQty = 0;
        $totalCups = 0;

        foreach ($laporan as $item) {
            $totalQty  += (int) $item->getValue('FOOD', 'CURRENT', 'QTY');
            $totalCups += (int) $item->getValue('ALL', 'CURRENT', 'TOTAL_CUP');
        }

        $rangeSales = $this->reportService->getSalesData($startDate, $endDate, $siteCode);

        return response()->json([
            'summary' => [
                'total_sales'        => $rangeSales['net_revenue'],
                'total_qty'          => $totalQty,
                'total_cups'         => $totalCups,
                'total_transactions' => $rangeSales['total_transactions'],
                // 🔥 Average check — sama seperti StoreManagerController
                // (net_revenue / total_transactions), bukan dibagi jumlah
                // hari lagi. Dulu ini dihitung sebagai "rata-rata per hari"
                // (net_revenue / $laporan->count()), beda konsep.
                'avg_sales' => $rangeSales['total_transactions'] > 0
                    ? round($rangeSales['net_revenue'] / $rangeSales['total_transactions'], 2)
                    : 0,
            ],

            'chart' => $laporan->map(function ($item) {
                return [
                    'date'         => Carbon::parse($item->trans_date)->format('d M'),
                    'sales'        => (float) $item->getValue('ALL', 'CURRENT', 'SALES'),
                    'qty'          => (int) $item->getValue('FOOD', 'CURRENT', 'QTY'),
                    'cups'         => (int) $item->getValue('ALL', 'CURRENT', 'TOTAL_CUP'),
                    'transactions' => (int) $item->getValue('ALL', 'CURRENT', 'TC'),
                ];
            }),
        ]);
    }

    /**
     * 🔥 Sekarang delegasi penuh ke SalesReportService::searchProducts() —
     * dulu ada query manual (termasuk ROUND(.../1.1) sendiri) di sini.
     */
    public function searchTopMenu(Request $request)
    {
        $siteCode = Auth::user()->site_code;

        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate   = $request->end_date ?? now()->format('Y-m-d');
        $search    = $request->search;

        $products = $this->reportService->searchProducts($startDate, $endDate, $siteCode, $search, 10);

        return response()->json($products);
    }

    /**
     * 🔥 Sekarang delegasi penuh ke SalesReportService::getArticleDetail() —
     * dulu ada query manual (termasuk ROUND(.../1.1) sendiri) di sini.
     */
    public function topMenuDetail(Request $request)
    {
        $siteCode = Auth::user()->site_code;

        $startDate   = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate     = $request->end_date ?? now()->format('Y-m-d');
        $articleCode = $request->article_code;

        $data = $this->reportService->getArticleDetail($startDate, $endDate, $siteCode, $articleCode);

        if (!$data) {
            return response()->json([
                'success' => false
            ]);
        }

        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        return response()->json([
            'success' => true,
            'data' => [
                'article_name'       => $data->article_name,
                'total_qty'          => (int) $data->total_qty,
                'total_sales'        => (float) $data->total_sales,
                'total_transaction'  => (int) $data->total_transaction,
                'avg_per_day' => round(
                    $data->total_qty / max($days, 1),
                    1
                )
            ]
        ]);
    }
}