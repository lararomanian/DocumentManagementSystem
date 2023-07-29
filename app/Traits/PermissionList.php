<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

trait PermissionList
{

    public function permissionList()
    {
        $permissions = [];
        $actions = ['create', 'update', 'delete', 'view'];
        $modelsPath = app_path('Models');
        $modelFiles = File::allFiles($modelsPath);
        foreach ($modelFiles as $modelFile) {
            $model =  strtolower($modelFile->getFilenameWithoutExtension());
            foreach ($actions as $action) {
                $permissions[] = $model . "." . $action;
            }
        }

        //also add the remaining permission from the table permissions here
        $permissionsFromTable = Permission::all();
        foreach ($permissionsFromTable as $permission) {
            $permissions[] = $permission->name;
        }
        return $permissions;
    }
}

