<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\UpdateUserTypeRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct(protected UserService $userService) {}

    public function me(Request $request)
    {
        return new UserResource($request->user()->loadMissing('roles'));
    }

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
            return $this->apiErrorResponse(message: trans('messages.user not found'), status_code: 404);
        }

        return $this->apiResponse(new UserResource($user));
    }

    public function updateType(UpdateUserTypeRequest $request, $id)
    {
        $user = $this->userService->updateType($id, (int) $request->input('type'));

        if (! $user) {
            return $this->apiErrorResponse(message: trans('messages.user not found'), status_code: 404);
        }

        return $this->apiResponse(new UserResource($user));
    }

    public function updateRole(UpdateUserRoleRequest $request, $id)
    {
        // Guard against locking everyone out: no self-demotion, and the last superadmin stays.
        $target = $this->userService->show($id);
        $newRole = $request->string('role')->toString();

        if ($target && $newRole !== UserRole::SUPERADMIN && $target->hasRole(UserRole::SUPERADMIN)
            && ($target->is($request->user()) || User::role(UserRole::SUPERADMIN)->count() <= 1)) {
            return $this->apiErrorResponse(message: trans('messages.cannot demote superadmin'), status_code: 422);
        }

        $user = $this->userService->updateRole($id, $request->string('role')->toString());

        if (! $user) {
            return $this->apiErrorResponse(message: trans('messages.user not found'), status_code: 404);
        }

        return $this->apiResponse(new UserResource($user));
    }
}
