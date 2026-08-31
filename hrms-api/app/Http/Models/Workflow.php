<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Workflow extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (features like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT *
            FROM workflowview
            WHERE id is not null {$filter} {$where}
            ORDER BY features
            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    public static function getCount($args) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (features like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM workflowview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }



    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO workflow
                    (id, features, approval1, approval2, approval3, approval4, approval5, department)
                 VALUES
                    (:id, :features, :approval1, :approval2, :approval3, :approval4, :approval5, :department)",
                [
                    "id" => $input->id,
                    "features" => $input->features,
                    "approval1" => $input->approval1,
                    "approval2" => $input->approval2,
                    "approval3" => $input->approval3,
                    "approval4" => $input->approval4,
                    "approval5" => $input->approval5,
                    "department" => $input->department
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



    public static function updateEx($params) {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE workflow SET
                    features = :features,
                    approval1 = :approval1,
                    approval2 = :approval2,
                    approval3 = :approval3,
                    approval4 = :approval4,
                    approval5 = :approval5,
                    approval1 = :department
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "features" => $input->features,
                    "approval1" => $input->approval1,
                    "approval2" => $input->approval2,
                    "approval3" => $input->approval3,
                    "approval4" => $input->approval4,
                    "approval5" => $input->approval5,
                    "department" => $input->department
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
            DB::delete("DELETE FROM workflow WHERE id = :id",['id' => $input->id]);
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
