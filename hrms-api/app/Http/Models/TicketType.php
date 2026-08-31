<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class TicketType extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (cat.strname like '%" . $params->src . "%')";
        }

        $stmt = DB::select(
            "SELECT cat.*, divs.name division_name
            FROM ticket_problem_category cat
            LEFT JOIN ticket_problem_division divs
            ON cat.ticket_problem_division = divs.id
            WHERE cat.id is not null {$filter} {$where}
            ORDER BY cat.strname

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

        // Editable SQL WHERE
        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (strname like '%" . $params->src . "%')";
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM ticket_problem_category
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO ticket_problem_category
                    (id, strname, ticket_problem_division)
                 VALUES
                    (:id, :strname, :ticket_problem_division)
                ",
                [
                    "id" => $input->id,
                    "strname" => $input->strname,
                    "ticket_problem_division" => $input->ticket_problem_division
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
                "UPDATE ticket_problem_category SET
                    strname = :strname,
                    ticket_problem_division = :ticket_problem_division
                 WHERE id = :id
                ",
                [
                    "id" => $input->id,
                    "strname" => $input->strname,
                    "ticket_problem_division" => $input->ticket_problem_division
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
            DB::delete("DELETE FROM ticket_problem_category WHERE id = :id", ['id' => $input->id]);
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
