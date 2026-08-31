<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class ProblemType extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";
        $stmt = DB::select(
            "SELECT *
            FROM problem_category_view
            WHERE id is not null {$filter} {$where}
            ORDER BY name

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
        $filter = "";
        $where = "";
        $stmt = DB::select("SELECT count(id) cnt
            FROM problem_category_view
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function getByID($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        if (isset($params->ticket_problem_category) && ($params->ticket_problem_category != "")) {
            $where = $where . " AND (ticket_problem_category like '%" . $params->ticket_problem_category . "%')";
        }

        $stmt = DB::select(
            "SELECT *
            FROM ticket_problem_type
            WHERE id is not null {$filter} {$where}
            ORDER BY name

            LIMIT :offset, :rec",
            [
                'offset' => $offset,
                'rec' => $rec
            ]
        );
        return $stmt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO ticket_problem_type
                    (id,ticket_problem_category, name)
                 VALUES
                    (:id, :ticket_problem_category, :name)
                ",
                [
                    "id" => $input->id,
                    "ticket_problem_category" => $input->ticket_problem_category,
                    "name" => $input->name
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
                "UPDATE ticket_problem_type SET
                    name = :name,
                    ticket_problem_category = :ticket_problem_category
                 WHERE id = :id
                ",
                [
                    "id" => $input->id,
                    "ticket_problem_category" => $input->ticket_problem_category,
                    "name" => $input->name
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
            DB::delete("DELETE FROM ticket_problem_type WHERE id = :id", ['id' => $input->id]);
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
