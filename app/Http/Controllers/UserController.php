<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getAllUsers() {
        $users = UserResource::collection(User::all());
        return response()->json(['users' => $users], 200);
    }

    public function getUserById($id) {
        $user = User::find($id);
        if(!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['user' => $user], 200);
    }

}
