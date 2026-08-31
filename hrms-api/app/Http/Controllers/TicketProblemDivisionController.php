<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

// Declare All Used Models
use App\Models\TicketProblemDivision;

class TicketProblemDivisionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    function getTicketProblemDivision()
    {
        $ticketProblemDivisions = TicketProblemDivision::get();
        return response()->json([
            'success' => true,
            'message' => "Ticket Problem Division retrieved successfully",
            'rows' => ($ticketProblemDivisions)
        ], 200);
    }
}
