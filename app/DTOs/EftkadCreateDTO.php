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
    public string $need_eftkad_by_father;
    public ?string $location;
    public ?string $general_notes;

    public function __construct(

        string $membership_code = parent::STRING,
        string $date = parent::STRING,
        string $correspondence_address = parent::STRING,
        int $mass_attendence = parent::INT,
        array $needs = parent::STRING,
        array $communication_means = parent::STRING,
        int $attend_meetings = parent::INT,
        string $need_eftkad_from_meeting = parent::STRING,
        string $father_confession = parent::STRING,
        string $mother_confession = parent::STRING,
        string $children_confession = parent::STRING,
        string $need_eftkad_by_father = parent::STRING,
        string $location = parent::STRING,
        string $general_notes = parent::STRING,
    ) {
        parent::__construct(compact(...$this->getParameterList()));
    }
}
