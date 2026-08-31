<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\Feature;

class FeatureController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    function listAll(Request $request) {
		$page = $request->json()->get('page'); if (($page == null) || ($page == '')) { $page = 1; }
		$rows = $request->json()->get('rows'); if (($rows == null) || ($rows == '')) { $rows = Utility::DEFAULT_LIMIT(); }
        $args = json_encode($request->json()->get('args'));
        $offset = ($page-1)*$rows;

        // Editable Class
        $data = Feature::getList($args, $offset, $rows);
        $dataRec = Feature::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

}
