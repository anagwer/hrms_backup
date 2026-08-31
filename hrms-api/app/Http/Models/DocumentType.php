<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class DocumentType extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $where = "";
        if (isset($params->id) && ($params->id != "")) {
            $where = $where . " AND (id = '" . $params->id . "')";
        }
        $stmt = DB::select(
            "SELECT *
            FROM document_type
            WHERE id is not null {$where}
            ORDER BY document_name
            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    public static function getCount($args)
    {
        $params = json_decode($args);
        $where = "";
        if (isset($params->id) && ($params->id != "")) {
            $where = $where . " AND (id = '" . $params->id . "')";
        }
        $stmt = DB::select("SELECT count(id) cnt
            FROM document_type
            WHERE id is not null {$where}");
        return $stmt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO document_type
                    (id,document_name)
                 VALUES
                    (:id, :document_name)
                ",
                [
                    "id" => $input->id,
                    "document_name" => $input->document_name
                ]
            );
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
        return json_encode($status);
    }

    public static function updateEx($params)
    {
        $input = json_decode($params);
        try {
            DB::update(
                "UPDATE document_type SET
                    document_name = :document_name
                 WHERE id = :id
                ",
                [
                    "id" => $input->id,
                    "document_name" => $input->document_name
                ]
            );
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
        return json_encode($status);
    }

    public static function deleteEx($params)
    {
        $input = json_decode($params);
        try {
            DB::delete("DELETE FROM document_type WHERE id = :id", ['id' => $input->id]);
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
        return json_encode($status);
    }
}
