<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password1234'), // পাসওয়ার্ডটি এনক্রিপ্ট করে দিবে
            'email_verified_at' => now(),
        ]);
    }
}
