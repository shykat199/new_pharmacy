<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable=['company_id','slug','unit_price','box_per_pic','stock','status','name','type','low_stock','strength'];

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }
}
