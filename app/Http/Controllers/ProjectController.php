<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Illuminate\Auth\Access\AuthorizationException;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/projects",
     *     summary="Get all projects for the authenticated user",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Projects retrieved successfully"
     *     )
     * )
     */
    public function index()
    {
        $projects = Project::where('user_id', Auth::id())->with('tasks')->get();

        return response()->json([
            "success"  => true,
            "projects" => $projects,
            "message"  => "Projects retrieved successfully"
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/projects",
     *     summary="Create a new project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Project"),
     *             @OA\Property(property="description", type="string", example="Project description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'name'        => $request->name,
            'description' => $request->description,
            'user_id'     => Auth::id(),
        ]);

        return response()->json([
            "success" => true,
            "project" => $project,
            "message" => "Project created successfully"
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/projects/{projectId}",
     *     summary="Get a specific project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to view this project."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found."
     *     )
     * )
     */
    public function show(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            return response()->json([
                "success" => false,
                "error"   => "You are not authorized to view this project."
            ], 403);
        }

        $project->load('tasks');

        return response()->json([
            "success" => true,
            "project" => $project,
            "message" => "Project retrieved successfully"
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/projects/{projectId}",
     *     summary="Update a project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Project Name"),
     *             @OA\Property(property="description", type="string", example="Updated project description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project updated successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to update this project."
     *     )
     * )
     */
    public function update(Request $request, Project $project)
    {
        try {
            $this->authorize('update', $project);

            $request->validate([
                'name'        => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $project->update([
                'name'        => $request->name ?? $project->name,
                'description' => $request->description ?? $project->description,
            ]);

            return response()->json([
                "success" => true,
                "project" => $project,
                "message" => "Project updated successfully"
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage()
            ], 403);
        }
    }

    /**
     * @OA\Delete(
     *     path="/projects/{projectId}",
     *     summary="Delete a project",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to delete this project."
     *     )
     * )
     */
    public function destroy(Project $project)
    {
        try {
            $this->authorize('delete', $project);

            $project->delete();

            return response()->json([
                "success" => true,
                "message" => "Project deleted successfully"
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                "success" => false,
                "error"   => $e->getMessage()
            ], 403);
        }
    }
}
