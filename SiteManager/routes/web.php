<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/documentation', function () {
//     return view('swagger.index');
// });

Route::get('/documentation', function () {
    return view('swagger.swagger');
});


// Route::get('/swagger', function () {
//     return view('swagger');
// });

// Route::get('/swagger-json', function () {
//     return response()->file(storage_path('app/openapi/swagger.yaml'));
// });

