<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

// Authentication Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // CRUD للمشاريع والمهام
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::get('tasks/{task}', [TaskController::class, 'show']);

    // CRUD للمستخدمين (index, show, destroy)
    Route::apiResource('users', UserController::class)->only(['index', 'show', 'destroy', 'update']);

    // Routes إضافية للمستخدمين (ترقية/تخفيض)
    Route::post('users/{user}/promote', [UserController::class, 'promoteToOwner']);
    Route::post('users/{user}/downgrade', [UserController::class, 'downgradeToMember']);

    // Nested Resource للمرفقات داخل المهام
    Route::apiResource('tasks.attachments', AttachmentController::class);
    Route::apiResource('attachments', AttachmentController::class);

    // لو محتاج تعرض كل المرفقات بشكل عام
    Route::get('attachments', [AttachmentController::class, 'all']);

    Route::get('dashboard', [DashboardsController::class, 'index']);
});
/**
 * @OA\Get(
 *     path="/health-check",
 *     summary="Simple health check to verify Swagger is working",
 *     tags={"System"},
 *     @OA\Response(
 *         response=200,
 *         description="API is alive",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="ok")
 *         )
 *     )
 * )
 */
Route::get('/health', function () {
    return response()->json(['status' => 'healthy']);
});
