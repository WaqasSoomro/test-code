<?php

use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\GeneralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PassportAuthController;
use App\Http\Controllers\Api\ReasonController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });



// Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);
Route::post('forgot-password', [PassportAuthController::class, 'forgot_password']);
Route::get('localization', [GeneralController::class, 'get_localization_data']);
Route::get('setting', [GeneralController::class, 'get_setting_data']);

Route::middleware('auth:api')->group(function () {
    Route::get('logout', [PassportAuthController::class, 'logout']);

    Route::get('get-user', [UserController::class, 'user_info']);
    Route::get('equipment-issue', [EquipmentController::class, 'user_equipment_issue']);


    Route::get('equipment-data', [EquipmentController::class, 'equipment_data']);
    Route::get('equipment-category', [EquipmentController::class, 'equipment_category']);
    Route::get('equipment-reason', [ReasonController::class, 'equipment_reason']);
    Route::get('equipment-request-history', [EquipmentController::class, 'equipment_request_history']);

    Route::post('equipment-request', [EquipmentController::class, 'equipment_request']);


});
