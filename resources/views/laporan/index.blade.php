@extends('layouts.app')

@section('content')

<div style="max-width:1200px; margin:auto;">

    <a href="{{ url('/dashboard/daftar-laporan') }}" 
       style="display:inline-block; margin-bottom:15px; padding:8px 14px; background:#16a34a; color:white; text-decoration:none; border-radius:6px;">
        ← Kembali
    </a>

    <h2 style="margin-bottom:20px;">Laporan Tanggal: {{ $date }}</h2>

    @foreach ($laporan as $store)

        @php
            $metrics = $store->metrics;

            $achievement = optional($metrics->where('metric', 'ACHIEVEMENT')->first())->value;
            $timeProgress = optional($metrics->where('metric', 'TIME_PROGRESS')->first())->value;

            $sales = optional($metrics->where('metric', 'SALES')->where('category','ALL')->first())->value;
            $tc = optional($metrics->where('metric', 'TC')->first())->value;
            $avg = optional($metrics->where('metric', 'AVG_CHECK')->first())->value;
            $cup = optional($metrics->where('metric', 'TOTAL_CUP')->first())->value;

            $mtd = optional($metrics->where('metric', 'MTD_SALES')->first())->value;
            $target = optional($metrics->where('metric', 'TARGET_SALES')->first())->value;

            $shopee = optional($metrics->where('channel','SHOPEE')->first())->value;
            $gojek = optional($metrics->where('channel','GOJEK')->first())->value;
            $grab = optional($metrics->where('channel','GRAB')->first())->value;
            $ojolTotal = optional($metrics->where('category','OJOL')->where('channel','ALL')->where('metric','TOTAL_OJOL')->first())->value;
            $lastWeek = optional($metrics->where('metric','SALES_LW')->where('period','LW')->first())->value;
            $lwTC = optional($metrics->where('metric','TC_LW')->where('period','LW')->first())->value;
            $lwAvg = optional($metrics->where('metric','AVG_CHECK_LW')->where('period','LW')->first())->value;

            $lastWeekGrowth = optional($metrics->where('metric','GROWTH_LW')->where('period','LW')->first())->value;
            $lwGrowthTC = optional($metrics->where('metric','GROWTH_TC_LW')->where('period','LW')->first())->value;
            $lwGrowthAvg = optional($metrics->where('metric','GROWTH_AVG_LW')->where('period','LW')->first())->value;

            $lastMonth = optional($metrics->where('metric','SALES_LM')->where('period','LM')->first())->value;
            $lmTC = optional($metrics->where('metric','TC_LM')->where('period','LM')->first())->value;
            $lmAvg = optional($metrics->where('metric','AVG_CHECK_LM')->where('period','LM')->first())->value;
 
            $lastMonthGrowth = optional($metrics->where('metric','GROWTH_LM')->where('period','LM')->first())->value;
            $lmGrowthTC = optional($metrics->where('metric','GROWTH_TC_LM')->where('period','LM')->first())->value;
            $lmGrowthAvg = optional($metrics->where('metric','GROWTH_AVG_LM')->where('period','LM')->first())->value;

            $lwColor = $lastWeekGrowth >= 0 ? 'green' : 'red';
            $lmColor = $lastMonthGrowth >= 0 ? 'green' : 'red';

            $ojolLW = optional($metrics->where('category','OJOL')->where('metric','SALES_LW')->first())->value;
            $ojolLM = optional($metrics->where('category','OJOL')->where('metric','SALES_LM')->first())->value;

            $ojolGrowthLW = optional($metrics->where('category','OJOL')->where('metric','GROWTH_LW')->first())->value;
            $ojolGrowthLM = optional($metrics->where('category','OJOL')->where('metric','GROWTH_LM')->first())->value;

            $ojolColorLW = $ojolGrowthLW >= 0 ? 'green' : 'red';
            $ojolColorLM = $ojolGrowthLM >= 0 ? 'green' : 'red';

            $instore = optional($metrics->where('category','INSTORE')->first())->value;

            $color = ($achievement >= $timeProgress) ? 'green' : 'red';
            $status = ($achievement >= $timeProgress) ? 'ON TRACK' : 'BEHIND';
        @endphp

        <div style="background:white; border-radius:12px; padding:20px; margin-bottom:25px; box-shadow:0 3px 10px rgba(0,0,0,0.08);">

            {{-- 🔥 HEADER --}}
            <h3 style="margin-bottom:15px;">
                {{ $store->store }} ({{ $store->site }})
            </h3>

            {{-- 🔥 KPI BOX --}}
            <div style="display:flex; gap:25px; margin-bottom:15px; flex-wrap:wrap;">

                <div>
                    <div style="font-size:12px; color:#888;">Achievement</div>
                    <div style="font-weight:bold; color:{{ $color }}">
                        {{ number_format($achievement,1) }}%
                    </div>
                </div>
                
                <div>
                    <div style="font-size:12px; color:#888;">Time Progress</div>
                    <div style="font-weight:bold; color:#2563eb;">
                        {{ number_format($timeProgress,1) }}%
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#888;">Status</div>
                    <div style="font-weight:bold; color:{{ $color }}">
                        {{ $status }}
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#888;">MTD</div>
                    <div style="font-weight:bold;">
                        {{ number_format($mtd,0,',','.') }}
                    </div>
                </div>

                <div>
                    <div style="font-size:12px; color:#888;">Target</div>
                    <div style="font-weight:bold;">
                        {{ number_format($target,0,',','.') }}
                    </div>
                </div>

            </div>

            {{-- 🔥 SUMMARY --}}
            <div style="margin-bottom:15px; font-size:14px;">
                Sales This Weeks : <b>{{ number_format($sales,0,',','.') }}</b> |
                TC: <b>{{ $tc }}</b> |
                Avg: <b>{{ number_format($avg,0,',','.') }}</b> |
                Cup: <b>{{ $cup }}</b>
            </div>


            <table width="100%" style="margin-bottom:15px; border-collapse:collapse; font-size:14px; text-align:center;">
    
                <thead style="background:#f3f4f6;">
                    <tr>
                        <th rowspan="2" align="left">Metric</th>
                        <th colspan="2">
                            Last Week <br>
                            <span style="font-size:12px; color:#666;">{{ $lwDate }}</span>
                        </th>
                        
                        <th colspan="2">
                            Last Month <br>
                            <span style="font-size:12px; color:#666;">{{ $lmDate }}</span>
                        </th>
                    </tr>
                    <tr>
                        <th>Sales</th>
                        <th>%</th>
                        <th>Sales</th>
                        <th>%</th>
                    </tr>
                </thead>
                
                <tbody>
                    
                    {{-- 🔥 SALES --}}
                    <tr style="border-bottom:1px solid #eee;">
                        <td align="left"><b>Sales</b></td>
                        <td>{{ number_format($lastWeek,0,',','.') }}</td>
                        <td style="color:{{ $lwColor }}">
                            {{ number_format($lastWeekGrowth,1) }}%
                        </td>
                        <td>{{ number_format($lastMonth,0,',','.') }}</td>
                        <td style="color:{{ $lmColor }}">
                            {{ number_format($lastMonthGrowth,1) }}%
                        </td>
                    </tr>
            
                    {{-- 🔥 TC --}}
                    @php
                        $lwTC = optional($metrics->where('metric','TC_LW')->where('period','LW')->first())->value;
                        $lmTC = optional($metrics->where('metric','TC_LM')->where('period','LM')->first())->value;
            
                        $lwGrowthTC = $lwTC > 0 ? round((($tc - $lwTC)/$lwTC)*100,1) : 0;
                        $lmGrowthTC = $lmTC > 0 ? round((($tc - $lmTC)/$lmTC)*100,1) : 0;
            
                        $tcColorLW = $lwGrowthTC >= 0 ? 'green' : 'red';
                        $tcColorLM = $lmGrowthTC >= 0 ? 'green' : 'red';
                    @endphp
            
                    <tr style="border-bottom:1px solid #eee;">
                        <td align="left"><b>TC</b></td>
                        <td>{{ $lwTC }}</td>
                        <td style="color:{{ $tcColorLW }}">
                            {{ number_format($lwGrowthTC,1) }}%
                        </td>
                        <td>{{ $lmTC }}</td>
                        <td style="color:{{ $tcColorLM }}">
                            {{ number_format($lmGrowthTC,1) }}%
                        </td>
                    </tr>
            
                    {{-- 🔥 AVG CHECK --}}
                    @php
                        $lwAvg = optional($metrics->where('metric','AVG_CHECK_LW')->where('period','LW')->first())->value;
                        $lmAvg = optional($metrics->where('metric','AVG_CHECK_LM')->where('period','LM')->first())->value;
            
                        $lwAvgGrowth = $lwAvg > 0 ? round((($avg - $lwAvg)/$lwAvg)*100,1) : 0;
                        $lmAvgGrowth = $lmAvg > 0 ? round((($avg - $lmAvg)/$lmAvg)*100,1) : 0;
            
                        $avgColorLW = $lwAvgGrowth >= 0 ? 'green' : 'red';
                        $avgColorLM = $lmAvgGrowth >= 0 ? 'green' : 'red';
                    @endphp
            
                    <tr style="border-bottom:1px solid #eee;">
                        <td align="left"><b>Avg Check</b></td>
                        <td>{{ number_format($lwAvg,0,',','.') }}</td>
                        <td style="color:{{ $avgColorLW }}">
                            {{ number_format($lwAvgGrowth,1) }}%
                        </td>
                        <td>{{ number_format($lmAvg,0,',','.') }}</td>
                        <td style="color:{{ $avgColorLM }}">
                            {{ number_format($lmAvgGrowth,1) }}%
                        </td>
                    </tr>

                    <tr style="border-bottom:1px solid #eee;">
                        <td align="left"><b>OJOL</b></td>
                        <td>{{ number_format($ojolLW,0,',','.') }}</td>
                        <td style="color:{{ $ojolColorLW }}">
                            {{ number_format($ojolGrowthLW,1) }}%
                        </td>
                        <td>{{ number_format($ojolLM,0,',','.') }}</td>
                        <td style="color:{{ $ojolColorLM }}">
                            {{ number_format($ojolGrowthLM,1) }}%
                        </td>
                    </tr>
            
                </tbody>
            </table>
            
          

            {{-- 🔥 CHANNEL --}}
            <table width="100%" style="margin-bottom:15px; border-collapse:collapse; font-size:14px;">
                <thead style="background:#f3f4f6;">
                    <tr>
                        <th align="left">Channel</th>
                        <th align="right">Sales</th>
                    </tr>
                </thead>
                <tbody>
            
                    <tr style="border-bottom:1px solid #eee;">
                        <td>Shopee</td>
                        <td align="right">{{ number_format($shopee,0,',','.') }}</td>
                    </tr>
            
                    <tr style="border-bottom:1px solid #eee;">
                        <td>Gojek</td>
                        <td align="right">{{ number_format($gojek,0,',','.') }}</td>
                    </tr>
            
                    <tr style="border-bottom:1px solid #eee;">
                        <td>Grab</td>
                        <td align="right">{{ number_format($grab,0,',','.') }}</td>
                    </tr>

                    <tr style="border-bottom:1px solid #eee;">
                        <td><b>Total Ojol</b></td>
                        <td align="right"><b>{{ number_format($ojolTotal,0,',','.') }}</b></td>
                    </tr>
            
                    <tr>
                        <td><b>Instore</b></td>
                        <td align="right"><b>{{ number_format($instore,0,',','.') }}</b></td>
                    </tr>
            
                </tbody>
            </table>

                {{-- 🔥 FOOD TABLE --}}
        <table width="100%" cellpadding="6" style="border-collapse: collapse;">
            <thead style="background:#f3f4f6;">
                <tr>
                    <th align="left">Food</th>
                    <th align="right">Qty</th>
                    <th align="right">Sales</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($metrics->where('category','FOOD')->groupBy('channel') as $channel => $items)

                        @php
                        $qty       = optional($items->where('metric','QTY')->first())->value;
                        $salesFood = optional($items->where('metric','SALES')->first())->value;
                    
                        // 🔥 debug sementara
                        // dd($channel, $salesFood);
                    
                        $salesDisplay = strtoupper(trim($channel)) === 'ALL'
                            ? $salesFood
                            : intval(($salesFood * 10) / 11);
                    @endphp
                    <tr style="border-bottom:1px solid #eee;">
                        <td>{{ $channel }}</td>
                        <td align="right">{{ number_format($qty, 2) }}</td>
                        <td align="right">{{ number_format($salesDisplay, 0, ',', '.') }}</td>
                    </tr>

                @endforeach
            </tbody>
        </table>

        </div>

    @endforeach

</div>

@endsection