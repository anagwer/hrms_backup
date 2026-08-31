<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Mutation extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;


    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (empname like '%" . $params->src . "%')";
        }

            $stmt = DB::select("SELECT *
                FROM mutationview
                WHERE id is not null {$filter} {$where}
                ORDER BY employee
                LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    // public static function getList($args, $offset, $rec) {
    //     $params = json_decode($args);
    //     $filter = ""; $where = "";
    //
    //     // Editable SQL WHERE
    //     if (isset($params->src) && ($params->src != "")) {
    //         $where=$where." AND (empname like '%".$params->src."%')";
    //     }
    //
    //     $stmt = DB::select("SELECT *
    //         FROM mutationview
    //         WHERE id is not null {$filter} {$where}
    //         ORDER BY employee
    //         LIMIT :offset, :rec",
    //     [
    //         'offset' => $offset,
    //         'rec' => $rec
    //     ]);
    //     return $stmt;
    // }

    public static function getCount($args) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (empname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM mutationview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getMutationByParams($params)
    {
        $outstanding = DB::table('mutationview')
            ->where($params)
            ->get();
        return $outstanding;
    }



    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO mutation
                    (id, employee, frombusinessunit, frombranch, fromplant, frompool, fromdivision, fromdepartment, fromjob, fromgrade, fromrole,
                        tobusinessunit, tobranch, toplant, topool, todivision, todepartment, tojob, tograde, torole, activedate, mutationtype,description, state, approveby, approvedatetime, createdby)
                    VALUES
                    (:id, :employee, :frombusinessunit, :frombranch, :fromplant, :frompool, :fromdivision, :fromdepartment, :fromjob, :fromgrade, :fromrole,
                        :tobusinessunit, :tobranch, :toplant, :topool, :todivision, :todepartment, :tojob, :tograde, :torole, :activedate, :mutationtype, :description, :state, :approveby, :approvedatetime, :createdby)",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "frombusinessunit" => $input->frombusinessunit,
                    "frombranch" => $input->frombranch,
                    "fromplant" => $input->fromplant,
                    "frompool" => $input->frompool,
                    "fromdivision" => $input->fromdivision,
                    "fromdepartment" => $input->fromdepartment,
                    "fromjob" => $input->fromjob,
                    "fromgrade" => $input->fromgrade,
                    "fromrole" => $input->fromrole,
                    "tobusinessunit" => $input->tobusinessunit,
                    "tobranch" => $input->tobranch,
                    "toplant" => $input->toplant,
                    "topool" => $input->topool,
                    "todivision" => $input->todivision,
                    "todepartment" => $input->todepartment,
                    "tojob" => $input->tojob,
                    "tograde" => $input->tograde,
                    "torole" => $input->torole,
                    "activedate" => $input->activedate,
                    "mutationtype" => $input->mutationtype,
                    "description" => $input->description,
                    "state" => $input->state,
                    "approveby" => $input->approveby,
                    "approvedatetime" => $input->approvedatetime,
                    // "createddatetime" => $input->createddatetime,
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
                "UPDATE mutation SET
                id = :id,
                employee = :employee,
                frombusinessunit = :frombusinessunit,
                frombranch = :frombranch,
                fromplant = :fromplant,
                frompool = :frompool,
                fromdivision = :fromdivision,
                fromdepartment = :fromdepartment,
                fromjob = :fromjob,
                fromgrade = :fromgrade,
                fromrole = :fromrole,
                tobusinessunit = :tobusinessunit,
                tobranch = :tobranch,
                toplant = :toplant,
                topool = :topool,
                todivision = :todivision,
                todepartment = :todepartment,
                tojob = :tojob,
                tograde = :tograde,
                torole = :torole,
                activedate = :activedate,
                mutationtype = :mutationtype,
                description = :description,
                state = :state,
                approveby = :approveby,
                approvedatetime = :approvedatetime,
                -- createddatetime = :createddatetime,
                createdby = :createdby
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "frombusinessunit" => $input->frombusinessunit,
                    "frombranch" => $input->frombranch,
                    "fromplant" => $input->fromplant,
                    "frompool" => $input->frompool,
                    "fromdivision" => $input->fromdivision,
                    "fromdepartment" => $input->fromdepartment,
                    "fromjob" => $input->fromjob,
                    "fromgrade" => $input->fromgrade,
                    "fromrole" => $input->fromrole,
                    "tobusinessunit" => $input->tobusinessunit,
                    "tobranch" => $input->tobranch,
                    "toplant" => $input->toplant,
                    "topool" => $input->topool,
                    "todivision" => $input->todivision,
                    "todepartment" => $input->todepartment,
                    "tojob" => $input->tojob,
                    "tograde" => $input->tograde,
                    "torole" => $input->torole,
                    "activedate" => $input->activedate,
                    "mutationtype" => $input->mutationtype,
                    "description" => $input->description,
                    "state" => $input->state,
                    "approveby" => $input->approveby,
                    "approvedatetime" => $input->approvedatetime,
                    // "createddatetime" => $input->createddatetime,
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
                DB::delete("DELETE FROM mutation WHERE id = :id",['id' => $input->id]);
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

        public static function approveMutation($params)
        {
            // return json_encode($params);
            try {
                DB::table('mutation')
                    ->where('id', $params['id'])
                    // ->where('approvalby', $params['approvalby'])
                    // ->where('state', '=', $params['state'])
                    ->update([
                        'state' => $params['state'],
                        'approveby' => $params['approveby'],
                        'approvedatetime' => date('Y-m-d')
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
