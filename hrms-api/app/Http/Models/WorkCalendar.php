<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class WorkCalendar extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->year) && ($params->year != "")) {
            $filter=$filter." AND (year(leaveday) ='.$params->year.')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (description like '%".$params->src."%')";
        }

        // Editable SQL Fields
        else {$field = "*";}

        $stmt = DB::select("SELECT *
            FROM workcalendar
            WHERE id is not null {$filter} {$where}
            ORDER BY leaveday

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
        if (isset($params->year) && ($params->year != "")) {
            $filter=$filter." AND (year(leaveday) ='.$params->year.')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (description like '%".$params->src."%')";
        }


        $stmt = DB::select("SELECT count(id) cnt
            FROM workcalendar
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }



    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO workcalendar
                    (id, leaveday, description)
                 VALUES
                    (:id, :leaveday, :description)
                ",
            [
                "id" => $input->id,
                "leaveday" => $input->leaveday,
                "description" => $input->description
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
                "UPDATE workcalendar SET
                    leaveday = :leaveday,
                    description = :description
                 WHERE id = :id
                ",
            [
                "id" => $input->id,
                "leaveday" => $input->leaveday,
                "description" => $input->description

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
            DB::delete("DELETE FROM workcalendar WHERE id = :id",['id' => $input->id]);
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
