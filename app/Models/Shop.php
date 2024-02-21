<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Shop extends Model
{

    protected $with = [ 'user' ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function seller_package() {
        return $this->belongsTo(SellerPackage::class);
    }

    public function seller_spread_package() {
        return $this->belongsTo(SellerSpreadPackage::class);
    }

    // 获取已验证通过的卖家列表
    public static function approvedList()
    {
        $shops = DB::table('shops', 'a')->leftJoin('users as b', function($join) {
            $join->on('a.user_id', '=', 'b.id')->where('b.user_type', 'seller');
        })->select(['a.id', 'a.name as seller_name', 'b.email', 'a.user_id'])
            ->orderBy('a.id', 'desc')->get()->toArray();
        return $shops;
    }
}
