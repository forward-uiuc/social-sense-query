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
Route::get('home', 'HomeController@index')->name('home');
Route::get('about', 'PagesController@about')->name('about');
Route::get('/', 'PagesController@landing')->name('landing');

Route::get('/meta-queries/create', 'MetaQueryController@create');

Route::get('login/{provider}', 'AuthorizationController@authorizeProvider')->name('authorizeProvider');
Route::get('login/{prodier}/return', 'AuthorizationController@createAuthorization')->name('authorizationReturn');

Route::resource('queries', 'QueryController');
Route::resource('users', 'UserController');
Route::get('queries/{id}/submit', 'QueryController@submit');

