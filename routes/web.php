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

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::post('submit_feedbacks', 'ProcessFeedbacksController@index');

// Admin Interface Routes
Route::group(['prefix' => 'panel', 'middleware' => 'admin'], function()
{

    Route::get('connect', 'Panel\ConnectController@showConnect');
    Route::post('connect', 'Panel\ConnectController@storeConnect');

    CRUD::resource('products', 'Panel\ProductCrudController');

    Route::get('products/{id}/chart', 'Panel\ProductChartController@showChart');

    CRUD::resource('templates', 'Panel\TemplateCrudController');
    CRUD::resource('emails', 'Panel\EmailCrudController');
    CRUD::resource('unsubscribers', 'Panel\UnsubscriberCrudController');
    CRUD::resource('feedbacks', 'Panel\FeedbackCrudController');

});
