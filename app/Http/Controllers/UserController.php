<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use App\Constants\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponse;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $users = $this->userService->getUsers($request);
            $resource = UserResource::collection($users);

            return $this->successResponse(
                $resource->items(),
                Message::SUCCESS,
                200,
                [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                ]
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return $this->successResponse(
                new UserResource($user),
                Message::SUCCESS,
                201
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }

    public function show(User $user): JsonResponse
    {
        try {
            return $this->successResponse(
                new UserResource($user),
                Message::SUCCESS
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::NOT_FOUND,
                404
            );
        }
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            return $this->successResponse(
                new UserResource($updatedUser),
                Message::SUCCESS
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateStatus($user, $request->validated('is_active'));

            return $this->successResponse(
                new UserResource($updatedUser),
                Message::SUCCESS
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }

    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);

            return $this->successResponse(
                null,
                Message::SUCCESS
            );
        } catch (Exception $e) {
            Log::error(Message::ERROR . ': ' . $e->getMessage());

            return $this->errorResponse(
                Message::INTERNAL_SERVER_ERROR,
                500
            );
        }
    }
}