<?php

namespace Database\Seeders;

use App\Constants\RoleNames;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if (! Role::where('name', RoleNames::ADMIN->value)->exists()) {
            Role::create(['name' => RoleNames::ADMIN->value]);
        }
    }
}
