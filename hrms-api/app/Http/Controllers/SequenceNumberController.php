<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\SequenceNumber;

class SequenceNumberController extends Controller
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
        $data = SequenceNumber::getList($args, $offset, $rows);
        $dataRec = SequenceNumber::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }


    function update(Request $request){
        $newID = $request->json()->get('id');
        $params = array (
            "id" => $newID,
            'prefix' => $request->json()->get('prefix'),
            'digit' => $request->json()->get('digit'),
            'lastnumber' => $request->json()->get('lastnumber')
        );
        $resJSON = SequenceNumber::updateEx(json_encode($params));
        $object = json_decode($resJSON);
        $resArray = SequenceNumber::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function generateSequence(Request $request){

        $params = array(
            "code" => $request->json()->get('code')
        );

        $number = SequenceNumber::generateNumberEx(json_encode($params));
        // $ret = array(
        //     "rows" => $number
        // );
        return Utility::setResponseExt( 200, '', $number, 0, 1, 1, 1);

        // return json_encode($ret);
    }
}
