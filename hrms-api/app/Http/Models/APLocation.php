<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class APLocation extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (strname like '%" . $params->src . "%')";
        }

        // Editable SQL Fields
        else {
            $field = "*";
        }

        $stmt = DB::select(
            "SELECT *
            FROM aplocation
            WHERE id is not null {$filter} {$where}
            ORDER BY strname

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
            $where = $where . " AND (strname like '%" . $params->src . "%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM aplocation
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO aplocation
                    (id, strname, longitude, latitude, radius, description)
                 VALUES
                    (:id, :strname, :longitude, :latitude, :radius, :description)
                ",
                [
                    "id" => $input->id,
                    "strname" => $input->strname,
                    "longitude" => $input->longitude,
                    "latitude" => $input->latitude,
                    "radius" => $input->radius,
                    "description" => $input->description
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
                "UPDATE aplocation SET
                    strname = :strname,
                    longitude = :longitude,
                    latitude = :latitude,
                    radius = :radius,
                    description = :description
                 WHERE id = :id
                ",
                [
                    "id" => $input->id,
                    "strname" => $input->strname,
                    "longitude" => $input->longitude,
                    "latitude" => $input->latitude,
                    "radius" => $input->radius,
                    "description" => $input->description
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
            DB::delete("DELETE FROM aplocation WHERE id = :id", ['id' => $input->id]);
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

    public static function getByID($id)
    {
        $stmt = DB::select(
            "SELECT *
            FROM aplocation
            WHERE id = '$id'"
        );
        return $stmt;
    }
}
