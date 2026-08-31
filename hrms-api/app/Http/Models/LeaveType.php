<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class LeaveType extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

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
            FROM leavetype
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
            FROM leavetype
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }



    // public static function createEx($params) {
    //     $input = json_decode($params);
    //     try {
    //         DB::insert(
    //             "INSERT INTO job
    //                 (id, strname)
    //              VALUES
    //                 (:id, :strname)
    //             ",
    //         [
    //             "id" => $input->id,
    //             "strname" => $input->strname
    //         ]);
    //         $status = array(
    //             "error" => false,
    //             "message" =>  "",
    //             "code" => 200
    //         );
    //     }
    //     catch(PDOException $e){
    //         $status = array(
    //             "error" => true,
    //             "message" =>  $e->getMessage(),
    //             "code" => 405
    //         );
    //     }
    //     return json_encode($status);
    // }



    // public static function updateEx($params) {
    //     $input = json_decode($params);
    //     try {
    //         DB::update(
    //             "UPDATE job SET
    //                 strname = :strname
    //              WHERE id = :id
    //             ",
    //         [
    //             "id" => $input->id,
    //             "strname" => $input->strname
    //         ]);
	// 		$status = array(
	// 			"error" => false,
	// 			"message" =>  "",
	// 			"code" => 200
	// 		);
	// 	}
	// 	catch(PDOException $e){
	// 		$status = array(
	// 			"error" => true,
	// 			"message" =>  $e->getMessage(),
	// 			"code" => 405
	// 		);
	// 	}
    //     return json_encode($status);
	// }

    // public static function deleteEx($params) {
    //     $input = json_decode($params);
    //     try {
    //         DB::delete("DELETE FROM job WHERE id = :id",['id' => $input->id]);
    //         $status = array(
    //             "error" => false,
    //             "message" =>  "",
    //             "code" => 200
    //         );
    //     }
    //     catch(PDOException $e){
    //         $status = array(
    //             "error" => true,
    //             "message" =>  $e->getMessage(),
    //             "code" => 405
    //         );
    //     }
    //     return json_encode($status);
    // }



}
