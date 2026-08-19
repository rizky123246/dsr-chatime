<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Store Manager
        User::updateOrCreate(
            ['email' => 'storemanager@example.com'],
            [
                'name' => 'Store Manager User',
                'password' => Hash::make('password'),
                'role' => 'store_manager',
            ]
        );

        // Area Manager
        User::updateOrCreate(
            ['email' => 'areamanager@example.com'],
            [
                'name' => 'Area Manager User',
                'password' => Hash::make('password'),
                'role' => 'area_manager',
            ]
        );

        // Kasir
        User::updateOrCreate(
            ['email' => 'kasir@example.com'],
            [
                'name' => 'Kasir User',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );

        $this->command->info('Users created/updated successfully!');
    }
}