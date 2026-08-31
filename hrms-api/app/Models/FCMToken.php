<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class FCMToken extends Model
{
    use UsesUuid;

    protected $table = 'fcm_tokens';

    protected $fillable = ['token', 'employee_id'];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
