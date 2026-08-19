<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Services\SalesReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StoreManagerController extends Controller
{
    public function __construct(private SalesReportService $reportService)
    {
    }

    public function index(Request $request)
    {
        $siteCode = Auth::user()->site_code;

        $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate   = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $dateRange = [
            'start'         => $startDate,
            'end'           => $endDate,
            'start_display' => Carbon::parse($startDate)->format('d M Y'),
            'end_display'   => Carbon::parse($endDate)->format('d M Y'),
        ];

        $lastWeekStart  = Carbon::parse($startDate)->subDays(7)->format('Y-m-d');
        $lastWeekEnd    = Carbon::parse($endDate)->subDays(7)->format('Y-m-d');
        $lastMonthStart = Carbon::parse($startDate)->subDays(28)->format('Y-m-d');
        $lastMonthEnd   = Carbon::parse($endDate)->subDays(28)->format('Y-m-d');

        
        $thisWeekData  = $this->reportService->getSalesData($startDate, $endDate, $siteCode);
        $lastWeekData  = $this->reportService->getSalesData($lastWeekStart, $lastWeekEnd, $siteCode);
        $lastMonthData = $this->reportService->getSalesData($lastMonthStart, $lastMonthEnd, $siteCode);

        $thisWeekPayments  = $this->reportService->getPaymentMethodData($startDate, $endDate, $siteCode);
        $lastWeekPayments  = $this->reportService->getPaymentMethodData($lastWeekStart, $lastWeekEnd, $siteCode);
        $lastMonthPayments = $this->reportService->getPaymentMethodData($lastMonthStart, $lastMonthEnd, $siteCode);

        $thisWeekSnacks  = $this->reportService->getSnackData($startDate, $endDate, $siteCode, $thisWeekData['net_revenue']);
        $lastWeekSnacks  = $this->reportService->getSnackData($lastWeekStart, $lastWeekEnd, $siteCode, $lastWeekData['net_revenue']);
        $lastMonthSnacks = $this->reportService->getSnackData($lastMonthStart, $lastMonthEnd, $siteCode, $lastMonthData['net_revenue']);

        $mtdProgress = $this->reportService->getMtdProgress($siteCode, $endDate);

       
        $netSales = $thisWeekData['net_revenue'];
        $lastWeekData['total_revenue']  = $lastWeekData['net_revenue'];
        $lastMonthData['total_revenue'] = $lastMonthData['net_revenue'];

        $totalTransactions = $thisWeekData['total_transactions'];
        $averageCheck      = $totalTransactions > 0 ? $netSales / $totalTransactions : 0;

        $vsLastWeekSales  = $this->reportService->calculatePercentageChange($lastWeekData['total_revenue'], $netSales);
        $vsLastMonthSales = $this->reportService->calculatePercentageChange($lastMonthData['total_revenue'], $netSales);

        $vsLastWeekTransactions  = $this->reportService->calculatePercentageChange($lastWeekData['total_transactions'], $totalTransactions);
        $vsLastMonthTransactions = $this->reportService->calculatePercentageChange($lastMonthData['total_transactions'], $totalTransactions);

        $lwAC = $lastWeekData['total_transactions'] > 0
            ? $lastWeekData['total_revenue'] / $lastWeekData['total_transactions'] : 0;
        $lmAC = $lastMonthData['total_transactions'] > 0
            ? $lastMonthData['total_revenue'] / $lastMonthData['total_transactions'] : 0;

        $vsLastWeekAC  = $this->reportService->calculatePercentageChange($lwAC, $averageCheck);
        $vsLastMonthAC = $this->reportService->calculatePercentageChange($lmAC, $averageCheck);

        // Target: heuristik 5% di atas Last Month
        $targetSales   = $lastMonthData['total_revenue'] * 1.05;
        $acvPercentage = $targetSales > 0 ? ($netSales / $targetSales) * 100 : 0;

        return view('dashboard.store-manager', compact(
            'netSales',
            'totalTransactions',
            'averageCheck',
            'acvPercentage',
            'vsLastWeekSales',
            'vsLastMonthSales',
            'vsLastWeekTransactions',
            'vsLastMonthTransactions',
            'vsLastWeekAC',
            'vsLastMonthAC',
            'lastWeekData',
            'lastMonthData',
            'dateRange',
            'thisWeekPayments',
            'lastWeekPayments',
            'lastMonthPayments',
            'thisWeekSnacks',
            'lastWeekSnacks',
            'lastMonthSnacks',
            'mtdProgress'
        ));
    }

    public function checkDataExists(Request $request)
    {
        try {
            $siteCode  = Auth::user()->site_code;
            $startDate = $request->get('start_date');
            $endDate   = $request->get('end_date');

            if (!$startDate || !$endDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'start_date dan end_date wajib diisi',
                ], 400);
            }

            [$rangeStart, $rangeEnd] = $this->reportService->dayRange($startDate, $endDate);

            $hasData = Penjualan::whereBetween('created_date', [$rangeStart, $rangeEnd])
                ->where('site_code', $siteCode)
                ->where('void', 0)
                ->exists();

            return response()->json([
                'success' => true,
                'hasData' => $hasData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint pencarian snack by keyword (AJAX).
     * GET /dashboard/store-manager/search-snack?q=...&start_date=...&end_date=...
     */
    public function searchSnackSales(Request $request)
    {
        try {
            $siteCode  = Auth::user()->site_code;
            $keyword   = $request->get('q', '');
            $startDate = $request->get('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
            $endDate   = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            $results = $this->reportService->searchSnackProducts($startDate, $endDate, $siteCode, $keyword);

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}