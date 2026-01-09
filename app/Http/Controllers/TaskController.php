<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // عرض كل المهام الخاصة بالمستخدم الحالي
    public function index()
    {
        return Task::where('user_id', auth()->id())->paginate(10);
    }

    // إضافة مهمة جديدة
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        return Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);
    }

    // تحديث مهمة
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update($request->only(['title', 'description', 'completed']));
        return $task;
    }

    // حذف مهمة
    public function destroy(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->delete();
        return response()->noContent();
    }
}
