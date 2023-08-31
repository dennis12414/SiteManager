<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\SiteManager;
use App\Models\Worker;
use Illuminate\Http\Request;
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
            'phoneNumber' => 'required|numeric',
            'dateRegistered' => 'required|date',
            'payRate' => 'required|numeric',
            'siteManagerId' => 'required|numeric',
        ]);

        $existingWorker = Worker::where('phoneNumber', $request->phoneNumber)
            ->where('siteManagerId', $request->siteManagerId)
            ->first();

        if ($existingWorker) {
            return response(['message' => 'Phone Number already exists'], 409);
        }

        $siteManager = SiteManager::find($request->siteManagerId);
        if (!$siteManager) {
            return response(['message' => 'Site Manager does not exist'], 404);
        }

        $worker = Worker::create([
            'name' => $request->name,
            'phoneNumber' => $request->phoneNumber,
            'dateRegistered' => $request->dateRegistered,
            'payRate' => $request->payRate,
            'siteManagerId' => $request->siteManagerId,
        ]);

        return response([
            'message' => 'Worker created successfully',
            'worker' => $worker->only(['workerId', 'name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']),
        ], 201);
    }

    /**
     * search for workers
     */
    public function search(string $siteManagerId, string $searchTerm)
    {
        $workers = Worker::where('siteManagerId', $siteManagerId)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('phoneNumber', 'LIKE', '%' . $searchTerm . '%');
            })
            ->get();

            //the where method can take a closure as an argument. 
            //The closure receives a $query parameter, 
            //which is a new query builder instance that can be used to add more 
            //conditions to the query.



        if ($workers->isEmpty()) {
            return response([
                'message' => 'No workers found'
            ], 404);
        }

        $workerData = $workers->map(function ($worker) {
            return $worker->only(['workerId', 'name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']);
        });

        return response([
            'message' => 'Retrieved successfully', 
            'workers' => $workerData
        ], 200);
    }


    public function show(string $siteManagerId, string $startDate = null, string $endDate = null, string $searchQuery = null)
    {
        $query = Worker::where('siteManagerId', $siteManagerId);

        if ($startDate && $endDate) {
            $query->whereBetween('dateRegistered', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $query->where('dateRegistered', $startDate . ' 00:00:00');
        }
        $workers = $query->get();

        if ($searchQuery) {
            $workers = $workers->filter(function ($worker) use ($searchQuery) {
                return strpos(strtolower($worker->name), strtolower($searchQuery)) !== false ||
                    strpos(strtolower($worker->phoneNumber), strtolower($searchQuery)) !== false;
            })->values(); 
        }

        if ($workers->isEmpty()) {
            return response([
                'message' => 'No workers found'
            ], 404);
        }

        $workerData = $workers->map(function ($worker) {
            return $worker->only(['workerId', 'name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']);
        });

        return response([
            'message' => 'Retrieved successfully', 
            'workers' => $workerData
        ], 200);
    }


    public function update(Request $request, string $workerId)
    {
        $request->validate([
            'name' => 'required|string',
            'phoneNumber' => 'required|numeric',
            'payRate' => 'required|numeric',
        ]);

        $worker = Worker::find($workerId);
        if (!$worker) {
            return response([
                'message' => 'Worker does not exist'
            ], 404);
        }

        $worker->update([
            'name' => $request->name,
            'phoneNumber' => $request->phoneNumber,
            'payRate' => $request->payRate,
        ]);

        return response([
            'message' => 'Worker updated successfully',
            'worker' => $worker->only(['workerId', 'name', 'phoneNumber', 'payRate', 'dateRegistered', 'siteManagerId']),
        ], 200);
    }

    public function archive(string $workerId)
    {
        $worker = Worker::find($workerId);
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



