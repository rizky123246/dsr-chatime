<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\Pembayaran;
use App\Services\GenerateLaporanService;
use App\Models\LaporanHarian;
use App\Models\LaporanMetric;
use Illuminate\Http\Request;

class DaftarLaporanController extends Controller
{
    public function index(GenerateLaporanService $service, Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'bulan'   => 'nullable|integer|min:1|max:12',
            'tahun'   => 'nullable|integer|min:2000|max:2100',
            'status'  => 'nullable|in:submitted,approved,rejected,draft',
        ]);

        $query = LaporanHarian::with('metrics');

        /*
        |--------------------------------------------------------------------------
        | KHUSUS KASIR
        |--------------------------------------------------------------------------
        */

        if (
            Auth::user()->role == 'kasir' ||
            Auth::user()->role == 'store_manager'
        ) {

            $site = Auth::user()->site_code;

            // ambil semua tanggal dari penjualan store ini
            $dates = Penjualan::where('site_code', $site)
                ->selectRaw('DATE(created_date) as tanggal')
                ->distinct()
                ->pluck('tanggal');

            // generate laporan jika belum ada
            foreach ($dates as $date) {

                $exists = LaporanHarian::where('site', $site)
                    ->whereDate('trans_date', $date)
                    ->exists();

                if (!$exists) {
                    $service->generateAndSave($date, $site);
                }
            }

            // filter hanya store kasir
            $query->where('site', $site);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL / BULAN / TAHUN
        |--------------------------------------------------------------------------
        | Kalau 'tanggal' spesifik diisi, itu prioritas (abaikan bulan/tahun).
        | Kalau tidak, filter berdasarkan bulan dan/atau tahun (bisa salah satu
        | atau keduanya sekaligus).
        */

        if (!empty($validated['tanggal'])) {
            $query->whereDate('trans_date', $validated['tanggal']);
        } else {
            if (!empty($validated['bulan'])) {
                $query->whereMonth('trans_date', $validated['bulan']);
            }
            if (!empty($validated['tahun'])) {
                $query->whereYear('trans_date', $validated['tahun']);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        /*
        |--------------------------------------------------------------------------
        | AREA MANAGER
        |--------------------------------------------------------------------------
        */

        $laporan = $query
            ->orderBy('trans_date', 'desc')
            ->limit(50)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | MAPPING DATA
        |--------------------------------------------------------------------------
        */

        $data = $laporan->map(function ($item) {

            $metrics = $item->metrics;

            return [

                'tanggal' => $item->trans_date,

                'site_description' => $item->store,

                'total_transactions' => optional(
                    $metrics->where('metric', 'TC')->first()
                )->value ?? 0,

                'total_cup' => optional(
                    $metrics->where('metric', 'TOTAL_CUP')->first()
                )->value ?? 0,

                'net_sales' => optional(
                    $metrics->where('metric', 'SALES')->first()
                )->value ?? 0,

                'void_count' => 0,

                'status' => $item->status
            ];
        });

        // 🔥 Daftar tahun untuk dropdown filter — ambil dari data yang ada
        $availableYears = LaporanHarian::selectRaw('DISTINCT YEAR(trans_date) as year')
            ->orderByDesc('year')
            ->pluck('year');

        return view('dashboard.daftar-laporan', compact('data', 'availableYears') + [
            'filters' => [
                'tanggal' => $validated['tanggal'] ?? null,
                'bulan'   => $validated['bulan'] ?? null,
                'tahun'   => $validated['tahun'] ?? null,
                'status'  => $validated['status'] ?? null,
            ],
        ]);
    }

    public function destroy($tanggal)
    {
        $site = Auth::user()->site_code;

        // ambil laporan dulu, di luar transaksi
        $laporan = LaporanHarian::with('metrics')
            ->where('site', $site)
            ->whereDate('trans_date', $tanggal)
            ->first();

        // validasi status HARUS rejected sebelum boleh dihapus
        if (!$laporan || $laporan->status !== 'rejected') {
            return redirect()->back()
                ->with('error', 'Laporan hanya bisa dihapus jika sudah ditolak.');
        }

        try {

            DB::transaction(function () use ($site, $tanggal, $laporan) {

                // ambil receipt dari penjualan
                $receiptNos = Penjualan::where('site_code', $site)
                    ->whereDate('created_date', $tanggal)
                    ->pluck('receipt_no');

                // hapus pembayaran
                Pembayaran::whereIn('receipt_no', $receiptNos)
                    ->delete();

                // hapus penjualan
                Penjualan::where('site_code', $site)
                    ->whereDate('created_date', $tanggal)
                    ->delete();

                // hapus metrics laporan
                LaporanMetric::where('laporan_id', $laporan->id)
                    ->delete();

                // hapus laporan harian
                $laporan->delete();
            });

            return redirect()->back()
                ->with('success', 'Data berhasil dihapus');

        } catch (\Exception $e) {

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function areaManager(Request $request)
    {
        $validated = $request->validate([
            'store'   => 'nullable|string',
            'status'  => 'nullable|in:submitted,approved,rejected,draft',
            'tanggal' => 'nullable|date',
            'bulan'   => 'nullable|integer|min:1|max:12',
            'tahun'   => 'nullable|integer|min:2000|max:2100',
        ]);

        $query = LaporanHarian::query();

        // FILTER STORE
        if ($request->filled('store')) {
            $query->where('site', $request->store);
        }

        // FILTER STATUS
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // FILTER TANGGAL / BULAN / TAHUN (pola sama dengan index())
        if (!empty($validated['tanggal'])) {
            $query->whereDate('trans_date', $validated['tanggal']);
        } else {
            if (!empty($validated['bulan'])) {
                $query->whereMonth('trans_date', $validated['bulan']);
            }
            if (!empty($validated['tahun'])) {
                $query->whereYear('trans_date', $validated['tahun']);
            }
        }

        $laporans = $query
            ->latest('trans_date')
            ->paginate(20)
            ->withQueryString(); // 🔥 supaya filter tetap ikut di link pagination

        $stores = LaporanHarian::select('site')
            ->distinct()
            ->pluck('site');

        $availableYears = LaporanHarian::selectRaw('DISTINCT YEAR(trans_date) as year')
            ->orderByDesc('year')
            ->pluck('year');

        return view(
            'area-manager.daftar-laporan',
            compact('laporans', 'stores', 'availableYears') + [
                'filters' => [
                    'store'   => $validated['store'] ?? 'all',
                    'status'  => $validated['status'] ?? null,
                    'tanggal' => $validated['tanggal'] ?? null,
                    'bulan'   => $validated['bulan'] ?? null,
                    'tahun'   => $validated['tahun'] ?? null,
                ],
            ]
        );
    }

    public function approve($id)
    {
        if (Auth::user()->role != 'area_manager') {
            abort(403);
        }

        $laporan = LaporanHarian::findOrFail($id);

        $laporan->status = 'approved';
        $laporan->save();

        return back()->with('success', 'Laporan approved');
    }

    public function reject($id)
    {
        if (Auth::user()->role != 'area_manager') {
            abort(403);
        }

        $laporan = LaporanHarian::findOrFail($id);

        $laporan->status = 'rejected';
        $laporan->save();

        return back()->with('success', 'Laporan rejected');
    }
}