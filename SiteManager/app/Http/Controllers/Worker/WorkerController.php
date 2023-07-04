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
            'siteManagerId' => 'required|numeric', //this should be hidden from the user, it should be gotten from the token
        ]);

       
        $dateCreated = date('Y-m-d H:i:s'); 

        $worker = Worker::create([
            'name' => $request->name,
            'phoneNumber' => $request->phoneNumber,
            'dateRegistered' => $dateCreated,
            'payRate' => $request->payRate,
            'siteManagerId' => $request->siteManagerId,
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
            'workers' => $workers->only(['name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']),
        ], 200);
    } 

    /**
     * Display the specified resource.
     */
    public function show(string $id) 
    {
       
       // $siteManagerId = auth()->user()->siteManagerId;

        $worker = Worker::where('siteManagerId', $id)
            ->first(); 

        if(!$worker){
            return response([
                'message' => 'No workers found',
            ], 404);
        }

        return response([
            'message' => 'Retrieved successfully',
            'worker' => $worker->only(['name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId'])
        ], 200);

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
