<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class RoleOrganization extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (roleName like '%".$params->src."%')";
        }

        // Editable SQL Fields
        else {$field = "*";}

        $stmt = DB::select("SELECT *
            FROM roleorganizationview
            WHERE id is not null {$filter} {$where}
            ORDER BY roleName

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

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (roleName like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM roleorganizationview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getAccess($role){
        $stmt = DB::select("SELECT *
            FROM roleorganizationview
            WHERE id is not null and role = :role",
            [
                'role' => $role
            ]);
        return $stmt;
    }

    /**
     * Get role organization by its id.
     *
     * @param string $id
     * @return object
     */
    public static function getRoleOrganizationById($id){
        $stmt = DB::select("SELECT *
            FROM roleorganizationview
            WHERE id = :id",
            [
                'id' => $id
            ]);

        return count($stmt) > 0 ? $stmt[0] : $stmt;
    }

    public static function getRoleOrganizationByRoleFeature($role, $feature){
        $stmt = DB::select("SELECT canaccess, cancreate, canedit, candelete, canaprove, cancreatereport
            FROM roleorganizationview
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
                "INSERT INTO roleorganization
                    (id, idRole, idDept)
                 VALUES
                    (:id, :idRole, :idDept)
                ",
            [
                "id" => $input->id,
                "idRole" => $input->idRole,
                "idDept" => $input->idDept
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
                "UPDATE roleorganization SET
                    idRole = :idRole,
                    idDept = :idDept
                 WHERE id = :id
                ",
            [
                "id" => $input->id,
                "idRole" => $input->idRole,
                "idDept" => $input->idDept
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


    public static function deleteEx($params) {
        $input = json_decode($params);
        try {
            DB::delete("DELETE FROM roleorganization WHERE id = :id",['id' => $input->id]);
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
