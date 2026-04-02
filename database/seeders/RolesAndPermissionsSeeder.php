<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Worker;
use App\Models\Product;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Prices — super admin only
            'manage_prices',
            'manage_products',
            'manage_users',

            // Office admin
            'create_books',
            'cancel_jobs',
            'change_team_leader',
            'approve_worksheets',
            'correct_worksheets',
            'override_prices',
            'waive_signature',
            'view_client_prices',
            'view_labor_prices',
            'process_payments',
            'export_payments',
            'manage_workers',
            'manage_clients',

            // TL — verified per job, not by role
            'submit_worksheet',
            'request_tl_change',
            'request_signature_waiver',
            'create_teams',

            // Worker
            'fill_worksheet',
            'view_own_books',
            'view_own_payments',
            'view_own_labor_price',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Super Admin — everything
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Office Admin — everything except price management
        $officeAdmin = Role::firstOrCreate(['name' => 'office_admin']);
        $officeAdmin->givePermissionTo(array_filter($permissions, fn($p) => !in_array($p, [
            'manage_prices', 'manage_products', 'manage_users'
        ])));

        // Worker — base permissions
        // Note: TL permissions (submit_worksheet, create_teams etc.) are
        // granted contextually via job.team_leader_id check, not by role
        $worker = Role::firstOrCreate(['name' => 'worker']);
        $worker->givePermissionTo([
            'fill_worksheet',
            'view_own_books',
            'view_own_payments',
            'view_own_labor_price',
        ]);

        // Create default super admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@ims.com.au'],
            ['password' => bcrypt('admin123'), 'is_active' => true]
        );
        $adminUser->assignRole('super_admin');

        $this->command->info('Roles and permissions seeded.');
        $this->command->info('Default admin: admin@ims.com.au / admin123');
    }
}
