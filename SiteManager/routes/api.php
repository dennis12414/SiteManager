<?php

use App\Http\Controllers\Advertise\AdvertController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Chat\ChatMessageController;
use App\Http\Controllers\Project\ProjectController;
use Illuminate\Http\Request;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\ClockIns\ClockInsController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\SiteManager\SiteManagerController;
use App\Http\Controllers\SiteManager\ProjectSiteImages;
use App\Http\Controllers\PAYMENT\B2C\B2CCntroller;
use App\Http\Controllers\PAYMENT\B2C\B2CResponse;
use App\Http\Controllers\PAYMENT\C2B\C2BController;
use App\Http\Controllers\PAYMENT\C2B\C2BResponse;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\TaskController;
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
Route::post('/forgotPassword', [AuthenticationController::class, 'forgotPassword']);//login


Route::post('/payWorker', [B2CCntroller::class, 'initiatePayment']);
Route::post('callback', [B2CResponse::class, 'b2CResponse']);
//Route::post('/b2c/timeout', [MPESAController::class, 'timeout'])->name('b2c.timeout');
Route::post('confirmation', [C2BResponse::class, 'confirmation']);



//Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']);


    Route::Get('/task/{projectId}', [TaskController::class, 'show']);
    Route::Get('/task/status/{projectId}', [TaskController::class, 'taskStatusCounts']);
    Route::post('/task/create', [TaskController::class, 'store']);
    Route::put('/task/{taskId}/update', [TaskController::class, 'update']);


    Route::Get('/advert', [AdvertController::class, 'index']);
    Route::Get('/advert/{advertId}', [AdvertController::class, 'show']);
    Route::post('/advert/create', [AdvertController::class, 'store']);
    Route::put('/advert/{advertId}/update', [AdvertController::class, 'update']);



    Route::Get('/projects/{siteManagerId}', [ProjectController::class, 'show']);//show projects
    Route::post('/projects', [ProjectController::class, 'store']);//create project
   Route::post('/projects/addMember/{userId}/{inviteCode}', [ProjectController::class, 'addMember']);//add member
    Route::Get('/projects/details/{projectId}', [ProjectController::class, 'details']);//get project
    Route::post('/projects/update/{projectId}/{userId}', [ProjectController::class, 'update']);//update project
    Route::delete('/projects/archive/{projectId}', [ProjectController::class, 'archive']);//archive project
    Route::get('/projects/getMembers/{projectId}', [ProjectController::class, 'getMembers']);//archive project


   Route::Get('/chat', [ChatMessageController::class, 'index']);
   Route::Post('/chat/create', [ChatMessageController::class, 'store']);


    //handle project images
    Route::post('/projectImages/{projectId}', [ProjectSiteImages ::class, 'uploadSiteImage']);//create project
    Route::get('/images/{filename}', [ProjectSiteImages ::class, 'show']);
    Route::get('/images/projectImages/{projectId}', [ProjectSiteImages ::class, 'projectImages']);

    Route::Get('/workers/{siteManagerId}',[WorkerController::class, 'show']);//show workers
    Route::post('/workers',[WorkerController::class, 'store'])->name('workers.store');//create worker
    Route::Get('/workers/search/{siteManagerId}/{searchTerm}',[WorkerController::class, 'search']);//search worker
    Route::put('/workers/update/{workerId}',[WorkerController::class, 'update']);//update worker
    Route::delete('/workers/archive/{workerId}',[WorkerController::class, 'archive']);//archive worker


    Route::get('/clockInss/{id}/{pId}',[ClockInsController::class, 'clockednotClocked']);
    Route::post('/clockIn',[ClockInsController::class, 'clockIn']);//clock in
    Route::get('/clockedInWorker/{siteManagerId}/{projectId}',[ClockInsController::class, 'clockedInWorker']);
    Route::post('/clockIns/undo', [ClockInsController::class, 'undoClockedIn']);

    Route::post('/clockedInWorkers',[ClockInsController::class, 'clockedInWorkers']);//show clock ins

    Route::Get('/report/payments/{siteManagerId}/{projectId}',[ReportController::class, 'getWorkerToPay']);
    Route::Get('/report/{projectId}',[ReportController::class, 'generateReport']);
    Route::Get('/workerReport/{workerId}/{projectId}',[ReportController::class, 'generateWorkerReport']);

    Route::Get('/siteManager',[SiteManagerController::class, 'index']);//show workers
    Route::delete('/siteManager/archive/{siteManagerId}',[SiteManagerController::class , 'destroy']);//create worker

    Route::get('/walletBalance/{phoneNumber}', [WalletController::class, 'getWalletBalance']);

    Route::post('/debitWallet', [C2BController::class, 'initiatePayment']);
    Route::get('/walletLoadingStatus/{partnerReferenceID}', [C2BController::class, 'getPaymentStatus']);
    Route::get('/paymentStatus/{payerTransactionID}', [B2CCntroller::class, 'getPaymentStatus']);
    Route::get('/transactionHistory/{phoneNumber}', [WalletController::class, 'getTransactionHistory']);

//});




Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
























