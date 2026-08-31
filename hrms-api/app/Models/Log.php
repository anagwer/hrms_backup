<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use UsesUuid;

    protected $fillable = ['detail'];
}
