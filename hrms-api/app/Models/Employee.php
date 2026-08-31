<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use UsesUuid;

    protected $table = 'employee';

    protected $hidden = ['strpassword'];

    protected $fillable = ['pin'];

    public $timestamps = false;

    public function employeeOrganization()
    {
        return $this->hasOne(EmployeeOrganization::class, 'employee', 'id');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class, 'employee', 'id');
    }

    public function exitPermits()
    {
        return $this->hasMany(ExitPermit::class, 'employee', 'id');
    }

    public function messagingToken()
    {
        return $this->belongsTo(FCMToken::class, 'id', 'employee_id');
    }

    public function role()
    {
        return $this->hasOneThrough(
            Role::class,
            EmployeeOrganization::class,
            'employee',
            'id',
            'id',
            'role'
        );
    }

    public function department()
    {
        return $this->hasOneThrough(
            Department::class,
            EmployeeOrganization::class,
            'employee',
            'id',
            'id',
            'department'
        );
    }

    public function directHead()
    {
        return $this->hasOne(Employee::class, 'id', 'reports_to');
    }
}
