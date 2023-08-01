<?php

use App\Http\Controllers\PromoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\RideController;



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
    Route::get('get-users', [AuthController::class, 'getUsers']);
    Route::get('get-user/{id}', [AuthController::class, 'getUserById']);
    Route::post('create-card', [AccountController::class, 'createCard']);
    Route::post('create-help', [PrivacyController::class, 'create_help']);
    Route::get('get-card', [AccountController::class, 'get']);
    Route::post('create-account', [AccountController::class, 'createAccount']);
    Route::get('get-account', [AccountController::class, 'getAccount']);
    Route::post('create-rating', [RatingController::class, 'createRating']); 
    Route::post('create-ride', [RideController::class, 'createRide']);
    Route::post('update-ride', [RideController::class, 'updateRideStatusToZero']);
    Route::get('get-ride', [RideController::class, 'getRide']);
    Route::get('get-ride-requests', [RideController::class, 'getDriverRideRequests']);
    Route::post('create-service', [ServicesController::class, 'createService']);
    Route::post('service-delete/{id}', [ServicesController::class, 'delete']);
});
    Route::post('otp', [AuthController::class, 'otpGenerate']);
    Route::post('card-delete/{id}', [AccountController::class, 'delete']);
    Route::post('privacy-create', [PrivacyController::class, 'create']);

    Route::get('privacy-get', [PrivacyController::class, 'getPrivacy']);
    Route::post('term-create', [PrivacyController::class, 'create_term']);
    Route::get('term-get', [PrivacyController::class, 'getTerm']);

  
    Route::get('services-get', [ServicesController::class, 'getService']); 


    Route::post('create-promo', [PromoController::class, 'createCodes']);
    Route::post('promo-valid', [PromoController::class, 'coupanValid']);
    Route::get('get-promo', [PromoController::class, 'getCodes']);
    Route::post('promo-delete/{id}', [PromoController::class, 'delete']);




