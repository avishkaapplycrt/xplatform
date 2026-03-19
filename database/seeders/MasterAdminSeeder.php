<?php

namespace Database\Seeders;

use App\Models\MasterAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        MasterAdmin::create([
            'name' => 'Super Admin',
            'email' => 'admin@analytics-platform.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }
}