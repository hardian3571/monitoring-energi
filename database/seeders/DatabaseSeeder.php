<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Buat User Admin Otomatis
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@pkt.com',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);
    }
}