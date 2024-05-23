<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Setting;

//use carbon
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


use function PHPUnit\Framework\isEmpty;

class WorkerController extends Controller
{
    /**
     * create a new worker
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phoneNumber' => 'required|string',
            'dateRegistered' => 'required|date',
            'payRate' => 'required|numeric',
            'siteManagerId' => 'required|numeric',
            'role' => 'required|string',
            'gender' => 'string',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $existingWorker = Worker::where('phoneNumber', $request->phoneNumber)
            ->where('siteManagerId', $request->siteManagerId)
            ->first();

        if ($existingWorker) {
            return response(['message' => 'Phone Number already exists'], 409);
        }

        //site manager should exist
        $siteManager = SiteManager::where('siteManagerId', $request->siteManagerId)->first();
        if(!$siteManager){
            return response([
                'message' => 'Site Manager does not exist',
            ], 404);
        }



        if($request->file('image') != null){
            $uuid = Str::uuid();

            $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
            if (!$imageFolderPath) {
                return response([
                    'ty' => $imageFolderPath,
                    'message' => 'Image folder path not found in settings',
                ], 500);
            }
            $uploadedImage = $request->file('image');

            $imageName = $uuid . '_' . $uploadedImage->getClientOriginalName();

            $uploadedImage->storeAs($imageFolderPath, $imageName);

            $worker = Worker::create([
                'name' => $request->name,
                'phoneNumber' => $request->phoneNumber,
                'dateRegistered' => $request->dateRegistered,
                'payRate' => $request->payRate,
                'siteManagerId' => $request->siteManagerId,
                'role' => $request->role,
                'gender' => $request->gender,
                'profilePic' => $imageName,
            ]);

            $domain = Setting::where('setting_key', 'domain')->value('value');

            $worker->profilePic = $domain . '/' .$imageFolderPath. '/' . $worker->profilePic;

            return response([
                'message' => 'Worker created successfully',
                'worker' => $worker->only(['workerId',
                'name', 'phoneNumber', 'payRate',
                 'dateRegistered', 'siteManagerId',
                'role',
                'gender',
                'profilePic',]),
            ], 201);
        }else{
            $worker = Worker::create([
                'name' => $request->name,
                'phoneNumber' => $request->phoneNumber,
                'dateRegistered' => $request->dateRegistered,
                'payRate' => $request->payRate,
                'siteManagerId' => $request->siteManagerId,
                'role' => $request->role,
                'gender' => $request->gender,
                'profilePic' => null,
            ]);

            return response([
                'message' => 'Worker created successfully',
                'worker' => $worker->only(['workerId',
                'name', 'phoneNumber', 'payRate',
                 'dateRegistered', 'siteManagerId',
                'role',
                'gender',
                'profilePic',]),
            ], 201);

        }


    }

    /**
     * search for workers
     */
    public function search(string $siteManagerId, string $searchTerm)
    {
        $workers = Worker::where('siteManagerId', $siteManagerId)
            ->where('name', 'LIKE', '%'.$searchTerm.'%')
            ->orWhere('phoneNumber', 'LIKE', '%'.$searchTerm.'%')
            ->get();

        //workers is empty
        if(!$workers){
            return response([
                'message' => 'No workers found',
            ], 404);
        }

        // $domain = Setting::where('setting_key', 'domain')->value('value');
        // $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
        // $workers->profilePic = $domain . '/' .$imageFolderPath. '/' . $workers->profilePic;


        return response([
            'message' => 'Retrieved successfully',
            'workers' => $workers->map(function($worker){

                return $worker->only(['workerId','name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId', 'role',
                'gender','profilePic',]);
            })
        ], 200);

    }


    public function show(string $id, string $startDate = null, string $endDate = null, string $searchQuery = null)
    {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $searchQuery = request('searchQuery');

        if($startDate && $endDate){
            $startDate = $startDate . ' 00:00:00';
            $endDate = $endDate . ' 23:59:59';
            $workers = Worker::where('siteManagerId', $id)
                ->whereBetween('dateRegistered', [$startDate, $endDate])
                ->get();
        }elseif($startDate){

            $workers = Worker::where('siteManagerId', $id)
                ->where('dateRegistered',  [$startDate . ' 00:00:00', $startDate . ' 23:59:59'])
                ->get();
        }
        else{

            $workers = Worker::where('siteManagerId', $id)->get();
        }



        if($searchQuery){
            $workers = $workers->filter(function($worker) use ($searchQuery){
                if(strpos(strtolower($worker->name), strtolower($searchQuery)) !== false || strpos(strtolower($worker->phoneNumber) , strtolower($searchQuery)) !== false){
                    return true;
                }
            })->values();
        }

        if(!$workers){
            return response([
                'message' => 'No workers found',
            ], 404);
        }

        return response([
            'message' => 'Retrieved successfully',
            'workers' => $workers->map(function($worker){
                return $worker->only(['workerId','name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId',    'role','gender',
                'profilePic',]);
            })
        ], 200);

    }

    public function update(Request $request,string $workerId)
    {
        $request->validate([
            'name' => 'required|string',
            'phoneNumber' => 'required|numeric',
            'payRate' => 'required|numeric',
        ]);

        $worker = Worker::where('workerId', $workerId)
            ->first();
        if(!$worker){
            return response([
                'message' => 'Worker does not exist',
            ], 404);
        }

        $worker->name = $request->name;
        $worker->phoneNumber = $request->phoneNumber;
        $worker->payRate = $request->payRate;
        $worker->save();

        return response([
            'message' => 'Worker updated successfully',
            'worker' => $worker->only(['workerId','name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId',     'role',
            'gender',
            'profilePic',]),
        ], 200);

    }

    public function archive(string $workerId){

        $worker = Worker::where('workerId', $workerId)->first();

        if (!$worker) {
            return response([

                'workerId' => $workerId,
                'message' => 'Worker does not exist',
            ], 404);
        }

        $worker->delete();

        return response([

            'workerId' => $workerId,
            'message' => 'Worker archived successfully',
        ], 200);


    }

}



