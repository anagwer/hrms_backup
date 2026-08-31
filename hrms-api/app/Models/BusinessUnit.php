<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model
{
    use UsesUuid;

    protected $table = 'businessunit';
}
