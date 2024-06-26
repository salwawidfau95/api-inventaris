<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $fillable = ["product_id", "order_date", "quantity"];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
