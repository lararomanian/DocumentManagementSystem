<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $role = Role::where('name', 'admin')->first();
        if(!$role) {
            $this->generateRole();
        }
        $this->setAdminRole();

    }

    public function generateRole()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    public function setAdminRole() {
        $user = User::where('email', "admin@email.com")->first();
        $user->assignRole("admin");
    }
}
