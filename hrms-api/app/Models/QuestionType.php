<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    use UsesUuid;

    protected $guarded = ['id'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
