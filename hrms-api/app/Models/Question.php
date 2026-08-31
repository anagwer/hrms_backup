<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use UsesUuid;

    protected $guarded = ['id'];

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
