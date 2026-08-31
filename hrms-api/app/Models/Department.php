<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use UsesUuid;

    protected $table = 'department';

    public function departmentHead()
    {
        return $this->belongsTo(Employee::class, 'head', 'id');
    }
}
