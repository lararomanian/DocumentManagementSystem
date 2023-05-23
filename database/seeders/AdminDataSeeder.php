<?php

namespace Database\Seeders;

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class AdminDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin =
            [
                "email" => "admin@email.com",
                "password" => bcrypt("password"),
                "name" => "Admin",
                'remember_token' => Str::random(10),
            ];


        $user = User::create($admin);
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $user->token = $token;
    }
}
