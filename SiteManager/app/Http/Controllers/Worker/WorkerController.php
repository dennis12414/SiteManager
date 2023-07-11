<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phoneNumber' => 'required|unique:workers|numeric',
            'dateRegistered'=> 'required|date',
            'payRate' => 'required|numeric',
            'siteManagerId' => 'required|numeric', //this should be hidden from the user, it should be gotten from the token
        ]);

    
        $worker = Worker::create([
            'name' => $request->name,
            'phoneNumber' => $request->phoneNumber,
            'dateRegistered' => $request->dateRegistered,
            'payRate' => $request->payRate,
            'siteManagerId' => $request->siteManagerId,
        ]);

        return response([
            'message' => 'Worker created successfully',
            'worker' => $worker->only(['name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']),
        ], 201); 
    }

    public function search(String $siteManagerId, String $phoneNumber)
    {
        $workers = Worker::where('siteManagerId', $siteManagerId)
            ->where('phoneNumber', 'like', '%'.$phoneNumber.'%')
            ->get(); 

        if(!$workers){
            return response([
                'message' => 'No workers found',
            ], 404);
        }

        return response([
            'message' => 'Retrieved successfully',
            'workers' => $workers->map(function($worker){
                return $worker->only(['name', 'phoneNumber', 'payRate', 'dateRegistered']);
            })
        ], 200);

        
    } 

    /**
     * Display the specified resource.
     */
    public function show(string $id) 
    {
       
        $workers = Worker::where('siteManagerId', $id)
            ->get(); 

        if(!$workers){
            return response([
                'message' => 'No workers found',
            ], 404);
        }

        return response([
            'message' => 'Retrieved successfully',
            'workers' => $workers->map(function($worker){
                return $worker->only(['name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']);
            })
        ], 200);

    }

    /**
     * Update worker details
     */
    public function update(Request $request,string $siteManager, string $phoneNumber)
    {
        $request->validate([
            'name' => 'required|string',
            'phoneNumber' => 'required|numeric',
            'payRate' => 'required|numeric',
        ]);

        $worker = Worker::where('phoneNumber', $phoneNumber)
            ->where('siteManagerId', $siteManager)
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
            'worker' => $worker->only(['name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']),
        ], 200);

    }
    
    public function archive(string $phoneNumber){
        
        $worker = Worker::where('phoneNumber', $phoneNumber)->first();
        if (!$worker) {
            return response([
                'message' => 'Worker does not exist',
            ], 404); 
        }

        $worker->delete();

        return response([
            'message' => 'Worker archived successfully',
        ], 200); 

       
    }

}
