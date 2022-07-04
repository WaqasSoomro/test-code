<?php

//Route::post('notify', function(){
//    $data['message'] = 'test from backend';
//    $data['title'] = 'test title';
//    $data['from_user_id'] = 1;
//    $data['to_user_id'] = 4;
//    $data['model_type'] = 'posts/like';
//    $data['model_type_id'] = 23;
//    $data['click_action'] = 'VideoAndComments';
//    $data['message'] =  'Muhammad shahzaib from backend liked your post';
//    $data['badge_count'] = 1;
//    $token = 'dKEh1181QCGZyWTviPDHzl:APA91bFQh_zxmeB8KE0UC3p_nT6Y0vmghII9sb0ju6W3FCgd6fc0GSdyWoOqSgVl83rXU0S2wfw06jf4ZgzXOxlPlQaJ-IRyqoeR6LsHFNCxH9QZ1l0aVqUm6HL7KRmX0Vv7tuxa6ACM';
////dd(config('firebase.server_api_key'));
//    return \App\Helpers\Helper::sendNotification($data, $token);
//});

/**
 * app (player) auth APIs
 */
Route::group(['namespace' => 'Api\Auth', 'prefix' => 'v1/app'], function () {
    Route::get('check-updates', 'AppAuthController@checkUpdates');
    Route::post('login', 'AppAuthController@login');
    Route::post('register', 'AppAuthController@register');
    Route::post('verify-user', 'AppAuthController@verifyUser');
    Route::post('auto-login', 'AppAuthController@autoLogin');
    Route::post('send-code', 'AppAuthController@sendVerificationCode');
    Route::post('logout', 'AppAuthController@logout')->middleware(['auth:api', 'role:player']);
});


Route::get('v1/app/get-data', 'Api\WantController@index');

/**
 * App APIs
 */
Route::group(['namespace' => 'Api', 'prefix' => 'v1/app', 'middleware' => ['auth:api', 'role:player|trainer']], function () {

    Route::group(['namespace' => 'App', 'prefix' => 'user'], function () {
        Route::post('update-profile-picture', 'UserController@updateProfilePicture');
    });

    Route::post('update-profile', 'App\UserController@updateProfile');
    Route::post('contact', 'App\UserController@contact');

    Route::group(['prefix' => 'profile'], function ()
    {
        Route::delete('delete/{id}', 'App\UserController@delete');
    });

    Route::group(['prefix' => 'home'], function () {
        Route::get('feeds', 'HomeController@feeds');
        Route::post('single-feed', 'HomeController@single_feed');
        Route::post('shinguard-request', 'HomeController@shinguardRequest');
    });

    Route::group(['prefix' => 'posts'], function () {
        Route::get('/', 'PostController@index');
        Route::get('/juggles', 'PostController@getJuggles');
        Route::get('/show', 'PostController@show');
        Route::post('/add-edit', 'PostController@addEdit');
        Route::post('/delete', 'PostController@delete');
    });


    Route::post('/video/save','PostController@saveVideo');
//    Route::post('/video/get','PostController@getVideos');

    Route::group(['prefix' => 'stories'], function () {
        Route::get('/', 'StoryController@index');
        Route::get('feeds', 'StoryController@feeds');
        Route::get('/show', 'StoryController@show');
        Route::post('/add-edit', 'StoryController@addEdit');
        Route::post('/delete', 'StoryController@delete');
    });

    Route::group(['prefix' => 'comments'], function () {
        Route::post('/add-edit', 'CommentController@addEdit');
        Route::post('/delete', 'CommentController@delete');
    });

    Route::group(['prefix' => 'likes'], function () {
        Route::post('/add-edit', 'LikeController@addEdit');
    });

    Route::get('get-exercise-categories', 'PracticeController@getExerciseCategories');
    Route::get('get-category-exercises', 'PracticeController@getCategoryExercises');
    Route::get('get-platform-exercises', 'PracticeController@platformExercises');
    Route::get('get-custom-exercises', 'PracticeController@getCustomExercises');
    Route::get('get-exercise-detail', 'PracticeController@getExerciseDetail');
    Route::get('get-previous-result', 'PracticeController@getPreviousResults');
    Route::get('all-exercises', 'PracticeController@allExercises');

    Route::get('get-assignments', 'PracticeController@getAssignments');
    Route::get('get-assignment-detail', 'PracticeController@getAssignmentDetail');
    Route::post('start-exercise', 'PracticeController@startExercise');
    Route::post('end-exercise', 'PracticeController@endExercise');
    Route::post('perform-exercise', 'PracticeController@performExercise');
    Route::post('exercise/upload-video', 'PracticeController@uploadVideo');
    Route::post('exercise/upload-csv', 'PracticeController@uploadCsv');
    Route::get('remove-assignments', 'PracticeController@removeAssignments');

    Route::post('complete-assignment', 'PracticeController@completeAssignment');
    Route::get('get-user-exercise-detail', 'PracticeController@getUserExerciseDetail');
    Route::post('share-post', 'PracticeController@sharePost');

    Route::get('get-player-exercise-json','PracticeController@getPlayerExerciseJson');
    Route::post('alki-player-exercise-json','PracticeController@alkiPlayerExerciseJson');

});


