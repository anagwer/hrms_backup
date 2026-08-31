<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Absence extends Model implements AuthenticatableContract, AuthorizableContract
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

        if (isset($params->date) && ($params->date != "")) {
            $filter = $filter . " AND (date_format(transdate, '%Y-%m-%d')  = '$params->date')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM absenceview
            WHERE id is not null {$filter} {$where}
            ORDER BY createddatetime desc -- bisa menggunakan nik

            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    public static function getListByEmployee($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";

        // Editable SQL WHERE
        if (isset($params->employee) && ($params->employee != "")) {
            $filter = $filter . " AND (employee  = '$params->employee')";
        }

        //This feature is not used now, it's request from Mr. Pam to delete data filtering per period

        // if (isset($params->dateFilter) && ($params->dateFilter != "")) {
        //     $startDate = date("Y/m/d", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
        //     $endDate = date("Y/m/d", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));

        //     // check if requested day is > 20th
        //     if (date('d', strtotime($params->dateFilter)) > 20) {
        //         $startDate = date("Y/m/d", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
        //         $endDate = date("Y/m/d", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
        //     }

        //     $filter = $filter . " AND (createddatetime BETWEEN '$startDate' AND '$endDate')";
        // }

        $stmt = DB::select(
            "SELECT *
            FROM absenceview
            WHERE id is not null {$filter} 
            ORDER BY createddatetime desc

            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
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

        if (isset($params->date) && ($params->date != "")) {
            $filter = $filter . " AND (date_format(transdate, '%Y-%m-%d')  = '$params->date')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%$params->src%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM absenceview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getCountByEmployee($args)
    {
        $params = json_decode($args);
        $where = "";

        if (isset($params->employee) && ($params->employee != "")) {
            $where = $where . " AND (employee  = '$params->employee')";
        }

        $stmt = DB::select("SELECT count(id) total
            FROM absenceview
            WHERE id is not null {$where}");
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

        if (isset($params->date) && ($params->date != "")) {
            $filter = $filter . " AND (date_format(transdate, '%Y-%m-%d')  = '$params->date')";
        }

        if (isset($params->nik) && ($params->nik != "")) {
            $filter = $filter . " AND (nik = '$params->nik')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%$params->src%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM absenceview
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

    public static function getAbsenceByParams($params)
    {
        $outstanding = DB::table('absenceview')
            ->where($params)
            ->get();
        return $outstanding;
    }

    /**
     * Get count of absence with certain businessUnitIds. (filtered by array of securityRoles)
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

        if (isset($params->date) && ($params->date != "")) {
            $filter = $filter . " AND (date_format(transdate, '%Y-%m-%d')  = '$params->date')";
        }

        if (isset($params->nik) && ($params->nik != "")) {
            $filter = $filter . " AND (nik = '$params->nik')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%$params->src%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%$params->src%')";
        }

        $stmt = DB::select(
            "SELECT count(nik) total
            FROM absenceview
            WHERE id is not null {$filter} {$where}"
        );
        return $stmt[0]->total;
    }

    public static function checkInEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT into presence (id, employee, devicetype, deviceid, checkin, checkinby, checkinlong, checkinlat, checkstate)
                VALUES(:id, :employee, :devicetype, :deviceid, :checkin, checkinby, :checkinlong, :checkinlat, :checkstate)",
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
                where employee = :employee and chekcstate = 1",
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

    public static function manualAbsence($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT into absence (id, employee, transdate, absencetype, remarks, qty, attachment, state, createdby)
                VALUES(:id, :employee, :transdate, :absencetype, :remarks, :qty, :attachment, :state, :createdby )",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "transdate" => $input->transdate,
                    "absencetype" => $input->absencetype,
                    "remarks" => $input->remarks,
                    "attachment" => $input->attachment,
                    "qty" => $input->qty,
                    "state" => $input->state,
                    "createdby" => $input->createdby
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "Pengajuan izin berhasil",
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

    public static function postAbsence($params)
    {
        // return json_encode($params);
        try {
            DB::table('absence')
                ->where('id', $params['id'])
                // ->where('approvalby', $params['approvalby'])
                // ->where('state', '=', $params['state'])
                ->update([
                    'state' => $params['state'],
                    'approvalby' => $params['approvalby'],
                    'approvaldatetime' => date('Y-m-d')
                ]);
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
        return ($status);
    }

    public static function updateEx($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE absence SET
                    employee = :employee,
                    transdate = :transdate,
                    absencetype = :absencetype,
                    remarks = :remarks,
                    attachment = :attachment,
                    state = :state,
                    createdby = :createdby,
                    createddatetime = :createddatetime

                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "transdate" => $input->transdate,
                    "absencetype" => $input->absencetype,
                    "remarks" => $input->remarks,
                    "attachment" => $input->attachment,
                    "state" => $input->state,
                    "createdby" => $input->createdby,
                    "createddatetime" => $input->createddatetime // datetime waktu absen masuk

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
            DB::delete("DELETE FROM absence WHERE id = :id", ['id' => $input->id]);
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

    /**
     * Get all absence accumulation or filter it by month.
     *
     * @param string $employeeId
     * @param string $args
     * @return object
     */
    public static function getAccumulationByEmployeeId($employeeId, $args)
    {
        $params = json_decode($args);
        $filter = "";

        // add filter by month and year
        if (isset($params->dateFilter) && ($params->dateFilter != "")) {
            $startDate = date("Y/m/d", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
            $endDate = date("Y/m/d", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));

            // check if requested day is > 20th
            if (date('d', strtotime($params->dateFilter)) > 20) {
                $startDate = date("Y/m/d", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
                $endDate = date("Y/m/d", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
            }

            $filter = $filter . " AND (createddatetime BETWEEN '$startDate' AND '$endDate')";
        }

        // query only if sikel state is 9 (CLOSED).  Absence state is not implemented yet!
        // $filter = $filter . " AND state = '9'";

        $stmt = DB::select(
            "SELECT
                CONCAT(SUM(IF(DAYNAME(transdate) = 'Saturday', 5, 8)), ' jam') as accumulation, count(id) as accumulation_recap
            FROM absenceview
            WHERE id is not null AND employee = :employeeId {$filter}",
            [
                'employeeId' => $employeeId
            ]
        );

        return $stmt[0];
    }

    /**
     * Get list of absence with certain businessUnitIds. (filtered by array of securityRoles)
     *
     * @param string $args
     * @param int $offset
     * @param int $rec
     * @return array
     */
    public static function getAbsenceRecap($args, $offset, $rec)
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
            private_permit.private_permit_duration, abs_count.izin, abs_count.sakit, abs_count.alpha, abs_count.sdr
            FROM presenceview pres 
            LEFT JOIN employment emp ON pres.employee=emp.id
            LEFT JOIN (SELECT s.employee, TIME(SUM( TIMEDIFF(s.securitycheckindatetime, s.securitycheckoutdatetime))) private_permit_duration
                        FROM sikel s WHERE s.sikeltype=2 AND (s.createddatetime >= '$params->dateFrom 00:00:00' AND s.createddatetime <= '$params->dateTo 23:59:59')
                        GROUP BY employee) private_permit ON private_permit.employee=pres.employee
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
}
