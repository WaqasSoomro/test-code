<?php

use Illuminate\Support\Facades\Mail;


/**
 * Dashboard APIs
 */

Route::group(['namespace' => 'Api\Dashboard', 'prefix' => 'v1/dashboard', 'middleware' => ['auth:api', 'role:trainer']], function () {

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/get-profile', 'TrainerProfileController@getTrainerProfile');
        Route::post('/update-profile', 'TrainerProfileController@updateTrainerProfile');

    });

    Route::group(['prefix' => 'notification'], function ()
    {
        Route::get('/get-notification', 'NotificationsController@getNotification');
        Route::post('/set-notification', 'NotificationsController@setNotification');
    });

    Route::group(['prefix' => 'exercises'], function () {
        Route::get('/get-player-all-exercise', 'ExerciseController@getPlayerAllExercise');
        Route::get('/get-player-exercise', 'ExerciseController@getPlayerExercise');
        Route::get('/get-exercise-types', 'ExerciseController@getExerciseTypes');
        Route::get('/get-teams', 'ExerciseController@getTeams');
        Route::get('/get-privacy', 'ExerciseController@getPrivacy');
        Route::get('/get-categories', 'ExerciseController@getCategories');
        Route::get('/get-tools', 'ExerciseController@getTools');
        Route::get('/get-skills', 'ExerciseController@getSkills');
        Route::get('/get-levels', 'ExerciseController@getLevels');
        Route::get('/get-my-exercises', 'ExerciseController@getMyExercises');
        Route::get('/get-my-exercises-detail', 'ExerciseController@getMyExercisesDetail');
        Route::post('/update-exercise-status', 'ExerciseController@updateExerciseStatus');
        Route::post('/add-exercise', 'ExerciseController@addExercise');
    });

});

/**
 * Stream APIs
 */

Route::group(['namespace' => 'Api\Stream', 'prefix' => 'v1/stream', 'middleware' => ['auth:api']], function () {

    Route::post('start', 'StreamController@start');
    Route::put('stop', 'StreamController@stop');
    Route::post('player', 'StreamController@getLiveStreamByPlayer');
    Route::get('player/{player}', 'StreamController@getLiveStreamByPlayer');

});
