<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\Traits\UsesUuid;

class TicketNew extends Model
{
    use HasFactory, UsesUuid;

    public $timestamps = false;
    protected $table = 'ticket';
    protected $guarded = ['id'];

    /**
     * this function isn't best naming.
     * the proper naming is "employee"
     */
    public function requestor()
    {
        return $this->belongsTo(Employee::class, 'requestor', 'id');
    }

    /**
     * this function isn't best naming.
     * the proper naming is "businessUnit"
     */
    public function businessunit()
    {
        return $this->belongsTo(BusinessUnit::class, 'businessunit', 'id');
    }

    /**
     * this function isn't best naming.
     * the proper naming is "ticketProblemCategory"
     */
    public function tickettype()
    {
        return $this->belongsTo(TicketProblemCategory::class, 'tickettype', 'id');
    }
    
    public function ticketProblemDivision()
    {
        return $this->hasOneThrough(
            TicketProblemDivision::class,
            TicketProblemCategory::class,
            'id',
            'id',
            'tickettype',
            'ticket_problem_division'
        );
    }

    public function ticketProblemTypeRelation()
    {
        return $this->belongsTo(TicketProblemType::class, 'ticket_problem_type', 'id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division', 'id');
    }
}
