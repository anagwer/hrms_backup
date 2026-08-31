<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class TUsers extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getUserLogin($username) {
        $stmt = DB::select("SELECT * FROM users WHERE username = :username",
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

        $stmt = DB::select("SELECT * from users where id is not null {$filter} {$where}
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

        $stmt = DB::select("SELECT count(id) cnt from users
            where id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function createEx($params) {
        $input = json_decode($params);
            try {
                DB::insert(
                    "INSERT INTO users(id, username, email, password)
                     VALUES (:id, :username, :email, :password)",
                [
                    "id" => $input->id,
                    "username" => $input->username,
                    "email" => $input->email,
                    "password" => $input->password
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
