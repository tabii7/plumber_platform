<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'full_name'       => 'Super Admin',
                'phone'           => '0000000000',
                'whatsapp_number' => '0000000000',
                'address'         => 'Admin Street',
                'number'          => '1',
                'postal_code'     => '1000',
                'city'            => 'Brussels',
                'country'         => 'Belgium',
                'btw_number'      => null,
                'werk_radius'     => null,
                'password'        => Hash::make('password'), // 🔑 default password
                'role'            => 'admin',
            ]
        );
    }
}
