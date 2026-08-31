<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\ResignType;

class ResignTypeController extends Controller
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
        $data = ResignType::getList($args, $offset, $rows);
        $dataRec = ResignType::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    

    function create(Request $request){
        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => "$newID",
                'strname' => $request->json()->get('strname')
            );
            $resJSON = resigntype::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = resigntype::getList(Utility::setArgs($newID), 0, 1);
            $params = array (
                "id" => $newID,
                'strname' => $request->json()->get('strname')
            );
            $resJSON = resigntype::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = resigntype::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

}
