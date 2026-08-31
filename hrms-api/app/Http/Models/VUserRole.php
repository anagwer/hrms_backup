<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class VUserRole extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getUserInfo($username) {
        $stmt = DB::select("SELECT * FROM userrole WHERE username = :username",
        [
            'username' => $username
        ]);
        return $stmt;
    }

    public static function getList($args, $offset, $rec){
        $params = json_decode($args);
        $filter = ''; $where = '';

        if(isset($params->src) && ($params->src != '')) {
            $where = $where." AND (username like '%$params->src%')";
        }

        $stmt = DB::select("SELECT * from userrole where id is not null {$filter} {$where}
            limit :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]);
        return $stmt;
    }

    public static function getCount($args){
        $params = json_decode($args);
        $filter = ''; $where = '';

        if(isset($params->src) && ($params->src != '')) {
            $where = $where." AND (username like '%$params->src%')";
        }

        $stmt = DB::select("SELECT count(id) cnt from userrole
            where id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function createEx($params) {
        $input = json_decode($params);
            try {
                DB::insert(
                    "INSERT INTO user_role(id, roleid, userid)
                     VALUES (:id, :roleid, :userid)",
                [
                    "id" => $input->id,
                    "roleid" => $input->roleid,
                    "userid" => $input->userid
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

}
