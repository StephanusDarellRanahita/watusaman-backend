<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\ResetPassword;
use Carbon\Carbon;
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
    public function register(Request $request)
    {
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
        $token = $registerUserData->createToken('authToken')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Register Telah Berhasil! Silahkan Cek Email Untuk Verifikasi',
            'token' => $token,
            'data' => $registerUserData
        ]);
    }

    public function login(Request $request)
    {
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
        } else if (!$user->email_verified_at) {
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

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
        $customToken = $user->name . '|' . $token;


        return response()->json([
            'success' => true,
            'message' => 'Login Telah Berhasil',
            'token' => $customToken,
            'user' => new PostResource(true, 'Login Telah Berhasil', $user)
        ]);

    }
    public function updateProfil(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'nomor_telepon' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User Tidak Ditemukan!'
            ], 200);
        }

        $user->update([
            'name' => $request->name,
            'nomor_telepon' => $request->nomor_telepon
        ]);

        return new PostResource(true, 'Profil Berhasil Diupdate!', $user);
    }

    public function setVerif(Request $request)
    {
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
        }
        $user->update([
            'email_verified_at' => Carbon::now()
        ]);
        Log::info('Debugging Password Check', [
            'provided_password' => $userValidated['password'],
            'stored_password' => $user->password
        ]);
        if (!Hash::check($userValidated['password'], $user->password)) {
            return response()->json([
                'message' => "Password Salah!"
            ], 401);
        }

        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email Terverifikasi!',
            'token' => $token,
            'user' => new PostResource(true, 'Login Telah Berhasil', $user)
        ]);
    }
    public function adminLogin($pin)
    {
        $admin = Admin::where('pin', $pin)->first();
        if (!$admin) {
            return response()->json([
                'message' => 'Admin Tidak Ditemukan!'
            ], 404);
        }

        $token = $admin->createToken($admin->pin . '-AuthToken')->plainTextToken;
        $cutomToken = $admin->roles . '|' . $token;

        return response()->json([
            'success' => true,
            'message' => 'Login Sebagai Admin!',
            'token' => $cutomToken,
            'admin' => new PostResource(true, 'Login Sebagai Admin!', $admin)
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'User Tidak Ditemukan!'
            ], 422);
        }
        $generateOtp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $resetPassword = ResetPassword::create([
            'id_user' => $request->id_user,
            'otp' => $generateOtp,
            'status' => 'BELUM'
        ]);

        return response()->json([
            'message' => 'Cek Email Untuk Masukkan Kode OTP!',
            'data' => $resetPassword
        ]);
    }
    public function OTP(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string',
        ]);

        if (!$validator) {
            return response()->json([
                'message' => $validator->errors()
            ]);
        }

        $resetOTP = ResetPassword::whereNotNull('otp')
            ->where('id_user', $id)
            ->first();

        if ($resetOTP->otp !== $request->otp) {
            return response()->json([
                'message' => 'Kode OTP Salah!'
            ], 200);
        }
        $resetOTP->update([
            'otp' => null,
            'status' => 'SUDAH'
        ]);

        return response()->json([
            'message' => 'OTP Sudah Benar! Silahkan Reset Password Anda!'
        ]);
    }

    public function verifyPassword(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ],422);
        }

        $user = User::find($id);

        if(!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Password Tidak Sesuai!'
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return new PostResource(true, 'Password Berhasil Diganti!', $user);
    }

    public function getUser($id)
    {
        $user = User::find($id);

        return new PostResource(true, 'Detail Data User!', $user);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            "message" => 'Berhasil Logout'
        ]);
    }

    // public function destroy() {
    //     $user = User::where()
    // }
}
