<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRole extends Model
{
    protected $table = 'accessrole';

    protected $casts = [
        'canaccess' => 'int',
        'cancreate' => 'int',
        'canedit' => 'int',
        'candelete' => 'int',
        'canaprove' => 'int',
        'cancreatereport' => 'int',
        'canshowsallary' => 'int',
        'cancancel' => 'int',
    ];
}
