<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDOException;

class Cuti extends Model
{
    protected $table = "leaves";
    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%" . $params->src . "%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM leaveview
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
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%" . $params->src . "%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM leaveview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    /**
     * Get list of cuti with certain businessUnitIds. (filtered by array of securityRoles)
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

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%" . $params->src . "%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " OR (nik like '%" . $params->src . "%')";
        }

        if (isset($params->status) && ($params->status != "")) {
            $filter = $filter . " AND (state = '$params->status')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM leaveview
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
     * Get count of cuti with certain businessUnitIds. (filtered by array of securityRoles)
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

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%" . $params->src . "%')";
        }

        if (isset($params->status) && ($params->status != "")) {
            $filter = $filter . " AND (state = '$params->status')";
        }

        $stmt = DB::select(
            "SELECT count(id) total
            FROM leaveview
            WHERE id is not null {$filter} {$where}"
        );
        return $stmt[0]->total;
    }

    public static function getCutiType()
    {
        $data = DB::table('leavetype')
            ->get();
        return $data;
    }

    public static function getCutiByParams($params)
    {
        $outstanding = DB::table('leaveview')
            ->where($params)
            ->get();
        return $outstanding;
    }

    public static function getOutstandingByEmployee($params)
    {
        $input = json_decode($params);
        $stmt = DB::select("SELECT * FROM leaveview WHERE employee = :employee and `state` not in (9,99)  ORDER BY createddatetime desc LIMIT 1", ['employee' => $input->employee]);
        return $stmt;
    }


    public static function insertCuti($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO leaves
                    (id, employee, fromdate, qty, description, leavetype, state, createdby)
                 VALUES
                    (:id, :employee, :fromdate, :qty, :description, :leavetype, :state, :createdby)",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "fromdate" => $input->fromdate,
                    "qty" => $input->qty,
                    "description" => $input->description,
                    "leavetype" => $input->leavetype,
                    "state" => $input->state,
                    "createdby" => $input->createdby
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "",
                "code" => 200
            );
        }
        catch(PDOException $e){
            $status = array(
                "error" => true,
                "message" =>  $e->getMessage(),
                "code" => 405
            );
        }
        return json_encode($status);
    }


    public static function editCutiByID($params) {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE leaves SET
                    employee = :employee,
                    fromdate = :fromdate,
                    qty = :qty,
                    description = :description,
                    leavetype = :leavetype,
                    state = :state,
                    createdby = :createdby

                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "fromdate" => $input->fromdate,
                    "qty" => $input->qty,
                    "description" => $input->description,
                    "leavetype" => $input->leavetype,
                    "state" => $input->state,
                    "createdby" => $input->createdby
                ]
            );
			$status = array(
				"error" => false,
				"message" =>  "",
				"code" => 200
			);
		}
		catch(PDOException $e){
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
            DB::delete("DELETE FROM leaves WHERE id = :id", ['id' => $input->id]);
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


    public static function approveCuti($params)
    {
        // return json_encode($params);
        try {
            DB::table('leaves')
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

    public static function cancelCuti($params)
    {
        // return json_encode($params);
        try {
            DB::table('leaves')
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

    /**
     * Get all cuti by employeeId and filter by employee's annual join date. Also accept filter by :
     * args: {src} leavename and description like %...%
     *
     * @param string $employeeId
     * @param string $args
     * @param int $offset
     * @param int $rec
     * @return array
     */
    public static function getListByEmployeeId($employeeId, $args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND ((leavename like '%" . $params->src . "%') OR (description like '%" . $params->src . "%'))";
        }

        // add filter by employee's annual join date
        if (isset($params->joinDate) && ($params->joinDate != "")) {
            $joinBirthdayTimestamp = strtotime(date('Y/m/d', strtotime(date('Y') . date('/m/d', strtotime($params->joinDate)))));
            $startDate = date("Y/m/d", strtotime("last year", $joinBirthdayTimestamp));
            $endDate = date("Y/m/d", $joinBirthdayTimestamp);

            // if join birthday has passed or is happening
            if (date('Y/m/d') >= date('Y/m/d', $joinBirthdayTimestamp)) {
                $startDate = date("Y/m/d", $joinBirthdayTimestamp);
                $endDate = date("Y/m/d", strtotime("next year", $joinBirthdayTimestamp));
            }

            $filter = $filter . " AND (createddatetime >= '$startDate')";
            $filter = $filter . " AND (createddatetime < '$endDate')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM leaveview
            WHERE id is not null AND employee = :employeeId {$filter} {$where}
            ORDER BY createddatetime desc
            LIMIT :offset, :rec",
            [
                'employeeId' =>$employeeId,
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    /**
     * Get count all cuti by employeeId and filter by annual employee's join date. Also accept filter by :
     * args: {src} leavename and description like %...%
     *
     * @param string $employeeId
     * @param string $args
     * @return int
     */
    public static function getCountByEmployeeId($employeeId, $args)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND ((leavename like '%" . $params->src . "%') OR (description like '%" . $params->src . "%'))";
        }

        // add filter by employee's annual join date
        if (isset($params->joinDate) && ($params->joinDate != "")) {
            $joinBirthdayTimestamp = strtotime(date('Y/m/d', strtotime(date('Y') . date('/m/d', strtotime($params->joinDate)))));
            $startDate = date("Y/m/d", strtotime("last year", $joinBirthdayTimestamp));
            $endDate = date("Y/m/d", $joinBirthdayTimestamp);

            // if join birthday has passed or is happening
            if (date('Y/m/d') >= date('Y/m/d', $joinBirthdayTimestamp)) {
                $startDate = date("Y/m/d", $joinBirthdayTimestamp);
                $endDate = date("Y/m/d", strtotime("next year", $joinBirthdayTimestamp));
            }

            $filter = $filter . " AND (createddatetime >= '$startDate')";
            $filter = $filter . " AND (createddatetime < '$endDate')";
        }

        $stmt = DB::select(
            "SELECT count(id) total
            FROM leaveview
            WHERE id is not null AND employee = :employeeId {$filter} {$where}
            ORDER BY createddatetime desc",
            [
                'employeeId' => $employeeId
            ]
        );
        return $stmt[0]->total;
    }
}
