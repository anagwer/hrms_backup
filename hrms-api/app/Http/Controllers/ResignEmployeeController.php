<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Carbon\Carbon;

// Declare All Used Models
use App\Http\Models\ResignEmployee;

class ResignEmployeeController extends Controller
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
        $data = ResignEmployee::getList($args, $offset, $rows);
        $dataRec = ResignEmployee::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function detail(Request $request, $id)
    {
        $params['id'] = $id;
        $data = ResignEmployee::getResignByParams(($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    function gettypelist(Request $request)
    {
        $data = ResignEmployee::getResignType();
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }


    function create(Request $request){
        $resigndate = date("Y/m/d", strtotime($request->json()->get('resigndate')));
        $approvedatetime = date("Y/m/d", strtotime($request->json()->get('approvedatetime')));
        $createddatetime = date("Y/m/d", strtotime($request->json()->get('createddatetime')));

        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => $newID,
                "employee" => $request->json()->get('employee'),
                "businessunit" => $request->json()->get('businessunit'),
                "branch" => $request->json()->get('branch'),
                "plant" => $request->json()->get('plant'),
                "pool" => $request->json()->get('pool'),
                "division" => $request->json()->get('division'),
                "department" => $request->json()->get('department'),
                "job" => $request->json()->get('job'),
                "grade" => $request->json()->get('grade'),
                "resigndate" => $resigndate,
                "resigntype" => $request->json()->get('resigntype'),
                "description" => $request->json()->get('description'),
                "state" => 0,
                "approveby" => $request->json()->get('approveby'),
                "approvedatetime" => $approvedatetime,
                "createdby" => $request->json()->get('createdby')
            );
            $resJSON = ResignEmployee::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = ResignEmployee::getList(Utility::setArgs($newID), 0, 1);
            $params = array (
                "id" => $newID,
                "employee" => $request->json()->get('employee'),
                "businessunit" => $request->json()->get('businessunit'),
                "branch" => $request->json()->get('branch'),
                "plant" => $request->json()->get('plant'),
                "pool" => $request->json()->get('pool'),
                "division" => $request->json()->get('division'),
                "department" => $request->json()->get('department'),
                "job" => $request->json()->get('job'),
                "grade" => $request->json()->get('grade'),
                "resigndate" => $resigndate,
                "resigntype" => $request->json()->get('resigntype'),
                "description" => $request->json()->get('description'),
                "state" => 0,
                "approveby" => $request->json()->get('approveby'),
                "approvedatetime" => $approvedatetime,
                "createdby" => $request->json()->get('createdby')
            );
            $resJSON = ResignEmployee::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = ResignEmployee::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        $id = $request->json()->get('id');
        $params = array(
            "id" => $id
        );
        $resign = ResignEmployee::getResignByParams($params);

        if ($resign) {
            if ($resign[0]->state != 0) {
                $resArray = ResignEmployee::getList('', 0, 1);
                return Utility::setResponse($request, 403, "Data Sudah di-Apporve Tidak Dapat Dihapus", $resArray, 0, 0, 0, 0);
            } else {
                $resJSON = ResignEmployee::deleteEx(json_encode($params));
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
            return Utility::setResponseExT(901, "ID Resign wajib diisi", []);
        }
        if ($request['approval'] == "") {
            return Utility::setResponseExT(901, "Approval Resign wajib diisi", []);
        }
        $search['id'] = $request['id'];
        $data = ResignEmployee::getResignByParams(($search));
        if($data->isEmpty()) {return Utility::setResponseExT(901, "ID Resign tidak ditemukan", []);}
        if($data[0]->state != 0) {return Utility::setResponseExT(901, "Resign sudah di ".$data[0]->statestr, []);}
        $current_time = Carbon::now()->toDateTimeString();

        switch ($request['approval']) {
            case 1:
                $temp = array(
                    "id"=>$request['id'],
                    "state" => 1,
                    "approveby" => $request->creds->id,
                    "approvedatetime" => $current_time
                );
                break;
            case 9:
                $temp = array(
                    "id"=>$request['id'],
                    "state" => 9,
                    "approveby" => $request->creds->id,
                    "approvedatetime" => $current_time
                );
                break;
            default:
                return Utility::setResponseExT(901, "Terdapat kesalahan pada field approval reward", []);
                break;
        }

        $resJSON = ResignEmployee::approveResign($temp);
        // echo $resJSON;
        return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    }


    // function approve(Request $request)
    // {
    //     if ($request['id'] == "") {
    //         return Utility::setResponseExT(901, "ID resign wajib diisi", []);
    //     }
    //     if ($request['approval'] == "") {
    //         return Utility::setResponseExT(901, "Approval cuti wajib diisi", []);
    //     }
    //     $search['id'] = $request['id'];
    //     $data = Cuti::getCutiByParams(($search));
    //     if($data->isEmpty()) {return Utility::setResponseExT(901, "ID Cuti tidak ditemukan", []);}
    //     if($data[0]->state != 0) {return Utility::setResponseExT(901, "Cuti sudah di ".$data[0]->statestr, []);}
    //     $current_time = Carbon::now()->toDateTimeString();
    //
    //     switch ($request['approval']) {
    //         case 1:
    //             $temp = array(
    //                 "id"=>$request['id'],
    //                 "state" => 1,
    //                 "approvalby" => $request->creds->id,
    //                 "approvaldatetime" => $current_time
    //             );
    //             break;
    //         case 9:
    //             $temp = array(
    //                 "id"=>$request['id'],
    //                 "state" => 9,
    //                 "approvalby" => $request->creds->id,
    //                 "approvaldatetime" => $current_time
    //             );
    //             break;
    //         default:
    //             return Utility::setResponseExT(901, "Terdapat kesalahan pada field approval cuti", []);
    //             break;
    //     }
    //
    //     $resJSON = Cuti::approveCuti($temp);
    //     // echo $resJSON;
    //     return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    // }
}
