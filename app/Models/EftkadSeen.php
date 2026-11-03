<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EftkadSeen extends Model
{
    protected $table = 'eftkad_seen';

    protected $fillable = ['eftkad_id', 'membership_code'];
}
