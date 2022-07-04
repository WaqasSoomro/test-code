<?php
/**
 * TRAINER APP V4 ROUTES
 */

Route::group(["prefix"=>"v1/trainerapp","namespace"=>"Api\TrainerApp","middleware"=>["auth:api","role:trainer"]],function (){


    /**
     * V4 Club Routes
     */
    Route::group(["prefix"=>"club","namespace"=>"Club"],function ()
    {
       Route::get("/","ClubController@get_trainer_clubs");
       Route::post("save-selected-club","ClubController@save_selected_club");
    });


});