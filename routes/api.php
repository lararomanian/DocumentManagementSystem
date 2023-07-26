<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login.api');
    Route::post('/register', [AuthController::class, 'register'])->name('register.api');
    Route::get('/login/user', [AuthController::class, 'getLoggedUserData'])->name('login.user.api');
});

// Protected routes
Route::middleware('auth:api')->group(function () {
    // User Routes
    Route::group(['prefix' => 'usermamangement'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/edit/{id}', [UserController::class, 'getUserById']);
        Route::post('/update/{id}', [UserController::class, 'editUser']);
        Route::delete('/delete/{id}', [UserController::class, 'deleteUser']);
        Route::post('/{id}/password', [AuthController::class, 'resetPassword']);
        Route::get("data", [AuthController::class, 'getLoggedUserData']);
        Route::post('/toggleStatus', [UserController::class, 'toggleStatus']);

    });

    // Role Routes
    Route::group(['prefix' => 'roles'], function () {
        Route::get('/', [RolePermissionController::class, 'getAllRoles']);
        Route::get('/permissions', [RolePermissionController::class, 'getAllPermissions']);
        Route::post('/store', [RolePermissionController::class, 'createRole']);
        Route::post('/update/{id}', [RolePermissionController::class, 'updateRole']);
        Route::delete('/delete/{id}', [RolePermissionController::class, 'deleteRole']);
    });

    // User Role Routes
    Route::post('/users/{id}/roles', [UserRoleController::class, 'setRole']);

    // Project Routes
    Route::group(['prefix' => 'projects'], function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/store', [ProjectController::class, 'store']);
        Route::get('/edit/{project}', [ProjectController::class, 'show']);
        Route::post('/update', [ProjectController::class, 'update']);
        Route::delete('/delete/{project}', [ProjectController::class, 'destroy']);
        Route::post('add-user/{project}', [ProjectController::class, 'addUser']);
        Route::post('remove-user/{project}', [ProjectController::class, 'removeUser']);
        Route::get('users/{project}', [ProjectController::class, 'getUsersInProject']);
        Route::get('/childs', [ProjectController::class, 'getChilds']);
    });

    // Document Routes
    Route::group(['prefix' => 'documents'], function () {
        Route::get('/', [DocumentsController::class, 'index']);
        Route::post('/store', [DocumentsController::class, 'store']);
        Route::post('/update/{documents}', [DocumentsController::class, 'update']);
        Route::get('/edit/{documents}', [DocumentsController::class, 'show']);
        Route::delete('/delete/{documents}', [DocumentsController::class, 'delete']);
    });

    Route::get('/abilities', [RolePermissionController::class, 'abilities']);
});

// Folder Routes
Route::group(['prefix' => 'folders'], function () {
    Route::get('/{id}', [FolderController::class, 'index']);
    Route::post('/store', [FolderController::class, 'store']);
    Route::get('/edit/{id}', [FolderController::class, 'show']);
    Route::post('/update/{id}', [FolderController::class, 'update']);
    Route::delete('/delete/{id}', [FolderController::class, 'destroy']);
});

Route::get("/folders/{id}/export", [DocumentsController::class, 'exportFolder']);
Route::get("/documents/{id}/export", [DocumentsController::class, 'exportPDF']);
