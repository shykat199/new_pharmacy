<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAccessPassword extends Model
{
    protected $fillable =['password','user_id'];
}
