<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserDana;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Str;

use App\Http\Resources\PostResource;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Log;

use Illuminate\Auth\Events\Registered;

class UserAuthDanaController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'email' => 'required|string|unique:user_danas',
            'nomor_telepon' => 'required|regex:/^0\d{9,11}$/',
            'password' => 'required|min:3',
            'pin' => 'required|size:6'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $generateOtp = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $registerUserData = UserDana::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'nomor_telepon' => $request->nomor_telepon,
            'password' => Hash::make($request->password),
            'pin' => Hash::make($request->pin),
            'otp' => $generateOtp
        ]);

        event(new Registered($registerUserData));

        return response()->json([
            'success' => true,
            'message' => 'Register Telah Berhasil',
            'data' => new PostResource(true, 'Register Telah Berhasil', $registerUserData)
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_telepon' => 'required',
            'password' => 'required|min:3'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $userValidated = $validator->validated();

        $user = UserDana::where('nomor_telepon', $userValidated['nomor_telepon'])->first();
        if(!$user) {
            return response()->json([
                'message' => 'User Tidak Ditemukan'
            ], 401);
        } else if(!$user->email_verified_at) {
            $message = "Email Belum Terverifikasi! \n";
            $message .= "Silahkan Cek Email Untuk Verifikasi";
            return response()->json([
                'message' => $message
            ], 401);
        }
        if(!Hash::check($userValidated['password'], $user->password)) {
            return response()->json([
                'message' => 'Password Salah!'
            ], 401);
        }

        $token = $user->createToken($user->nama . '-AuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Telah Berhasil',
            'token' => $token,
            'user' => new PostResource(true, 'Login Telah Berhasi', $user)
        ]);
    }

    public function setVerif(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|size:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $userValidated = $validator->validated();
        
        $user = UserDana::where('otp', $userValidated['otp'])->first();
        if (!$user) {
            return response()->json([
                'message' => 'User Tidak Ditemukan'
            ], 401);
        }
        $user->update([
            'email_verified_at' => Carbon::now(),
            'otp' => null
        ]);
        return response()->json([
            'success' => true,
            'message' => 'User Dana Terferivikasi!',
            'user' => new PostResource(true, 'Login Telah Berhasil', $user)
        ]);
    }

    
}
