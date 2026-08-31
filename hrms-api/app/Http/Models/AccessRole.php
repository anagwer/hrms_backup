<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class AccessRole extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (rolename like '%" . $params->src . "%')";
        }

        // Editable SQL Fields
        else {
            $field = "*";
        }

        $stmt = DB::select(
            "SELECT *
            FROM accessroleview
            WHERE id is not null {$filter} {$where}
            ORDER BY rolename

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

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (rolename like '%" . $params->src . "%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM accessroleview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getAccess($role)
    {
        $stmt = DB::select(
            "SELECT *
            FROM accessroleview
            WHERE id is not null and role = :role",
            [
                'role' => $role
            ]
        );
        return $stmt;
    }

    public static function getAccessRole($role)
    {
        $stmt = DB::select(
            "SELECT *
            FROM accessroleview
            WHERE id is not null and role = :role",
            [
                'role' => $role
            ]
        );
        return $stmt;
    }

    public static function getRoleOrganization($employeeId)
    {
        $stmt = DB::select(
            "SELECT roleOrganizationId, businessUnitId, plantId
            FROM securityrole
            WHERE id is not null and employeeId = :employeeId",
            [
                'employeeId' => $employeeId
            ]
        );
        return $stmt;
    }

    /**
     * Get Access Role (canaccess, cancreate, canedit, candelete, canaprove, cancreatereport, canshowsallary) by roleId and featureId.
     *
     * @param string $roleId
     * @param string $featureId
     * @return object
     */
    public static function getAccessRoleByRoleFeature($roleId, $featureId)
    {
        $stmt = DB::select(
            "SELECT canaccess, cancreate, canedit, candelete, canaprove, cancreatereport, canshowsallary, cancancel
            FROM accessroleview
            WHERE id is not null and role = :role and feature = :feature",
            [
                'role' => $roleId,
                'feature' => $featureId
            ]
        );
        return count($stmt) > 0 ? $stmt[0] : $stmt;
    }

    /**
     * Get canaccess and cancreate by roleId and featureId.
     *
     * @param string $roleId
     * @param string $featureId
     * @return object
     */
    public static function getCanAccess($roleId, $featureId)
    {
        $stmt = DB::select(
            "SELECT canaccess, cancreate, canaprove
            FROM accessroleview
            WHERE id is not null and role = :role and feature = :feature",
            [
                'role' => $roleId,
                'feature' => $featureId
            ]
        );
        return count($stmt) > 0 ? $stmt[0] : $stmt;
    }
}
