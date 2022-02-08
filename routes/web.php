<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
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

Route::group(['middleware' => 'auth'], function(){
    Route::view('home', 'home');
    Route::resource('articles', ArticleController::class);
    // invitation link for users to join organization
    Route::view('invite', 'invite')->name('invite');

    // administrative routes
    Route::group(['middleware' => 'is_admin'], function(){
        Route::resource('categories', CategoryController::class);
    });
});


