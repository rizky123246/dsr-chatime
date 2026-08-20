<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\Target;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class SalesReportService
{
    
    private const TAX_DIVISOR = 1.1;

    // ─── Helper konversi ────────────────────────────────────────────────

    public function toNet(float|int $grossAmount): float
    {
        return round($grossAmount / self::TAX_DIVISOR);
    }

    public function toNetInt(float|int $grossAmount): int
    {
        return intval(($grossAmount * 10) / 11);
    }

    
    public function dayRange(string $startDate, string $endDate): array
    {
        return [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ];
    }

   
    private function applySiteScope($query, string $column, string|array|null $siteCode)
    {
        if (is_array($siteCode)) {
            $query->whereIn($column, $siteCode);
        } elseif ($siteCode !== null) {
            $query->where($column, $siteCode);
        }

        return $query;
    }

    // ─── Sales Data ─────────────────────────────────────────────────────

    public function getSalesData(string $startDate, string $endDate, string|array|null $siteCode = null): array
    {
        try {
            $range = $this->dayRange($startDate, $endDate);
            $query = Penjualan::whereBetween('created_date', $range)
                ->where('void', 0);

            $this->applySiteScope($query, 'site_code', $siteCode);

            $sales = $query->selectRaw('
                    COUNT(DISTINCT CASE WHEN net_price > 0 THEN receipt_no END) as total_transactions,
                    SUM(net_price) as total_revenue
                ')
                ->first();

            $grossRevenue = $sales->total_revenue ?? 0;

            return [
                'total_transactions' => $sales->total_transactions ?? 0,
                'total_revenue'      => $grossRevenue,
                'net_revenue'        => $this->toNet($grossRevenue),
            ];
        } catch (\Exception $e) {
            return [
                'total_transactions' => 0,
                'total_revenue'      => 0,
                'net_revenue'        => 0,
            ];
        }
    }

   
    public function getSalesDataByStore(string $startDate, string $endDate, ?array $siteCodes = null)
    {
        $query = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
            ->where('void', 0);

        if ($siteCodes !== null) {
            $query->whereIn('site_code', $siteCodes);
        }

        return $query->selectRaw('
                site_code,
                COUNT(DISTINCT CASE WHEN net_price > 0 THEN receipt_no END) as total_transactions,
                SUM(net_price) as total_revenue
            ')
            ->groupBy('site_code')
            ->get()
            ->keyBy('site_code')
            ->map(function ($row) {
                return [
                    'total_transactions' => (int) $row->total_transactions,
                    'total_revenue'      => (float) $row->total_revenue,
                    'net_revenue'        => $this->toNet($row->total_revenue),
                ];
            });
    }

    // ─── Payment Method Data ────────────────────────────────────────────

    public function getPaymentMethodData(string $startDate, string $endDate, ?string $siteCode = null): array
    {
        try {
           
            $ojolBreakdown = $this->getOjolBreakdown($startDate, $endDate, $siteCode);

            $receiptQuery = DB::table('penjualans')
                ->whereBetween('created_date', $this->dayRange($startDate, $endDate))
                ->where('void', 0);

            if ($siteCode !== null) {
                $receiptQuery->where('site_code', $siteCode);
            }

            $receipts = $receiptQuery->distinct()->pluck('receipt_no');

            $payments = DB::table('pembayarans')
                ->whereIn('receipt_no', $receipts)
                ->selectRaw('
                    mop_name as payment_method,
                    SUM(amount) as total_amount,
                    COUNT(DISTINCT receipt_no) as transaction_count
                ')
                ->groupBy('mop_name')
                ->get();

            $rawInstore = 0;
            $instoreTrx = 0; $totalTrx = 0;

            foreach ($payments as $payment) {
                $totalTrx += $payment->transaction_count;

               
                $method = strtoupper($payment->payment_method);
                $isOjol = str_contains($method, 'SHOPEEFOOD')
                    || str_contains($method, 'GOFOOD')
                    || str_contains($method, 'GRAB');

                if (!$isOjol) {
                    $rawInstore += $payment->total_amount;
                    $instoreTrx += $payment->transaction_count;
                }
            }

            $instore = $this->toNetInt($rawInstore);
            $ojol    = $ojolBreakdown['total_ojol'];
            $ojolTrx = $ojolBreakdown['ojol_tc'];
            $total   = $instore + $ojol;

            return [
                'instore' => $instore,
                'ojol'    => $ojol,
                'total'   => $total,
                'instore_transactions' => $instoreTrx,
                'ojol_transactions'    => $ojolTrx,
                'total_transactions'   => $totalTrx,
                'instore_percentage' => $total > 0 ? ($instore / $total) * 100 : 0,
                'ojol_percentage'    => $total > 0 ? ($ojol / $total) * 100 : 0,
                'instore_transaction_percentage' => $totalTrx > 0 ? ($instoreTrx / $totalTrx) * 100 : 0,
                'ojol_transaction_percentage'    => $totalTrx > 0 ? ($ojolTrx / $totalTrx) * 100 : 0,
            ];
        } catch (\Exception $e) {
            return [
                'instore' => 0, 'ojol' => 0, 'total' => 0,
                'instore_transactions' => 0, 'ojol_transactions' => 0, 'total_transactions' => 0,
                'instore_percentage' => 0, 'ojol_percentage' => 0,
                'instore_transaction_percentage' => 0, 'ojol_transaction_percentage' => 0,
            ];
        }
    }

    
    public function getOjolBreakdown(string $startDate, string $endDate, ?string $siteCode = null): array
    {
        try {
            $receiptQuery = DB::table('penjualans')
                ->whereBetween('created_date', $this->dayRange($startDate, $endDate))
                ->where('void', 0);

            if ($siteCode !== null) {
                $receiptQuery->where('site_code', $siteCode);
            }

            $receipts = $receiptQuery->distinct()->pluck('receipt_no');

            $result = DB::table('pembayarans')
                ->selectRaw("
                    SUM(CASE WHEN UPPER(mop_name) LIKE '%SHOPEEFOOD%' THEN amount ELSE 0 END) as shopee,
                    SUM(CASE WHEN UPPER(mop_name) LIKE '%GOFOOD%' THEN amount ELSE 0 END) as gofood,
                    SUM(CASE WHEN UPPER(mop_name) LIKE '%GRAB%' THEN amount ELSE 0 END) as grab,
                    COUNT(DISTINCT CASE WHEN UPPER(mop_name) LIKE '%SHOPEEFOOD%'
                                      OR UPPER(mop_name) LIKE '%GOFOOD%'
                                      OR UPPER(mop_name) LIKE '%GRAB%' THEN receipt_no END) as ojol_tc
                ")
                ->whereIn('receipt_no', $receipts)
                ->first();

            $rawShopee = (float) ($result->shopee ?? 0);
            $rawGofood = (float) ($result->gofood ?? 0);
            $rawGrab   = (float) ($result->grab ?? 0);
            $rawTotal  = $rawShopee + $rawGofood + $rawGrab;

            return [
                'shopee'     => $this->toNetInt($rawShopee),
                'gofood'     => $this->toNetInt($rawGofood),
                'grab'       => $this->toNetInt($rawGrab),
                'total_ojol' => $this->toNetInt($rawTotal),
                'ojol_tc'    => (int) ($result->ojol_tc ?? 0),
            ];
        } catch (\Exception $e) {
            return ['shopee' => 0, 'gofood' => 0, 'grab' => 0, 'total_ojol' => 0, 'ojol_tc' => 0];
        }
    }

    // ─── MTD Progress ───────────────────────────────────────────────────

    
    public function getMonthlyTarget(int $year, int $month, string|array|null $siteCode = null): float
    {
        $query = Target::where('year', $year)->where('month', $month);

        $this->applySiteScope($query, 'site_code', $siteCode);

        return (float) ($query->sum('target_sales') ?? 0);
    }

   
    public function getMtdProgress(string|array|null $siteCode = null, ?string $referenceDate = null): array
    {
        try {
            $refDate      = $referenceDate ? Carbon::parse($referenceDate) : Carbon::now();
            $startOfMonth = $refDate->copy()->startOfMonth();
            $queryEnd     = $referenceDate ? $refDate->copy()->endOfDay() : $refDate;

            $currentDay  = $refDate->day;
            $daysInMonth = $refDate->daysInMonth;

            $mtdQuery = Penjualan::whereBetween('created_date', [$startOfMonth, $queryEnd])
                ->where('void', 0);

            $this->applySiteScope($mtdQuery, 'site_code', $siteCode);

            $grossMtd = $mtdQuery->sum('net_price');
            $mtdSales = $this->toNetInt($grossMtd);

            $target = $this->getMonthlyTarget($refDate->year, $refDate->month, $siteCode);

            $achievement  = $target > 0 ? round(($mtdSales / $target) * 100, 1) : 0;
            $timeProgress = round(($currentDay / $daysInMonth) * 100, 1);

            return [
                'mtd_sales'     => $mtdSales,
                'target'        => $target,
                'achievement'   => $achievement,
                'time_progress' => $timeProgress,
                'current_day'   => $currentDay,
                'days_in_month' => $daysInMonth,
                'month_name'    => $refDate->translatedFormat('F Y'),
                'on_track'      => $achievement >= $timeProgress,
            ];
        } catch (\Exception $e) {
            return [
                'mtd_sales' => 0, 'target' => 0, 'achievement' => 0, 'time_progress' => 0,
                'current_day' => 0, 'days_in_month' => 0, 'month_name' => '-', 'on_track' => false,
            ];
        }
    }

    // ─── Cup Breakdown ──────────────────────────────────────────────────

    
    public function getCupBreakdown(string $date, ?string $siteCode = null)
    {
        $query = Penjualan::selectRaw("
            created_date,
            SUM( CASE WHEN UPPER(TRIM(merchandise_name)) = 'LARGE'
            AND UPPER(TRIM(product_group_name)) <> 'TOPPING'
            THEN quantity ELSE 0 END) AS large,

            SUM(CASE WHEN size = 'R' AND type = 'drink' THEN quantity ELSE 0 END) as regular,
            SUM(CASE WHEN size = 'S' AND type = 'drink' THEN quantity ELSE 0 END) as small,
            SUM(CASE WHEN size = 'C' AND type = 'drink' THEN quantity ELSE 0 END) as cold,
            SUM(CASE WHEN size = 'H' AND type = 'drink' THEN quantity ELSE 0 END) as hot,
            SUM(CASE WHEN size = 'BC' AND type = 'drink' THEN quantity ELSE 0 END) as butterfly,
            SUM(CASE WHEN size = 'PC' AND type = 'drink' THEN quantity ELSE 0 END) as pc,
            SUM(CASE WHEN size = 'XL' AND type = 'drink' THEN quantity ELSE 0 END) as extra_large
        ")
        ->whereDate('created_date', $date)
        ->where('void', 0)
        ->where('type', 'drink');

        if ($siteCode !== null) {
            $query->where('site_code', $siteCode);
        }

        return $query->groupBy('created_date')->first();
    }

    // ─── Food Breakdown ─────────────────────────────────────────────────

   
    public function getFoodBreakdown(string $date, ?string $siteCode = null): array
    {
        $categories = \App\Models\FoodCategory::with('items')->get();

        $allArticles = $categories
            ->flatMap(fn($cat) => $cat->items->pluck('article_code'))
            ->map(fn($code) => trim((string)$code))
            ->unique()
            ->toArray();

        $rowsQuery = Penjualan::whereDate('created_date', $date)
            ->where('void', 0)
            ->where('type', 'foods')
            ->whereIn(DB::raw('TRIM(article_code)'), $allArticles);

        if ($siteCode !== null) {
            $rowsQuery->where('site_code', $siteCode);
        }

        $rows = $rowsQuery
            ->select(
                'article_code',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(net_price) as sales')
            )
            ->groupBy('article_code')
            ->get()
            ->keyBy('article_code');

        $result = [];

        foreach ($categories as $category) {
            $qty      = 0;
            $rawSales = 0;

            foreach ($category->items as $item) {
                if (isset($rows[$item->article_code])) {
                    $qty      += $rows[$item->article_code]->qty;
                    $rawSales += $rows[$item->article_code]->sales;
                }
            }

            $result[$category->name] = [
                'label' => $category->label,
                'qty'   => $qty,
                'sales' => $this->toNet($rawSales),
            ];
        }

        $uncategorizedQuery = Penjualan::whereDate('created_date', $date)
            ->where('void', 0)
            ->where('type', 'foods')
            ->whereNotIn(DB::raw('TRIM(article_code)'), $allArticles);

        if ($siteCode !== null) {
            $uncategorizedQuery->where('site_code', $siteCode);
        }

        $uncategorized = $uncategorizedQuery
            ->select(
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(net_price) as sales')
            )
            ->first();

        $result['uncategorized'] = [
            'label' => 'Uncategorized',
            'qty' => $uncategorized->qty ?? 0,
            'sales' => $this->toNet($uncategorized->sales ?? 0),
        ];

        return $result;
    }

    // ─── Snack Data ─────────────────────────────────────────────────────

    private function snackScope($query)
    {
        return $query->where(function ($q) {
            $q->where('product_group_name', 'KOREAN STREET FOOD')
              ->orWhere(function ($q2) {
                  $q2->where('product_group_name', 'Topping')
                     ->where('merchandise_name', 'Food')
                     ->where('net_price', '>', 0);
              });
        });
    }

    public function getSnackData(string $startDate, string $endDate, ?string $siteCode = null, float $netSales = 0): array
    {
        try {
            $base = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
                ->where('void', 0);

            if ($siteCode !== null) {
                $base->where('site_code', $siteCode);
            }

            $snacks = $this->snackScope($base)
                ->selectRaw('SUM(quantity) as total_quantity, SUM(net_price) as total_sales')
                ->first();

            $grossSales    = $snacks->total_sales ?? 0;
            $snackNetSales = $this->toNetInt($grossSales);

            $targetSales = round($netSales * 0.30);

            $contributionPercentage = $netSales > 0
                ? round(($snackNetSales / $netSales) * 100, 1)
                : 0;

            $snackData = [
                'quantity'                => $snacks->total_quantity ?? 0,
                'sales'                   => $snackNetSales,
                'target_quantity'         => 400,
                'target_sales'            => $targetSales,
                'contribution_percentage' => $contributionPercentage,
                'top_products'            => $this->getTopSnackProducts($startDate, $endDate, $siteCode, 5),
            ];

            $snackData['quantity_acv'] = $snackData['target_quantity'] > 0
                ? ($snackData['quantity'] / $snackData['target_quantity']) * 100 : 0;
            $snackData['sales_acv'] = $snackData['target_sales'] > 0
                ? ($snackData['sales'] / $snackData['target_sales']) * 100 : 0;

            return $snackData;
        } catch (\Exception $e) {
            return [
                'quantity' => 0, 'sales' => 0, 'target_quantity' => 400, 'target_sales' => 0,
                'contribution_percentage' => 0, 'quantity_acv' => 0, 'sales_acv' => 0, 'top_products' => [],
            ];
        }
    }

    public function getTopSnackProducts(string $startDate, string $endDate, ?string $siteCode, int $limit = 5)
    {
        $base = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
            ->where('void', 0);

        if ($siteCode !== null) {
            $base->where('site_code', $siteCode);
        }

        return $this->snackScope($base)
            ->selectRaw('article_name, SUM(quantity) as qty, ROUND(SUM(net_price) * 10 / 11) as sales')
            ->groupBy('article_name')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }

    /**
     * Pencarian produk snack by keyword — dipakai endpoint AJAX search.
     * Pakai snackScope yang sama dengan getSnackData()/getTopSnackProducts(),
     * supaya hasil pencarian konsisten dengan angka yang tampil di dashboard.
     */
    public function searchSnackProducts(string $startDate, string $endDate, ?string $siteCode, string $keyword = '')
    {
        $base = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
            ->where('void', 0);

        if ($siteCode !== null) {
            $base->where('site_code', $siteCode);
        }

        return $this->snackScope($base)
            ->when($keyword !== '', fn ($q) => $q->where('article_name', 'LIKE', "%{$keyword}%"))
            ->selectRaw('article_name, SUM(quantity) as qty, ROUND(SUM(net_price) * 10 / 11) as sales')
            ->groupBy('article_name')
            ->orderByDesc('qty')
            ->get();
    }

    
    public function searchProducts(string $startDate, string $endDate, ?string $siteCode, ?string $search = null, int $limit = 10)
    {
        $query = DB::table('products')
            ->leftJoin('penjualans', function ($join) use ($startDate, $endDate, $siteCode) {
                $join->on('products.article_code', '=', 'penjualans.article_code')
                    ->whereBetween('penjualans.created_date', $this->dayRange($startDate, $endDate));

                if ($siteCode !== null) {
                    $join->where('penjualans.site_code', $siteCode);
                }
            })
            ->where(function ($q) {
                $q->whereRaw("UPPER(products.type) = 'DRINK'")
                  ->orWhere(function ($sub) {
                      $sub->whereRaw("UPPER(products.type) = 'FOODS'")
                          ->whereRaw("UPPER(products.series) != 'SAUCE'");
                  });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($s) use ($search) {
                    $s->where('products.article_code', 'like', "%{$search}%")
                      ->orWhere('products.name', 'like', "%{$search}%")
                      ->orWhere('products.series', 'like', "%{$search}%");
                });
            })
            ->select(
                'products.id',
                'products.article_code',
                'products.name',
                'products.type',
                'products.series',
                DB::raw('COALESCE(SUM(penjualans.quantity),0) as total_quantity'),
                DB::raw('ROUND(COALESCE(SUM(penjualans.net_price),0) / 1.1, 0) as total_sales')
            )
            ->groupBy(
                'products.id',
                'products.article_code',
                'products.name',
                'products.type',
                'products.series'
            )
            ->orderByDesc('total_quantity')
            ->limit($limit);

        return $query->get();
    }

    
    public function getArticleDetail(string $startDate, string $endDate, ?string $siteCode, string $articleCode): ?object
    {
        $query = DB::table('penjualan_details')
            ->selectRaw('
                article_name,
                SUM(quantity) as total_qty,
                ROUND(SUM(net_price) / 1.1, 0) as total_sales,
                COUNT(DISTINCT transaction_number) as total_transaction
            ')
            ->where('article_code', $articleCode)
            ->whereBetween('created_date', $this->dayRange($startDate, $endDate));

        if ($siteCode !== null) {
            $query->where('site_code', $siteCode);
        }

        return $query->groupBy('article_name')->first();
    }

    // ─── Top Products (drink/food, non-snack) ──────────────────────────

    public function getTopProducts(string $startDate, string $endDate, ?string $siteCode = null, int $limit = 6)
{
    $query = DB::table('products')
        ->whereIn(DB::raw('UPPER(products.type)'), ['DRINK', 'FOODS'])
        ->where(function ($q) {
            $q->whereRaw("UPPER(products.type) = 'DRINK'")
              ->orWhere(function ($sub) {
                  $sub->whereRaw("UPPER(products.type) = 'FOODS'")
                      ->whereRaw("UPPER(products.series) != 'SAUCE'");
              });
        })
        ->leftJoin('penjualans', function ($join) use ($startDate, $endDate, $siteCode) {
            $join->on('products.article_code', '=', 'penjualans.article_code')
                ->whereBetween('penjualans.created_date', $this->dayRange($startDate, $endDate))
                ->where('penjualans.void', 0); 

            if ($siteCode !== null) {
                $join->where('penjualans.site_code', $siteCode);
            }
        })
        ->select(
            'products.id',
            'products.article_code',
            DB::raw('COALESCE(MAX(products.name), products.article_code) as article_name'),
            'products.type',
            'products.series',
            DB::raw('COALESCE(SUM(penjualans.quantity),0) as total_quantity'),
            DB::raw('ROUND(COALESCE(SUM(penjualans.net_price),0) / 1.1, 0) as total_sales')
        )
        ->groupBy('products.id', 'products.article_code', 'products.name', 'products.type', 'products.series')
        ->havingRaw('SUM(penjualans.quantity) > 0')
        ->orderByDesc('total_quantity')
        ->limit($limit);

    return $query->get();
}

    public function getSnackDataByStore(string $startDate, string $endDate, ?array $siteCodes = null)
    {
        $base = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
            ->where('void', 0);

        if ($siteCodes !== null) {
            $base->whereIn('site_code', $siteCodes);
        }

        return $this->snackScope($base)
            ->selectRaw('
                site_code,
                site_description as store_name,
                SUM(quantity) as total_quantity,
                SUM(net_price) as gross_sales
            ')
            ->groupBy('site_code', 'site_description')
            ->orderByDesc('total_quantity')
            ->get()
            ->map(function ($r) {
                return [
                    'code'           => $r->site_code,
                    'store_name'     => $r->store_name,
                    'total_quantity' => $r->total_quantity ?? 0,
                    'total_sales'    => $this->toNetInt($r->gross_sales ?? 0),
                ];
            });
    }

    // ─── Top Beverages (khusus drink, dipakai Area Manager) ─────────────

   
    public function getTopBeverages(string $startDate, string $endDate, string|array|null $siteCode = null, int $limit = 20)
    {
        $query = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
            ->where('void', 0)
            ->where('type', 'drink');
    
        $this->applySiteScope($query, 'site_code', $siteCode);
    
        return $query->selectRaw('
                article_code,
                MAX(article_name) as article_name,
                SUM(quantity) as total_quantity,
                ROUND(SUM(net_price) * 10 / 11) as total_revenue
            ')
            ->groupBy('article_code')   
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    // ─── Promotions (dipakai Area Manager) ───────────────────────────────

    public function getTopPromotions(string $startDate, string $endDate, string|array|null $siteCode = null, int $limit = 10)
    {
        $query = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
            ->where('void', 0)
            ->whereNotNull('promotion_name')
            ->where('promotion_name', '!=', '');

        $this->applySiteScope($query, 'site_code', $siteCode);

        return $query->selectRaw('
                promotion_name,
                promotion_code,
                COUNT(*) as usage_count,
                SUM(quantity) as total_quantity,
                ROUND(SUM(promotion_amount)) as total_discount,
                ROUND(SUM(net_price) * 10 / 11) as total_sales
            ')
            ->groupBy('promotion_name', 'promotion_code')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }

    public function searchPromotions(string $startDate, string $endDate, string|array|null $siteCode = null, string $keyword = '')
    {
        $query = Penjualan::whereBetween('created_date', $this->dayRange($startDate, $endDate))
            ->where('void', 0)
            ->whereNotNull('promotion_name')
            ->where('promotion_name', '!=', '');

        $this->applySiteScope($query, 'site_code', $siteCode);

        return $query
            ->when($keyword !== '', fn ($q) => $q->where('promotion_name', 'LIKE', "%{$keyword}%"))
            ->selectRaw('
                promotion_name,
                promotion_code,
                COUNT(*) as usage_count,
                SUM(quantity) as total_quantity,
                ROUND(SUM(promotion_amount)) as total_discount,
                ROUND(SUM(net_price) * 10 / 11) as total_sales
            ')
            ->groupBy('promotion_name', 'promotion_code')
            ->orderByDesc('usage_count')
            ->get();
    }

    // ─── Helper ─────────────────────────────────────────────────────────

    public function calculatePercentageChange($oldValue, $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return (($newValue - $oldValue) / $oldValue) * 100;
    }
}