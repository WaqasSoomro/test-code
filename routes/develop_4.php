<?php

Route::group(['namespace' => 'Api', 'prefix' => 'v1', 'middleware' => ['auth:api']], function ()
{
	Route::group(['namespace' => 'App', 'prefix' => 'app', 'middleware' => ['role:player|trainer']], function ()
	{
		Route::group(['namespace' => 'Events', 'prefix' => 'events'], function ()
		{
			Route::get('/', 'IndexController@index');
			Route::get('/by-date', 'IndexController@byDate');
			Route::get('/details/{id}', 'IndexController@details');
			Route::get('/is-attending', 'IndexController@isAttending');
		});
	});

	Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard', 'middleware' => ['role:trainer']], function ()
	{
		/*Route::group(['namespace' => 'Teams', 'prefix' => 'teams'], function ()
		{
			Route::group(['prefix' => 'players'], function ()
			{
				Route::get('/', 'PlayersController@index');
			});
		});

		Route::group(['namespace' => 'Events', 'prefix' => 'events'], function ()
		{
			Route::group(['prefix' => 'categories'], function ()
			{
				Route::get('/', 'CategoriesController@index');
			});

			Route::get('/', 'IndexController@index');
			Route::post('/create', 'IndexController@create');
			Route::get('/edit/{id}', 'IndexController@edit');
			Route::post('/update/{id}', 'IndexController@update');
			Route::get('/delete/{id}', 'IndexController@delete');
		});*/
	});
});