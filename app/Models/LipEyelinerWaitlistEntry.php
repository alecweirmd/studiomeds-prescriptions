<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LipEyelinerWaitlistEntry extends Model
{
    protected $table = 'lip_eyeliner_waitlist';

    protected $fillable = ['email'];

    public $timestamps = false;
}