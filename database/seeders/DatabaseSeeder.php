<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Master data
            StoreSeeder::class,
            ProductSeeder::class,            
            FoodCategorySeeder::class,
            
            // User
            UserSeeder::class,
            StoreManagerSeeder::class,
            AreaManagerSeeder::class,
            KasirSeeder::class,

            // Data transaksi
           // PembayaranSeeder::class,
           // PenjualanSeeder::class,
        ]);

        $this->command->info('All user seeders have been executed successfully!');
        $this->command->info('Default password for all users: password');
    }   
}
