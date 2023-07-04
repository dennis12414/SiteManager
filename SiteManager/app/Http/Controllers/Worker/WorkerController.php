<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    /**
     * get all workers
     */
    public function index()
    {
        $siteManagerId = auth()->user()->siteManagerId;
        $workers = Worker::where('siteManagerId', $siteManagerId)->get();
        return response([
            'message' => 'Retrieved successfully',
            'workers' => $workers,
        ], 200);
        
    }

    /**
     * Create a new worker
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phoneNumber' => 'required|unique:workers|numeric',
            'payRate' => 'required|numeric',
        ]);

        $siteManagerId = auth()->user()->siteManagerId;
        $dateCreated = date('Y-m-d H:i:s'); 

        $worker = Worker::create([
            'name' => $request->name,
            'phoneNumber' => $request->phoneNumber,
            'dateRegistered' => $dateCreated,
            'payRate' => $request->payRate,
            'siteManagerId' => $siteManagerId,
        ]);

        return response([
            'message' => 'Worker created successfully',
            'worker' => $worker,
        ], 201);
    }

    /**
     * Search
     */
    public function search(String $name)
    {

        $siteManagerId = auth()->user()->siteManagerId;

        $workers = Worker::where('name', 'LIKE', '%' . $name . '%')
            ->where('siteManagerId', $siteManagerId)
            ->get();
        
       if($workers->isEmpty()){
            return response([
                'message' => 'No workers found',
            ], 404);
        }

        return response([
            'message' => 'Retrieved successfully',
            'workers' => $workers,
        ], 200);
    } 

    /**
     * Display the specified resource.
     */
    public function show(string $id) 
    {
        

    }

    /**
     * Update worker details
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'phoneNumber' => 'required|numeric',
            'payRate' => 'required|numeric',
        ]);

        $siteManagerId = auth()->user()->siteManagerId;

        $worker = Worker::where('workerId', $id)
            ->where('siteManagerId', $siteManagerId)
            ->first(); //

        if(!$worker){
            return response([
                'message' => 'Worker not found',
            ], 404);
        }

        $worker->update([
            'name' => $request->name,
            'phoneNumber' => $request->phoneNumber,
            'payRate' => $request->payRate,
        ]);

        return response([
            'message' => 'Worker updated successfully',
            'worker' => $worker,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
