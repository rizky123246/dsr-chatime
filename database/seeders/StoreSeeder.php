<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'code' => 'F535',
                'name' => 'Chatime Dempo Malang',
                'city' => 'Malang',
                'is_active' => 1,
            ],
            [
                'code' => 'F585',
                'name' => 'Chatime Matos Malang',
                'city' => 'Malang',
                'is_active' => 1,
            ],
            [
                'code' => 'F654',
                'name' => 'Chatime Soehat Malang',
                'city' => 'Malang',
                'is_active' => 1,
            ],
        ];

        foreach ($stores as $store) {
            Store::updateOrCreate(
                ['code' => $store['code']],
                $store
            );
        }
    }
}
