<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class EmployeeFamily extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (employeename like '%".$params->src."%')";
        }

        if (isset($params->employeeid) && ($params->employeeid != "")) {
            $where=$where." AND (employeeid = '".$params->employeeid."')";
        }
        // Editable SQL Fields
        else {$field = "*";}

        $stmt = DB::select("SELECT *
            FROM employeefamilyview
            WHERE id is not null {$filter} {$where}
            ORDER BY employeename

            LIMIT :offset, :rec",
        [
            'offset' => $offset,
            'rec' => $rec
        ]);
        return $stmt;
    }

    public static function getCount($args) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        if (isset($params->employee) && ($params->employee != "")) {
            $where=$where." AND (employee = '".$params->employeeid."')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM employeefamilyview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getAccess($role){
        $stmt = DB::select("SELECT *
            FROM securityroleview
            WHERE id is not null and role = :role",
            [
                'role' => $role
            ]);
        return $stmt;
    }

    public static function getEmployeeFamily($role){
        $stmt = DB::select("SELECT *
            FROM employeefamilyview
            WHERE id is not null and role = :role",
            [
                'role' => $role
            ]);
        return $stmt;
    }

    public static function getEmployeeFamilyByRoleFeature($role, $feature){
        $stmt = DB::select("SELECT canaccess, cancreate, canedit, candelete, canaprove, cancreatereport
            FROM securityroleview
            WHERE id is not null and role = :role and feature = :feature",
            [
                'role' => $role,
                'feature' => $feature
            ]);
        return $stmt;
    }

    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO employeefamily
                    (id, familyname, status, gender, datebirth, employee)
                 VALUES
                    (:id, :familyname, :status, :gender, :datebirth, :employee)
                ",
            [
                "id" => $input->id,
                "familyname" => $input->familyname,
                "status" => $input->status,
                "gender" => $input->gender,
                "datebirth" => $input->datebirth,
                "employee" => $input->employee
            ]);
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

    public static function updateEx($params) {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE employeefamily SET
                id = :id,
                employee = :employee,
                familyname = :familyname,
                status = :status,
                gender = :gender,
                datebirth = :datebirth
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "familyname" => $input->familyname,
                    "status" => $input->status,
                    "gender" => $input->gender,
                    "datebirth" => $input->datebirth
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


    public static function deleteEx($params) {
        $input = json_decode($params);
        try {
            DB::delete("DELETE FROM employeefamily WHERE id = :id",['id' => $input->id]);
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

}
