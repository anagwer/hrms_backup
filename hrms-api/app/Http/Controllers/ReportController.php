<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Services\ReportService;
use App\Utilities\Utility;

class ReportController extends Controller
{
    public function getLeavesRecap(ReportService $service)
    {        
        $data = $service->generateLeavesRecap();
        return Utility::setResponseExT(200, '', $data);
    }

    public function getLeavesYearDistinct()
    {
        return response()->json(Leave::selectRaw('YEAR(createddatetime) as year')->distinct()->get());
    }
}