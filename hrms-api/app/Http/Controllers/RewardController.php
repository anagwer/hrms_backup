<?php

namespace App\Http\Controllers;
use Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Carbon\Carbon;

// Declare All Used Models
use App\Http\Models\Reward;

class RewardController extends Controller
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
        $data = Reward::getList($args, $offset, $rows);
        $dataRec = Reward::getCount($args)[0]->cnt;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function detail(Request $request, $id)
    {
        $params['id'] = $id;
        $data = Reward::getRewardByParams(($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }


    function create(Request $request){

        if ($request['employee'] == "") {
            return Utility::setResponseExT(901, "Field Karyawan harus diisi!", []);
        }
        if ($request['dateinit'] == "") {
            return Utility::setResponseExT(901, "Field Tanggal harus diisi!", []);
        }
        if ($request['action'] == "") {
            return Utility::setResponseExT(901, "Field Tindakan harus diisi!", []);
        }

        $dateinit = date("Y/m/d", strtotime($request->json()->get('dateinit')));
        $dateaction = date("Y/m/d", strtotime($request->json()->get('dateaction')));

        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => $newID,
                "employee" => $request->json()->get('employee'),
                "rewardtype" => $request->json()->get('rewardtype'),
                "rdetail" => $request->json()->get('rdetail'),
                "dateinit" => $request->json()->get('dateinit'),
                "action" => $request->json()->get('action'),
                "dateaction" => $request->json()->get('dateaction'),
                "initiator" => $request->json()->get('initiator')
            );
            $resJSON = Reward::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $lastD = Reward::getList(Utility::setArgs($newID), 0, 1);
            $params = array (
                "id" => $newID,
                "employee" => $request->json()->get('employee'),
                "rewardtype" => $request->json()->get('rewardtype'),
                "rdetail" => $request->json()->get('rdetail'),
                "dateinit" => $request->json()->get('dateinit'),
                "action" => $request->json()->get('action'),
                "dateaction" => $request->json()->get('dateaction'),
                "initiator" => $request->json()->get('initiator')
            );
            $resJSON = Reward::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $resArray = Reward::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        $id = $request->json()->get('id');
        $params = array(
            "id" => $id
        );
        $cuti = Reward::getRewardByParams($params);

        if ($cuti) {
            if ($cuti[0]->state != 0) {
                $resArray = Reward::getList('', 0, 1);
                return Utility::setResponse($request, 403, "Data Sudah di-Apporve Tidak Dapat Dihapus", $resArray, 0, 0, 0, 0);
            } else {
                $resJSON = Reward::deleteEx(json_encode($params));
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
            return Utility::setResponseExT(901, "ID Reward wajib diisi", []);
        }
        if ($request['approval'] == "") {
            return Utility::setResponseExT(901, "Approval Reward wajib diisi", []);
        }
        $search['id'] = $request['id'];
        $data = Reward::getRewardByParams(($search));
        if($data->isEmpty()) {return Utility::setResponseExT(901, "ID Reward tidak ditemukan", []);}
        if($data[0]->state != 0) {return Utility::setResponseExT(901, "Reward sudah di ".$data[0]->statestr, []);}
        $current_time = Carbon::now()->toDateTimeString();

        switch ($request['approval']) {
            case 1:
                $temp = array(
                    "id"=>$request['id'],
                    "state" => 1,
                    "approvalby" => $request->creds->id,
                    "approvaldatetime" => $current_time
                );
                break;
            case 9:
                $temp = array(
                    "id"=>$request['id'],
                    "state" => 9,
                    "approvalby" => $request->creds->id,
                    "approvaldatetime" => $current_time
                );
                break;
            default:
                return Utility::setResponseExT(901, "Terdapat kesalahan pada field approval reward", []);
                break;
        }

        $resJSON = Reward::approveReward($temp);
        // echo $resJSON;
        return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    }
}
