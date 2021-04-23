<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\frontends\PageController;
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
Route::get('image/{scr}/{w}/{h}', function($src, $w=100, $h=100){
	$caheimage = Image::cache(function($image) use ($src, $w, $h){ return $image->make(public_path('uploads/').$src)->fit($w, $h);}, 10, true);
	$extention = explode(".", $src);
	return $caheimage->response($extention[1]);
});

// Route::get('/', ['as'=>'home','uses'=>'frontends/PageController@index']);
Route::get('/', [PageController::class, 'index'])->name('home')->middleware('guest');
Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');


Route::view('login2', 'frontends.login');
