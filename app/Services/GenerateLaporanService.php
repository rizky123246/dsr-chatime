<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Pembayaran;
use App\Models\FoodCategory;
use App\Models\Target;

class GenerateLaporanService
{
    public function __construct(private SalesReportService $reportService)
    {
    }

    public function generate($date, $site)
    {
        $parsed = Carbon::parse($date);

        $currentDay = $parsed->day;
        $daysInMonth = $parsed->daysInMonth;
        $today = $this->getDailySalesData($date, $site);
        $sales = $today['net_revenue'];
        $dailyProgress = round(($currentDay / $daysInMonth) * 100, 1);
        $tc = $today['total_transactions'];
        $averageCheck = $tc > 0 ? round($sales / $tc, 2) : 0;

        // 🔥 LAST WEEK
        $lastWeekDate = Carbon::parse($date)->subWeek();
        $lastWeek = $this->getDailySalesData($lastWeekDate, $site);
        $lastWeekSales = $lastWeek['net_revenue'];
        $lwTC = $lastWeek['total_transactions'];
        $lwAvg = $lwTC > 0 ? round($lastWeekSales / $lwTC, 2) : 0;

        // 🔥 LAST MONTH (weekday sama)
        $lastMonthDate = Carbon::parse($date)->subDays(28);
        $lastMonth = $this->getDailySalesData($lastMonthDate, $site);
        $lastMonthSales = $lastMonth['net_revenue'];
        $lmTC = $lastMonth['total_transactions'];
        $lmAvg = $lmTC > 0 ? round($lastMonthSales / $lmTC, 2) : 0;

        // ojol
        $ojol = $this->generateOjol($date, $site);
        $ojolLW = $this->generateOjol($lastWeekDate, $site);
        $ojolLM = $this->generateOjol($lastMonthDate, $site);

        $ojolGrowthLW = $ojolLW['total_ojol'] > 0 ? round((($ojol['total_ojol'] - $ojolLW['total_ojol']) / $ojolLW['total_ojol']) * 100, 1): 0;
        $ojolGrowthLM = $ojolLM['total_ojol'] > 0 ? round((($ojol['total_ojol'] - $ojolLM['total_ojol']) / $ojolLM['total_ojol']) * 100, 1): 0;

        $cup = $this->getCupBreakdown($date, $site) ?? (object)[];
        $totalCup =
            ($cup->large ?? 0) +
            ($cup->regular ?? 0) +
            ($cup->small ?? 0) +
            ($cup->butterfly ?? 0) +
            ($cup->pc ?? 0) +
            ($cup->extra_large ?? 0);

        // 🔥 GROWTH
        $lastWeekGrowth = $lastWeekSales > 0 ? round((($sales - $lastWeekSales) / $lastWeekSales) * 100, 1): 0;
        $lastMonthGrowth = $lastMonthSales > 0 ? round((($sales - $lastMonthSales) / $lastMonthSales) * 100, 1): 0;

        // TC
        $lwTCGrowth = $lwTC > 0 ? round((($tc - $lwTC)/$lwTC)*100,1) : 0;
        $lmTCGrowth = $lmTC > 0 ? round((($tc - $lmTC)/$lmTC)*100,1) : 0;

        // AVG
        $lwAvgGrowth = $lwAvg > 0 ? round((($averageCheck - $lwAvg)/$lwAvg)*100,1) : 0;
        $lmAvgGrowth = $lmAvg > 0 ? round((($averageCheck - $lmAvg)/$lmAvg)*100,1) : 0;

        $mtdSales = $this->getMTDSales($date, $site);
        $target = $this->getMonthlyTarget($date, $site);
        $achievement = $target > 0 ? round(($mtdSales / $target) * 100, 2) : 0;

        $InStoreSales = $this->getInstoreSales($date, $site);

        $food = $this->getFoodBreakdown($date, $site);
        $foodSummary = $this->getFoodSummary($date, $site);

        return [
            'date' => $date,
            'site' => $site,

            'large' => $cup->large ?? 0,
            'regular' => $cup->regular ?? 0,
            'small' => $cup->small ?? 0,
            'cold' => $cup->cold ?? 0,
            'hot' => $cup->hot ?? 0,
            'butterfly' => $cup->butterfly ?? 0,
            'pc' => $cup->pc ?? 0,
            'extra_large' => $cup->extra_large ?? 0,
            'total_cup' => $totalCup,

            'daily_progress' => $dailyProgress,
            'sales' => $sales,

            'last_week_date' => $lastWeekDate,
            'last_week_sales' => $lastWeekSales,
            'last_week_growth' => $lastWeekGrowth,
            'last_week_tc' => $lwTC,
            'last_week_growth_tc' => $lwTCGrowth,
            'last_week_average_check' => $lwAvg,
            'last_week_growth_avg' => $lwAvgGrowth,

            'last_month_date' => $lastMonthDate,
            'last_month_sales' => $lastMonthSales,
            'last_month_growth' => $lastMonthGrowth,
            'last_month_tc' => $lmTC,
            'last_month_growth_tc' => $lmTCGrowth,
            'last_month_average_check' => $lmAvg,
            'last_month_growth_avg' => $lmAvgGrowth,

            'ojol_lw' => $ojolLW['total_ojol'],
            'ojol_lm' => $ojolLM['total_ojol'],
            'ojol_growth_lw' => $ojolGrowthLW,
            'ojol_growth_lm' => $ojolGrowthLM,

            'tc' => $tc,
            'avg_check' => $averageCheck,
            'mtd_sales' => $mtdSales,
            'target_sales' => $target,
            'achievement_mtd' => $achievement,

            'shopee' => $ojol['shopee'],
            'gofood' => $ojol['gofood'],
            'grab' => $ojol['grab'],
            'total_ojol' => $ojol['total_ojol'],
            'ojol_tc' => $ojol['ojol_tc'],

            'InStoreSales' => $InStoreSales,

            'food' => $food,
            'food_total_sales' => $foodSummary['sales'],
            'food_total_qty' => $foodSummary['qty'],
        ];
    }

