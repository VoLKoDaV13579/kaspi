<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'url'] ;


    public function seller()
    {
        return $this->belongsToMany(Seller::class,"product_seller");
    }
    public function priceData()
    {
        return $this->hasMany(PriceHistory::class);
    }
}
