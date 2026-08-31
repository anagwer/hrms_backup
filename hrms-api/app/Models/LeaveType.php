<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use UsesUuid;
    
    protected $table = 'leavetype';
}