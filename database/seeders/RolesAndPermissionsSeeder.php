<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $subAdminRole = Role::firstOrCreate(['name' => 'staff']);

        $permissions = [
            'get user list',
            'add user',
            'view user',
            'update user',
            'delete user',

            'get company list',
            'add company',
            'view company',
            'update company',
            'delete company',

            'get medicine list',
            'add medicine',
            'view medicine',
            'update medicine',
            'delete medicine',

            'get pre medicine stock list',
            'add pre medicine stock',
            'view pre medicine stock',
            'update pre medicine stock',
            'delete pre medicine stock',
            'sync pre medicine stock',

            'get invoice list',
            'add invoice',
            'view invoice',
            'update invoice',
            'delete invoice',
            'apply invoice discount',
            'apply invoice paid amount',
            'apply other charge',

            'get site setting',
            'update site setting',

            'get acl list',
            'update acl',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole->syncPermissions($permissions);

        $admins = User::where('role', ADMIN_ROLE)->get();
        foreach ($admins as $admin) {
            $admin->assignRole($adminRole);
        }
    }
}
