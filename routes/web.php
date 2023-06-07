<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('admins-only', function(){
    //if (Gate::allows('visitAdminPages')) { return 'Only admins page'; } 
    return 'Only admins page';
})->middleware('can:visitAdminPages');

//Route::get('/', function () { return view('home'); });
Route::get('/', [UserController::class, 'showCorrectHomepage'])->name('login');

//User related routes
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('mustBeLoggedIn');
Route::get('/manage-avatar', [UserController::class, 'showAvatrForm'])->middleware('mustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('mustBeLoggedIn');

//Blog post related routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('mustBeLoggedIn');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('mustBeLoggedIn');
Route::get('/post/{post}', [PostController::class, 'showSinglePost']);
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');//
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');//
Route::put('/post/{post}',[PostController::class, 'update'])->middleware('can:update,post');


//Profile related routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']);

