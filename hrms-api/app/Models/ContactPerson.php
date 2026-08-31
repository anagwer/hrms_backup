<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class ContactPerson extends Model
{
    use UsesUuid;
    protected $table = 'contact_person';
    protected $guarded = ['id'];
}
