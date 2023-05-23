<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;


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

        return $permissions;
    }
}

