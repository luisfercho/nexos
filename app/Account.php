<?php

namespace Nexos;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{

    protected $fillable = [
        'product_id',
        'customer_id',
        'number',
        'balance',
        'status',
        'active',
        'password'
    ];

    public function customer(){
        return $this->belongsTo('Nexos\Customer');
    }

    public function product(){
        return $this->belongsTo('Nexos\Product');
    }
}
