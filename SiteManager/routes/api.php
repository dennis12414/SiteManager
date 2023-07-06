<?php
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Project\ProjectController;
use Illuminate\Http\Request;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\ClockIns\ClockInsController;
use App\Http\Controllers\Report\ReportController;
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
Route::post('/setPassword', [AuthenticationController::class, 'setPassword']);

Route::post('/login', [AuthenticationController::class, 'login']);

Route::Get('/projects/{siteManagerId}', [ProjectController::class, 'show']);
Route::post('/projects', [ProjectController::class, 'store']);

Route::Get('/workers/{siteManagerId}',[WorkerController::class, 'show']);
Route::post('/workers',[WorkerController::class, 'store']);
Route::Get('/workers/search/{siteManagerId}/{phoneNumber}',[WorkerController::class, 'search']);
Route::put('/workers/{siteManagerId}/{phoneNumber}',[WorkerController::class, 'update']);

Route::post('/clockIn',[ClockInsController::class, 'clockIn']);
Route::post('/clockOut',[ClockInsController::class, 'clockOut']);

Route::Get('/report/{projectId}',[ReportController::class, 'generateReport']);




