<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'invoice_id', 'amount', 'type', 'note', 'paid_date'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoices::class,'invoice_id','id');
    }
}
