<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    public $fillable = ['name'];

    public function product()
    {
        return $this->belongsToMany(Product::class,'product_seller');
    }
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }
}
