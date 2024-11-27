<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceChangeNotification extends Model
{
    protected $fillable = [
        'product_id',
        'seller_id',
        'old_price',
        'new_price',
    ];
}
