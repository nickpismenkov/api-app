<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'error' => ['These credentials do not match our records.']
            ], 404);
        }
        
        $user->tokens()->delete();
        $token = $user->createToken('my-app-token')->plainTextToken;
        
        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    function logout(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if(!$user) {
            return response([
                'error' => ['These credentials do not match our records.']
            ], 404);
        }

        $user->tokens()->delete();

        return response([
            'message' => ['Logged out successfully.']
        ], 201);
    }

    function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|min:5',
            'email' => 'required|email|max:255|min:5',
            'password' => 'required|max:255|min:5'
        ]);
        if($validator->fails()) {
            return response([
                'error' => [$validator->errors()->first()]
            ], 404);
        }

        $user = User::where('email', $request->email)->first();
        if($user) {
            return response([
                'error' => ['User with this email already exists.']
            ], 404);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        $user = User::create($userData);
        if($user) {
            $token = $user->createToken('my-app-token')->plainTextToken;

            return response([
                'user' => $user,
                'token' => $token
            ], 201);
        }
    }
}
