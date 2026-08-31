<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class ResignEmployee extends Model implements AuthenticatableContract, AuthorizableContract
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
            FROM resignview
            WHERE id is not null {$filter} {$where}
            ORDER BY employee
            LIMIT :offset, :rec",
        [
            'offset' => $offset,
            'rec' => $rec
        ]);
        return $stmt;
    }

    public static function getResignType()
    {
        $data = DB::table('resigntype')
            ->get();
        return $data;
    }

    public static function getCount($args) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where=$where. " AND (empname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM resignview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getResignByParams($params)
    {
        $outstanding = DB::table('resignview')
            ->where($params)
            ->get();
        return $outstanding;
    }

    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO resign
                    (id, employee, businessunit, branch, plant, pool, division, department, job, grade,
                        resigndate, description, resigntype, state, approveby, approvedatetime, createdby)
                    VALUES
                    (:id, :employee, :businessunit, :branch, :plant, :pool, :division, :department, :job, :grade,
                         :resigndate, :description, :resigntype, :state, :approveby, :approvedatetime, :createdby)",
                [
                    "id" => $input->id,
                    "employee" => $input->employee,
                    "businessunit" => $input->businessunit,
                    "branch" => $input->branch,
                    "plant" => $input->plant,
                    "pool" => $input->pool,
                    "division" => $input->division,
                    "department" => $input->department,
                    "job" => $input->job,
                    "grade" => $input->grade,
                    "resigndate" => $input->resigndate,
                    "resigntype" => $input->resigntype,
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
                "UPDATE resign SET
                id = :id,
                employee = :employee,
                businessunit = :businessunit,
                branch = :branch,
                plant = :plant,
                pool = :pool,
                division = :division,
                department = :department,
                job = :job,
                grade = :grade,
                resigndate = :resigndate,
                resigntype = :resigntype,
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
                    "businessunit" => $input->businessunit,
                    "branch" => $input->branch,
                    "plant" => $input->plant,
                    "pool" => $input->pool,
                    "division" => $input->division,
                    "department" => $input->department,
                    "job" => $input->job,
                    "grade" => $input->grade,
                    "resigndate" => $input->resigndate,
                    "resigntype" => $input->resigntype,
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
            DB::delete("DELETE FROM resign WHERE id = :id",['id' => $input->id]);
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

    public static function approveResign($params)
    {
        // return json_encode($params);
        try {
            DB::table('resign')
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
