<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

//import Controller Class
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\KamarController;
use App\Http\Controllers\Api\ReservasiController;
use App\Http\Controllers\WhatsAppController;

use App\Http\Controllers\UserAuthController;

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
Route::post('/register',[UserAuthController::class, 'register']);
Route::post('/login',[UserAuthController::class, 'login']);
Route::post('/logout',[UserAuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/users/{id}',[UserAuthController::class, 'getUser']);

//email verif
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
 
    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
 
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

//kamar
Route::get('/kamars', [KamarController::class, 'index']);
Route::post('/kamars', [KamarController::class, 'store']);
Route::put('/kamars/{id}', [KamarController::class, 'update']);

//reservasi
Route::get('/reservasi', [ReservasiController::class, 'index']);
Route::get('/reservasi/{id}', [ReservasiController::class, 'reservasiByUser']);
Route::post('/reservasi/{id}', [ReservasiController::class, 'store']);
Route::post('/check-date', [ReservasiController::class, 'checkDate']);
Route::put('/reservasi/{id}/{startDate}', [ReservasiController::class, 'update']);

//WhatsApp sender
Route::post('/send-whatsapp', [WhatsAppController::class, 'sendMessage']);
