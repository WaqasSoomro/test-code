<?php
/*
* Developer : Mr Optimist
* Email : fahadahmedcoder@gmail.com
*/
Route::group(['namespace' => 'Api', 'prefix' => 'v1/app', 'middleware' => ['auth:api','role:player|trainer']], function () {

    Route::group(['prefix' => 'contact'], function () {
        Route::post('store', 'ContactController@store');
        Route::get('/show', 'ContactController@show');
    });

    Route::get('/get-trainer-profile', 'PlayerAuthController@get_trainer_profile');
    Route::get('/get-player-profile', 'PlayerAuthController@get_player_profile');
    Route::get('/get-player-league', 'PlayerAuthController@get_player_league');

    Route::get('/get-player-skills', 'PlayerAuthController@get_player_skills');
    Route::get('/get-player-skill-insight', 'PlayerAuthController@get_player_skill_insight');
    Route::get('/get-player-skill-metrics', 'PlayerAuthController@get_player_skill_metrics');

    Route::get('/get-player-recommended-exercises', 'PlayerAuthController@get_player_recommended_exercises');
    Route::get('/get-player-sprint-speed', 'PlayerAuthController@getPlayerSprintSpeed');
    Route::get('/session-details-graph', 'GraphController@sessionDetailsGraph');
    Route::get('/training-sessions', 'PlayerAuthController@getTrainingSessions');
    Route::get('/training-session-dates', 'PlayerAuthController@getTrainingSessionDates');
    Route::get('/tempo-graph', 'GraphController@getTempoGraph');
    Route::get('/shots-graph', 'GraphController@getShotsGraph');
    Route::get('/leg-distribution-graph', 'GraphController@getLegDistributionGraph');
    Route::get('/suggested-exercises', 'PlayerAuthController@getSuggestedExcersices');
    Route::get('/suggested-skills-exercises', 'PlayerAuthController@getSuggestedSkillsExcersices');



    /**
     * Followers Followings
     **/
    Route::get('/get-player-followers-followings', 'PlayerAuthController@get_player_followers_followings');
    Route::get('/get-player-follow-requests', 'PlayerAuthController@getFollowingRequests');
    Route::post('/update-follow-request-status', 'PlayerAuthController@updateFollowRequestStatus');

    // 22 September 2020
    Route::get('/search-players-with-followers-followings', 'PlayerAuthController@search_players_with_followers_followings');

    Route::post('/create-remove-player-following', 'PlayerAuthController@create_remove_player_following');

    Route::get('/get-player-achievements', 'PlayerAuthController@get_player_achievements');
    Route::get('/get-player-teams', 'PlayerAuthController@get_player_teams');
    Route::get('/get-team-players', 'PlayerAuthController@getTeamPlayers');
    Route::get('/search-teams', 'PlayerAuthController@searchTeams');
    Route::post('/send-team-request', 'PlayerAuthController@sendTeamRequest');
    Route::post('/leave-team', 'PlayerAuthController@leaveTeam');
    Route::post('/review-trainer', 'PlayerAuthController@rateTrainer');
    Route::get('/session-details', 'PlayerAuthController@getSessionDetails');


    Route::get('/get-user-notifications', 'PlayerAuthController@get_user_notifications');
    Route::post('/update-user-notification', 'PlayerAuthController@update_user_notification');
    Route::post('/create-user-notification', 'PlayerAuthController@create_user_notification');
    Route::post('/notifications/delete', 'PlayerAuthController@deleteNotification');

    /**
     * Changing user privacy setting from public to private/follower
     * Changing user privacy setting from private to public/follower
     * Changing user privacy setting from follower to private/public
     */

    Route::get('/get-user-privacy-settings', 'PlayerAuthController@get_user_privacy_settings');
    Route::post('/update-user-privacy-setting', 'PlayerAuthController@update_user_privacy_setting');


    Route::group(['prefix' => 'battle'], function () {
        Route::get('friends', 'BattleAI\IndexController@getFriends');
//        Route::get('my-upcoming-battles', 'BattleController@myUpcomingBattles');
        Route::get('my-battle-results', 'BattleAI\ResultController@myBattleResults');
        Route::get('get-battle-round-result', 'BattleAI\ResultController@getBattleRoundResults');
        Route::post('create-match', 'BattleController@createMatch');
        Route::post('invite-friends', 'BattleAI\IndexController@inviteFriends');
        Route::post('end-round', 'BattleAI\BattleController@endRound');
        Route::post('respond-request', 'BattleAI\IndexController@respondRequest');
        Route::get('get-battle-channel','BattleAI\LobbyController@getBattleChannel');
        Route::get('get-ready','BattleAI\LobbyController@getReady');
        Route::get('force-ready','BattleAI\LobbyController@forceReady');
    });

    Route::group(["prefix" => "players-sensors-sessions", "namespace" => "App\PlayersSensorsSessions"], function ()
    {
        Route::get("/", "IndexController@index");
        Route::post("/create", "IndexController@create");
        Route::get("/get-aggregated", "IndexController@getAggregated");
    });

    /*Route::group(['prefix' => 'chat'], function () {
        Route::post('/send-message', 'Chat\ChatController@sendMessage');
        Route::get('/contacts', 'Chat\IndexController@contacts');
        Route::get('/messages', 'Chat\ChatController@getMessages');
        Route::post('/mark-read', 'Chat\ChatController@markAsRead');
        Route::post('/mark-unread', 'Chat\ChatController@markAsUnread');
        Route::post('/delete-message', 'Chat\ChatController@deleteMessage');
        Route::get('/teams', 'Chat\IndexController@trainer_teams');
        Route::get('/players', 'Chat\IndexController@club_players');
        Route::get('/search', 'Chat\IndexController@search');
        Route::group(['prefix'=>'groups','namespace'=>'Chat'],function(){
            Route::get('/','GroupController@userGroups');
            Route::get('/members','GroupController@getGroupMembers');
            Route::post('/save-group','GroupController@saveGroup');
            Route::get('/delete/{id}','GroupController@deleteGroup');
            Route::get('/remove-member','GroupController@removeMember');
            Route::get('/add-admin','GroupController@addAdmin');
            Route::get('/remove-admin','GroupController@removeAdmin');
        });
    });*/
});

