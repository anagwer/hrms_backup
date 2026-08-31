<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

// Declare All Used Models
use App\Http\Models\Cuti;
use App\Http\Models\Presence;
use App\Http\Models\Employment;

class MobileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getHomeData(Request $request) {
        $result = array();
        $id = $request->creds->id;
        $employee = Employment::getByID($id)[0];
        
        $params_terlambat = array (
            array(DB::raw('MONTH(presenceview.checkin)'), '=', 3),
            array(DB::raw('YEAR(presenceview.checkin)'), '=', 2021),
            array('presenceview.employee', '=', $id)
        );
        $terlambat = Presence::getCountTerlambat($params_terlambat);

        array_push($result, array(
            'name' => 'Total Sisa Cuti',
            'value' => $employee->totalcuti
        ));

        array_push($result, array(
            'name' => 'Total Terlambat',
            'value' => $terlambat->count
        ));

        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($result)
        ], 200);
    }
}