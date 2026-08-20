<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'type' => is_null($this->type) ? null : new EnumResource($this->type, UserType::class),
            'membership_code' => $this->membership_code,
            'role' => $this->roles->isEmpty() ? null : new EnumResource($this->roles->first()->name, UserRole::class),
        ];
    }
}
