<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Presence extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter = $filter . " AND (businessunitid  = '$params->businessunit')";
        }

        if (isset($params->division) && ($params->division != "")) {
            $filter = $filter . " AND (divisionid  = '$params->division')";
        }

        if (isset($params->department) && ($params->department != "")) {
            $filter = $filter . " AND (departmentid  = '$params->departmentid')";
        }

        if (isset($params->checkin) && ($params->checkin != "")) {
            $filter = $filter . " AND (checkin  = '$params->checkin')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " or (nik like '%" . $params->src . "%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM presenceview
            WHERE id is not null {$filter} {$where}
            ORDER BY createddatetime desc
            LIMIT :a, :b",
            [
                "a" => $offset,
                "b" => $rec
            ]
        );
        return $stmt;
    }


    public static function getCount($args)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter = $filter . " AND (businessunitid  = '$params->businessunit')";
        }

        if (isset($params->division) && ($params->division != "")) {
            $filter = $filter . " AND (divisionid  = '$params->division')";
        }

        if (isset($params->department) && ($params->department != "")) {
            $filter = $filter . " AND (departmentid  = '$params->departmentid')";
        }

        if (isset($params->checkin) && ($params->checkin != "")) {
            $filter = $filter . " AND (checkin  = '$params->checkin')";
        }


        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%" . $params->src . "%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM presenceview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    /**
     * Get list of presence with certain businessUnitIds. (filtered by array of securityRoles)
     *
     * @param string $args
     * @param int $offset
     * @param int $rec
     * @return array
     */
    public static function getListBySecurityRoles($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        $departmentIds = array();
        $divisionIds = array();
        $businessUnitIds = array();

        // get user businessunit, division, and department by user security roles.
        foreach ($params->securityRoles as $securityRole) {
            $department = RoleOrganization::getRoleOrganizationById($securityRole->roleOrganizationId);
            array_push($businessUnitIds, $securityRole->businessUnitId);
            array_push($departmentIds, $department->idDept);
            array_push($divisionIds, $department->idDivision);
        }

        // if filter by department is empty, then filter by user department from security roles
        if ((!isset($params->department)) || ($params->department == "")) {
            if (!empty($departmentIds)) {
                $filter = $filter . " AND (departmentid IN ('" . join("', '", $departmentIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (departmentid = '$params->department')";
        }

        // if filter by division is empty, then filter by user division from security roles
        if ((!isset($params->division)) || ($params->division == "")) {
            if (!empty($divisionIds)) {
                $filter = $filter . " AND (divisionid IN ('" . join("', '", $divisionIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (divisionid = '$params->division')";
        }

        // if filter by businessunit is empty, then filter by user businessunit from security roles
        if ((!isset($params->businessunit)) || ($params->businessunit == "")) {
            if (!empty($businessUnitIds)) {
                $filter = $filter . " AND (businessunitid IN ('" . join("', '", $businessUnitIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (businessunitid = '$params->businessunit')";
        }

        if (isset($params->checkinFrom) && ($params->checkinFrom != "")) {
            $filter = $filter . " AND (checkin >= '$params->checkinFrom 00:00:00'  AND checkin <= '$params->checkinTo 23:59:59' )";
        }

        if (isset($params->nik) && ($params->nik != "")) {
            $filter = $filter . " AND (nik = '$params->nik')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%$params->src%')";
        }
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT pres.* , (SELECT COUNT(*) FROM absenceview WHERE absencetypename='IZIN TERLAMBAT'
            AND DATE_FORMAT(createddatetime, '%d-%m-%Y')=DATE_FORMAT(pres.createddatetime, '%d-%m-%Y')) late_permit_count
            FROM presenceview pres
            WHERE id is not null {$filter} {$where}
            ORDER BY createddatetime desc
            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    /**
     * Get count of presence with certain businessUnitIds. (filtered by array of securityRoles)
     *
     * @param string $args
     * @return int
     */
    public static function getCountBySecurityRoles($args)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        $departmentIds = array();
        $divisionIds = array();
        $businessUnitIds = array();

        // get user businessunit, division, and department by user security roles.
        foreach ($params->securityRoles as $securityRole) {
            $department = RoleOrganization::getRoleOrganizationById($securityRole->roleOrganizationId);
            array_push($businessUnitIds, $securityRole->businessUnitId);
            array_push($departmentIds, $department->idDept);
            array_push($divisionIds, $department->idDivision);
        }

        // if filter by department is empty, then filter by user department from security roles
        if ((!isset($params->department)) || ($params->department == "")) {
            if (!empty($departmentIds)) {
                $filter = $filter . " AND (departmentid IN ('" . join("', '", $departmentIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (departmentid = '$params->department')";
        }

        // if filter by division is empty, then filter by user division from security roles
        if ((!isset($params->division)) || ($params->division == "")) {
            if (!empty($divisionIds)) {
                $filter = $filter . " AND (divisionid IN ('" . join("', '", $divisionIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (divisionid = '$params->division')";
        }

        // if filter by businessunit is empty, then filter by user businessunit from security roles
        if ((!isset($params->businessunit)) || ($params->businessunit == "")) {
            if (!empty($businessUnitIds)) {
                $filter = $filter . " AND (businessunitid IN ('" . join("', '", $businessUnitIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (businessunitid = '$params->businessunit')";
        }

        if (isset($params->checkinFrom) && ($params->checkinFrom != "")) {
            $filter = $filter . " AND (checkin >= '$params->checkinFrom 00:00:00'  AND checkin <= '$params->checkinTo 23:59:59' )";
        }

        if (isset($params->nik) && ($params->nik != "")) {
            $filter = $filter . " AND (nik = '$params->nik')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%$params->src%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT count(nik) total
            FROM presenceview
            WHERE id is not null {$filter} {$where}"
        );
        return $stmt[0]->total;
    }

    public static function insert_presence($params)
    {
        try {
            DB::table('presence')->insert($params);

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function update_presence($params)
    {
        try {
            DB::table('presence')
                ->where($params['where'])
                ->update($params['data']);

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function checkInEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT into presence (id, employee, devicetype, deviceid, checkin, checkinby, checkinlong, checkinlat, checkstate)
                VALUES(:id, :employee, :devicetype, :deviceid, :checkin, :checkinby, :checkinlong, :checkinlat, :checkstate)",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "devicetype" => $input->devicetype,
                    "deviceid" => $input->deviceid,
                    "checkin" => $input->checkin, // datetime waktu absen masuk
                    "checkinby" => $input->checkinby,
                    "checkinlong" => $input->checkinlong,
                    "checkinlat" => $input->checkinlat,
                    "checkstate" => $input->checkstate // 1: masuk, 2: istirahat keluar,  3: sikel keluar, 9 pulang
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function checkOutEx($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE presence set
                    checkout = :checkout,
                    checkoutby = :checkoutby,
                    checkoutlong = :checkoutlong,
                    checkoutlat = :checkoutlat,
                    checkstate = :checkstate,
                    reff = :reff,
                    remarks = :remarks
                where employee = :employee and checkstate = 1",
                [
                    "employee" => $input->employee,
                    "checkout" => $input->checkout,
                    "checkoutby" => $input->checkoutby,
                    "checkoutlong" => $input->checkoutlong,
                    "checkoutlat" => $input->checkoutlat,
                    "checkstate" => $input->checkstate, // 1: masuk, 2: istirahat keluar,  3: sikel keluar, 9 pulang
                    "reff" => $input->reff,
                    "remarks" => $input->remarks
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    // employee masuk dari SIKEL. Update status, kembalikan checkstate ke 1
    public static function sikelIn($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE presence set
                    checkout = :checkout,
                    checkoutby = :checkoutby,
                    checkoutlong = :checkoutlong,
                    checkoutlat = :checkoutlat,
                    checkstate = :checkstate,
                    reff = :reff,
                    remarks = :remarks
                where employee = :employee and chekcstate = 1",
                [
                    "employee" => $input->employee,
                    "checkout" => null,
                    "checkoutby" => null,
                    "checkoutlong" => null,
                    "checkoutlat" => null,
                    "checkstate" => 1, // 1: masuk, 2: istirahat keluar,  3: sikel keluar, 9 pulang
                    "reff" => null,
                    "remarks" => null
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function checkStatusIn($params)
    {
        $stmt = DB::select(
            "SELECT * from presenceview where employee = :employee and checkstate = 1",
            [
                "employee" => $params
            ]
        );

        return $stmt;
    }

    public static function manualAttendance($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT into presence (id, employee, devicetype, deviceid, checkin, checkinby, checkinlong, checkinlat, checkstate)
                VALUES(:id, :employee, :devicetype, :deviceid, :checkin, :checkinby, :checkinlong, :checkinlat, :checkstate)",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "devicetype" => $input->devicetype,
                    "deviceid" => $input->deviceid,
                    "checkin" => $input->checkin,
                    "checkinby" => $input->checkinby,
                    "checkinlong" => $input->checkinlong,
                    "checkinlat" => $input->checkinlat,
                    "checkstate" => $input->checkstate
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function updateEx($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE presence SET
                    employee = :employee,
                    devicetype = :devicetype,
                    deviceid = :deviceid,
                    checkin = :checkin,
                    checkinby = :checkinby,
                    checkinlong = :checkinlong,
                    checkinlat = :checkinlat,
                    checkstate = :checkstate
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "devicetype" => $input->devicetype,
                    "deviceid" => $input->deviceid,
                    "checkin" => $input->checkin,
                    "checkinby" => $input->checkinby,
                    "checkinlong" => $input->checkinlong,
                    "checkinlat" => $input->checkinlat,
                    "checkstate" => $input->checkstate
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function deleteEx($params)
    {
        $input = json_decode($params);
        try {
            DB::delete("DELETE FROM presence WHERE id = :id", ['id' => $input->id]);
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }

    public static function getOutstandingByEmployee($params)
    {
        $input = json_decode($params);
        $stmt = DB::select("SELECT * FROM presenceview WHERE employee = :employee and `checkstate` = 1  ORDER BY createddatetime desc LIMIT 1", ['employee' => $input->employee]);
        return $stmt;
    }


    public static function getListPulangAwal($params, $offset, $rows)
    {
        $data = DB::table('presenceview')
            ->where($params)
            ->whereRaw('timestampdiff(MINUTE, checkin, checkout) < 510')
            ->offset($offset)
            ->limit($rows)
            ->get();
        return $data;
    }

    public static function getCountPulangAwal($params)
    {
        $data = DB::table('presenceview')
            ->where($params)
            ->whereRaw('timestampdiff(MINUTE, checkin, checkout) < 510')
            ->select(DB::raw('count(*) as count'))
            ->first();
        return $data;
    }

    public static function getListTerlambat($params, $offset, $rows)
    {
        $data = DB::table('presenceview')
            ->where($params)
            ->whereRaw('TIMESTAMPDIFF(MINUTE, schedule.workday, presenceview.checkin) BETWEEN 5 AND 300')
            ->join('workgroupline', 'workgroupline.employee', '=', 'presenceview.employee')
            ->join('schedule', 'schedule.workgroup', '=', 'workgroupline.workgroup')
            ->offset($offset)
            ->limit($rows)
            ->get();
        return $data;
    }

    public static function getCountTerlambat($params)
    {
        $data = DB::table('presenceview')
            ->where($params)
            ->whereRaw('TIMESTAMPDIFF(MINUTE, schedule.workday, presenceview.checkin) BETWEEN 5 AND 300')
            ->join('workgroupline', 'workgroupline.employee', '=', 'presenceview.employee')
            ->join('schedule', 'schedule.workgroup', '=', 'workgroupline.workgroup')
            ->select(DB::raw('count(*) as count'))
            ->first();
        return $data;
    }

    /**
     * Get all late accumulation by monthly salary period.
     *
     * @param string $employeeId
     * @param string $args
     * @return object
     */
    public static function getLateAccumulationByEmployeeId($employeeId, $args)
    {
        $params = json_decode($args);
        $filter = "";

        // add filter by month and year
        if (isset($params->dateFilter) && ($params->dateFilter != "")) {
            // check if requested day is > 20th
            if (date('d', strtotime($params->dateFilter)) > 20) {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
            } else {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
            }

            $filter = $filter . " AND (checkin BETWEEN '$startDate' AND '$endDate')";
        }

        // query only if checkin more than 07:35
        $filter = $filter . " AND (checkin >= CONCAT(LEFT(checkin, 10), ' 07:35:00'))";

        $stmt = DB::select(
            "SELECT
                COUNT(id) as total,
                IF(
                    COUNT(id) = 0,
                    '0 menit',
                    CONCAT(
                        CONCAT(FLOOR(SUM(TIMESTAMPDIFF(MINUTE, CONCAT(LEFT(checkin, 10), ' 07:35:00'), checkin)) / 60), ' jam '),
                        CONCAT(SUM(TIMESTAMPDIFF(MINUTE, CONCAT(LEFT(checkin, 10), ' 07:35:00'), checkin)) % 60, ' menit')
                    )
                ) as accumulation
            FROM presenceview
            WHERE id is not null AND employee = :employeeId {$filter}",
            [
                'employeeId' => $employeeId
            ]
        );

        return $stmt[0];
    }

    /**
     * Get all presence by employeeId and filter it by monthly salary period.
     *
     * @param string $employeeId
     * @param int $offset
     * @param int $rec
     * @return array
     */
    public static function getListByEmployeeId($employeeId, $args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";

        // add filter by month and year
        if (isset($params->dateFilter) && ($params->dateFilter != "")) {
            // check if requested day is > 20th
            if (date('d', strtotime($params->dateFilter)) > 20) {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
            } else {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
            }

            $filter = $filter . " AND (checkin BETWEEN '$startDate' AND '$endDate')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM presenceview
            WHERE id is not null AND employee = :employeeId {$filter}
            ORDER BY createddatetime desc
            LIMIT :offset, :rec",
            [
                'employeeId' => $employeeId,
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    /**
     * Get count all presence by employeeId and filter it by monthly salary period.
     *
     * @param string $employeeId
     * @return int
     */
    public static function getCountByEmployeeId($employeeId, $args)
    {
        $params = json_decode($args);
        $filter = "";

        // add filter by month and year
        if (isset($params->dateFilter) && ($params->dateFilter != "")) {
            // check if requested day is > 20th
            if (date('d', strtotime($params->dateFilter)) > 20) {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
            } else {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
            }

            $filter = $filter . " AND (checkin BETWEEN '$startDate' AND '$endDate')";
        }

        $stmt = DB::select(
            "SELECT count(id) total
            FROM presenceview
            WHERE id is not null AND employee = :employeeId {$filter}
            ORDER BY createddatetime desc",
            [
                'employeeId' => $employeeId
            ]
        );
        return $stmt[0]->total;
    }


    /**
     * Get list of absence with certain businessUnitIds. (filtered by array of securityRoles)
     *
     * @param string $args
     * @param int $offset
     * @param int $rec
     * @return array
     */
    public static function getWorkDayRecap($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        $departmentIds = array();
        $divisionIds = array();
        $businessUnitIds = array();

        // get user businessunit, division, and department by user security roles.
        foreach ($params->securityRoles as $securityRole) {
            $department = RoleOrganization::getRoleOrganizationById($securityRole->roleOrganizationId);
            array_push($businessUnitIds, $securityRole->businessUnitId);
            array_push($departmentIds, $department->idDept);
            array_push($divisionIds, $department->idDivision);
        }

        // if filter by department is empty, then filter by user department from security roles
        if ((!isset($params->department)) || ($params->department == "")) {
            if (!empty($departmentIds)) {
                $filter = $filter . " AND (pres.departmentid IN ('" . join("', '", $departmentIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (pres.departmentid = '$params->department')";
        }

        // if filter by division is empty, then filter by user division from security roles
        if ((!isset($params->division)) || ($params->division == "")) {
            if (!empty($divisionIds)) {
                $filter = $filter . " AND (pres.divisionid IN ('" . join("', '", $divisionIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (pres.divisionid = '$params->division')";
        }

        // if filter by businessunit is empty, then filter by user businessunit from security roles
        if ((!isset($params->businessunit)) || ($params->businessunit == "")) {
            if (!empty($businessUnitIds)) {
                $filter = $filter . " AND (pres.businessunitid IN ('" . join("', '", $businessUnitIds) . "'))";
            }
        } else {
            $filter = $filter . " AND (pres.businessunitid = '$params->businessunit')";
        }

        if ((isset($params->dateFrom) && ($params->dateFrom != "")) && (isset($params->dateTo) && ($params->dateTo != ""))) {
            $filter = $filter . " AND (pres.createddatetime >= '$params->dateFrom 00:00:00'  AND pres.createddatetime <= '$params->dateTo 23:59:59' )";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (pres.employeename like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT DISTINCT (pres.nik) , emp.activedate, emp.contractstatus, pres.employeename empname, emp.jobname, emp.businessunitname ,
            private_permit.private_permit_duration, abs_count.izin, abs_count.sakit, abs_count.alpha, abs_count.sdr,
            cuti.cuti_qty,
            (SELECT work_day FROM workdays WHERE period='$params->period') work_day
            FROM presenceview pres
            LEFT JOIN employment emp ON pres.employee=emp.id
            LEFT JOIN (SELECT s.employee, TIME(SUM( TIMEDIFF(s.securitycheckindatetime, s.securitycheckoutdatetime))) private_permit_duration
                        FROM sikel s WHERE s.sikeltype=2 AND (s.createddatetime >= '$params->dateFrom 00:00:00' AND s.createddatetime <= '$params->dateTo 23:59:59')
                        GROUP BY employee) private_permit ON private_permit.employee=pres.employee
            LEFT JOIN (SELECT employee, SUM(qty) cuti_qty
					    FROM leaves WHERE state=1 and createddatetime >= '2021-01-01 00:00:00' AND createddatetime <= '2022-01-30 23:59:59'
						GROUP BY employee) cuti ON pres.employee=cuti.employee
            LEFT JOIN (SELECT ab.employee,
                        COUNT(case ab.absencetypename when 'IZIN' then 1 else null end) AS izin,
                        COUNT(case ab.absencetypename when 'SAKIT' then 1 else null end) AS sakit,
                        COUNT(case ab.absencetypename when 'ALPHA' then 1 else null end) AS alpha,
                        COUNT(case ab.absencetypename when 'SAKIT DENGAN SURAT DOKTER' then 1 else null end) AS sdr
                        FROM absenceview ab
                        WHERE ab.createddatetime >= '$params->dateFrom 00:00:00' AND ab.createddatetime <= '$params->dateTo 23:59:59'
                        GROUP BY ab.employee) abs_count ON abs_count.employee=pres.employee
            WHERE pres.id is not null {$filter} {$where}
            ORDER BY pres.employeename
            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    /**
     * Get today presence by employee id
     *
     * @param string $employeeId
     * @return array
     */
    public static function getTodayPresence($employeeId)
    {
        $stmt = DB::select(
            "SELECT
                DATE_FORMAT( checkin, '%H:%i:%s' ) clock_in,
                DATE_FORMAT( checkout, '%H:%i:%s' ) clock_out,
                TIMEDIFF( checkout, checkin ) work_hours
            FROM
                presence
            WHERE
                employee = :employeeId
                AND DATE(checkin) = CURDATE()",
            [
                'employeeId' => $employeeId
            ]
        );
        return count($stmt) > 0 ? $stmt[0] : null;
    }

    /**
     * Get last presence by employee id
     *
     * @param string $employeeId
     * @return array
     */
    public static function getLastPresence($employeeId)
    {
        $stmt = DB::select(
            "SELECT
                DATE_FORMAT( checkin, '%H:%i:%s' ) clock_in,
                DATE_FORMAT( checkout, '%H:%i:%s' ) clock_out,
                TIMEDIFF( checkout, checkin ) work_hours
            FROM
                presence
            WHERE
                employee = :employeeId
            ORDER BY
                createddatetime desc
            LIMIT 1",
            [
                'employeeId' => $employeeId
            ]
        );
        return count($stmt) > 0 ? $stmt[0] : null;
    }
}
