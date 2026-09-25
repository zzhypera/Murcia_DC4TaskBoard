<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = $request->user()->projects()
        ->withCount('tasks')
        ->latest()
        ->get();
 
    return ProjectResource::collection($projects);

    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
    ]);
 
    $project = $request->user()->projects()->create($data);
 
    return (new ProjectResource($project))
        ->response()->setStatusCode(201);
}


    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project)
{
    abort_unless($project->user()->is($request->user()), 403);
 
    return new ProjectResource($project->load(['user', 'tasks']));
}


    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request, Project $project)
{
    abort_unless($project->user()->is($request->user()), 403);
    $data = $request->validate([
        'name' => 'sometimes|required|string|max:100',
        'description' => 'nullable|string',
    ]);
    $project->update($data);
    return new ProjectResource($project);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project)
{
    abort_unless($project->user()->is($request->user()), 403);
    $project->delete();
    return response()->noContent();   // 204
}

}
