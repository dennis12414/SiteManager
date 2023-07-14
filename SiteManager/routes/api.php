<?php
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Project\ProjectController;
use Illuminate\Http\Request;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\ClockIns\ClockInsController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\SiteManager\SiteManagerController;
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
Route::post('/register', [AuthenticationController::class, 'register']); //register
Route::post('/verify', [AuthenticationController::class, 'verify']);//verify
Route::post('/setPassword', [AuthenticationController::class, 'setPassword']);//set password

Route::post('/login', [AuthenticationController::class, 'login']);//login

Route::Get('/projects/{siteManagerId}', [ProjectController::class, 'show']);//show projects
Route::post('/projects', [ProjectController::class, 'store']);//create project
Route::Get('/projects/details/{projectId}', [ProjectController::class, 'details']);//get project
Route::delete('/projects/archive/{projectId}/{siteManagerId}', [ProjectController::class, 'archive']);//archive project

Route::Get('/workers/{siteManagerId}',[WorkerController::class, 'show']);//show workers
Route::post('/workers',[WorkerController::class, 'store'])->name('workers.store');//create worker
Route::Get('/workers/search/{siteManagerId}/{searchTerm}',[WorkerController::class, 'search']);//search worker
Route::put('/workers/update/{siteManagerId}/{phoneNumber}',[WorkerController::class, 'update']);//update worker
Route::delete('/workers/archive/{siteManagerId}/{phoneNumber}',[WorkerController::class, 'archive']);//archive worker

Route::post('/clockIn',[ClockInsController::class, 'clockIn']);//clock in


Route::Get('/report/{projectId}',[ReportController::class, 'generateReport']); //`generate report

Route::Get('/siteManager',[SiteManagerController::class, 'index']);//show workers

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});





















