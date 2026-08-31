<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class TicketDivision extends Model implements AuthenticatableContract, AuthorizableContract
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
            FROM ticket_problem_division
            WHERE id is not null {$where}
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
        $where = "";
        if (isset($params->id) && ($params->id != "")) {
            $where = $where . " AND (id = '" . $params->id . "')";
        }
        $stmt = DB::select("SELECT count(id) cnt
            FROM ticket_problem_division
            WHERE id is not null {$where}");
        return $stmt;
    }
}