/**
 * Dashboard APIs
 */
Route::group(['prefix' => 'v1/dashboard'], function () {

    Route::get('settings/get-club-data','Api\Dashboard\Setting\ClubController@getClubData');
    Route::group(['namespace' => 'Api\Dashboard','middleware' => ['auth:api', 'role:trainer']], function () {
        /**
         * Assignment APIs
         */

        Route::group(['prefix' => 'battle'], function () {
            Route::post('create-battle', 'Battle\IndexController@createBattle');
            Route::get('get-battles', 'Battle\IndexController@getBattles');
            Route::get('get-round-result', 'Battle\IndexController@getBattleRoundResults');
        });

        Route::group(['prefix' => 'teams'], function () {

            Route::get('/get-all-teams', 'TeamPlayerController@get_all_teams');

        });

        Route::group(['prefix' => 'exercises'], function () {

            Route::get('/get-all-exercises', 'TeamPlayerController@get_all_exercises');
            Route::get('/get-exercise-data', 'TeamPlayerController@get_exercise_data');
            Route::get('/play-exercise-video', 'TeamPlayerController@play_exercise_video');
            Route::get('/details/{id}', 'TeamPlayerController@exerciseDetails');
        });

        Route::group(['prefix' => 'players'], function () {
            Route::get('/get-filters', 'TeamPlayerController@getFilters');

            Route::get('/get-all-players', 'TeamPlayerController@get_all_players');
            Route::get('/get-player-details', 'TeamPlayerController@get_player_details');

            Route::get('/get-player-assignment-details', 'TeamPlayerController@get_player_assignment_details');
            Route::get('/player-exercises-listing', 'TeamPlayerController@player_exericse_listing');
            Route::get('/get-player-exercise-details', 'TeamPlayerController@get_player_exercise_details');

            Route::group(['prefix'=>'statistics'],function(){
                Route::get('/top-records', 'TeamPlayerController@getPlayerStatisticsTopRecords');
                Route::get('/session-metrics', 'TeamPlayerController@getPlayerStatisticsSessionMetrics');
                Route::get('/average-speed', 'TeamPlayerController@getPlayerStatisticsAverageSpeed');
                Route::get('/speed-zone', 'TeamPlayerController@getPlayerStatisticsSpeedZone');
                Route::get('/heart-rate', 'TeamPlayerController@getPlayerStatisticsHeartRate');
            });
        });

        Route::group(['prefix' => 'comments'], function () {
            Route::post('/add-edit', 'CommentTrainerController@addEdit');
            Route::post('/delete', 'CommentTrainerController@delete');
        });

        /*Route::group(['prefix' => 'chat'], function () {
            Route::post('/send-message', 'Chat\ChatController@sendMessage');
            Route::get('/contacts', 'Chat\IndexController@contacts');
            Route::get('/messages', 'Chat\ChatController@getMessages');
            Route::get('/teams', 'Chat\IndexController@trainer_teams');
            Route::get('/players', 'Chat\IndexController@club_players');
            Route::get('/search', 'Chat\IndexController@search');
            Route::post('/mark-read', 'Chat\ChatController@markAsRead');
            Route::group(['prefix'=>'groups','namespace'=>'Chat'],function(){
                Route::get('/','GroupController@userGroups');
                Route::get('/members','GroupController@getGroupMembers');
                Route::post('/save-group','GroupController@saveGroup');
                Route::post('/add-members','GroupController@saveGroupMembers');
                Route::get('/delete/{id}','GroupController@deleteGroup');
                Route::get('/remove-member','GroupController@removeMember');
                Route::get('/add-admin','GroupController@addAdmin');
                Route::get('/remove-admin','GroupController@removeAdmin');
            });
        });*/

        /**
         * Settings APIs
         */
        Route::group(['prefix'=>'settings'],function(){

            Route::get('/trainers/get-tabs-track', 'Setting\TrainersController@getTabsTrack');

            Route::get('/teams/{id?}','Setting\TeamsController@index')->where('id', '[0-9]+'); // EXECUTE THIS ONLY IF "id" IS OF TYPE NUMBER
            Route::get('/teams/get-age-groups','Setting\TeamsController@getAgeGroups');
            Route::get('/teams/get-team-genders','Setting\TeamsController@getTeamGenders');
            Route::get('/teams/get-team-types','Setting\TeamsController@getTeamTypes');
            Route::get('/teams/get-team-privacies','Setting\TeamsController@getTeamPrivacies');
            Route::post('/teams/save','Setting\TeamsController@saveTeam');
            Route::get('/teams/details/{id}','Setting\TeamsController@teamDetails');
            Route::get('/teams/sample-export','Setting\TeamsController@sampleExport');
            Route::post('/teams/bulk-import','Setting\TeamsController@bulkImport');
            Route::get('/teams/requests','Setting\TeamsController@teamRequests');
            Route::post('/teams/requests/accept','Setting\TeamsController@acceptTeamRequests');
            Route::post('/teams/requests/reject','Setting\TeamsController@rejectTeamRequests');
            Route::post('/teams/remove-player','Setting\TeamsController@removePlayer');
            Route::get('/teams/delete/{id}', 'Setting\TeamsController@delete');

            Route::get('/trainers','Setting\TrainersController@index');
            Route::get('/trainers/details/{id}','Setting\TrainersController@trainerDetails');
            Route::post('/trainers/update','Setting\TrainersController@updateTrainer');
            Route::post('/trainers/add','Setting\TrainersController@addTrainer');
            Route::post('/trainers/bulk-import','Setting\TrainersController@bulkImport');
            Route::post('/trainers/delete','Setting\TrainersController@delete');
            Route::get('/players','Setting\PlayersController@index');
            Route::post('/players/add','Setting\PlayersController@addPlayer');
            Route::get('/players/get-age-groups','Setting\PlayersController@getAgeGroups');
            Route::get('/players/get-genders','Setting\PlayersController@getGenders');
            Route::post('/players/update','Setting\PlayersController@updatePlayer');
            Route::post('/players/bulk-import','Setting\PlayersController@bulkImport');
            Route::get('/players/sample-export','Setting\PlayersController@sampleExport');
            Route::post('/players/review','Setting\PlayersController@reviewPlayer');
            Route::get('/players/delete/{id}', 'Setting\PlayersController@delete');

            Route::post('/update-club','Setting\ClubController@saveClub');
            Route::get('/get-club','Setting\ClubController@getClub');
            Route::post('/redeem-coupon','Setting\PlanController@redeemCoupon');
            Route::get('/plans','Setting\PlanController@getPlans');
            Route::post('/purchase-plan','Setting\PlanTransactionController@purchasePlan');
//            Route::post('/checkout','Setting\PlanTransactionController@checkout');
            Route::post('/checkout-test','Setting\PlanTransactionController@subscribePlanTest');
            Route::post('/create-customer','Setting\PlanTransactionController@createCustomer');
            Route::post('/checkout','Setting\PlanTransactionController@subscribePlan');
            Route::get('/transactions','Setting\PlanTransactionController@transactions');
            Route::get('/testpayments','Setting\PlanTransactionController@testPayments');
            Route::get('/testpayments/success','Setting\PlanTransactionController@testPaymentSuccess')->name('success');
        });

    });

});

//Route::stripeWebhooks('stripe-webhook');





