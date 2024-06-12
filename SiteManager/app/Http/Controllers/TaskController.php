<?php

namespace App\Http\Controllers;

use App\Models\Advertiser;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Project;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::select('taskId', 'title', 'updated_at')->get();

        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->title,
                'updated_at' => $task->updated_at->format('Y-m-d H:i:s'), // Format the update timestamp as desired
            ];
        });

        return response()->json(['tasks' => $formattedTasks], 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'projectId'=>'required|integer',
            'title' => 'required|string',
            'budget' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            //'image_attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Example validation for image attachments
            'assignees' => 'array',
            'assignees.*' => 'integer|exists:workers,workerId', // Assuming workers are stored in the 'workers' table
            'status' => 'required|string|in:pending,in_progress,completed,not_started',
        ]);

        //check if site manager exists
        $project = Project::where('projectId', $request->projectId)->first();
        if (!$project) {
            return response([
                'message' => 'Project does not exist',
            ], 404);
        }


        $task = Task::create([
            'projectId' => $validatedData['projectId'],
            'title' => $validatedData['title'],
            'budget' =>  $validatedData['budget'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'status' => $validatedData['status'],
        ]);

        $task->refresh();

        if($request->assignees != null){
        $task->assignees()->attach($validatedData['assignees']);
        }

        return response([
            'message' => 'Task created successfully',
            'task' =>$task->only(['taskId','projectId','title','budget','start_date', 'end_date','status','assignees'])
        ],201);

    }

    public function taskStatusCounts(string $projectId)
    {
            $statuses = ['pending', 'in_progress', 'completed', 'not_started'];
            $statusCounts = Task::selectRaw('status, count(*) as count')
                ->where('projectId', $projectId)
                ->whereIn('status', $statuses)
                ->groupBy('status')
                ->get();


            $formattedStatusCounts = array_fill_keys($statuses, 0);


            foreach ($statusCounts as $statusCount) {
                $formattedStatusCounts[$statusCount->status] = $statusCount->count;
            }

            return response([
                'status_counts' => $formattedStatusCounts
            ],200);


    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tasks = Task::where('projectId', $id)->get();

        // if($tasks->isEmpty()){
        //     return response([
        //         'message' => 'No tasks found',
        //     ], 404);
        // }

        return response([
            'message' => 'Retrieved successfully',
            'project' => $tasks->map(function($task){
                return $task->only(['taskId','projectId','title','budget','start_date', 'end_date','status','assignees']);
            })
        ], 200);


    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'string',
            'budget' => 'string',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            //'image_attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            //'assignees' => 'array',
            //'assignees.*' => 'integer|exists:workers,id',
            'status' => 'string|in:pending,in_progress,completed,not_started',
        ]);

        $task = Task::findOrFail($id);

        $task->update($validatedData);

        // Attach assignees if provided
        if (isset($validatedData['assignees'])) {
            $task->assignees()->sync($validatedData['assignees']);
        }

        // Handle image attachments if needed

        return response([
            'message' => 'Task updated successfully',
            'task' => $task->only(['taskId','projectId','title','budget','start_date', 'end_date','status','assignees'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::where('taskId', $id)->first();

        if (!$task) {
            return response([
                'message' => 'Task not found',
            ], 404);
        }

        // Deleting the advertiser and its related adverts
        $task->delete();

        return response([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
