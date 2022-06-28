<?php

use App\Helpers\Helper;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

Route::post('upload-csv', 'Api\UploadController@upload');


Route::get('score-script',function(){
    $exercises = \App\Exercise::get();

    foreach ($exercises as $exercise){
        \App\ExerciseLevel::whereExerciseIdAndLevelId($exercise->id,'1')->update(['measure' => $exercise->score]);
    }
});

Route::get('remove-player-assignment',function(){
    $assignments = \App\Assignment::onlyTrashed()->get();
    foreach ($assignments as $assignment){
        \App\PlayerAssignment::whereAssignmentId($assignment->id)->delete();
    }
});

Route::get('/clear-cache', function() {
    $exitCode = \Illuminate\Support\Facades\Artisan::call('config:clear');
    $exitCode = \Illuminate\Support\Facades\Artisan::call('cache:clear');
    $exitCode = \Illuminate\Support\Facades\Artisan::call('config:cache');
    return response()->json(['status' => 'success','msg' => 'Cache cleared']);
});

Route::get('/generate-docs',function(){
    \Illuminate\Support\Facades\Artisan::call('apidoc:generate');
    return response()->json(['status' => 'success','msg' => 'Docs generated']);
});

// The API routes are exempted from app/Http/Middleware/VerifyCsrfToken.php
Route::get('test-payment', 'Api\Dashboard\Setting\PlanTransactionController@testPayment');
//Route::get('/callback', 'Api\Dashboard\StripeController@callback');
//Route::get('/checkout', 'Api\Dashboard\AdyenController@checkout');
//Route::post('/getPaymentMethods', 'Api\Dashboard\AdyenController@getPaymentMethods');
//Route::post('/initiatePayment', 'Api\Dashboard\AdyenController@initiatePayment');
//Route::post('/submitAdditionalDetails', 'Api\Dashboard\AdyenController@submitAdditionalDetails');
//Route::get( '/handleShopperRedirect', 'Api\Dashboard\AdyenController@handleShopperRedirect')->name('handleShopperRedirect');

Route::group(['namespace' => 'Api'], function ()
{
    Route::group(['namespace' => 'Scripts', 'prefix' => 'scripts'], function ()
    {
        Route::get('create-players-positions', 'IndexController@createPlayersPositions');
        Route::get('update-users', 'IndexController@updateUsers');
        Route::get('update-teams', 'IndexController@updateTeams');
        Route::get('update-exercises', 'IndexController@updateExercises');
        Route::get('update-events', 'IndexController@updateEvents');
    });
});

Route::group(['prefix' => 'translation'], function () {
    Route::post('/get-translation', 'Api\TranslationController@getTranslation');
});

include 'develop_1.php';
include 'develop_2.php';
include 'develop_3.php';
include 'develop_4.php';
include 'develop_5.php';
include 'develop_6.php';
include "trainerappv4routes.php";
include "v3dashboard.php";
include 'parentSharingRoutes.php';
include 'v4Routes.php';
include 'team_metric_v4_routes.php';
include 'commonRoute.php';