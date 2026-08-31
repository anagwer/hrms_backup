<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use UsesUuid;

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leavetype', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }
}
