<?php

namespace App\Http\Resources;

use App\Enums\BoolType;
use App\Enums\CommunicationType;
use App\Enums\EftkadType;
use App\Enums\MassAttendanceType;
use App\Enums\NeedType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EftkadResource extends JsonResource
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
            'membership_code' => $this->membership_code,
            'date' => Carbon::parse($this->date)->format('d-m-Y'),
            'correspondence_address' => $this->correspondence_address,
            'mass_attendence' => new EnumResource($this->mass_attendence, MassAttendanceType::class),
            'needs' => array_map(fn ($n) => new EnumResource($n, NeedType::class), $this->needs ?? []),
            'communication_means' => array_map(fn ($n) => new EnumResource($n, CommunicationType::class), $this->communication_means ?? []),
            'attend_meetings' => new EnumResource($this->attend_meetings, BoolType::class),
            'need_eftkad_from_meeting' => $this->need_eftkad_from_meeting,
            'father_confession' => $this->father_confession,
            'mother_confession' => $this->mother_confession,
            'children_confession' => $this->children_confession,
            'father_confessor' => $this->father_confessor,
            'mother_confessor' => $this->mother_confessor,
            'children_confessor' => $this->children_confessor,
            'need_eftkad_by_father' => new EnumResource($this->need_eftkad_by_father, BoolType::class),
            'location' => $this->location,
            'location_url' => $this->location_url,
            'general_notes' => $this->general_notes,
            'father_membership_code' => $this->father_membership_code,
            'servant_membership_code' => $this->servant_membership_code,
            'type' => new EnumResource($this->type, EftkadType::class),
            // Only present on the home page's "new" list: 'assigned' or 'shared'.
            'new_reason' => $this->whenHas('new_reason'),
            // Only present on the detail view, where the relation is loaded.
            'shared_with' => $this->whenLoaded('shares', fn () => $this->shares->pluck('membership_code')->all()),
        ];
    }
}
