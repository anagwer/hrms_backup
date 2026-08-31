<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\Presence;
use App\Http\Models\WorkDay;

class WorkDayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    function listAll(Request $request)
    {
        $page = $request->json()->get('page');
        if (($page == null) || ($page == '')) {
            $page = 1;
        }
        $rows = $request->json()->get('rows');
        if (($rows == null) || ($rows == '')) {
            $rows = Utility::DEFAULT_LIMIT();
        }
        $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        $data = WorkDay::getList($args, $offset, $rows);
        $dataRec = WorkDay::getCount($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function create(Request $request)
    {
        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array(
                "id" => "$newID",
                "period" => $request->json()->get('period'),
                "from_date" => $request->json()->get('from_date'),
                "to_date" => $request->json()->get('to_date'),
                "work_day" => $request->json()->get('work_day')
            );
            $resJSON = WorkDay::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array(
                "id" => "$newID",
                "period" => $request->json()->get('period'),
                "from_date" => $request->json()->get('from_date'),
                "to_date" => $request->json()->get('to_date'),
                "work_day" => $request->json()->get('work_day')
            );
            $resJSON = WorkDay::updateEx(json_encode($params));
        }

        $object = json_decode($resJSON);
        $resArray = WorkDay::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        $params = array(
            "id" => $request->json()->get('id')
        );
        $resJSON = WorkDay::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }
}
