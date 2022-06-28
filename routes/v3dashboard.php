<?php
/**
 * V3 Dashboard APIS
 */
Route::group(["prefix"=>"v3/dashboard","namespace"=>"Api\V3Dashboard","middleware"=>["auth:api","role:trainer"]],function (){

    /**
     * CLUB ROUTES
     */
    Route::get("clubs","IndexController@get_clubs");

    /**
     * TRAINER ROUTES
     */
    Route::group(["prefix"=>"settings/trainer"],function (){
        Route::post("addorremove","TrainerController@add_or_remove_trainer");
        //Route::post("deletetrainer","TrainerController@deleteTrainer");
    });

    /**
     * Exercise Routes
     */
    Route::group(["prefix"=>"exercise"],function (){
//        Route::post("create-or-update-exericse","ExerciseController@create_or_update_exercise");
        Route::post("delete-exercise","ExerciseController@delete_exercise");
    });

    /**
     * TEAM Routes
     */
    Route::group(["prefix"=>"team"],function (){
        Route::post("delete","TeamController@delete_team");
    });

    /**
     * Skill Assignment Routes
     */
    Route::group(["prefix"=>"assignment"],function (){
       Route::get("get-lines","SkillAssignment@get_lines");
       Route::get("get-positions","SkillAssignment@get_positions");
       Route::get("filter-exercises","SkillAssignment@filter_exercises");
    });
});