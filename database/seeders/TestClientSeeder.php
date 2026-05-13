<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;

class TestClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::create([
            'company_name' => 'Test Company',
            'email'        => 'test@test.com',
            'password'     => Hash::make('password'),
            'size'         => 'small',
            'country'      => 'AU',
            'status'       => 'active',
        ]);
    }
}