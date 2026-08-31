<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\UsesUuid;

class TicketProblemType extends Model
{
    use UsesUuid;

    protected $table = 'ticket_problem_type';
    protected $guarded = ['id'];

    public function ticketProblemCategory()
    {
        return $this->belongsTo(TicketProblemCategory::class, 'ticket_problem_category', 'id');
    }
}
