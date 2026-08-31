<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class EmployeeOrganization extends Model
{
    use UsesUuid;

    protected $table = 'emporganization';

    public function job()
    {
        return $this->hasOne(Job::class, 'id', 'job');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'department');
    }

    public function businessUnit()
    {
        return $this->hasOne(BusinessUnit::class, 'id', 'businessunit');
    }

    public function division()
    {
        return $this->hasOne(Division::class);
    }

    /**
     * this method naming isn't best practice.
     * we can't give it name "employee" because of the database
     * field name is not good designed.
     */
    public function employeeParent()
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }
}
