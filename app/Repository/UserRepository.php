<?php

namespace App\Repository;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Str;

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

    /**
     * A user added by an admin, not yet linked to SSO (avarewase_sub stays null). The password is
     * random and never used: sign-in is through SSO, which links the account by membership code.
     */
    public function create(array $data): User
    {
        $user = $this->model->create([
            'name' => $data['name'],
            'membership_code' => $data['membership_code'],
            'type' => $data['type'],
            'phone' => $data['phone'] ?? null,
            'password' => bcrypt(Str::random(40)),
        ]);
        $user->assignRole(UserRole::USER);

        return $user->load('roles');
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
