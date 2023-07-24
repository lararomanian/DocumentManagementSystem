<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (!Role::where('name', 'admin')->exists()) {
            $super_admin_role = Role::create(['name' => 'admin']);
        } else {
            $super_admin_role = Role::findByName('admin');
        }

        $super_admin_role->givePermissionTo(Permission::all());
        $user = User::first();
        $user->assignRole($super_admin_role);
    }
}
