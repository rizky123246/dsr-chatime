<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FoodCategory extends Model
{
    protected $fillable = ['name','label'];

    public function items()
    {
        return $this->hasMany(FoodCategoryItem::class, 'category_id');
    }
}