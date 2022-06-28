<?php
/**
 * TRAINER APP API
 */
Route::group(["prefix"=>"v1/trainerapp"],function (){


    /**
     * Chat API
     */
    Route::group(["namespace"=>"Api\TrainerApp"],function (){


       /* Route::group(["prefix"=>"chat",'middleware' => ['auth:api', 'role:trainer']],function (){

            Route::post('/save-group', 'Chat\ChatController@saveGroup'); //CREATE A NEW GROUP
            Route::get("/contacts","Chat\ChatController@contacts"); // GET CONTACTS
            Route::get('/players', 'Chat\ChatController@club_players'); // GET PLAYERS OF THE CURRENT TRAINER

        });*/

        /**
         * Events API
         */
        Route::group(["prefix"=>"events",'middleware' => ['auth:api', 'role:trainer']],function (){
            Route::get("/","Events\IndexController@index"); // LIST ALL THE EVENTS OF THE TRAINER
            Route::post("/create","Events\EventController@create"); // TO CREATE AN EVENT
            Route::post("/update/{id}","Events\EventController@update"); // TO UPDATE AN EVENT
            Route::get("/delete/{id}","Events\EventController@delete"); // TO DELETE AN EVENT
            Route::get("/categories","Events\EventController@eventCategories"); // LIST THE EVENT CATEGORIES
            Route::get("/by-date","Events\IndexController@byDate"); // LIST EVENTS BY DATE
            Route::get("/details/{id}","Events\EventController@details"); // GET EVENT DETAILS (THIS ROUTE STAYS IN THE LAST)
        });

        /**
         * Player / Team Routes
         */
        Route::group(["prefix"=>"teams","middleware"=>["auth:api","role:trainer"]],function ()
        {
            Route::get("/positions","Club\Teams\PlayerController@teamPositionListing"); // LIST THE TEAMS POSITIONS
            Route::get("/players","Club\Teams\PlayerController@listingByPositions"); // LIST THE PLAYERS OF THE TEAM
            Route::get("/get-teams-and-positions","Club\Teams\PlayerController@get_team_and_positions"); // GET TEAMS AND POSITIONS
        });

        /**
         * Trainer AUTH API
         */
        Route::post("/login","Auth\TrainerAuth@login");
        Route::post('/verifyuser',"Auth\TrainerAuth@verifyUser");
        Route::post("/send-code","Auth\TrainerAuth@send_code");
        Route::post("/verify-code","Auth\TrainerAuth@verify_code");
        Route::post("/reset-password","Auth\TrainerAuth@reset_password");
        Route::post("/logout","Auth\TrainerAuth@logout")->middleware(['auth:api', 'role:trainer']);

        /**
         * TrainerApp Profile API
         */
        Route::get("/get-trainer-profile","Auth\TrainerAuth@get_trainer_profile")->middleware(['auth:api', 'role:trainer']);
        Route::get("/get-team-players","Auth\TrainerAuth@getTeamPlayers")->middleware(['auth:api', 'role:trainer']);
        Route::get("/get-teams","Auth\TrainerAuth@trainer_teams")->middleware(['auth:api', 'role:trainer']);

        /**
         * Trainer Settings
         */
        Route::group(['prefix'=>'settings','middleware' => ['auth:api', 'role:trainer']],function (){
            Route::get("/get-team-requests","Settings\PlayerRequestController@getTeamRequests");
            Route::get("/get-team-details","Settings\PlayerRequestController@getTeamDetails");
            Route::post("/accept-team-request","Settings\PlayerRequestController@acceptTeamRequests");
            Route::post("/reject-team-request","Settings\PlayerRequestController@rejectTeamRequests");
        });


        /**
         * TrainerApp Assignment Share To Player API
         */
        Route::group(['middleware' => ['auth:api', 'role:trainer']],function (){
            Route::post("/share-to-player","Assignments\TrainerAssignmentController@shareToPlayer");
        });
    });

});