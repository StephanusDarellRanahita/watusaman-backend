<?php

//import Controller Class
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\KamarController;
use App\Http\Controllers\Api\ReservasiController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\DanaPaymentsController;
use App\Http\Controllers\Api\LaporanReservasiController;

use App\Http\Resources\PostResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Validator;


use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserAuthDanaController;

use App\Models\ResetPassword;

use App\Mail\SendEmail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//posts
Route::post('/posts', [PostController::class, 'store']);
Route::patch('/posts/{id}', [PostController::class, 'update']);

//auth
Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users/{id}', [UserAuthController::class, 'getUser']);
Route::put('/login-verif', [UserAuthController::class, 'setVerif']);
Route::put('/update-profil/{id}', [UserAuthController::class, 'updateProfil']);
Route::post('/reset-password', [UserAuthController::class, 'resetPassword']);
Route::post('/verif-otp', [UserAuthController::class, 'verifOTP']);
Route::post('/reset-otp/{id}', [UserAuthController::class, 'OTP']);
Route::put('/password-change/{id}', [UserAuthController::class, 'verifyPassword']);

Route::post('/login-admin/{pin}', [UserAuthController::class, 'adminLogin']);


//auth dana
Route::post('/register-dana', [UserAuthDanaController::class, 'register']);
Route::put('/otp-verif', [UserAuthDanaController::class, 'setVerif']);

//email Verif
Route::get('/send-email', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'nama' => 'required|string',
        'email' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => $validator->errors()
        ], 422);
    }
    $validated = $validator->validated();

    $verificationUrl = 'http://localhost:5173/verifLogin';

    $message = "Klik Link Ini Untuk Verifikasi : \n";
    $message .= $verificationUrl;
    $data = [
        'title' => $validated['email'],
        'name' => $validated['nama'],
        'message' => $message
    ];

    Mail::to($validated['email'])->send(new SendEmail($data, 'Verifikasi Akun Villa Watusaman', 'emails.sendemail'));
    return new PostResource(true, 'Silahkan Cek Email Untuk Registrasi!', $data);
});
//email reset password
Route::get('/send-email-otp/{id}', function ($id) {
    $resetUser = ResetPassword::where('id_user', $id)->whereNotNull('otp')->first();
    $user = User::find($resetUser->id_user);
    
    if(!$user) {
        return response()->json([
            'message' => 'User Tidak Ditemukan!'
        ],200);
    }

    $otp = $resetUser->otp;

    $message = "Berikut Kode OTP reset password anda : \n";
    $message .= $otp;
    $data = [
        'title' => $user['email'],
        'name' => $user['name'],
        'message' => $message
    ];

    Mail::to($user['email'])->send(new SendEmail($data, 'Kode OTP Reset Password', 'emails.sendemailotp'));
    return new PostResource(true, 'OTP Dikirim Ke Email Anda!', $data);
});
//kamar
Route::get('/kamars', [KamarController::class, 'index']);
Route::post('/kamars', [KamarController::class, 'store']);
Route::put('/kamars/{id}', [KamarController::class, 'update']);

//reservasi
Route::get('/reservasi/{tahun}', [ReservasiController::class, 'index']);
Route::get('/reservasi-user/{id}', [ReservasiController::class, 'reservasiByUser']);
Route::get('/reservasi-id/{id}',[ReservasiController::class, 'reservasiById']);
Route::get('/reservasi-payed/{id}', [ReservasiController::class, 'reservasiByUserPayed']);
Route::get('/reservasi-payment/{id}', [ReservasiController::class, 'reservasiUserPayment']);
Route::post('/reservasi/{id}', [ReservasiController::class, 'store']);
Route::post('/check-date', [ReservasiController::class, 'checkDate']);
Route::put('/reservasi/{id}/{startDate}', [ReservasiController::class, 'update']);
Route::put('/update-status/{id}', [ReservasiController::class, 'updateStatus']);
Route::put('/reservasi-cancel/{id}', [ReservasiController::class,'cancel']);
Route::delete('/delete-reservasi/{id}', [ReservasiController::class, 'destroy']);

//laporan
Route::get('/laporan/{tahun}', [LaporanReservasiController::class, 'getPendapatanBulanan']);

//payment
Route::post('/payment', [PaymentsController::class, 'store']);

//Dana
Route::get('/user-dana', [DanaPaymentsController::class, 'userDana']);

//WhatsApp sender
Route::post('/send-whatsapp', [WhatsAppController::class, 'sendMessage']);
Route::post('/send-whatsapp-cancel/{id}', [WhatsAppController::class, 'sendCancel']);
