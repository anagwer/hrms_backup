<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class EmployeeDocument extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $where = "";
        if (isset($params->employee_id) && ($params->employee_id != "")) {
            $where = $where . " AND (ed.employee_id = '" . $params->employee_id . "')";
        }
        $stmt = DB::select(
            "SELECT ed.*, doc.document_name
            FROM employees_document ed
            join document_type doc on ed.document_type_id = doc.id
            WHERE ed.id is not null {$where}
            ORDER BY doc.document_name
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
        if (isset($params->employee_id) && ($params->employee_id != "")) {
            $where = $where . " AND (employee_id = '" . $params->employee_id . "')";
        }
        $stmt = DB::select("SELECT count(id) cnt
            FROM employees_document
            WHERE id is not null {$where}");
        return $stmt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO employees_document
                    (id,employee_id, document_type_id, url, upload_by)
                 VALUES
                    (:id, :employee_id, :document_type_id, :url, :upload_by)
                ",
                [
                    "id" => $input->id,
                    "employee_id" => $input->employee_id,
                    "document_type_id" => $input->document_type_id,
                    "url" => $input->url,
                    "upload_by" => $input->upload_by
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
                "UPDATE employees_document SET
                    employee_id = :employee_id,
                    document_type_id = :document_type_id,
                    url = :url,
                    upload_by = :upload_by
                 WHERE id = :id
                ",
                [
                    "id" => $input->id,
                    "employee_id" => $input->employee_id,
                    "document_type_id" => $input->document_type_id,
                    "url" => $input->url,
                    "upload_by" => $input->upload_by
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
            DB::delete("DELETE FROM employees_document WHERE id = :id", ['id' => $input->id]);
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
