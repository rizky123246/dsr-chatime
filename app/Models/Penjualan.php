<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */

     
    protected $fillable = [
        'created_date',
        'created_time',
        'order_no',
        'receipt_no',
        'invoice_reference',
        'void',
        'site_code',
        'site_description',
        'article_code',
        'article_name',
        'quantity',
        'original_price',
        'net_price',
        'promotion_amount',
        'promotion_code',
        'promotion_name',
        'promotion_channel',
        'department_code',
        'department_name',
        'commodity_code',
        'commodity_name',
        'merchandise_code',
        'merchandise_name',
        'product_group_code',
        'product_group_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_date' => 'date',
            'created_time' => 'datetime:H:i:s',
            'void' => 'boolean',
            'quantity' => 'integer',
            'original_price' => 'decimal:2',
            'net_price' => 'decimal:2',
            'promotion_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
    public function store()
{
    return $this->belongsTo(Store::class, 'site_code', 'code');
}

}
