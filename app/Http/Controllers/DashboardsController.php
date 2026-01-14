<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class DashboardsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dashboard",
     *     summary="Get dashboard statistics",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="tasks",
     *                 type="object",
     *                 @OA\Property(property="completed", type="integer", example=12),
     *                 @OA\Property(property="pending", type="integer", example=8),
     *                 @OA\Property(property="in_progress", type="integer", example=5)
     *             ),
     *             @OA\Property(
     *                 property="All_Count",
     *                 type="object",
     *                 @OA\Property(property="AllCount_Users", type="integer", example=5),
     *                 @OA\Property(property="AllCount_Projects", type="integer", example=15),
     *                 @OA\Property(property="AllCount_Tasks", type="integer", example=25)
     *             ),
     *             @OA\Property(property="message", type="string", example="Dashboard statistics retrieved successfully")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $completedTasks = Task::where('mode', 'completed')->count();
        $in_progressTasks = Task::where('mode', 'in_progress')->count();
        $pendingTasks = Task::where('mode', 'pending')->count();

        $usersStats = User::withCount([
            'tasks as total_tasks',
            'tasks as completed_tasks' => function ($query) {
                $query->where('mode', 'completed');
            },
            'tasks as in_progress_tasks' => function ($query) {
                $query->where('mode', "in_progress");
            },
            'tasks as pending_tasks' => function ($query) {
                $query->where('mode', "pending");
            }
        ])->get();

        $AllCount_Users = User::count();
        $AllCount_Projects = Project::count();
        $AllCount_Tasks = Task::count();

        return response()->json([
            'tasks' => [
                'AllCount_Tasks' => $AllCount_Tasks,
                'completed' => $completedTasks,
                'pending'   => $pendingTasks,
                'in_progress'   => $in_progressTasks,
            ],
            'All_Count' => [
                'AllCount_Users' => $AllCount_Users,
                'AllCount_Projects' => $AllCount_Projects,
                'AllCount_Tasks' => $AllCount_Tasks,
            ],
            'users' => $usersStats,
            'message' => 'Dashboard statistics retrieved successfully'
        ]);
    }
}
