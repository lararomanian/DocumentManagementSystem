<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getAllUsers()
    {
        try{

            $users = UserResource::collection(User::all());
            return response()->json(['data' => $users, "message" => "All users fetched successfully", "status" => 200], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage(), "data" => "Something went wrong", "status" => $e->getCode()], $e->getCode());
        }
    }

    public function getUserById($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['data' => 'An Error Occurred' , "message" => "User not found", "status" => 204], 204);
        }
        return response()->json(['data' => $user, "message" => "User data fetched successfully", "status" => 200], 200);
    }

    public function editUser(UserRequest $request, $id)
    {
        $validator = Validator::make($request->all(),  $request->rules(), $request->messages());
        if ($validator->fails()) {
            return response()->json(["data" => "Validation Error",'message' => $validator->errors(), "status" => 401], 401);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['data' => 'An Error Occurred'  , "message" => "User not found", "status" => 204], 204);
        }
        $user->update($request->all());

        $user->password = bcrypt($request->password);
        $user->save();
        return response()->json(['data' => $user, "message" => "User Updated Succesfully", "status" => 200], 200);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['data' => 'An Error Occurred' , "message" => "User not found", "status" => 204], 204);
        }
        $user->delete();
        return response()->json(["data" => "User Deleted Successfully", "message" => "User Deleted Successfully", "status" => 200], 200);
    }
}
