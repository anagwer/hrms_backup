<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use UsesUuid;

    protected $guarded = ['id'];

    protected $table = 'role';
}
