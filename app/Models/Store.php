<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Store extends Model
{
    protected $fillable = ['code', 'name', 'city', 'is_active'];

    // relasi ke user
    public function users()
{
    return $this->hasMany(User::class, 'site_code', 'code');
}
}