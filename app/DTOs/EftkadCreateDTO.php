<?php

namespace App\DTOs;

use App\Attributes\HasEmptyPlaceholders;

#[HasEmptyPlaceholders]
class EftkadCreateDTO extends DTO
{
    public string $membership_code;
    public ?string $date;
    public ?string $correspondence_address;
    public int $mass_attendence;
    public ?array $needs;
    public ?array $communication_means;
    public ?int $attend_meetings;
    public ?string $need_eftkad_from_meeting;
    public ?string $father_confession;
    public ?string $mother_confession;
    public ?string $children_confession;
    public ?int $need_eftkad_by_father;
    public ?string $location;
    public ?string $location_url;
    public ?string $general_notes;
    public string $father_membership_code;
    public string $servant_membership_code;
    public int $type;

    public function __construct(

        string $membership_code = parent::STRING,
        string $date = parent::STRING,
        string $correspondence_address = parent::STRING,
        int $mass_attendence = parent::INT,
        array $needs = parent::ARRAY,
        array $communication_means = parent::ARRAY,
        int $attend_meetings = parent::INT,
        string $need_eftkad_from_meeting = parent::STRING,
        string $father_confession = parent::STRING,
        string $mother_confession = parent::STRING,
        string $children_confession = parent::STRING,
        int $need_eftkad_by_father = parent::INT,
        string $location = parent::STRING,
        string $location_url = parent::STRING,
        string $general_notes = parent::STRING,
        string $father_membership_code = parent::STRING,
        string $servant_membership_code = parent::STRING,
        int $type = parent::INT,
    ) {
        parent::__construct(compact(...$this->getParameterList()));
    }
}
