<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $username = 'Admin' . str_pad($i, 3, '0', STR_PAD_LEFT);

            User::create([
                'name' => $username,
                'email' => strtolower($username) . '@admin.local',
                'password' => Hash::make("A@2025admin{$i}!"),
                'role' => 'admin',
            ]);
        }
    }
}
