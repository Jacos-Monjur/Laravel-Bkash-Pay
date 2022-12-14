<?php

namespace monjur\bkash\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'product_name', 'currency', 'amount', 'invoice', 'trxID', 'status'
    ];
}
