<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Traits\UsesUuid;

class TicketProblemDivision extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'ticket_problem_division';
    protected $guarded = ['id'];

    public function ticketProblemCategory()
    {
        return $this->hasMany(TicketProblemCategory::class, 'id', 'ticket_problem_division');
    }
}
