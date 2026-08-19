<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Log;

/**
 * Service reusable untuk memastikan master data (Store & Product)
 * sudah ada sebelum data transaksi (Penjualan) di-insert.
 *
 * Kalau data master belum ada, otomatis dibuatkan (mirip pola backfill),
 * supaya insert ke tabel penjualans tidak gagal karena foreign key.
 */
class EnsureMasterDataService
{
    /**
     * Cache in-memory per proses import, supaya kode yang sama
     * tidak query/insert berkali-kali ke DB.
     *
     * @var array<string, Store>
     */
    protected array $storeCache = [];

    /**
     * @var array<string, Product>
     */
    protected array $productCache = [];

    /**
     * Pastikan store/site sudah ada di master data.
     * Kalau belum ada, buat otomatis dengan data minimal dari CSV.
     *
     * Catatan: kolom di tabel `stores` adalah `code` & `name`
     * (bukan `site_code` / `site_description`).
     */
    public function ensureStore(?string $siteCode, ?string $siteDescription = null): ?Store
    {
        $siteCode = trim((string) $siteCode);

        if ($siteCode === '') {
            return null;
        }

        $cacheKey = strtoupper($siteCode);

        if (isset($this->storeCache[$cacheKey])) {
            return $this->storeCache[$cacheKey];
        }

        $store = Store::firstOrCreate(
            ['code' => $siteCode],
            [
                'name' => $siteDescription ?: $siteCode,
                'is_active' => true,
            ]
        );

        if ($store->wasRecentlyCreated) {
            Log::info("EnsureMasterDataService: auto-created store baru → {$siteCode}");
        }

        return $this->storeCache[$cacheKey] = $store;
    }

    /**
     * Pastikan article/product sudah ada di master data.
     * Kalau belum ada, buat otomatis dengan fallback size/type "Unknown".
     *
     * Catatan: kolom nama di tabel `products` adalah `name`
     * (bukan `article_name`).
     */
    public function ensureProduct(
        ?string $articleCode,
        ?string $articleName = null,
        ?string $size = null,
        ?string $type = null
    ): ?Product {
        $articleCode = strtoupper(trim((string) $articleCode));

        if ($articleCode === '') {
            return null;
        }

        if (isset($this->productCache[$articleCode])) {
            return $this->productCache[$articleCode];
        }

        $product = Product::firstOrCreate(
            ['article_code' => $articleCode],
            [
                'name' => $articleName ?: $articleCode,
                'size' => $size ?: '-',
                'type' => $type ?: 'Unknown',
            ]
        );

        if ($product->wasRecentlyCreated) {
            Log::info("EnsureMasterDataService: auto-created product baru → {$articleCode}");
        }

        return $this->productCache[$articleCode] = $product;
    }

    /**
     * Reset cache in-memory (opsional, kalau service dipakai lintas file/job berbeda
     * dan ingin cache-nya dikosongkan).
     */
    public function resetCache(): void
    {
        $this->storeCache = [];
        $this->productCache = [];
    }
}