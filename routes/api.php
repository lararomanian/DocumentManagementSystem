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


Route::group(['middleware' => ['cors', 'json.response']], function () {
    // public routes
    Route::post('/login', [AuthController::class, 'login'])->name('login.api');
    Route::post('/register', [AuthController::class, 'register'])->name('register.api');
    Route::get('/login/user', [AuthController::class, 'getLoggedUserData'])->name('login.user.api');
});

Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    //User Routes
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/users/{id}', [UserController::class, 'getUserById']);
    Route::patch('/users/{id}', [UserController::class, 'editUser']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    Route::patch('/users/{id}/password', [AuthController::class, 'resetPassword']);
    Route::get("user/data", [AuthController::class, 'getLoggedUserData']);
    //Role Routes
    Route::get('/roles', [RolePermissionController::class, 'getAllRoles']);
    Route::get('/permissions', [RolePermissionController::class, 'getAllPermissions']);
    Route::post('/roles', [RolePermissionController::class, 'createRole']);
    Route::patch('/roles/{id}', [RolePermissionController::class, 'updateRole']);
    Route::delete('/roles/{id}', [RolePermissionController::class, 'deleteRole']);

    //User Role Routes
    Route::post('/users/{id}/roles', [UserRoleController::class, 'setRole']);

    //Project Routes
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::patch('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    Route::post('projects/{project}/add-user', [ProjectController::class, 'addUser']);
    Route::post('projects/{project}/remove-user', [ProjectController::class, 'removeUser']);
    Route::get('projects/{project}/users', [ProjectController::class, 'getUsersInProject']);

    Route::get('/documents', [DocumentsController::class, 'index']);
    Route::post('/documents', [DocumentsController::class, 'store']);
    Route::patch('/documents/{documents}', [DocumentsController::class, 'update']);
    Route::get('/documents/{documents}/show', [DocumentsController::class, 'show']);
    Route::delete('/documents/{documents}/delete', [DocumentsController::class, 'delete']);

    Route::get('/abilities', [RolePermissionController::class, 'abilities']);
});
Route::prefix('folders')->group(function () {
    Route::get('/', [FolderController::class, 'index']);
    Route::post('/', [FolderController::class, 'store']);
    Route::get('/{id}', [FolderController::class, 'show']);
    Route::patch('/{id}', [FolderController::class, 'update']);
    Route::delete('/{id}', [FolderController::class, 'destroy']);
    Route::post('/{parentId}', [FolderController::class, 'store']); // Create subfolder
});



