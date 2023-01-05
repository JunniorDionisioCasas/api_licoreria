<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

Route::get('/symlink', function () {
    Artisan::call('storage:link');
});

Route::get('/link', function () {        
    $target = '/api.licoreriasansebastian.com/storage/app/public/invoices';
    $shortcut = '/public_html/api_public/public/storage/invoices';
    symlink($target, $shortcut);
});