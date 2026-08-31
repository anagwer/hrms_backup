<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class SequenceNumber extends Model implements AuthenticatableContract, AuthorizableContract
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
            FROM sequencenumber
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
            FROM sequencenumber
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }


    public static function updateEx($params) {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE sequencenumber SET
                    prefix = :prefix,
                    digit = :digit,
                    lastnumber = :lastnumber
                 WHERE id = :id
                ",
            [
                "id" => $input->id,
                "prefix" => $input->prefix,
                "digit" => $input->digit,
                "lastnumber" => $input->lastnumber
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

    public static function generateNumberEx($params){
        $input = json_decode($params);
        $stmt = DB::select("select generatesequencenumber(:code) as docno",
        [
            'code' => $input->code
        ]);
        return $stmt;
    }

}
