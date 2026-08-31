<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use UsesUuid;

    protected $table = 'presence';
}
