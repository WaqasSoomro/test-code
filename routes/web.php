<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('docs/collection.json', function(){
    $file= public_path(). "/docs/collection.json";

    return \Illuminate\Support\Facades\Response::download($file, 'collection.json');
});


Route::get('pay','PaymentController@payment');
Route::post('pay','PaymentController@processPayment')->name('pay');
Route::get('payment-success','PaymentController@paymentSuccess')->name('payment-success');
Route::get('payment-failed','PaymentController@paymentFailed')->name('payment-failed');
Route::get('payment-session','PaymentController@createSession')->name('payment-session');
Route::get('payment','PaymentController@pay');

Route::get('docs', function ()
{
    return redirect(asset('docs/index.html'));
});

Route::get('docs/collection', function ()
{
    /*return asset('docs/index.html');

    return response()->download(asset('docs/collection.json'), 'collection.json');

    return \Illuminate\Support\Facades\Response::download(asset('docs/collection.json'), 'collection.json');*/

    return '<a href="'.asset('docs/collection.json').'" download>Download Collection</a>';
});