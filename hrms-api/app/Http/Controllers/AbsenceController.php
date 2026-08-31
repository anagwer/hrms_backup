<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\Utility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

// Declare All Used Models
use App\Http\Models\Absence;
use App\Http\Models\Employment;

class AbsenceController extends Controller
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

        if (empty(json_decode($args))) {
            return Utility::setResponseExT(400, 'securityRoles or businessunit, department, and division required.', '');
        }

        if (empty(json_decode($args)->securityRoles)) {
            return Utility::setResponseExT(400, 'args -> securityRoles required.', '');
        }

        $data = Absence::getListBySecurityRoles($args, $offset, $rows);
        $dataRec = Absence::getCountBySecurityRoles($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function listAllByEmployee(Request $request)
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

        if (empty(json_decode($args)->employee)) {
            return Utility::setResponseExT(400, 'args -> employee required.', '');
        }

        $data = Absence::getListByEmployee($args, $offset, $rows);
        $dataRec = Absence::getCountByEmployee($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function manualAbsence(Request $request)
    {
        if ($request['employee'] == "") {
            return Utility::setResponseExT(901, "Field Karyawan harus diisi!", []);
        }
        if ($request['absencetype'] == "") {
            return Utility::setResponseExT(901, "Field Jenis Absen harus diisi!", []);
        }
        if ($request['transdate'] == "") {
            return Utility::setResponseExT(901, "Field Tanggal harus diisi!", []);
        }
        if ($request['remarks'] == "") {
            return Utility::setResponseExT(901, "Field Keterangan harus diisi!", []);
        }
        $transdate = date("Y/m/d h:i:s", strtotime($request->json()->get('transdate')));
        if ($request->json()->get('id') == "") {

            $fileName = $request->json()->get('attachment');

            if (!empty($request->json()->get('attachment'))) {
                $attachment = base64_decode($request->json()->get('attachment'));
                $fileName = md5(uniqid(rand(), true)) . '.jpg';
                Storage::disk('public')->put($fileName, $attachment);
            }

            $newID = Utility::newID();
            $params = array(
                "id" => "$newID",
                "employee" => $request->json()->get('employee'),
                "absencetype" => $request->json()->get('absencetype'),
                "remarks" => $request->json()->get('remarks'),
                "attachment" => $fileName,
                "transdate" => $transdate,
                "state" => $request->json()->get('state'),
                "qty" => $request->json()->get('qty'),
                "createdby" => $request->json()->get('createdby')
            );
            $resJSON = Absence::manualAbsence(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array(
                "id" => "$newID",
                "employee" => $request->json()->get('employee'),
                "transdate" => $transdate,
                "absencetype" => $request->json()->get('absencetype'),
                "remarks" => $request->json()->get('remarks'),
                "attachment" => $request->json()->get('attachment'),
                "transdate" => $request->json()->get('transdate'),
                "state" => $request->json()->get('state'),
                "qty" => $request->json()->get('qty'),
                "createdby" => $request->json()->get('createdby'),
                "createddatetime" => $request->json()->get('createddatetime')
            );
            $resJSON = Absence::updateEx(json_encode($params));
        }

        $object = json_decode($resJSON);
        return Utility::setResponse($request, $object->code, $object->message, null, 0, 0, 0, 0);
    }

    public function getImage($name)
    {
        $imagePath = storage_path('app/public') . '/' . $name;
        if (file_exists($imagePath)) {
            $file = file_get_contents($imagePath);
            return response($file, 200)->header('Content-Type', 'image/jpeg');
        }
        return Utility::setResponseExT(400, 'Image not found', '');
    }

    function delete(Request $request)
    {
        $params = array(
            "id" => $request->json()->get('id')
        );
        $resJSON = Absence::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    function post(Request $request)
    {
        if ($request['id'] == "") {
            return Utility::setResponseExT(901, "ID absence wajib diisi", []);
        }
        $search['id'] = $request['id'];
        $poster = Employment::getByID($request->creds->id)[0];
        $employee = ($request['employee'] != '') && $poster->divisionname == 'HUMAN RESOURCE AND GENERAL AFFAIR' ? $request['employee'] : $request->creds->id;
        $data = Absence::getAbsenceByParams(($search));
        $requestor = Employment::getByID($employee)[0];
        if ($data->isEmpty()) {
            return Utility::setResponseExT(901, "ID Absence tidak ditemukan", []);
        }
        if ($data[0]->state != 0) {
            return Utility::setResponseExT(901, "Absence sudah di " . $data[0]->statestr, []);
        }
        if ($requestor->totalcuti < $request['qty']) {
            return Utility::setResponseExT(901, "Jatah cuti kurang atau sudah habis", []);
        }
        $current_time = Carbon::now()->toDateTimeString();

        switch ($request['approval']) {
            case 1:
                // check if leave is expired

                $temp = array(
                    "id" => $request['id'],
                    "state" => 1,
                    "qty" => 1,
                    "approvalby" => $request->creds->id,
                    "approvaldatetime" => $current_time
                );
                break;
            default:
                return Utility::setResponseExT(901, "Terdapat kesalahan pada field approval absence", []);
                break;
        }

        $resJSON = Absence::postAbsence($temp);
        // echo $resJSON;
        return Utility::setResponse($request, $resJSON['code'], $resJSON['message'], array(), 0, 0, 0, 0);
    }

    /**
     * Get absence accumulation by employee id and filter by month
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

        $presenceHistory = Absence::getAccumulationByEmployeeId($employeeId, $args);

        return Utility::setResponseExT(200, 'Absence accumulation retrieved successfully.', $presenceHistory, 0, 0);
    }

    function absenceRecap(Request $request)
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

        $data = Absence::getAbsenceRecap($args, $offset, $rows);

        return Utility::setResponseExT(200, '', $data, intval($page), 0, 0, $rows);
    }
}
