<?php

namespace App\Http\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Facades\DB;
use PDOException;

class Ticket extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public static function getByID($params)
    {
        $input = json_decode($params);
        $stmt = DB::select(
            "SELECT *
                FROM ticketview
                WHERE id = :id",
            [
                'id' => $input->id
            ]
        );
        return $stmt;
    }

    public static function getList($args, $offset, $rec)
    {
        $params = json_decode($args);
        $filter = "";
        $where = "";

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (docno like '%" . $params->src . "%'";
            $where = $where . " OR requestor like '%" . $params->src . "%'";
            $where = $where . " OR namarequestor like '%" . $params->src . "%')";
        }

        if (isset($params->filter) && ($params->filter != "")) {
            $filter = $filter . " AND statusTicket = '$params->filter'";
        }

        if (isset($params->ticketDivision) && ($params->ticketDivision != "")) {
            $filter = $filter . " AND ticket_problem_division_id = '$params->ticketDivision'";
        }

        if (isset($params->startDate) && ($params->startDate != "")) {
            if (($params->startDate != "") && ($params->endDate != "")) {
                if ($params->startDate == $params->endDate) {
                    $filter = $filter . " AND (createddatetime like '%" . $params->startDate . "%')";
                } else {
                    $filter = $filter . " AND (createddatetime >= '$params->startDate 00:00:00'  AND createddatetime <= '$params->endDate 23:59:59' )";
                }
            }
        }

        $stmt = DB::select(
            "SELECT *
            FROM ticketview
            WHERE id is not null {$filter} {$where}
            ORDER BY docno desc
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

        if (isset($params->src) && ($params->src != "")) {
            $where = $where . " AND (docno like '%" . $params->src . "%'";
            $where = $where . " OR requestor like '%" . $params->src . "%'";
            $where = $where . " OR namarequestor like '%" . $params->src . "%')";
        }

        if (isset($params->filter) && ($params->filter != "")) {
            $filter = $filter . " AND statusTicket = '$params->filter'";
        }

        if (isset($params->ticketDivision) && ($params->ticketDivision != "")) {
            $filter = $filter . " AND ticket_problem_division_id = '$params->ticketDivision'";
        }

        if (isset($params->startDate) && ($params->startDate != "")) {
            if ($params->startDate == $params->endDate) {
                $filter = $filter . " AND (createddatetime like '%" . $params->startDate . "%')";
            } else {
                $filter = $filter . " AND (createddatetime >= '$params->startDate 00:00:00'  AND createddatetime <= '$params->endDate 23:59:59' )";
            }
        }

        $stmt = DB::select("SELECT count(id) cnt
            FROM ticketview
            WHERE id is not null {$filter} {$where}");
        return $stmt;
    }

    public static function createEx($params)
    {
        $input = json_decode($params);
        try {
            DB::insert(
                "INSERT INTO ticket
                    (id, docno, tickettype, ticket_problem_type,regarding, requestor, businessunit, division, location, phone, attachment, description, ipaddress)
                    VALUES
                    (:id, :docno, :tickettype, :ticket_problem_type, :regarding, :requestor, :businessunit, :division, :location, :phone, :attachment, :description, :ipaddress)",
                [
                    "id" => $input->id,
                    "docno" => $input->docno,
                    "tickettype" => $input->tickettype,
                    "ticket_problem_type" => $input->ticket_problem_type,
                    "regarding" => $input->regarding,
                    "requestor" => $input->requestor,
                    "businessunit" => $input->businessunit,
                    "division" => $input->division,
                    "location" => $input->location,
                    "phone" => $input->phone,
                    "attachment" => $input->attachment,
                    "description" => $input->description,
                    "ipaddress" => $input->ipaddress
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
                "UPDATE ticket SET
                    tickettype = :tickettype,
                    ticket_problem_type = :ticket_problem_type,
                    regarding = :regarding,
                    requestor = :requestor,
                    businessunit = :businessunit,
                    division = :division,
                    location = :location,
                    phone = :phone,
                    description = :description,
                    createddatetime = :createddatetime,
                    confirmdatetime = :confirmdatetime,
                    target = :target,
                    corrective = :corrective,
                    startdatetime = :startdatetime,
                    enddatetime = :enddatetime,
                    feedbacknote = :feedbacknote,
                    feedbackdatetime = :feedbackdatetime,
                    score = :score,
                    status = :status,
                    isread = :isread,
                    pic = :pic
                 WHERE id = :id",
                [
                    "id" => $input->id,
                    "tickettype" => $input->tickettype,
                    "ticket_problem_type" => $input->ticket_problem_type,
                    "regarding" => $input->regarding,
                    "requestor" => $input->requestor,
                    "businessunit" => $input->businessunit,
                    "division" => $input->division,
                    "location" => $input->location,
                    "phone" => $input->phone,
                    "description" => $input->description,
                    "createddatetime" => $input->createddatetime,
                    "confirmdatetime" => $input->confirmdatetime,
                    "target" => $input->target,
                    "corrective" => $input->corrective,
                    "startdatetime" => $input->startdatetime,
                    "enddatetime" => $input->enddatetime,
                    "feedbacknote" => $input->feedbacknote,
                    "feedbackdatetime" => $input->feedbackdatetime,
                    "score" => $input->score,
                    "status" => $input->status,
                    "isread" => $input->isread,
                    "pic" => $input->pic
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
            DB::update(
                "UPDATE ticket SET
                status = 9
                WHERE id = :id",
                [
                    "id" => $input->id
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
}
