<?php
Route::group(['namespace' => 'Api\TrainerApp\DataPreference','prefix' => 'v1/trainerapp/datapreference', 'middleware' => ['auth:api', 'role:trainer']], function () {
    Route::get('/get-team-metric', 'IndexController@getTeamMetrics');
    Route::post('/save-team-metric', 'IndexController@saveTeamMetrics');
});