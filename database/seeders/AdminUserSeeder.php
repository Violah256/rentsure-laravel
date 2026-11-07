<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'System Administrator',
            'role' => User::ROLE_ADMINISTRATOR,
            'phone' => '0758648265',
            'email' => 'admin@rentsure.com',
            'neighbourhood' => 'Kampala',
            'password' => Hash::make('Violah2025!'),
            
        ]);
    }
}
