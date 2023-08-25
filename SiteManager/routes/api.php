<?php
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Project\ProjectController;
use Illuminate\Http\Request;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\ClockIns\ClockInsController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\SiteManager\SiteManagerController;
use App\Http\Controllers\PAYMENT\B2C\B2CCntroller;
use App\Http\Controllers\PAYMENT\B2C\B2CResponse;
use App\Http\Controllers\PAYMENT\C2B\C2BController;
use App\Http\Controllers\PAYMENT\C2B\C2BResponse;
use App\Http\Controllers\Wallet\WalletController;
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


Route::post('/payWorker', [B2CCntroller::class, 'initiatePayment']);
Route::post('callback', [B2CResponse::class, 'b2CResponse']);
Route::post('/b2c/timeout', [MPESAController::class, 'timeout'])->name('b2c.timeout');



Route::post('/debitWallet', [C2BController::class, 'initiatePayment']);
Route::post('confirmation', [C2BResponse::class, 'confirmation']);



Route::middleware('auth:api')->group(function () { 

    Route::post('/logout', [AuthenticationController::class, 'logout']);

    Route::Get('/projects/{siteManagerId}', [ProjectController::class, 'show']);//show projects
    Route::post('/projects', [ProjectController::class, 'store']);//create project
    Route::Get('/projects/details/{projectId}', [ProjectController::class, 'details']);//get project
    Route::put('/projects/update/{projectId}', [ProjectController::class, 'update']);//update project
    Route::delete('/projects/archive/{projectId}', [ProjectController::class, 'archive']);//archive project

    Route::Get('/workers/{siteManagerId}',[WorkerController::class, 'show']);//show workers
    Route::post('/workers',[WorkerController::class, 'store'])->name('workers.store');//create worker
    Route::Get('/workers/search/{siteManagerId}/{searchTerm}',[WorkerController::class, 'search']);//search worker
    Route::put('/workers/update/{workerId}',[WorkerController::class, 'update']);//update worker
    Route::delete('/workers/archive/{workerId}',[WorkerController::class, 'archive']);//archive worker

    Route::post('/clockIn',[ClockInsController::class, 'clockIn']);//clock in
    Route::get('/clockedInWorker/{siteManagerId}/{projectId}',[ClockInsController::class, 'clockedInWorker']);

    Route::post('/clockedInWorkers',[ClockInsController::class, 'clockedInWorkers']);//show clock ins


    Route::Get('/report/{projectId}',[ReportController::class, 'generateReport']);
    Route::Get('/workerReport/{workerId}',[ReportController::class, 'generateWorkerReport']);

    Route::Get('/siteManager',[SiteManagerController::class, 'index']);//show workers
    Route::delete('/siteManager/archive/{siteManagerId}',[SiteManagerController::class , 'destroy']);//create worker

    Route::get('/walletBalance/{phoneNumber}', [WalletController::class, 'getWalletBalance']);
    Route::get('/walletTransactions/{phoneNumber}', [WalletController::class, 'getWalletTransactions']);

});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
























