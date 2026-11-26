<?php

namespace App\Services;

use App\Repository\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepository) {}

    public function index()
    {
        $users = $this->userRepository->index();

        return [
            'users' => $users,
            'users_count' => $users->total(),
        ];
    }

    public function show($id)
    {
        return $this->userRepository->show($id);
    }

    public function create(array $data)
    {
        return $this->userRepository->create($data);
    }

    public function updateType($id, int $type)
    {
        $user = $this->userRepository->find($id);

        if (! $user) {
            return null;
        }

        return $this->userRepository->updateType($user, $type);
    }

    public function updateRole($id, string $role)
    {
        $user = $this->userRepository->find($id);

        if (! $user) {
            return null;
        }

        return $this->userRepository->updateRole($user, $role);
    }
}
