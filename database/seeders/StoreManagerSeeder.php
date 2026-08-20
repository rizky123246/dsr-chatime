<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class StoreManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple Store Managers
        $storeManagers = [
            [
                'name' => 'John Store Manager',
                'email' => 'john.sm@store.com',
                'password' => Hash::make('password'),
                'role' => 'store_manager',
                'site_code' => 'F535',
            ],
            [
                'name' => 'Sarah Store Manager',
                'email' => 'sarah.sm@store.com',
                'password' => Hash::make('password'),
                'role' => 'store_manager',
                'site_code' => 'F654',
            ],
            [
                'name' => 'Michael Store Manager',
                'email' => 'michael.sm@store.com',
                'password' => Hash::make('password'),
                'role' => 'store_manager',
            ],
        ];
        foreach ($storeManagers as $manager) {
            User::updateOrCreate(
                ['email' => $manager['email']],
                $manager
            );
        }
        $this->command->info('Store Managers created successfully!');
        $this->command->info('Created ' . count($storeManagers) . ' Store Manager accounts');
    }
}
