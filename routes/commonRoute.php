<?php

//common event data route
Route::group(['prefix' => 'v1/common', 'middleware' => ['auth:api']], function () {
    Route::get('/get-event-data', 'Api\EventDataController@getEventData');
});