<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\Auth\AuthController;


Route::post('/social-register', [SocialAuthController::class, 'socialRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/sendOtp', [AuthController::class, 'sendOtp']);
Route::post('/verifyPhone', [AuthController::class, 'verifyPhone']);
Route::group(['middleware' =>'auth:api'], function () {
Route::apiResource('users', UserController::class);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
