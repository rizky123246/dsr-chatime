<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class KasirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple Kasirs
        $kasirs = [
            [
                'name' => 'Anna Kasir',
                'email' => 'anna.kasir@store.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'site_code' => 'F585'
            ],
            [
                'name' => 'Budi Kasir',
                'email' => 'budi.kasir@store.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'site_code' => 'F654'
            ],
            [
                'name' => 'Cindy Kasir',
                'email' => 'cindy.kasir@store.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'site_code' => 'F535'
            ],
            [
                'name' => 'Doni Kasir',
                'email' => 'doni.kasir@store.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'site_code' => 'F585'
            ],
            [
                'name' => 'Eva Kasir',
                'email' => 'eva.kasir@store.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'site_code' => 'F654'
            ],
            [
                'name' => 'Rizky Kasir',
                'email' => 'rizky.kasir@store.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'site_code' => 'F535'
            ],
        ];

        foreach ($kasirs as $kasir) {
            User::updateOrCreate(
                ['email' => $kasir['email']],
                $kasir
            );
        }

        $this->command->info('Kasirs created successfully!');
        $this->command->info('Created ' . count($kasirs) . ' Kasir accounts');
    }
}
