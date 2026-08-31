<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

use App\Http\Models\Presence;

class Sikel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter = $filter . " AND (businessunit  = '$params->businessunit')";
        }

        if (isset($params->division) && ($params->division != "")) {
            $filter = $filter . " AND (division  = '$params->division')";
        }

        if (isset($params->department) && ($params->department != "")) {
            $filter = $filter . " AND (department  = '$params->department')";
        }

        // if (isset($params->src) && ($params->src != "")) {
        //     $where = $where . " AND (docnumber like '%" . $params->src . "%')";
        // }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " or (nik like '%" . $params->src . "%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM sikelview
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

    public static function getCount($args)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter = $filter . " AND (businessunit  = '$params->businessunit')";
        }

        if (isset($params->division) && ($params->division != "")) {
            $filter = $filter . " AND (division  = '$params->division')";
        }

        if (isset($params->department) && ($params->department != "")) {
            $filter = $filter . " AND (department  = '$params->department')";
        }

        // if (isset($params->src) && ($params->src != "")) {
        //     $where = $where . " AND (docnumber like '%" . $params->docnumber . "%')";
        // }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%" . $params->src . "%')";
        }


        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (nik like '%" . $params->src . "%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM sikelview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    /**
     * Get list of sikel with certain businessUnitIds. (filtered by array of securityRoles)
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
            $filter = $filter . " AND (createddatetime >= '$params->checkinFrom 00:00:00'  AND createddatetime <= '$params->checkinTo 23:59:59' )";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " or (nik like '%" . $params->src . "%')";
        }

        if (isset($params->status) && ($params->status != "")) {
            $filter = $filter . " AND (state = '$params->status')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM sikelview
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
     * Get count Get list of sikel with certain businessUnitIds. (filtered by array of securityRoles)
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
            $filter = $filter . " AND (createddatetime >= '$params->checkinFrom 00:00:00'  AND createddatetime <= '$params->checkinTo 23:59:59' )";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (employeename like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (nik like '%" . $params->src . "%')";
        }

        if (isset($params->status) && ($params->status != "")) {
            $filter = $filter . " AND (state = '$params->status')";
        }

        $stmt = DB::select(
            "SELECT count(id) total
            FROM sikelview
            WHERE id is not null {$filter} {$where}"
        );

        return $stmt[0]->total;
    }

    public static function getSikelByDocNumber($params)
    {
        $input = json_decode($params);
        $stmt = DB::select("SELECT * from sikelview where docnumber = :docnumber", ['docnumber' => $input->docnumber]);
        return $stmt;
    }

    public static function getSikelById($params)
    {
        $input = json_decode($params);
        $stmt = DB::select("SELECT * FROM sikel WHERE id = :id", ['id' => $input->id]);
        return $stmt;
    }

    //mendapatkan data dari number
    public static function getSikelByNumber($params)
    {
        $input = json_decode($params);
        $stmt = DB::select("SELECT * FROM sikelview WHERE docnumber = :docnumber", ['docnumber' => $input->docnumber]);
        return $stmt;
    }

    //get outstanding sikel
    public static function getOutstandingByEmployee($params)
    {
        $input = json_decode($params);
        $stmt = DB::select("SELECT * FROM sikelview WHERE employee = :employee and `state` not in (9,99)  ORDER BY createddatetime desc LIMIT 1", ['employee' => $input->employee]);
        return $stmt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO sikel
                    (id, docnumber, employee, sikeltype, description, businessunit, department, createdby)
                 VALUES
                    (:id, :docnumber, :employee, :sikeltype, :description, :businessunit, :department, :createdby
                    )",
                [
                    "id" =>  $input->id,
                    "docnumber" =>  $input->docnumber,
                    "employee" =>  $input->employee,
                    "sikeltype" =>  $input->sikeltype,
                    "description" =>  $input->description,
                    "businessunit" =>  $input->businessunit,
                    "department" =>  $input->department,
                    "createdby" =>  $input->createdby
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
                "UPDATE sikel SET
                    employee = :employee,
                    sikeltype = :sikeltype,
                    description = :description,
                    businessunit = :businessunit,
                    department = :department,
                    createdby = :createdby
                 WHERE id = :id",
                [
                    "id" =>  $input->id,
                    "employee" =>  $input->employee,
                    "sikeltype" =>  $input->sikeltype,
                    "description" =>  $input->description,
                    "businessunit" =>  $input->businessunit,
                    "department" =>  $input->department,
                    "createdby" =>  $input->createdby
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
            DB::delete("DELETE FROM sikel WHERE id = :id", ['id' => $input->id]);
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

    public static function approveSikel($update, $employee, $docnumber, $type)
    {
        DB::beginTransaction();
        try {
            DB::table('sikel')->where('docnumber', $docnumber)->update($update);

            if ($type == 3) {
                DB::table('presence')
                    ->where('employee', $employee['employee_id'])
                    ->where('checkstate', '=', 1)
                    ->update($employee['data']);
            } else if ($type == 4) {
                DB::table('presence')
                    ->where('employee', $employee['employee_id'])
                    ->where('reff', '=', $docnumber)
                    ->update($employee['data']);
            }

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
            DB::commit();
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
            DB::rollBack();
        }

        return json_encode($status);
    }

    public static function approvalEx($params)
    {
        $input = json_decode($params);
        DB::beginTransaction();
        try {
            DB::update(
                "UPDATE sikel SET
                    state = :state,
                    approver = :approver,
                    approvedatetime = :approvedatetime,
                    hrcheckdatetime = :hrcheckdatetime,
                    hrcheckby       = :hrcheckby,
                    securitycheckoutdatetime = :securitycheckoutdatetime,
                    securitycheckoutby = :securitycheckoutby,
                    longout = :longitudeout,
                    latout = :latitudeout,
                    securitycheckindatetime = :securitycheckindatetime,
                    securitycheckinby = :securitycheckinby,
                    longin = :longitudein,
                    latin = :latitudein
                WHERE docnumber = :docnumber",
                [
                    "docnumber" => $input->docnumber,
                    "state" =>  $input->status,
                    "approver" => $input->approver,
                    "approvedatetime" => $input->approvedatetime,
                    "hrcheckdatetime" => $input->hrcheckdatetime,
                    "hrcheckby" => $input->hrcheckby,
                    "securitycheckoutdatetme" => $input->securitycheckdatetime,
                    "securitycheckoutby" => $input->securitycheckoutby,
                    "longitudeout" => $input->longout,
                    "latitudeout" => $input->latout,
                    "securitycheckindatetime" => $input->securitycheckindatetime,
                    "securitycheckinby" => $input->securitycheckinby,
                    "longitudein" => $input->longin,
                    "latitudein" => $input->latin
                ]
            );
            // update attendance: menyatakan employee keluar saat check out di satpam
            if ($input->type == 3) {
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
                        "checkstate" => 3, // 1: masuk, 2: istirahat keluar,  3: sikel keluar, 9 pulang
                        "reff" => $input->reff,
                        "remarks" => $input->remarks
                    ]
                );
            }
            // end of update attendance: menyatakan employee keluar saat check out di satpam

            // update attendace saat kembali dari sikel, update checstate = 1
            if ($input->type == 3) {
                DB::update(
                    "UPDATE presence set
                    checkout = :checkout,
                    checkoutby = :checkoutby,
                    checkoutlong = :checkoutlong,
                    checkoutlat = :checkoutlat,
                    checkstate = :checkstate,
                    reff = :reff,
                    remarks = :remarks
                where employee = :employee and reff = :docnumber",
                    [
                        "employee" => $input->employee,
                        "docnumber" => $input->docnumber,
                        "checkout" => null,
                        "checkoutby" => null,
                        "checkoutlong" => null,
                        "checkoutlat" => null,
                        "checkstate" => 1, // 1: masuk, 2: istirahat keluar,  3: sikel keluar, 9 pulang
                        "reff" => null,
                        "remarks" => null
                    ]
                );
            }
            // end of update employee saat kembali dari sikel, update checstate = 1

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
            DB::commit();
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
            DB::rollBack();
        }
        return json_encode($status);
    }

    public static function approveDeptHeadEx($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE sikel SET
                    state = :state,
                    approver = :approver,
                    approvedatetime = :approvedatetime
                WHERE docnumber = :docnumber",
                [
                    "docnumber" => $input->docnumber,
                    "state" =>  $input->status,
                    "approver" => $input->approver,
                    "approvedatetime" => $input->approvedatetime
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

    public static function approveHREx($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE sikel SET
                    state = :state,
                    hrcheckdatetime = :hrcheckdatetime,
                    hrcheckby = :hrcheckby
                WHERE docnumber = :docnumber",
                [
                    "docnumber" =>  $input->docnumber,
                    "state" => 2,
                    "hrcheckdatetime" => $input->hrcheckdatetime,
                    "hrcheckby" => $input->hrcheckby
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

    public static function approveSecurityCheckOut($params)
    {
        $input = json_decode($params);

        DB::beginTransaction();
        try {
            DB::update(
                "UPDATE sikel SET
                    securitycheckout = 1,
                    securitycheckoutdatetime = :securitycheckoutdatetime,
                    securitycheckoutby = :securitycheckoutby,
                    longout = :longout,
                    latout = :latout
                WHERE id = :id",
                [
                    "id" => $input->id,
                    "securitycheckoutdatetime" => $input->securitycheckoutdatetime,
                    "securitycheckoutby" => $input->securitycheckoutby,
                    "longout" => $input->longout,
                    "latout" => $input->latout
                ]
            );

            $presenceParams = array(
                "employee" => $input->employee,
                "checkout" => $input->securitycheckoutdatetime,
                "checkoutby" => $input->employee,
                "checkoutlong" => $input->longout,
                "checkoutlat" => $input->latout,
                "checkstate" => 3,
                "reff" => $input->id,
                "remarks" => $input->docnumber
            );
            $jsonPresenceParams = json_encode($presenceParams);
            Presence::checkOutEx($jsonPresenceParams);

            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
            DB::commit();
        } catch (PDOException $e) {
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
            DB::rollback();
        }
        return json_encode($status);
    }

    public static function approveSecurityCheckIn($params)
    {
        $input = json_decode($params);
        $sikel = self::getSikelByDocNumber($input->docnumber);
        $state = $sikel[0]->securitycheckout;
        if ($state != 1) {
            $status = array(
                "error" => true,
                "message" =>  "SIKEL expired!",
                "code" => 405
            );
            return json_encode($status);
        }
        $id = $sikel[0]->id;
        try {
            DB::update(
                "UPDATE sikel SET
                    securitycheckin = 1,
                    securitycheckindatetime = :securitycheckindatetime,
                    securitycheckinby = :securitycheckinby,
                    longin = :longin,
                    latin = :latin
                WHERE id = :id",
                [
                    "id" =>  $id,
                    "securitycheckindatetime" => $input->securitycheckindatetime,
                    "securitycheckinby" => $input->securitycheckinby,
                    "longin" => $input->longin,
                    "latin" => $input->latin
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

    /**
     * Get all sikel by employeeId.
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

        //This feature is not used now, it's request from Pak Pam to delete data filtering per period

        // add filter by month and year
        // if (isset($params->dateFilter) && ($params->dateFilter != "")) {
        //     // check if requested day is > 20th
        //     if (date('d', strtotime($params->dateFilter)) > 20) {
        //         $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
        //         $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
        //     } else {
        //         $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
        //         $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
        //     }

        //     $filter = $filter . " AND (createddatetime BETWEEN '$startDate' AND '$endDate')";
        // }

        $stmt = DB::select(
            "SELECT *
            FROM sikelview
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
     * Get count all sikel by employeeId.
     *
     * @param string $employeeId
     * @return int
     */
    public static function getCountByEmployeeId($employeeId, $args)
    {
        $params = json_decode($args);
        $filter = "";

        //This feature is not used now, it's request from Mr. Pam to delete data filtering per period

        // add filter by month and year
        // if (isset($params->dateFilter) && ($params->dateFilter != "")) {
        //     // check if requested day is > 20th
        //     if (date('d', strtotime($params->dateFilter)) > 20) {
        //         $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
        //         $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
        //     } else {
        //         $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
        //         $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
        //     }

        //     $filter = $filter . " AND (createddatetime BETWEEN '$startDate' AND '$endDate')";
        // }

        $stmt = DB::select(
            "SELECT count(id) total
            FROM sikelview
            WHERE id is not null AND employee = :employeeId {$filter}
            ORDER BY createddatetime desc",
            [
                'employeeId' => $employeeId
            ]
        );
        return $stmt[0]->total;
    }

    /**
     * Get all sikel accumulation or filter it by month.
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
            // check if requested day is > 20th
            if (date('d', strtotime($params->dateFilter)) > 20) {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime("next month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
            } else {
                $startDate = date("Y/m/d 00:00:00", strtotime("+20 days", strtotime("last month", strtotime(date('Y-m-01', strtotime($params->dateFilter))))));
                $endDate = date("Y/m/d 23:59:59", strtotime("+19 days", strtotime(date('Y-m-01', strtotime($params->dateFilter)))));
            }

            $filter = $filter . " AND (createddatetime BETWEEN '$startDate' AND '$endDate')";
        }

        // query only if sikel state is 9 (CLOSED)
        $filter = $filter . " AND state = '9'";

        $stmt = DB::select(
            "SELECT
                COUNT(id) as total,
                IF(
                    COUNT(id) = 0,
                    '0 menit',
                    CONCAT(
                        CONCAT(FLOOR(SUM(TIMESTAMPDIFF(MINUTE, securitycheckoutdatetime, securitycheckindatetime)) / 60), ' jam '),
                        CONCAT(SUM(TIMESTAMPDIFF(MINUTE, securitycheckoutdatetime, securitycheckindatetime)) % 60, ' menit')
                    )
                ) as accumulation
            FROM sikelview
            WHERE id is not null AND employee = :employeeId {$filter}",
            [
                'employeeId' => $employeeId
            ]
        );

        return $stmt[0];
    }
}
