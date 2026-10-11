<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Owner Admin',
                'email' => 'admin@coffeeshop.test',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
            [
                'name' => 'Kasir Utama',
                'email' => 'kasir@coffeeshop.test',
                'password' => Hash::make('password'),
                'role' => UserRole::Cashier,
                'is_active' => true,
            ],
            [
                'name' => 'Barista Bar',
                'email' => 'barista@coffeeshop.test',
                'password' => Hash::make('password'),
                'role' => UserRole::Bar,
                'is_active' => true,
            ],
            [
                'name' => 'Waiter Lantai 1',
                'email' => 'waiter@coffeeshop.test',
                'password' => Hash::make('password'),
                'role' => UserRole::Waiter,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
