<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    protected $fillable =['invoice_id','attempt_by_admin','created_by','user_id','total_amount','custom_discount','other_charges','final_total','paid_amount','due_amount','status','note','created_at'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
}
