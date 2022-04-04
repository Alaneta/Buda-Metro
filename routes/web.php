<?php

use App\Http\Controllers\NetworkController;
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

Route::get('/', [NetworkController::class, 'index'])->name('home');
Route::get('/download', [NetworkController::class, 'downloadExample'])->name('network.download');
Route::get('/network/{id}', [NetworkController::class, 'showCalculateRoute'])->name('network.showCalculateRoute');
Route::post('/calculate/{id}', [NetworkController::class, 'calculateShortestRoute'])->name('network.calculateShortestRoute');

//Route::get('/welcome', function () {
//    return view('welcome');
//});

Route::post('/save', [NetworkController::class, 'save'])->name('save');
Route::delete('/delete/{id}', [NetworkController::class, 'delete'])->name('network.delete');
