<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'article_code',
        'name',
        'size',
        'type',
        'series',
        'brand'
    ];
}
