<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eftkad extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_code',
        'date',
        'correspondence_address',
        'mass_attendence',
        'needs',
        'communication_means',
        'attend_meetings',
        'need_eftkad_from_meeting',
        'father_confession',
        'mother_confession',
        'children_confession',
        'need_eftkad_by_father',
        'location',
        'location_url',
        'general_notes',
        'father_membership_code',
        'servant_membership_code',
        'type',
    ];

    protected $casts = [
        'needs' => 'array',
        'communication_means' => 'array',
    ];
}
