<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use App\Models\Project;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request,  Project $project)
    {
        abort_unless(
            $request->user()->projects()->whereKey($project->getKey())->exists(),
            403
        );

        return TaskResource::collection($project->tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        abort_unless(
            $request->user()->projects()->whereKey($project->getKey())->exists(),
            403
        );

        $task = $project->tasks()->create($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]));

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task)
    {
        abort_unless(
            $task->project->user()->is($request->user()),
            403
        );

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        abort_unless(
            $task->project->user()->is($request->user()),
            403
        );

        $task->update($request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'is_done' => ['sometimes', 'boolean'],
        ]));

        return new TaskResource($task->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        abort_unless(
            $task->project->user()->is($request->user()),
            403
        );

        $task->delete();
    
        return response()->noContent();
    }
}
