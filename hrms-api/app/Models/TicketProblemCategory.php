<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Traits\UsesUuid;

class TicketProblemCategory extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'ticket_problem_category';
    protected $guarded = ['id'];

    public function ticketProblemDivision()
    {
        return $this->belongsTo(TicketProblemDivision::class, 'ticket_problem_division', 'id');
    }
}
