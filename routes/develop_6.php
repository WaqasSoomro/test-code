<?php




/**
 * TrainerApp APIs- Trainer Skill Assignment
 * 
 */

Route::group(['namespace' => 'Api\TrainerApp\Assignments', 'prefix' => 'v1/trainerapp/assignments', 'middleware' => ['auth:api', 'role:trainer']], function () {

    Route::get('/', 'TrainerAssignmentController@index');
    Route::get('get-assignment-details', 'TrainerAssignmentController@detail');
    Route::get('/get-player-assignment-details', 'TrainerAssignmentController@get_player_assignment_details');
    Route::get('/player-skill-assignment-exercise', 'TrainerAssignmentController@play_exercise_video');
    Route::get('/get-player-details', 'TrainerAssignmentController@get_player_details');
    Route::post('/remove-assignment', 'TrainerAssignmentController@delete_assignment');
});

/**
 * TrainerApp APIs- Trainer Exercise
 * 
 */



Route::group(['namespace' => 'Api\TrainerApp\Exercises', 'prefix' => 'v1/trainerapp/exercises', 'middleware' => ['auth:api', 'role:trainer']], function () {

    Route::post('/trainer-session-create', 'TrainerExerciseController@sessionCreate');
    Route::post('/trainer-session-start', 'TrainerExerciseController@sessionStart');
    Route::post('/trainer-session-end', 'TrainerExerciseController@endExercise');
});

Route::group(['prefix' => 'v1/dashboard', 'middleware' => ['auth:api', 'role:trainer']], function () {
    Route::post('/set-device-token', 'Api\Dashboard\DeviceTokenController@setToken');
    Route::post('/set-notification-token', 'Api\Dashboard\DeviceTokenController@setOneSignalToken');
});


//CLUB FILL AND COUNTRY CODE SEPARATOR

Route::group(['namespace' => 'Api', 'prefix' => 'v1/dashboard', 'middleware' => ['auth:api', 'role:trainer|player']], function () {
    Route::post('/club-data-fill', 'ClubFillController@clubFill');
    Route::post('/set-country-code', 'CountryCodeController@country_code');
});

Route::group(['namespace' => 'Api', 'prefix' => 'v1/app', 'middleware' => ['auth:api', 'role:trainer|player']], function () {
    //Search Clubs
    Route::get('/search-clubs', 'ClubController@searchClubs');
    //Get Teams
    Route::get('/get-all-teams', 'PlayerAuthController@getAllTeams');
});

// SENSOR MODULE DETAILS APP/DASHBOARD

Route::group(["namespace" => "Api", "prefix" => "v1/app"], function () {
    Route::get("/get-file", "SensorController@getFile");
    Route::get("/sensor-logging-info", "SensorController@sensorLoggingInfo");
});

Route::group(
    [
        "namespace" => "Api",
        "prefix" => "v1/dashboard",
        'middleware' => ['auth:api']
    ],
    function () {
        Route::post("/post-sensor-detail", "SensorController@postSensorDetail");
    }
);
