<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Feature extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getFeature($application) {
        $stmt = DB::select("SELECT id featureid, code, strname featurename
            FROM feature
            WHERE id is not null and application = '$application'
            ORDER BY strname");
        return $stmt;
    }

    public static function getFeatureByApplicationCode($application) {
        $stmt = DB::select("SELECT *
            FROM featureview
            WHERE id is not null and code = '$application'");
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
            FROM feature
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
            FROM feature
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    /**
     * Get feature by feature code.
     *
     * @param string $featureCode
     * @return object
     */
    public static function getFeatureByFeatureCode($featureCode) {
        $stmt = DB::select("SELECT *
            FROM featureview
            WHERE id is not null and featurecode = '$featureCode'");
        return $stmt[0];
    }
}
