<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgetPasswordOtp', [AuthController::class, 'forgetPassword']);
    Route::post('verifyOtp', [AuthController::class, 'verifyOtp']);
    Route::post('imageUploadBase64', [AuthController::class, 'imageUploadBase64']);
    Route::post('fileUpload', [AuthController::class, 'fileUpload']);
    Route::post('/changePassword', [AuthController::class, 'changePassword']);
});
Route::middleware(['auth:api'])->group(function () {
    Route::post('/updateUser', [AuthController::class, 'updateUser']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::get('/userRequests', [AuthController::class, 'getUserRequests']);
    Route::post('/adminApproveRequests', [AuthController::class, 'adminApproveRequests']);
    Route::get('/logout', [AuthController::class, 'logout']);
});
Route::post('otp', [AuthController::class, 'otpGenerate']);
