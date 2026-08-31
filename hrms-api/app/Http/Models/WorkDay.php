<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class WorkDay extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        if (isset($params->id) && ($params->id != "")) {
            $where = $where . " AND (id = '$params->id')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM workdays
            WHERE id is not null {$filter} {$where}
            ORDER BY period desc
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

        if (isset($params->id) && ($params->id != "")) {
            $where = $where . " AND (id = '$params->id')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM workdays
            WHERE id is not null {$filter} {$where}");
        return $stmt[0]->cnt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO workdays
                    (id, period, from_date, to_date, work_day)
                 VALUES
                    (:id, :period, :from_date, :to_date, :work_day)",
                [
                    "id" => $input->id,
                    "period" => $input->period,
                    "from_date" => $input->from_date,
                    "to_date" => $input->to_date,
                    "work_day" => $input->work_day
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "Berhasil menambahkan hari kerja",
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
                "UPDATE workdays SET
                    period = :period,
                    from_date = :from_date,
                    to_date = :to_date,
                    work_day = :work_day
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "period" => $input->period,
                    "from_date" => $input->from_date,
                    "to_date" => $input->to_date,
                    "work_day" => $input->work_day
                ]
            );
            $status = array(
                "error" => false,
                "message" =>  "Hari kerja berhasil diubah",
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
            DB::delete("DELETE FROM workdays WHERE id = :id", ['id' => $input->id]);
            $status = array(
                "error" => false,
                "message" =>  "Hari kerja berhasil dihapus",
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
}
