<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    protected $table = 'laporan_harian';

    protected $fillable = [
        'site',
        'store',
        'trans_date',
        'year',
        'target',
        'status',
        'approved_by',
        'approved_at',
        'notes'
    ];
    public function metrics()
    {
        return $this->hasMany(LaporanMetric::class, 'laporan_id');
    }

    // 🔥 HELPER (OPTIONAL tapi berguna nanti)
    public function getValue($category, $period, $metric, $channel = 'ALL')
    {
        return $this->metrics
            ->where('category', $category)
            ->where('period', $period)
            ->where('metric', $metric)
            ->where('channel', $channel)
            ->first()?->value ?? 0;
    }
}
