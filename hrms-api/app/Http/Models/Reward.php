<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Reward extends Model implements AuthenticatableContract, AuthorizableContract
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
            FROM rewardview
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
            FROM rewardview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getRewardByParams($params)
    {
        $outstanding = DB::table('rewardview')
            ->where($params)
            ->get();
        return $outstanding;
    }

    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO reward
                    (id, employee, rewardtype, rdetail, dateinit, action, dateaction, initiator)
                    VALUES
                    (:id, :employee, :rewardtype, :rdetail, :dateinit, :action, :dateaction, :initiator)",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "rewardtype" => $input->rewardtype,
                    "rdetail" => $input->rdetail,
                    "dateinit" => $input->dateinit,
                    "action" => $input->action,
                    "dateaction" => $input->dateaction,
                    "initiator" => $input->initiator
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
                "UPDATE reward SET
                id = :id,
                employee = :employee,
                rewardtype = :rewardtype,
                rdetail = :rdetail,
                dateinit = :dateinit,
                action = :action,
                dateaction = :dateaction,
                initiator = :initiator
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "rewardtype" => $input->rewardtype,
                    "rdetail" => $input->rdetail,
                    "dateinit" => $input->dateinit,
                    "action" => $input->action,
                    "dateaction" => $input->dateaction,
                    "initiator" => $input->initiator
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
                DB::delete("DELETE FROM reward WHERE id = :id",['id' => $input->id]);
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

        public static function approveReward($params)
        {
            // return json_encode($params);
            try {
                DB::table('reward')
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
