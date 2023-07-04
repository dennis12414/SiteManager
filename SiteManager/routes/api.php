<?php
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Project\ProjectController;
use Illuminate\Http\Request;
use App\Http\Controllers\Worker\WorkerController;
use Illuminate\Support\Facades\Route; 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/verify', [AuthenticationController::class, 'verify']);
Route::post('setPassword', [AuthenticationController::class, 'setPassword']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::Get('/projects', [ProjectController::class, 'index']);
Route::post('/projects', [ProjectController::class, 'store'])->middleware('auth:sanctum');

Route::Get('/workers',[WorkerController::class, 'index']);
Route::post('/workers',[WorkerController::class, 'store'])->middleware('auth:sanctum');
Route::Get('/workers/search/{name}',[WorkerController::class, 'search'])->middleware('auth:sanctum');



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
