<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\APLocation;
use App\Http\Models\Employment;
use App\Utilities\Utility;
use Carbon\Carbon;

// Declare All Used Models
use App\Http\Models\Presence;
use App\Models\Presence as PresenceEloquent;

class PresenceController extends Controller
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
        $data = Presence::getListBySecurityRoles($args, $offset, $rows);
        $dataRec = Presence::getCountBySecurityRoles($args);
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function create(Request $request)
    {
        $checkin = date("Y-m-d H:i:s", strtotime($request->json()->get('checkin')));
        if ($request->json()->get('id') == "") {
            $newID = Utility::newID();
            $params = array(
                "id" => "$newID",
                "employee" => $request->json()->get('employee'),
                "devicetype" => $request->json()->get('devicetype'),
                "deviceid" => $request->json()->get('deviceid'),
                "checkin" => $checkin,
                "checkinby" => $request->json()->get('checkinby'),
                "checkinlong" => $request->json()->get('checkinlong'),
                "checkinlat" => $request->json()->get('checkinlat'),
                "checkstate" => $request->json()->get('checkstate')
            );
            $resJSON = Presence::checkInEx(json_encode($params));
        } else {
            $newID = $request->json()->get('id');
            $params = array(
                "id" => "$newID",
                "employee" => $request->json()->get('employee'),
                "devicetype" => $request->json()->get('devicetype'),
                "deviceid" => $request->json()->get('deviceid'),
                "checkin" => $checkin,
                "checkinby" => $request->json()->get('checkinby'),
                "checkinlong" => $request->json()->get('checkinlong'),
                "checkinlat" => $request->json()->get('checkinlat'),
                "checkstate" => $request->json()->get('checkstate')
            );
            $resJSON = Presence::updateEx(json_encode($params));
        }

        $object = json_decode($resJSON);
        $resArray = Presence::getList(Utility::setArgs($newID), 0, 1);

        return Utility::setResponse($request, $object->code, $object->message, $resArray, 0, 0, 0, 0);
    }

    function outstanding(Request $request)
    {
        $params['employee'] = $request->creds->id;
        $data = Presence::getOutstandingByEmployee(json_encode($params));
        return response()->json([
            'success' => true,
            'message' => 'Get record success',
            'data' => ($data)
        ], 201);
    }

    /*
    Request
        {
            "type": 1,1 for masuk, 2 for pulang
            "devicetype": "ANDROID",
            "deviceid": "1a65asf46a",
            "longitude" : 100,
            "latitude" : 100,
        }
    */
    function presence(Request $request)
    {
        if ($request['type'] == "") {
            return Utility::setResponseExT(901, "Type absensi wajib diisi", []);
        }
        if ($request['devicetype'] == "") {
            return Utility::setResponseExT(901, "Device type wajib diisi", []);
        }
        if ($request['deviceid'] == "") {
            return Utility::setResponseExT(901, "Device ID wajib diisi", []);
        }
        if ($request['longitude'] == "") {
            return Utility::setResponseExT(901, "Longitude wajib diisi", []);
        }
        if ($request['latitude'] == "") {
            return Utility::setResponseExT(901, "Latitude wajib diisi", []);
        }
        $checkinby = ($request['checkinby'] != '') ? $request['checkinby'] : $request->creds->id;
        $current_time = Carbon::now()->toDateTimeString();

        $employee = Employment::getEmployeeByID($request->creds->id)[0];
        $locationIds = explode(',', $employee->defaultlocation);
        $locationErrorsBag = [];
        foreach ($locationIds as $locationId) {
            $locationInfo = APLocation::getByID($locationId);
            if (!$locationInfo) return Utility::setResponseExt(901, "Lokasi default belum diset", []);
            $distance = Utility::vincentyGreatCircleDistance(
                $locationInfo[0]->latitude,
                $locationInfo[0]->longitude,
                $request['latitude'],
                $request['longitude']
            );
            if ($distance > $locationInfo[0]->radius) {
                array_push($locationErrorsBag, ['name' => $locationInfo[0]->strname, 'radius' => $locationInfo[0]->radius]);
            }
        }
        if (count($locationErrorsBag) == count($locationIds)) {
            $errorMessages = '';
            foreach ($locationErrorsBag as $index => $error) {
                if ($index == 0) {
                    $errorMessages .= 'Anda harus berada di lokasi ' . $error['name'] . ' dengan radius ' . $error['radius'];
                } else if ($index == count($locationErrorsBag) - 1) {
                    $errorMessages .= ', dan ' . $error['name'] . ' dengan radius ' . $error['radius'] . '.';
                } else {
                    $errorMessages .= ', ' . $error['name'] . ' dengan radius ' . $error['radius'];
                }
            }
            return Utility::setResponseExT(901, $errorMessages, []);
        }

        if ($request['type'] == 1) {
            $params['id'] = Utility::newID();
            $params['employee'] = $request->creds->id;
            $params['devicetype'] = $request['devicetype'];

            $params['deviceid'] = $request['deviceid'];
            $params['checkin'] = $current_time;
            $params['checkinby'] = $checkinby;

            $params['checkinlong'] = $request['longitude'];
            $params['checkinlat'] = $request['latitude'];
            $params['checkstate'] = 1;

            $status = Presence::insert_presence(($params));
        } else if ($request['type'] == 2) {
            $where = array(
                "employee" => $request->creds->id,
                "checkstate" => 1
            );

            $temp = array(
                "devicetype" => $request['devicetype'],
                "deviceid" => $request['deviceid'],
                "checkout" => $current_time,

                "checkoutby" => $checkinby,
                "checkoutlong" => $request['longitude'],
                "checkoutlat" => $request['latitude'],

                "checkstate" => 3,
                "reff" => null,
                "remarks" => null
            );

            $params = array(
                "where" => $where,
                "data" => $temp
            );

            $status = Presence::update_presence(($params));

            // $status = Presence::update_presence(($params));

        }

        $object = json_decode($status);
        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    function delete(Request $request)
    {
        $params = array(
            "id" => $request->json()->get('id')
        );
        $resJSON = Presence::deleteEx(json_encode($params));
        $object = json_decode($resJSON);

        return Utility::setResponse($request, $object->code, $object->message, array(), 0, 0, 0, 0);
    }

    function listEmployeePulangAwal(Request $request)
    {
        $params = array();
        $page = $request->json()->get('page');
        if (($page == null) || ($page == '')) {
            $page = 1;
        }
        $rows = $request->json()->get('rows');
        if (($rows == null) || ($rows == '')) {
            $rows = Utility::DEFAULT_LIMIT();
        }
        // $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        // Editable Class
        $data = Presence::getListPulangAwal($params, $offset, $rows);
        $dataRec = Presence::getCountPulangAwal($params)->count;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    function listEmployeeTerlambat(Request $request)
    {
        $params = array();
        $page = $request->json()->get('page');
        if (($page == null) || ($page == '')) {
            $page = 1;
        }
        $rows = $request->json()->get('rows');
        if (($rows == null) || ($rows == '')) {
            $rows = Utility::DEFAULT_LIMIT();
        }
        // $args = json_encode($request->json()->get('args'));
        $offset = ($page - 1) * $rows;

        // Editable Class
        $data = Presence::getListTerlambat($params, $offset, $rows);
        $dataRec = Presence::getCountTerlambat($params)->count;
        $dataCount = count($data);

        return Utility::setResponseExT(200, '', $data, intval($page), $dataRec, $dataCount, $rows);
    }

    /**
     * Get presence history by employee id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function presencehistory(Request $request)
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

        $presenceHistory = Presence::getListByEmployeeId($employeeId, $args, $offset, $rows);
        $dataRec = Presence::getCountByEmployeeId($employeeId, $args);
        $dataCount = count($presenceHistory);

        if ($request->versionExpiredMessage) {
            return Utility::setResponseExT(199, $request->versionExpiredMessage, $presenceHistory, intval($page), $dataRec, $dataCount, $rows);
        }

        return Utility::setResponseExT(200, 'Presence history retrieved successfully.', $presenceHistory, intval($page), $dataRec, $dataCount, $rows);
    }

    /**
     * Get late accumulation by employee id and filter by month
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function lateAccumulation(Request $request)
    {
        $employeeId = $request->json()->get('employeeId');
        $args = json_encode($request->json()->get('args'));

        if (!isset($employeeId) || $employeeId == NULL) : return Utility::setResponseExT(400, 'employeeId required.', '');
        endif;

        $presenceHistory = Presence::getLateAccumulationByEmployeeId($employeeId, $args);

        return Utility::setResponseExT(200, 'Late accumulation retrieved successfully.', $presenceHistory, 0, 0);
    }

    function workDayRecap(Request $request)
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

        $data = Presence::getWorkDayRecap($args, $offset, $rows);

        return Utility::setResponseExT(200, '', $data, intval($page), 0, 0, $rows);
    }

    /**
     * Get today presence by employee id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    function getTodayPresence(Request $request)
    {
        $lastPresence = PresenceEloquent::where('employee', $request->creds->id)->orderBy('createddatetime', 'desc')->first();
        if(date('Y-m-d', strtotime($lastPresence->checkin)) == date('Y-m-d')){
            $data = Presence::getTodayPresence($request->creds->id);
        } else {
            if ($lastPresence->checkout==null){
                $data = Presence::getLastPresence($request->creds->id);
            } else {
                $data = null;
            }
        }
        return response()->json([
            'success' => true,
            'message' => "Presence retrieved successfully",
            'data' => ($data)
        ], 200);
    }
}
