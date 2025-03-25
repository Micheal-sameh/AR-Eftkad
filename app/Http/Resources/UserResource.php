<?php

namespace App\Http\Resources;

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
        $locale = app()->getLocale();

        return [
            'id'     => $this->id,
            'name'   => $this->name[$locale],
            'phone'  => $this->phone,
            'membership_code' => $this->E1C1F . $this->NR,
            'E1C1F'  => $this->E1C1F,
            'NR'     => $this->NR,
        ];
    }
}
