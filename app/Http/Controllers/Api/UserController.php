<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Enums\UserType;
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

    public function updateFontSize(Request $request)
    {
        $data = $request->validate(['font_size' => 'required|integer|between:0,2']);

        $user = $request->user();
        $user->update(['font_size' => $data['font_size']]);

        return new UserResource($user->loadMissing('roles'));
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
        $target = $this->userService->show($id);

        if (! $target) {
            return $this->apiErrorResponse(message: trans('messages.user not found'), status_code: 404);
        }

        $actor = $request->user();
        $newType = (int) $request->input('type');

        // Owner and superadmin set any type; an admin may only assign or edit servants.
        $allowed = $this->canManage($actor, $target)
            && ($actor->hasAnyRole([UserRole::OWNER, UserRole::SUPERADMIN])
                || ($newType === UserType::SERVANT && ($target->type === null || (int) $target->type === UserType::SERVANT)));

        if (! $allowed) {
            return $this->apiErrorResponse(message: trans('messages.forbidden user action'), status_code: 403);
        }

        $user = $this->userService->updateType($id, $newType);

        return $this->apiResponse(new UserResource($user));
    }

    public function updateRole(UpdateUserRoleRequest $request, $id)
    {
        $target = $this->userService->show($id);

        if (! $target) {
            return $this->apiErrorResponse(message: trans('messages.user not found'), status_code: 404);
        }

        $actor = $request->user();
        $newRole = $request->string('role')->toString();

        // Superadmins can only hand out roles below their own (admin/user); the owner can hand out any.
        $isOwner = $actor->hasRole(UserRole::OWNER);
        if (! $this->canManage($actor, $target) || (! $isOwner && UserRole::rank($newRole) >= $this->rankOf($actor))) {
            return $this->apiErrorResponse(message: trans('messages.forbidden user action'), status_code: 403);
        }

        // Guard against locking everyone out: the last owner stays.
        if ($newRole !== UserRole::OWNER && $target->hasRole(UserRole::OWNER) && User::role(UserRole::OWNER)->count() <= 1) {
            return $this->apiErrorResponse(message: trans('messages.cannot demote owner'), status_code: 422);
        }

        $user = $this->userService->updateRole($id, $newRole);

        if (! $user) {
            return $this->apiErrorResponse(message: trans('messages.user not found'), status_code: 404);
        }

        return $this->apiResponse(new UserResource($user));
    }

    private function rankOf(User $user): int
    {
        return UserRole::highestRank($user->roles->pluck('name'));
    }

    /** The owner manages everyone; anyone else only users ranked below them. */
    private function canManage(User $actor, User $target): bool
    {
        return $actor->hasRole(UserRole::OWNER) || $this->rankOf($target) < $this->rankOf($actor);
    }
}
