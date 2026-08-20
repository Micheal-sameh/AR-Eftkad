<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\UpdateUserTypeRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends BaseController
{
    public function __construct(protected UserService $userService) {}

    public function index()
    {
        $data = $this->userService->index();

        return $this->respondResource(UserResource::collection($data['users']),
            additional_data: [
                'users_count' => $data['users_count'],
            ]);
    }

    public function show($id)
    {
        $user = $this->userService->show($id);

        if (! $user) {
            return $this->apiErrorResponse(message: 'User not found', status_code: 404);
        }

        return $this->apiResponse(new UserResource($user));
    }

    public function updateType(UpdateUserTypeRequest $request, $id)
    {
        $user = $this->userService->updateType($id, (int) $request->input('type'));

        if (! $user) {
            return $this->apiErrorResponse(message: 'User not found', status_code: 404);
        }

        return $this->apiResponse(new UserResource($user));
    }

    public function updateRole(UpdateUserRoleRequest $request, $id)
    {
        $user = $this->userService->updateRole($id, $request->string('role')->toString());

        if (! $user) {
            return $this->apiErrorResponse(message: 'User not found', status_code: 404);
        }

        return $this->apiResponse(new UserResource($user));
    }
}
