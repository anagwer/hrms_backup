<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class RequestFormDocument extends Model
{
    use UsesUuid;
    protected $guarded = ['id'];

    public function business_unit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}
