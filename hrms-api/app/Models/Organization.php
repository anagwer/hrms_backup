<?php

namespace App\Models;

use Kalnoy\Nestedset\NodeTrait;
use App\Http\Traits\UsesUuid;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use NodeTrait;
    use UsesUuid;
    protected $table = 'organizations';
    protected $fillable = [
        'text'
    ];
}
