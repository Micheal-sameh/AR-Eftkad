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
            'need_eftkad_by_father' => new EnumResource($this->need_eftkad_by_father, BoolType::class),
            'location' => $this->location,
            'general_notes' => $this->general_notes,
            'father_membership_code' => $this->father_membership_code,
            'servant_membership_code' => $this->servant_membership_code,
            'type' => new EnumResource($this->type, EftkadType::class),
        ];
    }
}
