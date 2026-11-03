<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EftkadShare extends Model
{
    protected $fillable = ['eftkad_id', 'membership_code', 'shared_by'];
}
