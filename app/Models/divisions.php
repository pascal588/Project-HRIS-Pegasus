<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use illuminate\Database\Eloquent\SoftDeletes;

class divisions extends Model
{
use SoftDeletes;

protected $table = 'divisions';
}
