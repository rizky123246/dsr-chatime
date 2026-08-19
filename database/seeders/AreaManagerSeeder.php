<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AreaManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple Area Managers
        $areaManagers = [
            [
                'name' => 'David Area Manager',
                'email' => 'david.am@company.com',
                'password' => Hash::make('password'),
                'role' => 'area_manager',
            ],
            [
                'name' => 'Lisa Area Manager',
                'email' => 'lisa.am@company.com',
                'password' => Hash::make('password'),
                'role' => 'area_manager',
            ],
            [
                'name' => 'Robert Area Manager',
                'email' => 'robert.am@company.com',
                'password' => Hash::make('password'),
                'role' => 'area_manager',
            ],
            [
                'name' => 'Rizky Area Manager',
                'email' => 'rizky.am@company.com',
                'password' => Hash::make('password'),
                'role' => 'area_manager',
            ],
        ];

        foreach ($areaManagers as $manager) {
            User::updateOrCreate(
                ['email' => $manager['email']],
                $manager
            );
        }
        $this->command->info('Area Managers created successfully!');
        $this->command->info('Created ' . count($areaManagers) . ' Area Manager accounts');
    }
}
