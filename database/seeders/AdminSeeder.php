<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                
            ]
        );
        User::updateOrCreate(
            ['email' => 'kevin@mail.com'],
            [
                'name' => 'Kevin',
                'password' => Hash::make('kevin123'),
                'role' => 'petugas',
            ]
        );
    }
}
