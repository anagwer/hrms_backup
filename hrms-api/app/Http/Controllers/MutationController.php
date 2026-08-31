<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Carbon\Carbon;

// Declare All Used Models
use App\Http\Models\Mutation;

class MutationController extends Controller
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
        $data = Mutation::getList($args, $offset, $rows);
        $dataRec = Mutation::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function detail(Request $request, $id)
    {
        $params['id'] = $id;
        $data = Mutation::getMutationByParams(($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }



    function create(Request $request)
    {
        $activedate = date("Y/m/d", strtotime($request->json()->get('activedate')));
        $approvedatetime = date("Y/m/d", strtotime($request->json()->get('approvedatetime')));
        $createddatetime = date("Y/m/d", strtotime($request->json()->get('createddatetime')));

        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array(
                "id" => $newID,
                "employee" => $request->json()->get('employee'),
                "frombusinessunit" => $request->json()->get('frombusinessunit'),
                "frombranch" => $request->json()->get('frombranch'),
                "fromplant" => $request->json()->get('fromplant'),
                "frompool" => $request->json()->get('frompool'),
                "fromdivision" => $request->json()->get('fromdivision'),
                "fromdepartment" => $request->json()->get('fromdepartment'),
                "fromjob" => $request->json()->get('fromjob'),
                "fromgrade" => $request->json()->get('fromgrade'),
                "fromrole" => $request->json()->get('fromrole'),
                "tobusinessunit" => $request->json()->get('tobusinessunit'),
                "tobranch" => $request->json()->get('tobranch'),
                "toplant" => $request->json()->get('toplant'),
                "topool" => $request->json()->get('topool'),
                "todivision" => $request->json()->get('todivision'),
                "todepartment" => $request->json()->get('todepartment'),
                "tojob" => $request->json()->get('tojob'),
                "tograde" => $request->json()->get('tograde'),
                "torole" => $request->json()->get('torole'),
                "activedate" => $activedate,
                "mutationtype" => $request->json()->get('mutationtype'),
                "description" => $request->json()->get('description'),
                "state" => 0,
                "approveby" => $request->json()->get('approveby'),
                "approvedatetime" => $approvedatetime,
                "createdby" => $request->json()->get('createdby')
            );
            $resJSON = Mutation::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = Mutation::getList(Utility::setArgs($newID), 0, 1);
            $params = array(
                "id" => $newID,
                "employee" => $request->json()->get('employee'),
                "frombusinessunit" => $request->json()->get('frombusinessunit'),
                "frombranch" => $request->json()->get('frombranch'),
                "fromplant" => $request->json()->get('fromplant'),
                "frompool" => $request->json()->get('frompool'),
                "fromdivision" => $request->json()->get('fromdivision'),
                "fromdepartment" => $request->json()->get('fromdepartment'),
                "fromjob" => $request->json()->get('fromjob'),
                "fromgrade" => $request->json()->get('fromgrade'),
                "fromrole" => $request->json()->get('fromrole'),
                "tobusinessunit" => $request->json()->get('tobusinessunit'),
                "tobranch" => $request->json()->get('tobranch'),
                "toplant" => $request->json()->get('toplant'),
                "topool" => $request->json()->get('topool'),
                "todivision" => $request->json()->get('todivision'),
                "todepartment" => $request->json()->get('todepartment'),
                "tojob" => $request->json()->get('tojob'),
                "tograde" => $request->json()->get('tograde'),
                "torole" => $request->json()->get('torole'),
                "activedate" => $activedate,
                "mutationtype" => $request->json()->get('mutationtype'),
                "description" => $request->json()->get('description'),
                "state" => 0,
                "approveby" => $request->json()->get('approveby'),
                "approvedatetime" => $approvedatetime,
                "createdby" => $request->json()->get('createdby')
            );
            $resJSON = Mutation::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = Mutation::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }



    // function delete(Request $request)
    // {
    //     $id = $request->json()->get('id');
    //     $params = array(
    //         "id" => $id
    //     );
    //     $mutation = Mutation::getCutiByParams($params);
    //
    //     if ($cuti) {
    //         if ($cuti[0]->state != 0) {
    //             $resArray = Mutation::getList('', 0, 1);
    //             return Utility::setResponse($request, 403, "Data Sudah di-Apporve Tidak Dapat Dihapus", $resArray, 0, 0, 0, 0);
    //         } else {
    //             $resJSON = Cuti::deleteEx(json_encode($params));
    //             $object = json_decode($resJSON);
    //             return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    //         }
    //     } else {
    //         return Utility::setResponse($request, 404, "Data Tidak Ditemukan", array(), 0, 0, 0, 0);
    //     }
    // }

    function delete(Request $request)
    {
        $id = $request->json()->get('id');
        $params = array(
            "id" => $id
        );
        $mutation = Mutation::getMutationByParams($params);

        if ($mutation) {
            if ($mutation[0]->status != "Created") {
                $resArray = Mutation::getList('', 0, 1);
                return Utility::setResponse($request, 403, "Data Sudah di-Apporve Tidak Dapat Dihapus", $resArray, 0, 0, 0, 0);
            } else {
                $resJSON = Mutation::deleteEx(json_encode($params));
                $object = json_decode($resJSON);
                return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
            }
        } else {
            return Utility::setResponse($request, 404, "Data Tidak Ditemukan", array(), 0, 0, 0, 0);
        }
    }

    function approve(Request $request)
    {
        if ($request['id'] == "") {
            return Utility::setResponseExT(901, "ID Mutation wajib diisi", []);
        }
        if ($request['approval'] == "") {
            return Utility::setResponseExT(901, "Approval Mutation wajib diisi", []);
        }
        $search['id'] = $request['id'];
        $data = Mutation::getMutationByParams(($search));
        if ($data->isEmpty()) {
            return Utility::setResponseExT(901, "ID Mutation tidak ditemukan", []);
        }
        if ($data[0]->status != "Created") {
            return Utility::setResponseExT(901, "Mutation sudah di " . $data[0]->status, []);
        }
        $current_time = Carbon::now()->toDateTimeString();

        switch ($request['approval']) {
            case 1:
                $temp = array(
                    "id" => $request['id'],
                    "state" => 1,
                    "approveby" => $request->creds->id,
                    "approvedatetime" => $current_time
                );
                break;
            case 9:
                $temp = array(
                    "id" => $request['id'],
                    "state" => 9,
                    "approveby" => $request->creds->id,
                    "approvedatetime" => $current_time
                );
                break;
            default:
                return Utility::setResponseExT(901, "Terdapat kesalahan pada field approval reward", []);
                break;
        }

        $resJSON = Mutation::approveMutation($temp);
        // echo $resJSON;
        return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    }
}
