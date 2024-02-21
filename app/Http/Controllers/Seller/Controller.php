<?php

namespace App\Http\Controllers\Seller;

use App\Models\Shop;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Auth;
use Closure;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $this->middleware(function (Request $request, Closure $next) {
            $uri = $request->path();
            if (0 == strcasecmp($request->method(), 'get') && !in_array($uri, ['seller/shop/improve'])) {
                $shop = Shop::where("user_id", Auth::user()->id)->first();
                if (0 == $shop->verification_status) {
                    return redirect()->route('seller.shop.improve');
                }
            }

            return $next($request);
        });

     }
}
