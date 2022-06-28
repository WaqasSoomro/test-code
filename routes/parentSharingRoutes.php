<?php

Route::group(['namespace' => 'Api', 'prefix' => 'v1'], function ()
{
	Route::group(['namespace' => 'ParentSharing', 'prefix' => 'parent-sharing'], function ()
	{
		Route::group(['namespace' => 'Auth', 'prefix' => 'auth'], function ()
		{
			Route::post('sign-up', 'IndexController@signUp');
			Route::post('sign-in', 'IndexController@signIn');
			Route::post('auto-sign-in', 'IndexController@autoSignIn');
			Route::post('verify-otp', 'IndexController@verifyOtp');
			Route::post('resend-otp', 'IndexController@resendOtp');
			Route::post('forget-password', 'IndexController@forgetPassword');
			Route::post('update-password', 'IndexController@updatePassword');
		});

		Route::group(['middleware' => ['auth:api', 'role:parents']], function ()
		{
			Route::group(['namespace' => 'Auth', 'prefix' => 'auth'], function ()
			{
				Route::post('sign-out', 'IndexController@signOut');

				Route::group(['prefix' => 'profile'], function ()
				{
					Route::group(['prefix' => 'update'], function ()
					{
						Route::post('/', 'ProfileController@update');
						Route::post('verify-otp', 'ProfileController@verifyOtp');
						Route::post('resend-otp', 'ProfileController@resendOtp');
						Route::post('password', 'ProfileController@updatePassword');
					});

					Route::get('edit', 'ProfileController@index');
				});
			});

			Route::group(['namespace' => 'Players', 'prefix' => 'players'], function ()
			{
				Route::get('/', 'IndexController@index');
				Route::get('/filters', 'IndexController@filters');
				Route::get('/exercises-details', 'IndexController@exercisesDetails');

				Route::group(['prefix'=>'statistics'], function()
				{
	                Route::get('top-records', 'IndexController@topRecords');
	                Route::get('session-metrics', 'IndexController@sessionMetrics');
	                Route::get('average-speed', 'IndexController@averageSpeed');
	                Route::get('speed-zone', 'IndexController@speedZone');
	                Route::get('heart-rate', 'IndexController@heartRate');
	            });
			});
		});
	});
});

Route::group(['namespace' => 'Api', 'prefix' => 'v1', 'middleware' => ['auth:api', 'role:trainer']], function ()
{
	Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard'], function ()
	{
		Route::group(['namespace' => 'ParentSharing', 'prefix' => 'parents'], function ()
		{
			Route::group(['prefix' => 'invited'], function ()
			{
				Route::get('/', 'IndexController@index');
				Route::post('/remove', 'IndexController@remove');
			});

			Route::group(['prefix' => 'invite'], function ()
			{
				Route::post('/', 'IndexController@invite');
			});
		});
	});
});