    private function getMTDSales($date, $site)
    {
        $startOfMonth = Carbon::parse($date)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($date)->format('Y-m-d');

        return $this->reportService->getSalesData($startOfMonth, $endDate, $site)['net_revenue'];
    }

    // 🔥 DIPINDAH ke SalesReportService::getMonthlyTarget() —
    // dipakai juga oleh getMtdProgress() di service.
    private function getMonthlyTarget($date, $site)
    {
        $parsed = Carbon::parse($date);

        return $this->reportService->getMonthlyTarget($parsed->year, $parsed->month, $site);
    }

    // 🔥 DIPINDAH ke SalesReportService::getCupBreakdown()
    public function getCupBreakdown($date, $site)
    {
        return $this->reportService->getCupBreakdown(Carbon::parse($date)->format('Y-m-d'), $site);
    }

    /**
     * 🔥 Satu query untuk sales + transaction count sekaligus (dari
     * SalesReportService::getSalesData()) — dulu ini dua query terpisah
     * (getSales() + getTC()) yang masing-masing hit tabel penjualans.
     * order_no dan receipt_no merepresentasikan hal yang sama, jadi
     * transaction count sekarang konsisten juga dengan StoreManager/Kasir
     * (yang keduanya pakai receipt_no lewat getSalesData()).
     */
    private function getDailySalesData($date, $site): array
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');

