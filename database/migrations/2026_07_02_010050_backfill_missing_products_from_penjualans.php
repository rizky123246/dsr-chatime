<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfill 'products' dari article_code yang ada di 'penjualans' tapi
     * belum punya master produk. Data name/size/type diambil dari kolom
     * redundant yang sudah ada di penjualans (article_name, size, type) --
     * inilah kegunaan nyata dari kolom-kolom denormalisasi itu: sebagai
     * sumber pemulihan data, bukan sesuatu yang harus langsung dihapus.
     */
    public function up(): void
    {
        $missingCodes = DB::table('penjualans')
            ->whereNotIn('article_code', function ($query) {
                $query->select('article_code')->from('products');
            })
            ->select('article_code')
            ->distinct()
            ->pluck('article_code');

        $now = now();
        $inserted = 0;

        foreach ($missingCodes as $code) {
            // Ambil contoh baris paling baru untuk artikel ini,
            // sebagai sumber nama/ukuran/tipe produk.
            $sample = DB::table('penjualans')
                ->where('article_code', $code)
                ->orderByDesc('created_date')
                ->first(['article_name', 'size', 'type']);

            DB::table('products')->insert([
                'article_code' => $code,
                'name'         => $sample->article_name ?: $code,
                'size'         => $sample->size,
                // products.type wajib diisi (NOT NULL), tapi penjualans.type
                // nullable -- kasih fallback biar tidak gagal insert.
                'type'         => $sample->type ?: 'Unknown',
                'brand'        => 'Chatime',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            $inserted++;
        }

        if ($inserted > 0) {
            echo "  Backfill: {$inserted} produk baru ditambahkan ke 'products' dari data penjualans.\n";
            echo "  Catatan: kolom 'type' yang kosong diisi 'Unknown' sementara -- cek dan lengkapi manual nanti.\n";
        }
    }

    /**
     * Reverse the migrations.
     *
     * Sengaja tidak menghapus data hasil backfill -- karena begitu FK
     * article_code sudah terpasang di migration berikutnya, menghapus
     * baris ini akan memutus transaksi penjualans yang sudah bergantung
     * padanya. Kalau perlu rollback total, hapus manual dengan hati-hati.
     */
    public function down(): void
    {
        // no-op, sengaja
    }
};
