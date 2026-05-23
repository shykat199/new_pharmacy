<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreProductStockItem extends Model
{
    protected $fillable=['company_id','pre_product_stock_id','product_id','box','pieces','status'];

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
