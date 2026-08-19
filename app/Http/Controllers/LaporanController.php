<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenerateLaporanService;
use App\Models\LaporanHarian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function generate($date, GenerateLaporanService $service)
{
    $result = DB::transaction(function () use ($date, $service) {

        $stores = \App\Models\Penjualan::whereDate('created_date', $date)
            ->select('site_code', 'site_description')
            ->distinct()
            ->get();

        $laporanList = [];

        foreach ($stores as $store) {

            //  ambil data per store
            $data = $service->generate($date, $store->site_code);

            $laporan = LaporanHarian::updateOrCreate(
                [
                    'site' => $store->site_code,
                    'trans_date' => $date
                ],
                [
                    'store' => $store->site_description,
                    'year' => date('Y', strtotime($date)),
                    'status' => 'submitted' 
                ]
            );
            // reset metrics
            $laporan->metrics()->delete();


            
            //  METRICS UTAMA
          
            $laporan->metrics()->createMany([
                ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'SALES','value'=>$data['sales']],
                ['category'=>'ALL','channel'=>'ALL','period'=>'CURRENT','metric'=>'TC','value'=>$data['tc']],
                ['category'=>'ALL', 'channel'=>'ALL', 'period'=>'CURRENT', 'metric'=>'AVG_CHECK', 'value'=>$data['avg_check']],
                ['category'=>'ALL','channel'=>'ALL','period'=>'MTD','metric'=>'TIME_PROGRESS','value'=>$data['daily_progress']],
                ['category'=>'ALL','channel'=>'ALL','period'=>'MTD','metric'=>'MTD_SALES','value'=>$data['mtd_sales']],
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

                ['category'=>'OJOL', 'channel'=>'ALL', 'period'=>'CURRENT', 'metric'=>'TOTAL_OJOL', 'value'=>$data['total_ojol']],
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

                ['category'=>'INSTORE','channel'=>'INSTORE','period'=>'CURRENT','metric'=>'SALES','value'=>$data['InStoreSales']],
            ]);

            //  FOOD BREAKDOWN
            foreach ($data['food'] as $key => $val) {

                $laporan->metrics()->create([
                    'category' => 'FOOD',
                    'channel' => strtoupper($key),
                    'period' => 'CURRENT',
                    'metric' => 'SALES',
                    'value' => $val['sales']
                ]);

                $laporan->metrics()->create([
                    'category' => 'FOOD',
                    'channel' => strtoupper($key),
                    'period' => 'CURRENT',
                    'metric' => 'QTY',
                    'value' => $val['qty']
                ]);
            }

            //  TOTAL FOOD (pakai summary + extra)
            $totalQty = 0;
            $totalSales = 0;
            
            foreach ($data['food'] as $key => $val) {
            
                // ❌ skip FREE
                if (strtoupper($key) === 'FREE') {
                    continue;
                }
            
                $totalQty += $val['qty'];
                $totalSales += $val['sales'];
            }

            $laporan->metrics()->create([
                'category' => 'FOOD',
                'channel' => 'ALL',
                'period' => 'CURRENT',
                'metric' => 'SALES',
                'value' => $totalSales
            ]);

            $laporan->metrics()->create([
                'category' => 'FOOD',
                'channel' => 'ALL',
                'period' => 'CURRENT',
                'metric' => 'QTY',
                'value' => $totalQty
            ]);

            $laporanList[] = $laporan->load('metrics');
        }

        return $laporanList;
    });

    return redirect()->route('laporan.show', $date)
    ->with('success', 'Laporan berhasil digenerate');

    
}

public function show($date, GenerateLaporanService $service)
{
    $query = LaporanHarian::with('metrics')
        ->whereDate('trans_date', $date);

    // KASIR hanya bisa lihat store sendiri
    if (
        Auth::user()->role == 'kasir' ||
        Auth::user()->role == 'store_manager'
    ) {

        $site = Auth::user()->site_code;

        $query->where('site', $site);
    }

    // AREA MANAGER bisa lihat semua store
    $laporan = $query->get();

    // kalau kosong dan role kasir
    if ($laporan->isEmpty() && Auth::user()->role == 'kasir') {

        $service->generateAndSave($date, Auth::user()->site_code);

        $laporan = LaporanHarian::with('metrics')
            ->where('site', Auth::user()->site_code)
            ->whereDate('trans_date', $date)
            ->get();
    }

    // TANGGAL
    $lwDate = Carbon::parse($date)->subWeek()->format('d M Y');
    $lmDate = Carbon::parse($date)->subDays(28)->format('d M Y');

    return view('laporan.index', [
        'laporan' => $laporan,
        'date' => $date,
        'lwDate' => $lwDate,
        'lmDate' => $lmDate,
    ]);
}
}