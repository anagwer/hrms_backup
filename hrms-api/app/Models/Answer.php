<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use UsesUuid;

    protected $guarded = ['id'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
