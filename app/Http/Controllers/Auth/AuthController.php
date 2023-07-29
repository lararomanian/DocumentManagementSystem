<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Mail\AccountCreationEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response(["data" => "Invalid Data", 'message' => $validator->errors()->all(), "status" => 401], 401);
        }
        // $request['password'] = Hash::make($request['password']);
        $request['password'] = $this->generatePassword();
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $this->sendMail($user);
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $user->token = $token;
        return response()->json(['data' => $user, "message" => "User registered successfully", "status" => 200], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(["data" => "Invalid Data", 'message' => $validator->errors()->all(), 'status' => 422], 422);
        }
        $user = User::where('email', $request->email)->first();


        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $user->token = $token;
                $user->role = $user->getRoles($user);
                $user->permissions = $user->getUserPermissions($user->role);
                $this->user = $user;
                $response = ['data' => $user, 'message' => 'User logged in successfully.', "status" => 200];
                return response()->json($response, 200);
            } else {
                $response = ["data" => "Password mismatch", "message" => "Password mismatch", "status" => 401];
                return response($response, 401);
            }
        } else {
            $response = ["data" => 'User does not exist', "message" => 'User does not exist', "status" => 401];
            return response()->json($response, 401);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!', "status" => 200];
        return response($response, 200);
    }

    public function sendMail($data)
    {

        $details = [
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'title' => 'Account Created',
            'body' => 'Your account has been created successfully. Please login to continue.'
        ];

        try {
            Mail::to($details["email"])->send(new AccountCreationEmail($details));
        } catch (\Exception $e) {
            return response()->json(['data' => 'An Error Occurred.', "message" => $e->getMessage(), "status" => $e->getCode()], $e->getCode());
        }
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'old_password' => 'required|string|min:8',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['data' => 'An Error Occurred.', 'message' => 'User not found.', "status" => 204], 204);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['data' => 'An Error Occurred.', 'message' => 'Old password is incorrect.', "status" => 401], 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(["data" => $user, 'message' => 'Password changed successfully.', "status" => 200], 200);
    }

    public function getClientToken($id)
    {
        $clientId = $id; // Replace with the actual client ID

        $client = User::find($clientId);

        if (!$client) {
            return response(['message' => 'Client not found'], 404);
        }
        $token = $client->createToken('Laravel Password Grant Client')->accessToken;

        return $token;
    }

    public function getLoggedUserData()
    {

        //from auth use get the respective user, its token, spatie roles and permissions and store them in an array and retun it
        $user = Auth::user();

        $token = $this->getClientToken($user->id);
        $user->role = $user->roles()->pluck('name')->first();
        $user->permissions = $user->getPermissionsViaRoles()->pluck('name');
        $user->token = $token;
        $name = $user->name;

        $data = [
            'name' => $name,
            'token' => $token,
            'role' => $user->role,
            'permissions' => $user->permissions,
        ];
        return response()->json(['data' => $data, "message" => "User data fetched successfully", "status" => 200], 200);
    }

    public function generatePassword() {
        // $password = Str::random(8) . time() . bin2hex(random_bytes(8));
        $password = Hash::make("password");
        return $password;

    }
}
