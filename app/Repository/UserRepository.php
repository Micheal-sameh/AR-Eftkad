<?php

namespace App\Repository;

use App\Models\User;

class UserRepository
{
    public function __construct(protected User $model) {}

    public int $perPage = 10;

    public function index()
    {
        // UserResource reads ->roles for every row; eager-load to avoid one query per user.
        return $this->model->query()->with('roles')->orderBy('name')->paginate($this->perPage);
    }

    public function find($id)
    {
        return $this->model->with('roles')->find($id);
    }

    public function show($id)
    {
        return $this->find($id);
    }

    public function updateType(User $user, int $type): User
    {
        $user->update(['type' => $type]);

        return $user;
    }

    public function updateRole(User $user, string $role): User
    {
        $user->syncRoles([$role]);

        return $user;
    }
}
