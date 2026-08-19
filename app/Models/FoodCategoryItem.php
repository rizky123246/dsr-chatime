<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class FoodCategoryItem extends Model
{
    protected $fillable = ['category_id','article_code'];

    public function category()
    {
        return $this->belongsTo(FoodCategory::class);
    }
}