<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

//import Resource "PostResource"
use App\Http\Resources\PostResource;

//import Facade "Validator"
use Illuminate\Support\Facades\Validator;

//import Facade "Hash"
use Illuminate\Support\Facades\Hash;

//import Facade "Log"
use Illuminate\Support\Facades\Log;

//import Registerd
use Illuminate\Auth\Events\Registered;

class UserAuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $registerUserData = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password) 
        ]);

        event(new Registered($registerUserData));

        return new PostResource(true, 'Register Telah Berhasil!', $registerUserData);
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $userValidated = $validator->validated();

        $user = User::where('email', $userValidated['email'])->first();
        if (!$user) {
            return response()->json([
                'message' => 'User Tidak Ditemukan'
            ], 401);
        } else if(!$user->email_verified_at) {
            $message = "Email Belum Terverifikasi \n";
            $message .= "Silahkan Cek Email Untuk Verifikasi!";
            return response()->json([
                'message' => $message
            ], 401);
        }
        Log::info('Debugging Password Check', [
            'provided_password' => $userValidated['password'],
            'stored_password' => $user->password
        ]);
        if (!Hash::check($userValidated['password'], $user->password)) {
            return response()->json([
                'message' => "Password Salah!"
            ], 401);
        }

        $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return new PostResource(true, 'Login Telah Berhasil', $user);
    }

    public function getUser($id) {
        $user = User::find($id);

        return new PostResource(true, 'Detail Data User!', $user);
    }

    public function logout() {
        auth()->user()->tokens()->delete();

        return response()->json([
            "message" => 'Berhasil Logout'
        ]);
    }
}
