<?php

Route::group(['namespace' => 'Api', 'prefix' => 'v4'], function ()
{
	Route::group([
		'namespace' => 'App\PlayersSensorsSessions',
		'prefix' => 'players-sensors-sessions',
		'middleware' => ['auth:api', 'role:demo_trainer|trainer']
	], function ()
	{
		Route::get("/", "IndexController@index");
		Route::get("/get-aggregated", "IndexController@getAggregated");
	});

	Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard'], function ()
	{
		Route::group(['namespace' => 'Auth', 'prefix' => 'auth'], function ()
		{
			Route::post('sign-up', 'IndexController@signUp');
			Route::post('resend-otp', 'IndexController@resendOtp');
			Route::post('verify-otp', 'IndexController@verifyOtp');
			Route::post('sign-in', 'IndexController@signIn');
			Route::post('auto-sign-in', 'IndexController@autoSignIn');

			Route::group(['prefix' => 'forget-password'], function ()
			{
				Route::post('/', 'IndexController@forgetPassword');
				Route::post('/verify-otp', 'IndexController@verifyForgetPasswordOtp');
			});

			Route::post('update-password', 'IndexController@updatePassword');
			Route::get('resend-setup-password-link', 'IndexController@resendSetupPasswordLink');
			Route::get('view-profile', 'ProfileController@viewProfile');
			Route::post('set-password', 'IndexController@setPassword');
		});

		Route::group(['middleware' => ['auth:api', 'role:demo_trainer|trainer']], function ()
		{
			Route::group(['namespace' => 'Auth', 'prefix' => 'auth'], function ()
			{
				Route::post('sign-out', 'IndexController@signOut');
			});

			Route::group(['namespace' => 'General', 'prefix' => 'general'], function ()
			{
				Route::get('clubs', 'IndexController@index');
				Route::get('countries', 'IndexController@countries');
				Route::get('phone-codes', 'IndexController@phoneCodes');
				Route::get('languages', 'IndexController@languages');
				Route::get('positions', 'IndexController@positions');
				Route::get('lines', 'IndexController@lines');
			});

			Route::group(['namespace' => 'Clubs', 'prefix' => 'clubs'], function ()
			{
				Route::post('joining', 'IndexController@joining');
				Route::get('remind-owner', 'IndexController@remindOwner');
				Route::get('explore-jogo', 'IndexController@exploreJogo');
			});
			
		});

		Route::group(['middleware' => ['auth:api', 'role:demo_trainer']], function ()
		{
			Route::group(['namespace' => 'Auth', 'prefix' => 'auth'], function ()
			{
				Route::group(['prefix' => 'profile'], function ()
				{
					Route::post('setup', 'ProfileController@setup');
					Route::get('edit-profile', 'ProfileController@editProfile');
					Route::post('update-profile', 'ProfileController@updateProfile');
				});
			});

			Route::group(['namespace' => 'Clubs', 'prefix' => 'clubs'], function ()
			{
				Route::post('create', 'IndexController@create');
			});
		});

		Route::group(['middleware' => ['auth:api', 'role:trainer']], function ()
		{
			Route::group(['namespace' => 'Auth', 'prefix' => 'auth'], function ()
			{
				Route::group(['prefix' => 'profile'], function ()
				{
					Route::get('edit', 'ProfileController@index');
					
					Route::group(['prefix' => 'update'], function ()
					{
						Route::post('/', 'ProfileController@update');
						Route::post('verify-otp', 'ProfileController@verifyOtp');
						Route::get('resend-otp', 'ProfileController@resendOtp');
						Route::post('password', 'ProfileController@updatePassword');
					});
				});
			});

			Route::group(['namespace' => 'Clubs', 'prefix' => 'clubs'], function ()
			{
				Route::get('own', 'IndexController@myCLubs');
				Route::post('create/another', 'IndexController@createAnother');
				Route::get('edit/{id}', 'IndexController@edit');
				Route::post('update/{id}', 'IndexController@update');
				Route::get('verification-request', 'IndexController@verificationRequest');
				Route::delete('delete/{id}', 'IndexController@delete');

				Route::group(['prefix' => 'trainers'], function ()
				{
					Route::get('/', 'TrainersController@index');
					Route::get('joining-requests', 'TrainersController@joiningRequests');
					Route::get('approve-request', 'TrainersController@approveRequest');
					Route::post('create', 'TrainersController@create');
					Route::get('edit/{id}', 'TrainersController@edit');
					Route::post('update/{id}', 'TrainersController@update');
					Route::delete('delete/{id}', 'TrainersController@delete');
					
					Route::group(['prefix' => 'teams/{teamId}'], function ()
					{
						Route::delete('delete/{id}', 'TrainersController@deleteTeam');
					});
				});

				Route::group(['namespace' => 'Teams', 'prefix' => 'teams'], function ()
				{
					Route::group(['namespace' => 'Players', 'prefix' => 'players'], function ()
					{
						Route::get('listing-by-positions', 'IndexController@listingByPositions');
						Route::get('charts', 'IndexController@playerCharts');
						
						Route::group(['prefix' => 'exercises'], function ()
						{
							Route::get('view-file-json-content', 'ExercisesController@JSONFileContent');
						});
					});
				});
			});
			
			Route::group(['namespace' => 'Notifications', 'prefix' => 'notifications'], function ()
			{
				Route::get('/', 'IndexController@index');
			});

			Route::group(['namespace' => 'Events', 'prefix' => 'events'], function ()
			{
				Route::group(['prefix' => 'categories'], function ()
				{
					Route::get('/', 'CategoriesController@index');
				});

				Route::get('/', 'IndexController@index');
				Route::post('create', 'IndexController@create');
				Route::get('edit/{id}', 'IndexController@edit');
				Route::post('update/{id}', 'IndexController@update');
				Route::delete('delete/{id}', 'IndexController@delete');
				Route::delete('delete/player/{id}', 'IndexController@deletePlayer');
			});
		});
	});
});