<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable=['user_id','invoice_id','company_id','product_id','box_qty','quantity','price','discount','final_total'];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
