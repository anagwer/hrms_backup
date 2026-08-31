<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Presence;
use App\Http\Models\Employment;
use App\Utilities\Utility;
use Carbon\Carbon;

// Declare All Used Models
use App\Http\Models\Sikel;
use App\Models\ExitPermit;
use App\Models\ExitPermitLine;
use App\Services\ExitPermitNotificationService;

class SikelController extends Controller
{
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

        if (empty(json_decode($args))) {
            return Utility::setResponseExT(400, 'securityRoles or businessunit, department, and division required.', '');
        }

        if (empty(json_decode($args)->securityRoles)) {
            return Utility::setResponseExT(400, 'args -> securityRoles required.', '');
        }

        $data = Sikel::getListBySecurityRoles($args, $offset, $rows);
        $dataRec = Sikel::getCountBySecurityRoles($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function detail(Request $request, $docnumber)
    {
        $params['docnumber'] = $docnumber;
        $data = Sikel::getSikelByNumber(json_encode($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    function detailById(Request $request)
    {
        $id = $request->json()->get('id');

        if (!isset($id) || $id == NULL) : return Utility::setResponseExT(400, 'Id required.', '');
        endif;

        $params['id'] = $id;
        $data = Sikel::getSikelById(json_encode($params));

        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    function outstanding(Request $request)
    {
        $params['employee'] = $request->creds->id;
        $data = Sikel::getOutstandingByEmployee(json_encode($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    function create(Request $request)
    {
        if ($request['docnumber'] == "") {
            return Utility::setResponseExT(901, "Field Nomor Document harus diisi!", []);
        }
        if ($request['employee'] == "") {
            return Utility::setResponseExT(901, "Field Karyawan harus diisi!", []);
        }
        if ($request['sikeltype'] == "") {
            return Utility::setResponseExT(901, "Field Jenis SIKEL harus diisi!", []);
        }
        if ($request['businessunit'] == "") {
            return Utility::setResponseExT(901, "Field Unit Bisnis harus diisi!", []);
        }
        if ($request['department'] == "") {
            return Utility::setResponseExT(901, "Field Departemen harus diisi!", []);
        }

        //cek employee dalam status masuk kantor (1) atau tidak
        if (!Presence::checkStatusIn($request->json()->get('employee'))) {
            return Utility::setResponseExT(901, "Pastikan anda sudah melakukan Absensi dan berada di lokasi kantor!", []);
        }

        // check if employee has an outstanding sikel
        $params['employee'] = $request->creds->id;
        if (Sikel::getOutstandingByEmployee(json_encode($params))) {
            return Utility::setResponseExT(901, "Anda masih memiliki sikel aktif (outstanding sikel)!", []);
        }

        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();

            $params = array(
                "id" => "$newID",
                'docnumber' => $request->json()->get('docnumber'),
                'employee' => $request->json()->get('employee'),
                'sikeltype' => $request->json()->get('sikeltype'),
                'description' => $request->json()->get('description'),
                'businessunit' => $request->json()->get('businessunit'),
                'department' => $request->json()->get('department'),
                'createdby' => $request->json()->get('createdby')
            );
            $resJSON = Sikel::createEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array(
                "id" => $newID,
                'employee' => $request->json()->get('employee'),
                'sikeltype' => $request->json()->get('sikeltype'),
                'description' => $request->json()->get('description'),
                'businessunit' => $request->json()->get('businessunit'),
                'department' => $request->json()->get('department'),
                'createdby' => $request->json()->get('createdby')
            );
            $resJSON = Sikel::updateEx(json_encode($params));
        }
        $object = json_decode($resJSON);
        $docnumber = $request->json()->get('docnumber');
        $resArray = Sikel::getList(Utility::setArgs($docnumber), 0, 1);

        $notification = new ExitPermitNotificationService(ExitPermit::find($newID));
        $notification->notify();

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        $id = $request->json()->get('id');
        $params = array(
            "id" => $id
        );
        $sikel = Sikel::getSikelById(json_encode($params));

        if ($sikel) {
            if ($sikel[0]->state != 0) {
                $resArray = Sikel::getList('', 0, 1);
                return Utility::setResponse($request, 403, "Data Sudah di-Apporve Tidak Dapat Dihapus", $resArray, 0, 0, 0, 0);
            } else {
                $resJSON = Sikel::deleteEx(json_encode($params));
                $object = json_decode($resJSON);
                return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
            }
        } else {
            return Utility::setResponse($request, 404, "Data Tidak Ditemukan", array(), 0, 0, 0, 0);
        }
    }

    /*
    Request
        {
            "docnumber": "LBG-20.0187",
            "approvaltype": "1", 1 for accept, 2 for reject
            "longitude" : 100,
            "latitude" : 100,
        }
    */
    function approve(Request $request)
    {
        if ($request['approvaltype'] == "") {
            return Utility::setResponseExT(901, "Jenis Approval harus diisi!", []);
        }
        if ($request['docnumber'] == "") {
            return Utility::setResponseExT(901, "Nomor Dokumen harus diisi!", []);
        }

        $find['docnumber'] = $request->docnumber;
        $sikel = Sikel::getSikelByNumber(json_encode($find))[0];
        if ($sikel == null) {
            return Utility::setResponseExT(901, "Sikel tidak ditemukan", []);
        }
        $approve = Employment::getByID($request->creds->id)[0];
        if ($approve == null) {
            return Utility::setResponseExT(901, "Approver tidak ditemukan", []);
        }
        // check if approver defaultlocationid isn't set.
        if (!$approve->defaultlocation) {
            return Utility::setResponseExt(901, "Default location approver belum diset!", []);
        }
        $current_time = Carbon::now();
        $employee = null;
        $type = null;

        if ($request['approvaltype'] == 2) {
            switch ($sikel->state) {
                case '9':
                case '99':
                    return Utility::setResponseExT(901, "Sikel sudah berakhir", []);
                    break;
                case '0':
                    $type = 1;
                    $params = array(
                        "docnumber" => $sikel->docnumber,
                        "state" => 99,
                        "approver" => $request->creds->id,
                        "approvedatetime" => $current_time->toDateTimeString()
                    );
                    break;
                case '1':
                    if ($approve->divisionname == "GENERAL AFFAIR") {
                        $type = 3;
                        $params = array(
                            "docnumber" => $sikel->docnumber,
                            "state" => 99,
                            "securitycheckoutdatetime" => $current_time->toDateTimeString(),
                            "securitycheckoutby" => $request->creds->id,
                        );
                    } else {
                        $type = 2;
                        $params = array(
                            "docnumber" => $sikel->docnumber,
                            "state" => 99,
                            "hrcheckby" => $request->creds->id,
                            "hrcheckdatetime" => $current_time->toDateTimeString()
                        );
                    }
                    break;
                case '2':
                    if ($approve->departmentname != "GENERAL AFFAIR") {
                        return Utility::setResponseExT(901, "Approver harus security", []);
                    }

                    $type = 3;
                    $params = array(
                        "docnumber" => $sikel->docnumber,
                        "state" => 99,
                        "securitycheckoutdatetime" => $current_time->toDateTimeString(),
                        "securitycheckoutby" => $request->creds->id,
                    );
                    break;
                default:
                    return Utility::setResponseExT(901, "Sikel sudah tidak dapat di tolak", []);
                    break;
            }
        } else {
            switch ($sikel->state) {
                case '0':
                    $type = 1;
                    $params = array(
                        "docnumber" => $sikel->docnumber,
                        "state" => 1, // kalau approval HR tidak ada, maka state diganti ke 2
                        "approver" => $request->creds->id,
                        "approvedatetime" => $current_time->toDateTimeString()
                    );
                    break;

                case '1':
                    if ($approve->divisionname == "GENERAL AFFAIR") {
                        $type = 3;
                        $params = array(
                            "docnumber" => $sikel->docnumber,
                            "state" => 3,
                            "securitycheckoutdatetime" => $current_time->toDateTimeString(),
                            "securitycheckoutby" => $request->creds->id,
                            "longout" => $request['longitude'],
                            "latout" => $request['latitude'],
                            "aplocationid" => $approve->defaultlocation
                        );

                        $temp = array(
                            "checkout" => $current_time->toDateTimeString(),
                            "checkoutby" => $request->creds->id,
                            "checkoutlong" => $request['longitude'],
                            "checkoutlat" => $request['latitude'],
                            "checkstate" => 3,
                            "reff" => $sikel->docnumber,
                            "remarks" => 'SIKEL'
                        );

                        $employee = array(
                            "employee_id" => $sikel->employee,
                            "data" => $temp
                        );
                    } else {
                        $type = 2;
                        $params = array(
                            "docnumber" => $sikel->docnumber,
                            "state" => 2,
                            "hrcheckby" => $request->creds->id,
                            "hrcheckdatetime" => $current_time->toDateTimeString()
                        );
                    }
                    break;
                case '2':
                    if ($approve->departmentname != "GENERAL AFFAIR") {
                        return Utility::setResponseExT(901, "Approver harus security", []);
                    }
                    if ($request['longitude'] == "") {
                        return Utility::setResponseExT(901, "Field Longitude harus diisi!", []);
                    }
                    if ($request['latitude'] == "") {
                        return Utility::setResponseExT(901, "Field Latitude harus diisi!", []);
                    }

                    $type = 3;
                    $params = array(
                        "docnumber" => $sikel->docnumber,
                        "state" => 3,
                        "securitycheckoutdatetime" => $current_time->toDateTimeString(),
                        "securitycheckoutby" => $request->creds->id,
                        "longout" => $request['longitude'],
                        "latout" => $request['latitude'],
                        "aplocationid" => $approve->defaultlocation
                    );

                    $temp = array(
                        "checkout" => $current_time->toDateTimeString(),
                        "checkoutby" => $request->creds->id,
                        "checkoutlong" => $request['longitude'],
                        "checkoutlat" => $request['latitude'],
                        "checkstate" => 3,
                        "reff" => $sikel->docnumber,
                        "remarks" => 'SIKEL'
                    );

                    $employee = array(
                        "employee_id" => $sikel->employee,
                        "data" => $temp
                    );
                    break;
                case '3':
                    if ($approve->departmentname != "GENERAL AFFAIR") {
                        return Utility::setResponseExT(901, "Approver harus security", []);
                    }
                    if ($request['longitude'] == "") {
                        return Utility::setResponseExT(901, "Field Longitude harus diisi!", []);
                    }
                    if ($request['latitude'] == "") {
                        return Utility::setResponseExT(901, "Field Latitude harus diisi!", []);
                    }

                    // check if approver is not from location origin, it's mean that the employee is
                    // traveling to other plant
                    if ($sikel->aplocationid != $approve->defaultlocation) {
                        $line = ExitPermitLine::where('ap_location_id', $approve->defaultlocation)
                            ->whereNull('check_out_datetime')->first();
                        if ($line) {
                            // if there is any existing open exit permit line, get the last record and update its check_out_datetime
                            $find = ExitPermitLine::where('sikel_id', $sikel->id)
                                ->where('ap_location_id', $approve->defaultlocation)
                                ->whereNull('check_out_datetime')->orderBy('created_at', 'desc')->first();
                            if ($find) {
                                $update = $find->update([
                                    'check_out_datetime' => $current_time->toDateTimeString(),
                                    'check_out_by' => $approve->id
                                ]);
                                if ($update) return Utility::setResponse($request, 200, 'Berhasil check out sikel line.', array(), 0, 0, 0, 0);
                                return Utility::setResponse($request, 405, 'Gagal update sikel line.', array(), 0, 0, 0, 0);
                            }
                            return Utility::setResponse($request, 405, 'Sikel line tidak ditemukan.', array(), 0, 0, 0, 0);
                        } else {
                            // create new exit permit line
                            $created = ExitPermitLine::create([
                                'sikel_id' => $sikel->id,
                                'ap_location_id' => $approve->defaultlocation,
                                'check_in_datetime' => $current_time->toDateTimeString(),
                                'check_in_by' => $approve->id,
                            ]);
                            if ($created) return Utility::setResponse($request, 200, 'Sikel line berhasil dibuat', array(), 0, 0, 0, 0);
                            return Utility::setResponse($request, 405, 'Berhasil check in sikel line.', array(), 0, 0, 0, 0);
                        }
                    } else {
                        $type = 4;
                        $params = array(
                            "docnumber" => $sikel->docnumber,
                            "state" => 9,
                            "securitycheckindatetime" => $current_time->toDateTimeString(),
                            "securitycheckinby" => $request->creds->id,
                            "longin" => $request['longitude'],
                            "latin" => $request['latitude'],
                        );

                        $employee_id = array(
                            "employee" => $sikel->employee
                        );

                        $temp = array(
                            "checkout" => null,
                            "checkoutby" => null,
                            "checkoutlong" => null,
                            "checkoutlat" => null,
                            "checkstate" => 1,
                            "reff" => null,
                            "remarks" => null
                        );

                        $employee = array(
                            "employee_id" => $employee_id,
                            "data" => $temp
                        );
                    }
                    break;
                default:
                    return Utility::setResponseExT(901, "Sikel sudah berakhir", []);
                    break;
            }
        }

        $resJSON = Sikel::approveSikel($params, $employee, $request->docnumber, $type);
        $object = json_decode($resJSON);
        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    function approval(Request $request)
    {
        if ($request['approvaltype'] == "") {
            return Utility::setResponseExT(901, "Jenis Approval harus diisi!", []);
        }
        if ($request['docnumber'] == "") {
            return Utility::setResponseExT(901, "Field Nomor Document harus diisi!", []);
        }
        if ($request['approvedatetime'] == "") {
            return Utility::setResponseExT(901, "Field Tanggal Approve harus diisi!", []);
        }
        $docnumber = $request->json()->get('docnumber');
        $sikel = Sikel::getSikelByDocNumber($docnumber);

        switch ($request['approvaltype']) {
            case 'depthead':
                if ($request['approver'] == "") {
                    return Utility::setResponseExT(901, "Field Approver harus diisi!", []);
                }
                if ($request['approvedatetime'] == "") {
                    return Utility::setResponseExT(901, "Field Tanggal Approve harus diisi!", []);
                }

                if ($sikel[0]->state != 0) {
                    return Utility::setResponseExT(901, "SIKEL sudah diapprove!", []);
                } else {
                    $params = array(
                        "type" => 1,
                        "docnumber" => $docnumber,
                        "status" => 1,
                        "approver" => $request->json()->get('approver'),
                        "approvedatetime" => $request->json()->get('approvedatetime')
                    );
                }
                break;
            case 'hrstaff':

                if ($sikel[0]->state != 1) {
                    return Utility::setResponseExT(901, "Approval tidak diijinkan, CEK STATUS APPROVAL!", []);
                } else {
                    $params = array(
                        "type" => 2,
                        "docnumber" => $docnumber,
                        "status" => 2,
                        "hrcheckby" => $request->json()->get('approver'),
                        "hrcheckdatetime" => $request->json()->get('approvedatetime')
                    );
                }
                break;
            case 'securityout':
                if ($request['securitycheckoutdatetime'] == "") {
                    return Utility::setResponseExT(901, "Field Tanggal Checkout harus diisi!", []);
                }
                if ($request['securitycheckoutby'] == "") {
                    return Utility::setResponseExT(901, "Field Nama Approval harus diisi!", []);
                }
                if ($request['longout'] == "") {
                    return Utility::setResponseExT(901, "Field Longitude harus diisi!", []);
                }
                if ($request['latout'] == "") {
                    return Utility::setResponseExT(901, "Field Latitude harus diisi!", []);
                }
                if ($request['employee'] == "") {
                    return Utility::setResponseExT(901, "Field Employee harus diisi!", []);
                }

                if ($sikel[0]->state >= 3 || $sikel[0]->state == 0) {
                    return Utility::setResponseExT(901, "Approval tidak diijinkan, CEK STATUS APPROVAL!", []);
                } else {
                    $params = array(
                        "type" => 3,
                        "docnumber" => $docnumber,
                        "status" => 3,
                        "securitycheckoutdatetime" => $request->json()->get('approvedatetime'),
                        "securitycheckoutby" => $request->json()->get('approver'),
                        "longout" => $request->json()->get('longitude'),
                        "latout" => $request->json()->get('latitude'),

                        "employee" => $sikel[0]->employee,
                        "checkout" => $request->json()->get('approvedatetime'),
                        "checkoutby" => $request->json()->get('approver'),
                        "checkoutlong" => $request->json()->get('longitude'),
                        "checkoutlat" => $request->json()->get('latitude'),
                        "checkstate" => 3,
                        "reff" => $docnumber,
                        "remarks" => 'SIKEL'
                    );
                }
                break;
            case 'securityin':
                if ($request['securitycheckindatetime'] == "") {
                    return Utility::setResponseExT(901, "Field Tanggal Checkout harus diisi!", []);
                }
                if ($request['securitycheckinby'] == "") {
                    return Utility::setResponseExT(901, "Field Nama Approval harus diisi!", []);
                }
                if ($request['employee'] == "") {
                    return Utility::setResponseExT(901, "Field Employee harus diisi!", []);
                }

                if ($sikel[0]->state != 3) {
                    return Utility::setResponseExT(901, "Approval tidak diijinkan, CEK STATUS APPROVAL!", []);
                } else {
                    $params = array(
                        "type" => 9,
                        "docnumber" => $docnumber,
                        "status" => 9,
                        "securitycheckindatetime" => $request->json()->get('approvedatetime'),
                        "securitycheckinby" => $request->json()->get('approver'),
                        "longin" => $request->json()->get('longitude'),
                        "latin" => $request->json()->get('latitude'),

                        "employee" => $sikel[0]->employee
                    );
                }
                break;
            case 'reject':
                if ($sikel[0]->state != 3) {
                    return Utility::setResponseExT(901, "Approval tidak diijinkan, CEK STATUS APPROVAL!", []);
                } else {
                    $params = array(
                        "type" => 5,
                        "docnumber" => $docnumber,
                        "status" => 5,
                        "securitycheckindatetime" => $request->json()->get('approvedatetime'),
                        "securitycheckinby" => $request->json()->get('approver'),
                        "longin" => $request->json()->get('longitude'),
                        "latin" => $request->json()->get('latitude'),

                        "employee" => $sikel[0]->employee
                    );
                }
                break;
            default:
                # code...
                break;
        }

        $resJSON = Sikel::approvalEx(json_encode($params));
        $object = json_decode($resJSON);
        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    /**
     * Get sikel accumulation by employee id and filter by month
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function accumulation(Request $request)
    {
        $employeeId = $request->json()->get('employeeId');
        $args = json_encode($request->json()->get('args'));

        if (!isset($employeeId) || $employeeId == NULL) : return Utility::setResponseExT(400, 'employeeId required.', '');
        endif;

        $presenceHistory = Sikel::getAccumulationByEmployeeId($employeeId, $args);

        return Utility::setResponseExT(200, 'Sikel accumulation retrieved successfully.', $presenceHistory, 0, 0);
    }

    /**
     * Get sikel history by employee id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function sikelhistory(Request $request)
    {
        $employeeId = $request->json()->get('employeeId');
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

        if (!isset($employeeId) || $employeeId == NULL) : return Utility::setResponseExT(400, 'employeeId required.', '');
        endif;

        $sikelHistory = Sikel::getListByEmployeeId($employeeId, $args, $offset, $rows);
        $dataRec = Sikel::getCountByEmployeeId($employeeId, $args);
        $dataCount = count($sikelHistory);

        return Utility::setResponseExT(200, 'Sikel history retrieved successfully.', $sikelHistory, intval($page), $dataRec, $dataCount, $rows);
    }
}
