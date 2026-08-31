<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Carbon\Carbon;

// Declare All Used Models
use App\Http\Models\Cuti;
use App\Http\Models\Employment;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Services\FCMService;
use Kreait\Firebase\Messaging\Notification;

class CutiController extends Controller
{
    /**
     * @param array $leave
     * @return bool
     */
    private function _pushLeaveNotification($leave)
    {
        $service = new FCMService();
        $receiverTokens = [];

        if ($receiver = $this->_getCurrentEmployeeHead()){
            if ($receiver->messagingToken()->count() != 0){
                array_push($receiverTokens, $receiver->messagingToken()->first()->token);
            }
        } else {
            $receivers = Employee::with('messagingToken')
                ->whereHas('employeeOrganization', function($query){
                    $query->whereHas('department', function ($query){
                        $query->where('strname', 'LIKE', 'HUMAN RESOURCE%');
                    });
                })
                ->has('messagingToken')
                ->get();
            foreach ($receivers as $receiver){
                array_push($receiverTokens, $receiver->messagingToken()->first()->token);
            }
        }

        $employee = Employee::findOrFail($leave['employee']);

        $notification = Notification::create('Permintaan Cuti Karyawan', $employee->strname);

        foreach ($receiverTokens as $token){
            $service->sendMessage($this->_buildMessage($leave, $employee), $token, $notification);
        }
    }

    private function _buildMessage($leave, $employee)
    {
        return [
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
            "type" => "leave",
            "data" => json_encode([
                "id" => $leave['id'],
                "empname" => $employee->strname,
                "leavename" => LeaveType::find($leave['leavetype'])->first()->strname,
                "qty" => $leave['qty'],
                "description" => $leave['description'],
                "state" => $leave['state'],
            ])
        ];
    }

    /**
     * @return App\Models\Employee | null
     */
    private function _getCurrentEmployeeHead()
    {
        $employee = Employee::findOrFail(request()->creds->id);
        if ($employee->reports_to){
            return Employee::findOrFail($employee->reports_to);
        }

        if ($head = $employee->employeeOrganization()->first()->department()->first()->head){
            return Employee::findOrFail($head);
        }

        return null;
    }

