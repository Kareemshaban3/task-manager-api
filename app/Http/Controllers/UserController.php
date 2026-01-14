<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="object"),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to view users"
     *     )
     * )
     */
    public function index()
    {
        if (!Auth::user()->isOwner()) {
            return response()->json([
                'error' => 'You are not authorized to view users.'
            ], 403);
        }

        $users = User::paginate(10);
        return response()->json([
            'users' => $users,
            'message' => 'Users retrieved successfully'
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/users/{userId}",
     *     summary="Get a specific user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to view users"
     *     )
     * )
     */
    public function show(User $user)
    {
        if (!Auth::user()->isOwner()) {
            return response()->json([
                'error' => 'You are not authorized to view users.'
            ], 403);
        }

        return response()->json([
            'user' => $user,
            'message' => 'User retrieved successfully'
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/users/{userId}",
     *     summary="Update a user's information",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, User $user)
    {
        if (Auth::id() !== $user->id && !Auth::user()->isOwner()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name'  => 'nullable|string|max:200',
            'email' => 'nullable|email|max:200|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/users/{userId}/promote",
     *     summary="Promote a user to owner",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User promoted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to promote members"
     *     )
     * )
     */
    public function promoteToOwner(User $user)
    {
        if (!Auth::user()->isOwner()) {
            return response()->json([
                'message' => 'You are not authorized to promote members'
            ], 403);
        }

        if ($user->isMember()) {
            $user->role = 'owner';
            $user->save();
        }

        return response()->json([
            'message' => 'User promoted to owner successfully',
            'user' => $user
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/users/{userId}/downgrade",
     *     summary="Downgrade a user to member",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User downgraded successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to downgrade owners"
     *     )
     * )
     */
    public function downgradeToMember(User $user)
    {
        if (!Auth::user()->isOwner()) {
            return response()->json([
                'message' => 'You are not authorized to downgrade owners'
            ], 403);
        }

        if ($user->isOwner()) {
            $user->role = 'member';
            $user->save();
        }

        return response()->json([
            'message' => 'User downgraded to member successfully',
            'user' => $user
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/users/{userId}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->isOwner()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
