<?php

namespace App\Services;

use App\Models\Employee;

class ReportService
{
    private $_employees;

    public function __construct()
    {
        $periode = request()->periode;
        $this->_employees = Employee::with([
            'employeeOrganization.job', 
            'employeeOrganization.department',
            'leaves' => function($query) use($periode){
                return $query->when($periode, function($query) use($periode){
                    return $query->whereYear('createddatetime', $periode);
                })->where('state', '1')->with('leaveType');
            },
            'exitPermits' => function($query) use($periode){
                return $query->when($periode, function($query) use($periode){
                    return $query->whereYear('createddatetime', $periode);
                })->where('state', '9');
            }
        ])->get()->toArray();
    }

    public function generateLeavesRecap()
    {
        foreach ($this->_employees as $index => $employee){
            $this->_employees[$index]['cuti_istimewa'] = 0;
            foreach ($employee['leaves'] as $leave){
                $month = $leave['leave_type']['code'] != 'TH' ? 'cuti_istimewa' : date('M', strtotime($leave['fromdate']));
                if (! isset($this->_employees[$index][$month])){
                    $this->_employees[$index][$month] = $leave['qty'];
                } else {
                    $this->_employees[$index][$month] += $leave['qty'];
                }
            }
            foreach ($employee['exit_permits'] as $exitPermit){
                $month = date('M', strtotime($exitPermit['securitycheckoutdatetime']));
                $securityOut = date_create($exitPermit['securitycheckoutdatetime']);
                $securityIn  = date_create($exitPermit['securitycheckindatetime']);
                $cuti = $this->_convertMinutesToCuti(date_diff($securityOut, $securityIn)->format('%i'));
                if (! isset($this->_employees[$index][$month])){
                    $this->_employees[$index][$month] = $cuti;
                } else {
                    $this->_employees[$index][$month] += $cuti;
                }
            }
        }
        return $this->_employees;
    }

    private function _convertMinutesToCuti($minutes)
    {
        $hour = ceil($minutes / 60);
        if ($hour > 4) return 1;
        return $hour / 8;
    }
}