        return $this->reportService->getSalesData($formattedDate, $formattedDate, $site);
    }

    private function getInstoreSales($date, $site)
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');

        return $this->reportService->getPaymentMethodData($formattedDate, $formattedDate, $site)['instore'];
    }

    // HITUNG SALES OJOL (Shopee, GoFood, Grab)
    // 🔥 Delegasi penuh ke SalesReportService::getOjolBreakdown() —
    // satu-satunya tempat total_ojol dihitung di seluruh aplikasi.
    public function generateOjol($date, $site)
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');

        return $this->reportService->getOjolBreakdown($formattedDate, $formattedDate, $site);
    }

    // 🔥 DIPINDAH ke SalesReportService::getFoodBreakdown()
    public function getFoodBreakdown($date, $site)
    {
        return $this->reportService->getFoodBreakdown(Carbon::parse($date)->format('Y-m-d'), $site);
    }

    // 🔥 Sudah delegasi ke getSnackData() sejak sebelumnya — tetap di sini
    // karena cuma rename key (quantity -> qty), bukan logic query baru.
    private function getFoodSummary($date, $site)
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');

        $snack = $this->reportService->getSnackData($formattedDate, $formattedDate, $site);

        return [
            'qty'   => $snack['quantity'],
            'sales' => $snack['sales'],
        ];
    }

    public function generateAndSave($date, $site)
    {
        $data = $this->generate($date, $site);

        DB::transaction(function () use ($data, $date, $site) {

            $laporan = LaporanHarian::updateOrCreate(
                [
                    'site' => $site,
                    'trans_date' => $date
                ],
                [
                    'store' => Penjualan::where('site_code', $site)
                                ->value('site_description') ?? '-',
                    'year' => date('Y', strtotime($date)),
                    'status' => 'submitted'
                ]
            );

            $laporan->metrics()->delete();

            $this->saveMetrics($laporan, $data);
        });
    }

    private function saveMetrics($laporan, $data)
    {
        $laporan->metrics()->createMany([
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'SALES','value'=>$data['sales']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'TC','value'=>$data['tc']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'AVG_CHECK','value'=>$data['avg_check']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'MTD','metric'=>'TIME_PROGRESS','value'=>$data['daily_progress']],
            ['category'=>'ALL', 'channel'=>'ALL', 'period'=>'MTD', 'metric'=>'MTD_SALES', 'value'=>$data['mtd_sales']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'MTD','metric'=>'TARGET_SALES','value'=>$data['target_sales']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'MTD','metric'=>'ACHIEVEMENT','value'=> $data['achievement_mtd']],

            ['category'=>'ALL','channel'=>'ALL','period'=>'LW','metric'=>'SALES_LW','value'=>$data['last_week_sales']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LW','metric'=>'TC_LW','value'=>$data['last_week_tc']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LW','metric'=>'AVG_CHECK_LW','value'=>$data['last_week_average_check']],

            ['category'=>'ALL','channel'=>'ALL','period'=>'LW','metric'=>'GROWTH_LW','value'=>$data['last_week_growth']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LW','metric'=>'GROWTH_TC_LW','value'=>$data['last_week_growth_tc']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LW','metric'=>'GROWTH_AVG_LW','value'=>$data['last_week_growth_avg']],

            ['category'=>'ALL','channel'=>'ALL','period'=>'LM','metric'=>'SALES_LM','value'=>$data['last_month_sales']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LM','metric'=>'TC_LM','value'=>$data['last_month_tc']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LM','metric'=>'AVG_CHECK_LM','value'=>$data['last_month_average_check']],

            ['category'=>'ALL','channel'=>'ALL','period'=>'LM','metric'=>'GROWTH_LM','value'=>$data['last_month_growth']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LM','metric'=>'GROWTH_TC_LM','value'=>$data['last_month_growth_tc']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'LM','metric'=>'GROWTH_AVG_LM','value'=>$data['last_month_growth_avg']],

            ['category'=>'OJOL','channel'=>'ALL','period'=>'LW','metric'=>'SALES_LW','value'=>$data['ojol_lw']],
            ['category'=>'OJOL','channel'=>'ALL','period'=>'LM','metric'=>'SALES_LM','value'=>$data['ojol_lm']],

            ['category'=>'OJOL','channel'=>'ALL','period'=>'LW','metric'=>'GROWTH_LW','value'=>$data['ojol_growth_lw']],
            ['category'=>'OJOL','channel'=>'ALL','period'=>'LM','metric'=>'GROWTH_LM','value'=>$data['ojol_growth_lm']],

            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'LARGE','value'=>$data['large']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'REGULER','value'=>$data['regular']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'SMALL','value'=>$data['small']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'COLD','value'=>$data['cold']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'HOT','value'=>$data['hot']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'BUTTERFLY','value'=>$data['butterfly']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'PC','value'=>$data['pc']],
            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'EXTRA_LARGE','value'=>$data['extra_large']],

            ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'TOTAL_CUP','value'=>$data['total_cup']],

            ['category'=>'OJOL','channel'=>'SHOPEE','period'=>'CURRENT','metric'=>'SALES','value'=>$data['shopee']],
            ['category'=>'OJOL','channel'=>'GOJEK','period'=>'CURRENT','metric'=>'SALES','value'=>$data['gofood']],
            ['category'=>'OJOL','channel'=>'GRAB','period'=>'CURRENT','metric'=>'SALES','value'=>$data['grab']],
            ['category'=>'OJOL', 'channel'=>'ALL', 'period'=>'CURRENT', 'metric'=>'TOTAL_OJOL', 'value'=>$data['total_ojol']],
            ['category'=>'OJOL','channel'=>'ALL','period'=>'CURRENT','metric'=>'TC','value'=>$data['ojol_tc']],

            ['category'=>'INSTORE','channel'=>'INSTORE','period'=>'CURRENT','metric'=>'SALES','value'=>$data['InStoreSales']],
        ]);

        foreach ($data['food'] as $key => $val) {
            $laporan->metrics()->create([
                'category'=>'FOOD',
                'channel'=>strtoupper($key),
                'period'=>'CURRENT',
                'metric'=>'SALES',
                'value'=>$val['sales']
            ]);

            $laporan->metrics()->create([
                'category'=>'FOOD',
                'channel'=>strtoupper($key),
                'period'=>'CURRENT',
                'metric'=>'QTY',
                'value'=>$val['qty']
            ]);
        }

        $totalQty = 0;

        foreach ($data['food'] as $key => $val) {
            if (strtoupper($key) === 'FREE') continue;
            $totalQty += $val['qty'];
        }

        $laporan->metrics()->create([
            'category' => 'FOOD',
            'channel'  => 'ALL',
            'period'   => 'CURRENT',
            'metric'   => 'SALES',
            'value'    => $data['food_total_sales']
        ]);

        $laporan->metrics()->create([
            'category' => 'FOOD',
            'channel'  => 'ALL',
            'period'   => 'CURRENT',
            'metric'   => 'QTY',
            'value'    => $totalQty
        ]);
    }
}