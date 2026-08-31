<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\ProblemType;

class ProblemTypeController extends Controller
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

        // Editable Class
        $data = ProblemType::getList($args, $offset, $rows);
        $dataRec = ProblemType::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExt(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function getByID(Request $request)
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

        $data = ProblemType::getByID($args, $offset, $rows);
        $dataRec = ProblemType::getCount($args)[0]->cnt;
        $dataCount = count($data);
        return Utility::setResponseExT(200, '', $data, intval(1), 1, 1, 1);
    }

    function create(Request $request)
    {
        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array(
                "id" => "$newID",
                'ticket_problem_category' => $request->json()->get('ticket_problem_category'),
                'name' => $request->json()->get('name')
            );
            $resJSON = ProblemType::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array(
                "id" => $newID,
                'ticket_problem_category' => $request->json()->get('ticket_problem_category'),
                'name' => $request->json()->get('name')
            );
            $resJSON = ProblemType::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = ProblemType::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        $params = array(
            "id" => $request->json()->get('id')
        );
        $resJSON = ProblemType::deleteEx(json_encode($params));
        $object = json_decode($resJSON);
        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }
}
