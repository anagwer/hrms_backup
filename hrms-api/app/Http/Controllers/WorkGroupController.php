<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\WorkGroup;

class WorkGroupController extends Controller
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
        $data = WorkGroup::getList($args, $offset, $rows);
        $dataRec = WorkGroup::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExt( 200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function listLine(Request $request) {
		$page = $request->json()->get('page'); if (($page == null) || ($page == '')) { $page = 1; }
        $rows = $request->json()->get('rows'); if (($rows == null) || ($rows == '')) { $rows = Utility::DEFAULT_LIMIT(); }
        
        $args = json_encode($request->json()->get('args'));
        $offset = ($page-1)*$rows;

        // Editable Class
        $data = WorkGroup::getLines($args, $offset, $rows);
        $dataRec = WorkGroup::getLinesCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExt( 200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    

    }


    function create(Request $request){
        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => "$newID",
                'strname' => $request->json()->get('strname'),
                'businessunit' => $request->json()->get('businessunit'),
                'plant' => $request->json()->get('plant'),
                'pool' => $request->json()->get('pool')
            );
            $resJSON = WorkGroup::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = WorkGroup::getList(Utility::setArgs($newID), 0, 1);
            $params = array (
                "id" => $newID,
                'strname' => $request->json()->get('strname'),
                'businessunit' => $request->json()->get('businessunit'),
                'plant' => $request->json()->get('plant'),
                'pool' => $request->json()->get('pool')
            );
            $resJSON = WorkGroup::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = WorkGroup::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function createLine(Request $request){
        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => "$newID",
                'workgroup' => $request->json()->get('workgroup'),
                'employee' => $request->json()->get('employee')
            );
            $resJSON = WorkGroup::createLineEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = WorkGroup::getLines(Utility::setArgs($newID), 0, 1);
            $params = array (
                "id" => "$newID",
                'workgroup' => $request->json()->get('workgroup'),
                'employee' => $request->json()->get('employee')
            );
            $resJSON = WorkGroup::updateLines(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = WorkGroup::getLines(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }


    function delete(Request $request) {
        // Editable Class
        $params = array (
            "id" => $request->json()->get('id')
        );
        $resJSON = WorkGroup::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    function deleteLine(Request $request) {
        // Editable Class
        $params = array (
            "id" => $request->json()->get('id')
        );
        $resJSON = WorkGroup::deleteLineEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }


}
