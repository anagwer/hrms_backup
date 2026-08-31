<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class ExitPermit extends Model
{
    use UsesUuid;

    protected $table = 'sikel';

    protected $guarded = 'id';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }
}
