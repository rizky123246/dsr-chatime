<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = [
        'site_code',
        'year',
        'month',
        'target_sales',
        'created_by'
    ];
    
    public function user()
{
    return $this->belongsTo(User::class, 'created_by');
}
}