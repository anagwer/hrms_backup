<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Role extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getRole($name) {
        $stmt = DB::select("SELECT * FROM roles WHERE name = :name",
        [
            'name' => $name
        ]);
        return $stmt;
    }

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }

        // Editable SQL Fields
        else {$field = "*";}

        $stmt = DB::select("SELECT *
            FROM role
            WHERE id is not null {$filter} {$where}
            ORDER BY strname

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

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM role
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

}
