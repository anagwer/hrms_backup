<?php

namespace App\Http\Controllers;

use App\Imports\SalaryImport;
use App\Models\Employee;
use App\Models\Salary;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class SalaryController extends Controller
{

    public function importSalary(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        $this->validate($request, [
            'salary_file' => 'required|mimes:xlsx,xls',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2000,3000',
        ]);
        try {
            $data = [
                'month' => $request->month,
                'year' => $request->year
            ];
            Excel::import(new SalaryImport($data), $request->file('salary_file'));
            return response()->json([
                'success' => true,
                'message' => 'Success importing file',
                'data' => ([])
            ], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->failures(),
                'data' => (null)
            ], 422);
        }
    }

    public function getSalaryInfo($nik)
    {
        $now = \Carbon\Carbon::now();
        try {
            $result = Salary::where([
                'month_period' => $now->month,
                'year_period' => $now->year,
                'nik' => $nik
            ])->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Get salary info success',
                'data' => ($result)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json([
                'success' => false,
                'message' => "Informasi gaji bulan ini belum tersedia",
                'data' => (null)
            ], 404);
        }
    }

    public function getSalaryState()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        $now = \Carbon\Carbon::now();
        $salaryUploaded = Salary::where([
            'month_period' => $now->month,
            'year_period' => $now->year
        ])->get()->count();
        $allEmployee = Employee::all()->count();
        if ($salaryUploaded > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Salary this month is already uploaded',
                'data' => ([
                    'uploaded' => $salaryUploaded,
                    'employee' => $allEmployee,
                ])
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Salary this month not uploaded yet',
            'data' => (null)
        ], 200);
    }

    public function getPrintedSalary(Request $request)
    {
        $this->validate($request, [
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2000,3000',
            'nik' => 'required|string|max:255'
        ]);
        try {
            $result = Salary::with(['employee' => function ($query) {
                $query->select('nik', 'strname');
            }])->where([
                'month_period' => $request->month,
                'year_period' => $request->year,
                'nik' => $request->nik
            ])->firstOrFail();
            return response()->json([
                'success' => true,
                'message' => 'Get salary info success',
                'data' => ($result)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json([
                'success' => false,
                'message' => "Informasi gaji bulan ini belum tersedia",
                'data' => (null)
            ], 404);
        }
    }
}
