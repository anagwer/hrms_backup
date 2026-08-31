<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\EncryptionTable;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use UsesUuid, EncryptionTable;
    protected $guarded = ['id'];
    protected $encryptedColumn = [
        'gross',
        'net_pay',
        'work_day',
        'pay_per_day',
        'basic_salary',
        'extra',
        'extra_hour',
        'third_shift',
        'transport_meal',
        'leave',
        'job',
        'skill',
        'service',
        'over_time',
        'h1',
        'other',
        'big_day',
        'complaint',
        'long_shift',
        'machine_standby',
        'half_day',
        'salary_increases',
        'go_on_holiday',
        'work_days_allowance',
        'pulse_allowance',
        'premi_allowance',
        'lanjut_allowance',
        'services_allowance',
        'total_other',
        'employment_bpjs',
        'kms',
        'stamp',
        'training_shirt',
        'id_card',
        'fine',
        'health_bpjs',
        'locker',
        'handphone',
        'uniform',
        'saving',
        'off_day',
        'driving_license_deduction',
        'short_deduction',
        'others_deduction',
        'others_2_deduction',
        'total_deduction'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nik', 'nik');
    }
}