/**
 * HumanOx APIs
 */
Route::group(['prefix' => 'v1/app/humanox', 'namespace' => 'Api\App', 'middleware' => ['auth:api']], function () {

    //Route::get('get-match-stats', 'HumanOxController@get_match_stat_types');
    //Route::get('get-match-stats-details', 'HumanOxController@get_single_stat_record');
    //Route::post('mount-sensor', 'HumanOxController@mountSensor');
    //Route::post('disconnect-sensor', 'HumanOxController@disconnectSensor');
    //Route::post('start-training-session', 'HumanOxController@startTrainingSession');
   // Route::post('end-training-session', 'HumanOxController@endTrainingSession');
    //Route::post('submit-training-session', 'HumanOxController@submitTrainingSession');
});
//Route::get('v1/app/humanox/update-graph', 'Api\App\HumanOxController@updateGraph');

//Route::get('v1/app/humanox/update-graph', 'Api\App\HumanOxController@updateGraph');


/**
 * Dashboard APIs
 */
Route::group(['prefix' => 'v1/dashboard'], function () {
    /**
     * Auth APIs
     */
    Route::get('test-payment', 'Api\Dashboard\TestPaymentController@makePayment');
    Route::get('response', 'Api\Dashboard\TestPaymentController@response');
    Route::group(['namespace' => 'Api\Auth'], function () {
        /*Route::post('register', 'WebAuthController@register');
        Route::post('login', 'WebAuthController@login');
        Route::post('verify-user', 'WebAuthController@verifyUser');
        Route::post('auto-login', 'WebAuthController@autoLogin');
        Route::post('verify-code', 'WebAuthController@verifyCode');
        Route::post('reset-password', 'WebAuthController@resetPassword');
        Route::post('logout', 'WebAuthController@logout')->middleware(['auth:api', 'role:trainer']);*/
        Route::post('send-code', 'WebAuthController@sendCode');
    });

    Route::group(['namespace' => 'Api\Dashboard', 'middleware' => ['auth:api', 'role:trainer']], function () {
        /**
         * Assignment APIs
         */
        Route::group(['prefix' => 'assignments'], function () {
            Route::get('/', 'AssignmentController@index');
            Route::get('/detail', 'AssignmentController@detail');
            Route::get('/view-edit-detail', 'AssignmentController@viewEditDetail');
            Route::post('/add-edit', 'AssignmentController@addEditAssignment');
            Route::post('/delete', 'AssignmentController@delete');
            Route::post('/copy', 'AssignmentController@copyAssignment');

            Route::post('/add-exercise', 'AssignmentController@addExerciseToAssignment');
            Route::post('/remove-exercise', 'AssignmentController@removeExerciseFromAssignment');
            Route::post('/remove-player', 'AssignmentController@removePlayerFromExercise');
            Route::get('/get-player-assignments', 'AssignmentController@getPlayerAssignments');
        });

        Route::group(['prefix' => 'training-session'], function () {
            Route::get('/players/{team_id}', 'TrainingSessionController@getTeamPlayers');
            Route::post('/start-session', 'TrainingSessionController@startTrainingSession');
            Route::post('/end-session', 'TrainingSessionController@endTrainingSession');
            Route::post('/update-graph', 'TrainingSessionController@updateGraph');
        });

    });
});








