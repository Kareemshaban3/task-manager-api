<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks/{taskId}/attachments",
     *     summary="Get all attachments for a task",
     *     tags={"Attachments"},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attachments retrieved successfully"
     *     )
     * )
     */
    public function index($taskId)
    {
        $task = Task::where('user_id', Auth::id())
            ->with('attachments')
            ->findOrFail($taskId);

        return response()->json([
            'data' => [
                'task'        => $task->title,
                'attachments' => $task->attachments->map(function ($attachment) {
                    return [
                        'id'        => $attachment->id,
                        'file_path' => $attachment->file_path,
                        'file_type' => $attachment->file_type,
                        'url'       => asset('storage/' . $attachment->file_path),
                    ];
                }),
            ],
            'message' => 'Attachments retrieved successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/attachments",
     *     summary="Get all attachments",
     *     tags={"Attachments"},
     *     @OA\Response(
     *         response=200,
     *         description="All attachments retrieved successfully"
     *     )
     * )
     */
    public function indexAll()
    {
        $attachments = Attachment::with('task')->get();

        return response()->json([
            'data'    => $attachments,
            'message' => 'All attachments retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks/{taskId}/attachments",
     *     summary="Upload a new attachment for a task",
     *     tags={"Attachments"},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File uploaded successfully"
     *     )
     * )
     */
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx|max:2048',
        ]);

        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);

        $path = $request->file('file')->store('attachments', 'public');

        $attachment = Attachment::create([
            'task_id'   => $task->id,
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientOriginalExtension(),
        ]);

        return response()->json([
            'data' => [
                'attachment' => $attachment,
                'url'        => Storage::url($attachment->file_path),
            ],
            'message' => 'File uploaded successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{taskId}/attachments/{attachmentId}",
     *     summary="Get a specific attachment",
     *     tags={"Attachments"},
     *     @OA\Parameter(name="taskId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="attachmentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Attachment retrieved successfully"
     *     )
     * )
     */
    public function show($taskId, $attachmentId)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);

        $attachment = $task->attachments()->where('id', $attachmentId)->first();

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found for this task'], 404);
        }

        return response()->json([
            'data' => [
                'attachment' => $attachment,
                'url'        => asset('storage/' . $attachment->file_path),
            ],
            'message' => 'Attachment retrieved successfully'
        ]);
    }


    /**
     * @OA\Put(
     *     path="/api/tasks/{taskId}/attachments/{attachmentId}",
     *     summary="Update an attachment",
     *     tags={"Attachments"},
     *     @OA\Parameter(name="taskId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="attachmentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attachment updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $taskId, $attachmentId)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $attachment = $task->attachments()->where('id', $attachmentId)->firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx|max:2048',
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        Storage::disk('public')->delete($attachment->file_path);

        $path = $request->file('file')->store('attachments', 'public');

        $attachment->update([
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientOriginalExtension(),
        ]);

        return response()->json([
            'data' => [
                'attachment' => $attachment,
                'url'        => asset('storage/' . $path),
            ],
            'message' => 'Attachment updated successfully'
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/tasks/{taskId}/attachments/{attachmentId}",
     *     summary="Delete an attachment",
     *     tags={"Attachments"},
     *     @OA\Parameter(name="taskId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="attachmentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Attachment deleted successfully"
     *     )
     * )
     */
    public function destroy($taskId, $attachmentId)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $attachment = $task->attachments()->where('id', $attachmentId)->firstOrFail();

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully'
        ]);
    }
}
