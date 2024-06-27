<?php

//import Controller Class
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\KamarController;
use App\Http\Controllers\Api\ReservasiController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\DanaPaymentsController;
use App\Http\Controllers\Api\LaporanReservasiController;

use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Validator;


use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserAuthDanaController;

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

    Mail::to($validated['email'])->send(new SendEmail($data));
    return new PostResource(true, 'Silahkan Cek Email Untuk Registrasi!', $data);
});
//kamar
Route::get('/kamars', [KamarController::class, 'index']);
Route::post('/kamars', [KamarController::class, 'store']);
Route::put('/kamars/{id}', [KamarController::class, 'update']);

//reservasi
Route::get('/reservasi', [ReservasiController::class, 'index']);
Route::get('/reservasi/{id}', [ReservasiController::class, 'reservasiByUser']);
Route::get('/reservasi-payed/{id}', [ReservasiController::class, 'reservasiByUserPayed']);
Route::get('/reservasi-payment/{id}', [ReservasiController::class, 'reservasiUserPayment']);
Route::post('/reservasi/{id}', [ReservasiController::class, 'store']);
Route::post('/check-date', [ReservasiController::class, 'checkDate']);
Route::put('/reservasi/{id}/{startDate}', [ReservasiController::class, 'update']);
Route::put('/update-status/{id}', [ReservasiController::class, 'updateStatus']);
Route::delete('/delete-reservasi/{id}', [ReservasiController::class, 'destroy']);

//laporan
Route::get('/laporan', [LaporanReservasiController::class, 'getPendapatanBulanan']);

//payment
Route::post('/payment', [PaymentsController::class, 'store']);

//Dana
Route::get('/user-dana', [DanaPaymentsController::class, 'userDana']);

//WhatsApp sender
Route::post('/send-whatsapp', [WhatsAppController::class, 'sendMessage']);
