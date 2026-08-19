<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanMetric extends Model
{
    protected $table = 'laporan_metrics';

    protected $fillable = [
        'laporan_id',
        'category',
        'channel',
        'period',
        'metric',
        'value'
    ];

    // 🔥 RELASI KE LAPORAN HARIAN
    public function laporan()
    {
        return $this->belongsTo(LaporanHarian::class, 'laporan_id');
    }
}