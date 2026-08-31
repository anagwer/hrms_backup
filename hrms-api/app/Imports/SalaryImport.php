<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Salary;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;


class SalaryImport implements ToModel, SkipsEmptyRows, WithStartRow
{
    private $request;

    public function  __construct(array $data = [])
    {
        $this->request = $data;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * @param array $row
     *
     * @return Salary|null
     */
    public function model(array $row)
    {
        $salary = Salary::where('nik', $row[0])
            ->where('month_period', $this->request['month'])
            ->where('year_period', $this->request['year'])
            ->first();

        if ($salary) {
            $salary->update([
                'line'                      => $row[1],
                'gross'                     => $row[2],
                'net_pay'                   => $row[3],
                'work_day'                  => $row[4],
                'pay_per_day'               => $row[5],
                'basic_salary'              => $row[6],
                'extra'                     => $row[7],
                'extra_hour'                => $row[8],
                'third_shift'               => $row[9],
                'transport_meal'            => $row[10],
                'leave'                     => $row[11],
                'job'                       => $row[12],
                'skill'                     => $row[13],
                'service'                   => $row[14],
                'over_time'                 => $row[15],
                'h1'                        => $row[16],
                'other'                     => $row[17],
                'big_day'                   => $row[18],
                'complaint'                 => $row[19],
                'long_shift'                => $row[20],
                'machine_standby'           => $row[21],
                'half_day'                  => $row[22],
                'salary_increases'          => $row[23],
                'go_on_holiday'             => $row[24],
                'work_days_allowance'       => $row[25],
                'pulse_allowance'           => $row[26],
                'premi_allowance'           => $row[27],
                'lanjut_allowance'          => $row[28],
                'services_allowance'        => $row[29],
                'total_other'               => $row[30],
                'employment_bpjs'           => $row[31],
                'kms'                       => $row[32],
                'stamp'                     => $row[33],
                'training_shirt'            => $row[34],
                'id_card'                   => $row[35],
                'fine'                      => $row[35],
                'health_bpjs'               => $row[37],
                'locker'                    => $row[38],
                'handphone'                 => $row[39],
                'uniform'                   => $row[40],
                'saving'                    => $row[41],
                'off_day'                   => $row[42],
                'driving_license_deduction' => $row[43],
                'short_deduction'           => $row[44],
                'others_deduction'          => $row[45],
                'others_2_deduction'        => $row[46],
                'total_deduction'           => $row[47],
            ]);
            return null;
        }
        return new Salary([
            'nik'                       => $row[0],
            'line'                      => $row[1],
            'gross'                     => $row[2],
            'net_pay'                   => $row[3],
            'work_day'                  => $row[4],
            'pay_per_day'               => $row[5],
            'basic_salary'              => $row[6],
            'extra'                     => $row[7],
            'extra_hour'                => $row[8],
            'third_shift'               => $row[9],
            'transport_meal'            => $row[10],
            'leave'                     => $row[11],
            'job'                       => $row[12],
            'skill'                     => $row[13],
            'service'                   => $row[14],
            'over_time'                 => $row[15],
            'h1'                        => $row[16],
            'other'                     => $row[17],
            'big_day'                   => $row[18],
            'complaint'                 => $row[19],
            'long_shift'                => $row[20],
            'machine_standby'           => $row[21],
            'half_day'                  => $row[22],
            'salary_increases'          => $row[23],
            'go_on_holiday'             => $row[24],
            'work_days_allowance'       => $row[25],
            'pulse_allowance'           => $row[26],
            'premi_allowance'           => $row[27],
            'lanjut_allowance'          => $row[28],
            'services_allowance'        => $row[29],
            'total_other'               => $row[30],
            'employment_bpjs'           => $row[31],
            'kms'                       => $row[32],
            'stamp'                     => $row[33],
            'training_shirt'            => $row[34],
            'id_card'                   => $row[35],
            'fine'                      => $row[35],
            'health_bpjs'               => $row[37],
            'locker'                    => $row[38],
            'handphone'                 => $row[39],
            'uniform'                   => $row[40],
            'saving'                    => $row[41],
            'off_day'                   => $row[42],
            'driving_license_deduction' => $row[43],
            'short_deduction'           => $row[44],
            'others_deduction'          => $row[45],
            'others_2_deduction'        => $row[46],
            'total_deduction'           => $row[47],
            'month_period'              => $this->request['month'],
            'year_period'               => $this->request['year'],
        ]);
    }
}
