<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pembayarans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'amount',
        'order_no',
        'receipt_no',
        'invoice_reference',
        'mop_code',
        'mop_name',
        'source_file',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
