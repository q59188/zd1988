<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\Brand;
use App\Models\Product;
use App\Models\CustomerProduct;
use App\Models\PickupPoint;
use App\Models\CustomerPackage;
use App\Models\User;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\Order;
use App\Models\BusinessSetting;
use App\Models\Coupon;
use Cookie;
use Illuminate\Support\Str;
use App\Mail\SecondEmailVerifyMailManager;
use App\Models\AffiliateConfig;
use App\Models\Page;
use App\Models\ProductQuery;
use Mail;
use Illuminate\Auth\Events\PasswordReset;
use Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Carbon;
use App\Models\SellerWithdrawRequest;
use function dd;
use function print_r;
use DB;
class ApiStoreController extends Controller
{
   public function password(Request $request) 
   {
       $res = Hash::check($request->password, $request->re_pass);
       if ($res) {
           return response()->json(['success' => 1, 'message' => 'ok']); 
       } else {
           return response()->json(['success' => 0, 'message' => translate('error')]); 
       }
   }
   // 提货
   public function pickgoods(Request $request) 
   {
       $wuliu = file_get_contents('wuliu.txt');
       $wuliu = json_decode($wuliu,true);
       foreach ($wuliu as $k => $v) {
           $wuliu[$k]['time'] = time() + ($v['time'] * 60);
       }
       $wuliu = json_encode($wuliu);
       $u_id = decryptApi($request->u_id);
       $ids = $request->ids;
       try {
           DB::beginTransaction();
           foreach ($ids as $value) {
              $this->pickgoodsAn($value,$u_id,$wuliu);
           }
           DB::commit();
           return response()->json(['success' => 1, 'message' =>  translate('Payment completed')]); 
       } catch (\Exception $e) {
           DB::rollBack();
           return response()->json(['success' => 0, 'message' => translate('error')]); 
       }
       
   }
   protected function pickgoodsAn($order_id,$u_id,$wuliu) 
   {
       $order = Order::findOrFail($order_id);
       if ($order->seller_id != $u_id) return response()->json(['success' => 0, 'message' => 'error']);
       if (!$order || $order->product_storehouse_total <= 0) return response()->json(['success' => 0, 'message' => translate('Something went wrong!')]);
       if ($order->product_storehouse_status == 1) return response()->json(['success' => 0, 'message' => translate('Payment completed')]);
       $shop = $order->shop;
       $user = $shop->user;
        if ($user->balance >= $order->product_storehouse_total) {
            $user->balance -= $order->product_storehouse_total;
            $user->save();
            $shop->admin_to_pay += $order->grand_total;
            $shop->save();
            // 保存订单冻结资金过期时间
            $freezeDays = get_setting('frozen_funds_unfrozen_days', 15);
            $order->freeze_expired_at = Carbon::now()->addDays($freezeDays)->timestamp;
            $order->product_storehouse_status = 1;
            $order->wuliu = $wuliu;
            $order->save();
        }
       
   }
   // 提现
   public function withdraw(Request $request) {
        $type = $request->type;
        $u_id = $request->u_id;
        $u_id = decryptApi($request->u_id);
        $user = User::findOrFail($u_id);
     
        try {
             if( $type == 1 )
                { 
                        if ($request->amount > $user->balance) {
                            return response()->json(['success' => 0, 'message' => translate('You do not have enough balance to send withdraw request')]);
                        }
                        $exits = SellerWithdrawRequest::where('status', '0')->where('type',1)->where('user_id', $user->id)->count();
                        if ($exits !== 0) {
                            return response()->json(['success' => 0, 'message' => translate('withdraw exited')]);
                        }
                        $seller_withdraw_request = new SellerWithdrawRequest;
                        $seller_withdraw_request->user_id = $user->id;
                        $seller_withdraw_request->amount = $request->amount;
                        $seller_withdraw_request->message = $request->message;
                        $seller_withdraw_request->status = '0';
                        $seller_withdraw_request->viewed = '0';
                        $seller_withdraw_request->w_type = $request->w_type;
                        
                        if ($seller_withdraw_request->save()) {//扣除余额
                            $userModel = User::find($user->id);
                            $userModel->balance = $user->balance-$request->amount;
                            $userModel->save();
                            return response()->json(['success' => 1, 'message' => translate('Request has been sent successfully')]);
                        } else {
                            return response()->json(['success' => 0, 'message' => translate('Something went wrong')]);
                        }
                }
                elseif ( $type == 2 )
                {
                    
                    if ($request->amount > $user->shop->bzj_money) {
                          return response()->json(['success' => 0, 'message' => translate('You do not have enough guarantee balance to send withdraw request')]);
                        }
                        $exits = SellerWithdrawRequest::where('status', '0')->where('type',2)->where('user_id', $user->id)->count();
                        
                        if ($exits !== 0) 
                        {
                            return response()->json(['success' => 0, 'message' => translate('withdraw exited')]);
                        }
                      
                        $seller_withdraw_request = new SellerWithdrawRequest;
                        $seller_withdraw_request->user_id = $user->id;
                        $seller_withdraw_request->amount = $request->amount;
                        $seller_withdraw_request->message = $request->message;
                        $seller_withdraw_request->status = '0';
                        $seller_withdraw_request->viewed = '0';
                        $seller_withdraw_request->type = 2;
                        $seller_withdraw_request->w_type =  $request->w_type;
                      
                        if ($seller_withdraw_request->save()) {//扣除余额
                            $userModel = Shop::find($user->shop->id);
                            $userModel->bzj_money = $userModel->bzj_money-$request->amount;
                            $userModel->save();
                            return response()->json(['success' => 1, 'message' => translate('Request has been sent successfully')]);
                        } else {
                            return response()->json(['success' => 1, 'message' => translate('Something went wrong')]);
                        }
        
                } 
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'message' => $this->getMessage()]);
        }
       
   }
}
