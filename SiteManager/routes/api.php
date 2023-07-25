<?php
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Project\ProjectController;
use Illuminate\Http\Request;
use App\Http\Controllers\Worker\WorkerController;
use App\Http\Controllers\ClockIns\ClockInsController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\SiteManager\SiteManagerController;
use App\Http\Controllers\PAYMENT\MPESAController;
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
Route::put('/projects/update/{projectId}', [ProjectController::class, 'update']);//update project
Route::delete('/projects/archive/{projectId}', [ProjectController::class, 'archive']);//archive project

Route::Get('/workers/{siteManagerId}',[WorkerController::class, 'show']);//show workers
Route::post('/workers',[WorkerController::class, 'store'])->name('workers.store');//create worker
Route::Get('/workers/search/{siteManagerId}/{searchTerm}',[WorkerController::class, 'search']);//search worker
Route::put('/workers/update/{siteManagerId}/{phoneNumber}',[WorkerController::class, 'update']);//update worker
Route::delete('/workers/archive/{workerId}',[WorkerController::class, 'archive']);//archive worker

Route::post('/clockIn',[ClockInsController::class, 'clockIn']);//clock in
Route::get('/clockedInWorker/{siteManagerId}/{projectId}',[ClockInsController::class, 'clockedInWorker'])
->where([
    // 'siteManagerId' => '[0-9]+',
    // 'projectId' => '[0-9]+',
    // 'startDate' => '[0-9]{4}-[0-9]{2}-[0-9]{2}', 
    // 'endDate' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
    // 'searchQuery' => '[a-zA-Z0-9]+',
    
]);//show clock ins

Route::post('/clockedInWorkers',[ClockInsController::class, 'clockedInWorkers']);//show clock ins


Route::Get('/report/{projectId}',[ReportController::class, 'generateReport']);
Route::Get('/workerReport/{workerId}',[ReportController::class, 'generateWorkerReport']);

Route::Get('/siteManager',[SiteManagerController::class, 'index']);//show workers
Route::delete('/siteManager/archive/{siteManagerId}',[SiteManagerController::class , 'destroy']);//create worker

Route::post('/b2c', [MPESAController::class, 'b2cRequest']);

// Route::post('v1/b2c/result', [MPESAController::class, 'result'])->name('b2c.result');
// Route::post('v1/b2c/timeout', [MPESAController::class, 'timeout'])->name('b2c.timeout');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// public function b2c(){

//     $credentials = [
//         'token'=>'ISSecretKey_test_637f9bea-9094-4eb0-ac1b-46c43fb6a90d',
//         'publishable_key'=>'ISPubKey_test_f082671e-ad0f-40ba-b734-595386c73565'
//     ];

//     // $phoneNumber = $request->phoneNumber;
//     // $amount = $request->amount;
    
//     $transactions = [
//         ['account'=>'254708374149','amount'=>'10', 'narrative'=>'Salary']
//     ];
    
//     $transfer = new Transfer();
//     $transfer->init($credentials);
    
//     $response=$transfer->mpesa("KES", $transactions);
    
//     //call approve method for approving last transaction
//     $response = $transfer->approve($response);
//     //json_decode($response);
//     print_r($response);
    
//     // How to check or track the transfer status
//     $response = $transfer->status($response->tracking_id);
//     print_r($response);
//}





















