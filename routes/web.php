<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/test', function () { dump(request()->user()); return 'hi'; })->middleware('auth');

Route::get('auth/github', 'Auth\GitHubAuthController@redirectToProvider')->name('auth_github');
Route::get('auth/github/callback', 'Auth\GitHubAuthController@handleProviderCallback');

Route::get('auth/facebook', 'Auth\FacebookAuthController@redirectToProvider')->name('auth_facebook');
Route::get('auth/facebook/callback', 'Auth\FacebookAuthController@handleProviderCallback');
