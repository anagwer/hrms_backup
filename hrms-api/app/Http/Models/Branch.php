<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Branch extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec) {
        $params = json_decode($args);
        $filter = ""; $where = "";

        // Editable SQL WHERE
        if (isset($params->businessunit) && ($params->businessunit != "")) {
            $filter=$filter." AND (businessunit  = '$params->businessunit')";
        }
        
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT *
            FROM branch
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
            $filter=$filter." AND (businessunit = '$params->businessunit')";
        }
        
        if (isset($params->src) && ($params->src != "")) {
            $where=$where." AND (strname like '%".$params->src."%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM branch
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }



    public static function createEx($params) {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO branch
                    (id, strname, address, city, postalcode, phonenumber, secondphonenumber, fax, website, email, businessunit)
                 VALUES
                    (:id, :strname, :address, :city, :postalcode, :phonenumber, :secondphonenumber, :fax, :website, :email, :businessunit)",
                [
                    "id" => $input->id,
                    "strname" => $input->strname,
                    "address" => $input->address,
                    "city" => $input->city,
                    "postalcode" => $input->postalcode,
                    "phonenumber" => $input->phonenumber,
                    "secondphonenumber" => $input->secondphonenumber,
                    "fax" => $input->fax,
                    "website" => $input->website,
                    "email" => $input->email,
                    "businessunit" => $input->businessunit
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
                "UPDATE branch SET
                    strname = :strname,
                    address = :address,
                    city = :city,
                    postalcode = :postalcode,
                    phonenumber = :phonenumber,
                    secondphonenumber = :secondphonenumber,
                    fax = :fax,
                    website = :website,
                    email = :email,
                    businessunit = :businessunit
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "strname" => $input->strname,
                    "address" => $input->address,
                    "city" => $input->city,
                    "postalcode" => $input->postalcode,
                    "phonenumber" => $input->phonenumber,
                    "secondphonenumber" => $input->secondphonenumber,
                    "fax" => $input->fax,
                    "website" => $input->website,
                    "email" => $input->email,
                    "businessunit" => $input->businessunit
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
                DB::delete("DELETE FROM branch WHERE id = :id",['id' => $input->id]);
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
