<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SmartOrder extends Model
{
    public function seller()
    {
        return $this->belongsTo(Shop::class, 'seller_id', 'user_id');
    }
}
