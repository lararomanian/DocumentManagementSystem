<?php

namespace Database\Seeders;

use App\Models\User;
use App\Traits\PermissionList;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Permission as ContractsPermission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    use PermissionList;
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
        $this->setPermissions();

    }

    public function generateRole()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    public function setAdminRole() {
        $user = User::where('email', "admin@email.com")->first();
        $role = Role::where('name', 'admin')->first();
        if($user) {
            $user->roles()->attach($role, ['model_type' => 'App\Models\User']);
        }

    }

    public function setPermissions() {
        app()[Permission::class]->forgetCachedPermissions();
        $lists =  $this->permissionList();

        foreach ($lists as $list) {
            if (!Permission::where('name', $list)->exists()) {
                Permission::create(['name' => $list]);
            }
        }

    }
}
