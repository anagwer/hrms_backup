<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Disciplinary extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (empname like '%".$params->src."%')";
        }

        if (isset($params->src) && ($params->src != "")) {
            $where=$where." OR (nik like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT *
            FROM disciplinaryview
            WHERE id is not null {$filter} {$where}
            ORDER BY employee
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
            $where=$where. " AND (empname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM disciplinaryview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getDisciplinaryByParams($params)
    {
        $outstanding = DB::table('disciplinaryview')
            ->where($params)
            ->get();
        return $outstanding;
    }

    public static function getDisciplinaryType()
    {
        $data = DB::table('disciplinarytype')
            ->get();
        return $data;
    }

    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO disciplinary
                    (id, employee, disciplinarytype, disciplinarydetail, dateinit, action, dateaction, disciplinaryinitiator, dateend, createdby)
                    VALUES
                    (:id, :employee, :disciplinarytype, :disciplinarydetail, :dateinit, :action, :dateaction, :disciplinaryinitiator, :dateend, :createdby)",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "disciplinarytype" => $input->disciplinarytype,
                    "disciplinarydetail" => $input->disciplinarydetail,
                    "dateinit" => $input->dateinit,
                    "disciplinaryinitiator" => $input->disciplinaryinitiator,
                    "action" => $input->action,
                    "dateaction" => $input->dateaction,
                    "dateend" => $input->dateend,
                    "createdby" => $input->createdby
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
                "UPDATE disciplinary SET
                id = :id,
                employee = :employee,
                disciplinarytype = :disciplinarytype,
                disciplinarydetail = :disciplinarydetail,
                dateinit = :dateinit,
                action = :action,
                disciplinaryinitiator = :disciplinaryinitiator,
                dateaction = :dateaction,
                dateend = :dateend,
                createdby = :createdby
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "disciplinarytype" => $input->disciplinarytype,
                    "disciplinarydetail" => $input->disciplinarydetail,
                    "dateinit" => $input->dateinit,
                    "disciplinaryinitiator" => $input->disciplinaryinitiator,
                    "action" => $input->action,
                    "dateaction" => $input->dateaction,
                    "dateend" => $input->dateend,
                    "createdby" => $input->createdby
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
                DB::delete("DELETE FROM disciplinary WHERE id = :id",['id' => $input->id]);
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

        public static function approveDisciplinary($params)
        {
            // return json_encode($params);
            try {
                DB::table('disciplinary')
                    ->where('id', $params['id'])
                    // ->where('approvalby', $params['approvalby'])
                    // ->where('state', '=', $params['state'])
                    ->update([
                        'state' => $params['state'],
                        'approvalby' => $params['approvalby'],
                        'approvaldatetime' => date('Y-m-d')
                    ]);
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
            return ($status);
        }
}
