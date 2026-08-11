<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = User::paginate(10);
        $resource = UserResource::collection($users);

        return $this->successResponse(
            $resource->items(),
            'Users retrieved successfully',
            200,
            [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ]
        );
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
        return $this->successResponse(new UserResource($user), 'User created successfully', 201);
    }

    public function show(User $user)
    {
        return $this->successResponse(new UserResource($user), 'User retrieved successfully');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated());
        return $this->successResponse(new UserResource($updatedUser), 'User updated successfully');
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user)
    {
        $updatedUser = $this->userService->updateStatus($user, $request->validated('is_active'));
        return $this->successResponse(new UserResource($updatedUser), 'User status updated successfully');
    }

    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);
        return $this->successResponse(null, 'User deactivated successfully');
    }
}