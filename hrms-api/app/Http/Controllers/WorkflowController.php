<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\Workflow;

class WorkflowController extends Controller
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
        $data = Workflow::getList($args, $offset, $rows);
        $dataRec = Workflow::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
        // return $request;
    }


    function create(Request $request){
        if($request['features'] ==""){return Utility::setResponseExT(901, "Filed Features harus diisi!", []);}
        if($request['approval1'] ==""){return Utility::setResponseExT(901, "Filed Approval1 harus diisi!", []);}
        if($request['department'] ==""){return Utility::setResponseExT(901, "Filed Department harus diisi!", []);}

        if($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => "$newID",
                "features" => $request->json()->get('features'),
                "approval1" => $request->json()->get('approval1'),
                "approval2" => $request->json()->get('approval2'),
                "approval3" => $request->json()->get('approval3'),
                "approval4" => $request->json()->get('approval4'),
                "approval5" => $request->json()->get('approval5'),
                "department" => $request->json()->get('department')
                
            );
            $resJSON = Workflow::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array (
                "id" => "$newID",
                "features" => $request->json()->get('features'),
                "approval1" => $request->json()->get('approval1'),
                "approval2" => $request->json()->get('approval2'),
                "approval3" => $request->json()->get('approval3'),
                "approval4" => $request->json()->get('approval4'),
                "approval5" => $request->json()->get('approval5'),
                "department" => $request->json()->get('department')
            );
            $resJSON = Workflow::updateEx(json_encode($params));
        }

        $object = json_decode($resJSON);
        $resArray = Workflow::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }


    function delete(Request $request) {
        $params = array (
            "id" => $request->json()->get('id')
        );
        $resJSON = Workflow::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }
}
