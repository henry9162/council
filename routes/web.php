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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::view('scan', 'scan');

//--------------- Threads -------------------

Route::get('/threads', 'ThreadController@index')->name('threads');

Route::get('/thread/{channel}', 'ThreadController@index');
Route::get('/threads/search', 'SearchController@show');

Route::get('/threads/create', 'ThreadController@create');

Route::get('/thread/{channel}/{thread}', 'ThreadController@show');
Route::patch('/thread/{channel}/{thread}', 'ThreadController@update');

//Route::patch('/thread/{channel}/{thread}', 'ThreadController@update')->name('threads.update');

Route::post('/locked-threads/{thread}', 'LockedThreadController@store')->name('locked-threads.store')->middleware('admin');
Route::delete('/locked-threads/{thread}', 'LockedThreadController@destroy')->name('locked-threads.destroy')->middleware('admin');

Route::delete('/thread/{channel}/{thread}', 'ThreadController@destroy');

Route::post('/threads', 'ThreadController@store')->middleware('must-be-confirmed');

//------------------- Reply ---------------------------

Route::get('/thread/{channel}/{thread}/replies', 'ReplyController@index');

Route::post('/thread/{channel}/{thread}/replies', 'ReplyController@store');

Route::delete('/replies/{reply}', 'ReplyController@destroy');

Route::patch('/replies/{reply}', 'ReplyController@update');

Route::post('/replies/{reply}/best', 'BestReplyController@store')->name('best-replies.store');

//-------------------- Subscriptions -------------------

Route::post('/thread/{channel}/{thread}/subscriptions', 'ThreadSubscriptionController@store')->middleware('auth');

Route::delete('/thread/{channel}/{thread}/subscriptions', 'ThreadSubscriptionController@destroy')->middleware('auth');

//------------------- Favorites -------------------------

Route::post('/replies/{reply}/favorites', 'FavoriteController@store');

Route::delete('/replies/{reply}/favorites', 'FavoriteController@destroy');

//--------------- Profiles & Notifications --------------

Route::get('/profiles/{user}', 'ProfileController@show')->name('profile');

Route::get('/profiles/{user}/notifications', 'UserNotificationController@index');

Route::delete('/profiles/{user}/notifications/{notification}', 'UserNotificationController@destroy');

//--------------------------- Avatars -----------------------

Route::get('api/users', 'Api\UserController@index');

Route::post('api/users/{user}/avatar', 'Api\UserAvatarController@store')->middleware('auth')->name('avatar');

//--------------------------- Emails -----------------------

Route::get('/register/confirm', 'Auth\RegisterConfirmationController@index')->name('register.confirm');




