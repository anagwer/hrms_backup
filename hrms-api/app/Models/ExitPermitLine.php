<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class ExitPermitLine extends Model
{
    use UsesUuid;
    
    protected $table = 'sikelline';
    protected $guarded = ['id'];
    
    public function getUpdatedAtColumn() {
        return null;
    }
}