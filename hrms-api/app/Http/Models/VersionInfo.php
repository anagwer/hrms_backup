<?php
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class VersionInfo extends Model
{
    public $table = 'version_info';
    public $incrementing = false;
    protected $fillable = ['application_name', 'version_number', 'active_date', 'version_info', 'update_link'];
}