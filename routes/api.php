<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

// Authentication Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Task Endpoints (محمي بالتوكن)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);       // عرض المهام
    Route::post('/tasks', [TaskController::class, 'store']);      // إضافة مهمة
    Route::put('/tasks/{task}', [TaskController::class, 'update']); // تعديل مهمة
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // حذف مهمة
});
