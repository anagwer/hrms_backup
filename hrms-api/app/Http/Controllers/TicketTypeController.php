<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;

// Declare All Used Models
use App\Http\Models\TicketType;

class TicketTypeController extends Controller
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
        $data = TicketType::getList($args, $offset, $rows);
        $dataRec = TicketType::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExt(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }


    function create(Request $request)
    {
        if ($request->json()->get('strname') == '') {
            return Utility::setResponseExT(901, "Field Nama Kategori Wajib Disikan", []);
        }
        if ($request->json()->get('ticket_problem_division') == '') {
            return Utility::setResponseExT(901, "Dvisi Wajib Dipilih", []);
        }
        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array(
                "id" => "$newID",
                'strname' => $request->json()->get('strname'),
                'ticket_problem_division' => $request->json()->get('ticket_problem_division')
            );
            $resJSON = TicketType::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = TicketType::getList(Utility::setArgs($newID), 0, 1);
            $params = array(
                "id" => $newID,
                'strname' => $request->json()->get('strname'),
                'ticket_problem_division' => $request->json()->get('ticket_problem_division')
            );
            $resJSON = TicketType::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = TicketType::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        // Editable Class
        $params = array(
            "id" => $request->json()->get('id')
        );
        $resJSON = TicketType::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }
}
