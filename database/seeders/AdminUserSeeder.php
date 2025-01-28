<?php

namespace Database\Seeders;

use App\Constants\RoleNames;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! User::role(RoleNames::ADMIN->value)->count()) {
            User::create([
                'email' => 'mohiuddinmostafakamal@gmail.com',
                'password' => 'akib1234',
            ])
                ->assignRole(RoleNames::ADMIN->value);
        }
    }
}
