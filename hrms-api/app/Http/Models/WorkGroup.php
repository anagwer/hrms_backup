<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class WorkGroup extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter=$filter." AND (businessunitid = '.$params->businessunit.')";
        }

        if (isset($params->plant) && ($params->plant != "")) {
            $filter=$filter." AND (plantid = '.$params->plant.')";
        }

        if (isset($params->pool) && ($params->pool != "")) {
            $filter=$filter." AND (poolid = '.$params->pool.')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }

        // Editable SQL Fields
        else {$field = "*";}

        $stmt = DB::select("SELECT *
            FROM workgroupview
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
        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter=$filter." AND (businessunitid = '.$params->businessunit.')";
        }

        if (isset($params->plant) && ($params->plant != "")) {
            $filter=$filter." AND (plantid = '.$params->plant.')";
        }

        if (isset($params->pool) && ($params->pool != "")) {
            $filter=$filter." AND (poolid = '.$params->pool.')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM workgroupview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }



    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO workgroup
                    (id, strname, businessunit, plant, pool)
                 VALUES
                    (:id, :strname, :businessunit, :plant, :pool)
                ",
            [
                "id" => $input->id,
                "strname" => $input->strname,
                "businessunit" => $input->businessunit,
                "plant" => $input->plant,
                "pool" => $input->pool
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
                "UPDATE workgroup SET
                    strname = :strname,
                    businessunit = :businessunit,
                    plant = :plant,
                    pool = :pool
                 WHERE id = :id
                ",
            [
                "id" => $input->id,
                "strname" => $input->strname,
                "businessunit" => $input->businessunit,
                "plant" => $input->plant,
                "pool" => $input->pool
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
            DB::delete("DELETE FROM workgroup WHERE id = :id",['id' => $input->id]);
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

    public static function getLines($args, $offset, $rec){
        $params = json_decode($args);
        $filter = ""; $where = "";

        if (isset($params->workgroup) && ($params->workgroup != "")) {
            $filter=$filter." AND (workgroup = '.$params->workgroup.')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT *
            FROM workgrouplineview
            WHERE id is not null {$filter} {$where}
            ORDER BY nik

            LIMIT :offset, :rec",
        [
            'offset' => $offset,
            'rec' => $rec
        ]);
        return $stmt;
    }

    public static function getLinesCount($args){
        $params = json_decode($args);
        $filter = ""; $where = "";

        if (isset($params->workgroup) && ($params->workgroup != "")) {
            $filter=$filter." AND (workgroup = '.$params->workgroup.')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }


        $stmt = DB::select("SELECT count(id) cnt
            FROM workgrouplineview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function createLineEx($params){
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO workgroupline
                    (id, workgroup, employee)
                 VALUES
                    (:id, :workgroup, :employee)
                ",
            [
                "id" => $input->id,
                "workgroup" => $input->workgroup,
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

    public static function updateLines($params){
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE workgroupline SET
                    workgroup = :workgroup,
                    employee = :employee
                 WHERE id = :id
                ",
            [
                "id" => $input->id,
                "workgroup" => $input->workgroup,
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

    public static function deleteLineEx($params) {
        $input = json_decode($params);
        try {
            DB::delete("DELETE FROM workgroupline WHERE id = :id",['id' => $input->id]);
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