    function listAll(Request $request)
    {
        $page = $request->json()->get('page'); if (($page == null) || ($page == '')) { $page = 1; }
        $rows = $request->json()->get('rows'); if (($rows == null) || ($rows == '')) { $rows = Utility::DEFAULT_LIMIT(); }
        $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        if (empty(json_decode($args)))
        {
            return Utility::setResponseExT(400, 'securityRoles or businessunit, department, and division required.', '');
        }

        if (empty(json_decode($args)->securityRoles))
        {
            return Utility::setResponseExT(400, 'args -> securityRoles required.', '');
        }

        $data = Cuti::getListBySecurityRoles($args, $offset, $rows);
        $dataRec = Cuti::getCountBySecurityRoles($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function gettypelist(Request $request)
    {
        $data = Cuti::getCutiType();
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    function outstanding(Request $request)
    {
        $params['employee'] = $request->creds->id;
        // $params['state'] = 0;
        $data = Cuti::getOutstandingByEmployee(json_encode($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    function detail(Request $request, $id)
    {
        $params['id'] = $id;
        $data = Cuti::getCutiByParams(($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    /*
        {
            "leavetype": "uuid_leavetype",
            "fromdate": "2020-02-01",
            "qty": 1,
            "description" : "melawan alien",
            "employee" : "uuid_employee" // pembuat perlu divisi hr untuk dapat digunakan
        }
    */


    function create(Request $request){
        // check if mandatory field is empty
        /*
        $this->validate($request,
            [
                'leavetype' => 'required',
                'fromdate' => 'required|date|after:now',
                'qty' => 'required|int',
                'description' => ''
            ],
            [
                'required' => ':attribute wajib diisi',
                'after' => ':attribute harus berupa tanggal setelah sekarang'
            ],
            [
                'leavetype' => 'Tipe Cuti',
                'fromdate' => 'Tanggal',
                'qty' => 'Jumlah',
                'description' => 'Deskripsi'
            ]
        );
        */

        if ($request['leavetype'] == "") {
            return Utility::setResponseExT(901, "Tipe cuti wajib diisi", []);
        }
        if ($request['fromdate'] == "") {
            return Utility::setResponseExT(901, "Tanggal cuti wajib diisi", []);
        }
        if ($request['qty'] == "") {
            return Utility::setResponseExT(901, "Quantity cuti wajib diisi", []);
        }
        if ($request['description'] == "") {
            return Utility::setResponseExT(901, "Deskripsi cuti wajib diisi", []);
        }

        // check if leave date has passed
        if (date('Y/m/d', strtotime($request['fromdate'])) < date('Y/m/d')) {
            return Utility::setResponseExT(901, "Tanggal cuti yang dipilih sudah lewat", []);
        }
        // check if leave date is start from today, it must be next day, not today
        if (date('Y/m/d', strtotime($request['fromdate'])) == date('Y/m/d')) {
            return Utility::setResponseExT(901, "Cuti harus diambil satu hari sebelumnya", []);
        }

        $poster = Employment::getByID($request->creds->id)[0];
        $employee = ($request['employee'] != '') && $poster->divisionname == 'HUMAN RESOURCE AND GENERAL AFFAIR' ? $request['employee'] : $request->creds->id;
        $requestor = Employment::getByID($employee)[0];
        if ($requestor == null) {return Utility::setResponseExT(901, "Employee tidak ditemukan", []);}
        if ($requestor->totalcuti < $request['qty']) {return Utility::setResponseExT(901, "Jatah cuti kurang atau sudah habis", []);}

        $fromdate = date("Y/m/d", strtotime($request->json()->get('fromdate')));

        if($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array (
                "id" => "$newID",
                "employee" => $request->json()->get('employee'),
                "fromdate" => $fromdate,
                "qty" => $request->json()->get('qty'),
                "description" => $request->json()->get('description'),
                "leavetype" => $request->json()->get('leavetype'),
                "state" => 0,
                "createdby" => $request->json()->get('createdby')
            );
            $resJSON = cuti::insertCuti(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array (
                "id" => "$newID",
                "employee" => $request->json()->get('employee'),
                "fromdate" => $fromdate,
                "qty" => $request->json()->get('qty'),
                "description" => $request->json()->get('description'),
                "leavetype" => $request->json()->get('leavetype'),
                "state" => 0,
                "createdby" => $request->json()->get('createdby')
            );
            $resJSON = cuti::editCutiByID(json_encode($params));
        }

        $this->_pushLeaveNotification($params);
        $object = json_decode($resJSON);
        $resArray = cuti::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        $id = $request->json()->get('id');
        $params = array(
            "id" => $id
        );
        $cuti = Cuti::getCutiByParams($params);

        if ($cuti) {
            if ($cuti[0]->state != 0) {
                $resArray = Cuti::getList('', 0, 1);
                return Utility::setResponse($request, 403, "Data Sudah di-Apporve Tidak Dapat Dihapus", $resArray, 0, 0, 0, 0);
            } else {
                $resJSON = Cuti::deleteEx(json_encode($params));
                $object = json_decode($resJSON);
                return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
            }
        } else {
            return Utility::setResponse($request, 404, "Data Tidak Ditemukan", array(), 0, 0, 0, 0);
        }
    }


    /*
        {
            "id" : "uuid_leaves",
            "approval": 1 // 1 for approve, 2 for reject
        }
    */
    function approve(Request $request)
    {
        if ($request['id'] == "") {
            return Utility::setResponseExT(901, "ID cuti wajib diisi", []);
        }
        if ($request['approval'] == "") {
            return Utility::setResponseExT(901, "Approval cuti wajib diisi", []);
        }
        $search['id'] = $request['id'];
        $data = Cuti::getCutiByParams(($search));
        if($data->isEmpty()) {return Utility::setResponseExT(901, "ID Cuti tidak ditemukan", []);}
        if($data[0]->state != 0) {return Utility::setResponseExT(901, "Cuti sudah di ".$data[0]->statestr, []);}
        $current_time = Carbon::now()->toDateTimeString();

        switch ($request['approval']) {
            case 1:
                // check if leave is expired
                if (date('Y/m/d', strtotime($data[0]->fromdate)) <= date('Y/m/d')): return Utility::setResponseExT(901, "Cuti sudah kadaluarsa. Silahkan reject.", []); endif;

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
                return Utility::setResponseExT(901, "Terdapat kesalahan pada field approval cuti", []);
                break;
        }

        $resJSON = Cuti::approveCuti($temp);
        // echo $resJSON;
        return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    }


    function cancel(Request $request)
    {
        if ($request['id'] == "") {
            return Utility::setResponseExT(901, "ID cuti wajib diisi", []);
        }
        $search['id'] = $request['id'];
        $data = Cuti::getCutiByParams(($search));
        if($data->isEmpty()) {return Utility::setResponseExT(901, "ID Cuti tidak ditemukan", []);}
        if($data[0]->state != 1) {return Utility::setResponseExT(901, "Cuti sudah di ".$data[0]->statestr, []);}
        $current_time = Carbon::now()->toDateTimeString();

        switch ($request['approval']) {
            case 0:
                // check if leave is expired

                $temp = array(
                    "id"=>$request['id'],
                    "state" => 0,
                    "approvalby" => $request->creds->id,
                    "approvaldatetime" => $current_time
                );
                break;
            default:
                return Utility::setResponseExT(901, "Terdapat kesalahan pada field approval cuti", []);
                break;
        }

        $resJSON = Cuti::cancelCuti($temp);
        // echo $resJSON;
        return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    }

    /**
     * Get cuti history by employee id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function cutihistory(Request $request)
    {
        $employeeId = $request->json()->get('employeeId');
        $page = $request->json()->get('page'); if (($page == null) || ($page == '')) { $page = 1; }
        $rows = $request->json()->get('rows'); if (($rows == null) || ($rows == '')) { $rows = Utility::DEFAULT_LIMIT(); }
        $args = $request->json()->get('args');
        $offset = ($page - 1) * $rows;

        // check if employeeId is empty
        if (! isset($employeeId) || $employeeId == NULL): return Utility::setResponseExT(901, 'employeeId required.', ''); endif;

        // check if employee didn't exist
        $employee = Employment::getByID($employeeId);
        if (!$employee): return Utility::setResponseExT(901, "employee not found.", ''); endif;

        // assign employee joinDate into args
        $args['joinDate'] = $employee[0]->joindate;

        $cutiHistory = Cuti::getListByEmployeeId($employeeId, json_encode($args), $offset, $rows);
        $dataRec = Cuti::getCountByEmployeeId($employeeId, json_encode($args));
        $dataCount = count($cutiHistory);

        return Utility::setResponseExT(200, 'Cuti history retrieved successfully.', $cutiHistory, intval($page), $dataRec, $dataCount, $rows);
    }
}
