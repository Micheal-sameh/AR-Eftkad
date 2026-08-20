<?php

namespace App\Repository;

use App\Models\User;

class UserRepository
{
    public function __construct(protected User $model) {}

    public int $perPage = 10;

    public function index()
    {
        return $this->model->query()->orderBy('name')->paginate($this->perPage);
    }

    public function find($id)
    {
        return $this->model->find($id);
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
