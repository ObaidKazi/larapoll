<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $superAdminEmail = 'admin@larapoll.com';
        if (!User::where('email', $superAdminEmail)->exists()) {
            $superAdmin = User::factory()->create([
                'name' => 'Super Admin',
                'email' => $superAdminEmail,
                'password' => bcrypt('admin@larapoll.com'), 
            ]);
        }
        $superAdmin->assignRole('super_admin');
        $this->command->info('Super admin created: ' . $superAdminEmail);

        $this->command->info('Roles seeded successfully!');
    }
}