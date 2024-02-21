<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\CombinedOrder;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\SmartOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class SmartOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $smart_orders = SmartOrder::paginate(10);
        return view('backend.smart_order.index', compact('smart_orders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sellers = Shop::approvedList();
        $customers = User::customerList();
        return view('backend.smart_order.create', compact('sellers', 'customers'));
    }


    // 保存
    public function store(Request $request)
    {
        $sellerId = $request->input('seller_id', 0);
        $userIds = $request->input('user_ids', '');
        $dateRange = $request->input('date_range', '');
        $quantity = intval($request->input('quantity', 0));
        $minPrice = floatval($request->input('min_price', 0));
        $maxPrice = floatval($request->input('max_price', 0));
        if (empty($userIds) || empty($sellerId) || empty($dateRange) || empty($quantity)
            || empty($maxPrice) || $maxPrice < $minPrice) {
            flash(translate('Parameters error'))->error();
            return back();
        }

        // 用户数必须大于或等于订单数，确保每人只有一单
        $userCount = count(explode(',', $userIds));
        if ($quantity > $userCount) {
            flash(translate('Quantity Less People'))->error();
            return back();
        }

        list($beginDate, $endDate) = explode(' ~ ', $dateRange);
        $days = (strtotime($endDate) - strtotime($beginDate)) / 86400 + 1; // 持续天数

        $smartOrder = new SmartOrder();
        $smartOrder->seller_id = $sellerId;
        $smartOrder->user_ids = $userIds;
        $smartOrder->days = $days;
        $smartOrder->quantity = $quantity;
        $smartOrder->min_price = $minPrice;
        $smartOrder->max_price = $maxPrice;
        $smartOrder->begin_date = $beginDate;
        $smartOrder->end_date = $endDate;
        if ($smartOrder->save()) {
            flash(translate('Add Successful'))->success();
            return redirect()->route('smart_order.index');
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }

    /**
     * 编辑页
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $row = SmartOrder::findOrFail(decrypt($id));
        $row['user_id_list'] = explode(',', $row['user_ids']);
        $row['date_range'] = $row['begin_date'] . ' ~ ' . $row['end_date'];
        $sellers = Shop::approvedList();
        $customers = User::customerList();
        return view('backend.smart_order.edit', compact('row', 'sellers', 'customers'));
    }

    /**
     * 编辑->保存
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $sellerId = $request->input('seller_id', 0);
        $userIds = $request->input('user_ids', '');
        $dateRange = $request->input('date_range', '');
        $quantity = intval($request->input('quantity', 0));
        $minPrice = floatval($request->input('min_price', 0));
        $maxPrice = floatval($request->input('max_price', 0));
        if (empty($userIds) || empty($sellerId) || empty($dateRange) || empty($quantity)
            || empty($maxPrice) || $maxPrice < $minPrice) {
            flash(translate('Parameters error'))->error();
            return back();
        }

        // 用户数必须大于或等于订单数，确保每人只有一单
        $userCount = count(explode(',', $userIds));
        if ($quantity > $userCount) {
            flash(translate('Quantity Less People'))->error();
            return back();
        }

        list($beginDate, $endDate) = explode(' ~ ', $dateRange);
        $days = (strtotime($endDate) - strtotime($beginDate)) / 86400 + 1; // 持续天数

        $smartOrder = SmartOrder::findOrFail($id);
        $smartOrder->seller_id = $sellerId;
        $smartOrder->user_ids = $userIds;
        $smartOrder->days = $days;
        $smartOrder->quantity = $quantity;
        $smartOrder->min_price = $minPrice;
        $smartOrder->max_price = $maxPrice;
        $smartOrder->begin_date = $beginDate;
        $smartOrder->end_date = $endDate;
        if ($smartOrder->save()) {
            flash(translate('Save Successfully'))->success();
            return redirect()->route('smart_order.index');
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }

    // 删除
    public function destroy($id)
    {
        SmartOrder::destroy($id);
        flash(translate('Record delete successfully'))->success();
        return redirect()->route('smart_order.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        //
    }

    // 修改状态
    public function change_status(Request $request)
    {
        $smartOrder = SmartOrder::findOrFail($request->id);
        $smartOrder->status = $request->status;
        $smartOrder->save();
        return 1;
    }

    // 检测并执行
    public function check_to_run()
    {
        set_time_limit(0);

        // 先选出待执行的任务
        $curDate = date('Y-m-d');
        $taskList = DB::table('smart_orders')->where('status', 1)
            ->where('begin_date', '<=', $curDate)
            ->where('end_date', '>', $curDate)
            ->orderBy('id', 'asc')->get()->toArray();
        if (empty($taskList)) {
            return date('Y-m-d H:i:s') . ' No smart order task!';
        }

        foreach ($taskList as $kv) {
            $this->handleOneTaskGroup((array)$kv);
        }

        return date('Y-m-d H:i:s') . ' Task Over!';
    }

    // 处理一个任务组
    private function handleOneTaskGroup($params)
    {
        $taskGroupId = $params['id'];
        if ($params['finished_count'] >= $params['quantity']) { // 已完成了
            Log::info( "task-{$taskGroupId} already finished!!!");
            return 0;
        }

        // 待参与用户
        $userIds = explode(',', $params['user_ids']);
        $totalUser = count($userIds);
        if ($totalUser == 0) {
            Log::info( "task-{$taskGroupId} no available user!!!");
            return 0;
        }


        // 计算下时间间隔
        $spaceTime = ceil(($params['days'] * 86400 - 14400) / $totalUser);
        /*
        // 获取下最后执行的用户
        $latestRow = DB::table('smart_order_tasks')->where('so_id', $taskGroupId)
            ->orderBy('id', 'desc')->first();
        if (!empty($latestRow) && time() - strtotime($latestRow['create_time']) < $spaceTime) {
            Log::info(" task-{$taskGroupId} too often, skip!!!");
            return 0;
        }
        */

        $cacheKey = 'smart_order#' . $taskGroupId;
        if (1 == Cache::get($cacheKey, 0)) {
            Log::info($cacheKey . " task-{$taskGroupId} running!!!");
            return 0;
        }

        Cache::put($cacheKey, 1, 120); // 有效期2分钟

        // 统计下已完成下单的用户ID列表(失败的也统计)
        $existIds = $this->getFinishedUserIds($params['id'], false);
        // 计算尚未完成下单的用户ID列表
        $leftUIds = array_diff($userIds, $existIds);
        if (count($leftUIds) == 0) {
            Log::info( "task-{$taskGroupId} no available user, all user handled!!!");
            return 0;
        }

        // 随机抽取一位客户下单
        $uidList = [];
        $needCount = 1;
        $defaultSpaceTime = env('SMART_ORDER_SPACE_TIME', 300);
        if ($spaceTime <= $defaultSpaceTime) { // 默认定时任务为5分钟一次
            $needCount = ceil($defaultSpaceTime / $spaceTime);
        }

        for ($i = 0; $i < $needCount; $i++) {
            $randIndex = mt_rand(0, count($leftUIds) - 1);
            $uidList[] = $leftUIds[$randIndex] ?? 0;
        }
        // 可能存在重复，去重下
        $uidList = array_unique($uidList);

        foreach ($uidList as $uid) {
            Log::info(" task-{$taskGroupId}, uid: {$uid} create order");
            $this->runOrderTask($uid, $params);
        }


        /*
        // 单个处理
        $uid = $leftUIds[mt_rand(0, count($leftUIds) - 1)] ?? 0;
        if ($uid > 0) {
            Log::info(" task-{$taskGroupId}, uid: {$uid} create order");
            $this->runOrderTask($uid, $params);
        }

        // 批量处理
        foreach ($leftUIds as $uid) {
            $this->runOrderTask($uid, $params);
        }
        */

        Cache::forget($cacheKey); // 处理完了，清楚下锁

        return 1;
    }

    // 统计下已完成下单的用户ID列表
    private function getFinishedUserIds($id, $onlyOk = false)
    {
        $query = DB::table('smart_order_tasks')->where('so_id', $id);
        if ($onlyOk) {
            $query = $query->where('status', 0);
        }

        $rows = $query->select(['id', 'user_id'])->get()->toArray();
        $ids = [];
        if (!empty($rows)) {
            foreach ($rows as $kv) {
                $ids[] = $kv->user_id;
            }
        }

        return $ids;
    }

    // 执行自动下单任务
    private function runOrderTask($uid, $params)
    {
        $bRet = false;
        DB::beginTransaction();
        try {
            list($bRet, $error, $id) = $this->createSmartOrder($uid, $params);
            $bRet ? DB::commit() : DB::rollBack();
        } catch (\Throwable $e) {
            DB::rollBack();
            $error = $e->getMessage();
        }

        // 添加智能下单记录
        DB::table('smart_order_tasks')->insert([
           'so_id' => $params['id'],
           'user_id' => $uid,
           'status' => $bRet ? 1 : 0,
           'order_id' => $id ?? 0,
           'memo' => $error ?? '',
           'create_time' => date('Y-m-d H:i:s'),
        ]);

        // 更新完成人数
        if ($bRet) {
            DB::table('smart_orders')->where('id', $params['id'])
                ->increment('finished_count', 1);
        }
    }

    // 智能下单
    private function createSmartOrder($uid, $params)
    {
        // 选出指定卖家的符合要求的商品
        $where = [
            ['a.user_id', '=', $params['seller_id']], // 卖家
            ['a.published', '=', 1], // 已上架
            // ['a.unit_price', 'between', [$params['min_price'], $params['max_price']]], // 价格区间
            ['b.qty', '>', 1], // 库存有货
        ];

        $fields = ['a.id', 'a.user_id', 'b.price', 'b.qty'];
        $productInfo = DB::table('products', 'a')
            ->join('product_stocks as b', 'a.id', '=', 'b.product_id')
            ->where($where)->whereBetween('a.unit_price', [$params['min_price'], $params['max_price']])
            ->select($fields)->first();
        if (empty($productInfo)) { // 查找产品失败
            return [false, "product not available", 0];
        }

        $productInfo = (array)$productInfo;
        $user = DB::table('users')->where('id', $uid)
            ->select(['id', 'name', 'email', 'balance'])->first();
        if (empty($user)) { // 用户不存在
            return [false, 'user not exists', 0];
        }

        if ($user->balance < $productInfo['price']) { // 余额不足
            return [false, '1-insufficient balance', 0];
        }

        // 创建订单
        $address = Address::where('user_id', $uid)->orderBy('set_default', 'desc')->first();
        $shippingAddress = [];
        if ($address != null) {
            $shippingAddress['name']        = $user->name;
            $shippingAddress['email']       = $user->email;
            $shippingAddress['address']     = $address->address;
            $shippingAddress['country']     = $address->country->name;
            $shippingAddress['state']       = $address->state->name;
            $shippingAddress['city']        = $address->city->name;
            $shippingAddress['postal_code'] = $address->postal_code;
            $shippingAddress['phone']       = $address->phone;
            if ($address->latitude || $address->longitude) {
                $shippingAddress['lat_lang'] = $address->latitude . ',' . $address->longitude;
            }
        }

        $shippingAddress = json_encode($shippingAddress, JSON_UNESCAPED_UNICODE);

        // step-1
        $combined_order = new CombinedOrder;
        $combined_order->user_id = $uid;
        $combined_order->shipping_address = $shippingAddress;
        $combined_order->save();

        // step-2
        $order = new Order;
        $order->combined_order_id = $combined_order->id;
        $order->user_id = $user->id;
        $order->shipping_address = $shippingAddress;
        $order->shipping_type = 'home_delivery'; // 送货上门
        $order->payment_type = 'wallet';
        $order->delivery_viewed = '0';
        $order->payment_status_viewed = '0';
        $order->code = date('Ymd-His') . rand(10, 99);
        $order->date = strtotime('now');
        $order->seller_id = $params['seller_id']; // 卖家
        $order->save();

        // step-3
        $subtotal = 0;
        $tax = 0;
        $shipping = 0;
        $coupon_discount = 0;
        // 产品仓库的产品货款
        $productStorehouseTotal = 0;
        // 构造一个购物车选项
        $seller_product = [[
            'product_id' => $productInfo['id'],
            'quantity' => 1,
            'variation' => '',
            'discount' => 0.0,
            'shipping_type' => 'home_delivery',
            'product_referral_code' => null,
            'shipping_cost' => 0.0,
            'coupon_code' => null,
        ]];

        //Order Details Storing
        foreach ($seller_product as $cartItem) {
            $product = Product::find($cartItem['product_id']);

            // 计算产品仓库的产品货款
            $originalProduct = null;
            if ($product->original_id) {
                $originalProduct = Product::query()->find($product->original_id);
                if ($originalProduct) {
                    $productStorehouseTotal += cart_product_price($cartItem, $originalProduct, false, false) * $cartItem['quantity'];
                }
            }

            $subtotal += cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
            $tax += cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
            $coupon_discount += $cartItem['discount'];
            $product_variation = $cartItem['variation'];
            $product_stock = $product->stocks->where('variant', $product_variation)->first();
            if ($product->digital != 1 && $cartItem['quantity'] > $product_stock->qty) {
                $order->delete();
                return [false, translate('The requested quantity is not available for ') . $product->getTranslation('name')];
            } elseif ($product->digital != 1) {
                $product_stock->qty -= $cartItem['quantity'];
                $product_stock->save();
            }

            $order_detail = new OrderDetail;
            $order_detail->order_id = $order->id;
            $order_detail->seller_id = $product->user_id;
            $order_detail->product_id = $product->id;
            $order_detail->is_storehouse_product = $product->original_id ? 1 : 0; // 是否产品仓库产品
            $order_detail->original_product_id = $product->original_id ?: null; // 原产品仓库产品ID
            $order_detail->original_product_price = $originalProduct ? $originalProduct->unit_price : null; // 原产品仓库产品价格(进货价)
            $order_detail->variation = $product_variation;
            $order_detail->price = cart_product_price($cartItem, $product, false, false) * $cartItem['quantity'];
            $order_detail->tax = cart_product_tax($cartItem, $product, false) * $cartItem['quantity'];
            $order_detail->shipping_type = $cartItem['shipping_type'];
            $order_detail->product_referral_code = $cartItem['product_referral_code'];
            $order_detail->shipping_cost = $cartItem['shipping_cost'];
            $shipping += $order_detail->shipping_cost;
            //End of storing shipping cost

            $order_detail->quantity = $cartItem['quantity'];
            $order_detail->save();

            $product->num_of_sale += $cartItem['quantity'];
            $product->save();

            $order->seller_id = $product->user_id;

            if ($product->added_by == 'seller' && $product->user->seller != null) {
                $seller = $product->user->seller;
                $seller->num_of_sale += $cartItem['quantity'];
                $seller->save();
            }

            if (addon_is_activated('affiliate_system')) {
                if ($order_detail->product_referral_code) {
                    $referred_by_user = User::where('referral_code', $order_detail->product_referral_code)->first();

                    $affiliateController = new AffiliateController;
                    $affiliateController->processAffiliateStats($referred_by_user->id, 0, $order_detail->quantity, 0, 0);
                }
            }
        }

        $order->grand_total = $subtotal + $tax + $shipping;
        $order->product_storehouse_total = $productStorehouseTotal;

        $couponCode = $seller_product[0]['coupon_code'] ?? '';
        if (!empty($couponCode)) {
            $order->coupon_discount = $coupon_discount;
            $order->grand_total -= $coupon_discount;

            $coupon_usage = new CouponUsage;
            $coupon_usage->user_id = $uid;
            $coupon_usage->coupon_id = Coupon::where('code', $couponCode)->first()->id;
            $coupon_usage->save();
        }

        $combined_order->grand_total += $order->grand_total;
        if ($user->balance < $combined_order->grand_total) { // 余额不足
            return [false, '2-insufficient balance', 0];
        }

        /*添加是否开启提货*/
        $order->picking_switch = get_setting('picking_switch');
        $order->payment_status = 'paid';
        $order->payment_details = null;
        $order->save();
        if (get_setting('picking_switch') != 1 ) { //如果不需要提货，直接修改订单为已提货状态
            $shop = $order->shop;
            $shop->admin_to_pay += ( $order->grand_total - $order->product_storehouse_total );
            $shop->save();
            // 保存订单冻结资金过期时间
            $freezeDays = get_setting('frozen_funds_unfrozen_days', 15);
            $order->freeze_expired_at = Carbon::now()->addDays($freezeDays)->timestamp;
            $order->product_storehouse_status = 1;
            $order->save();
        }

        $combined_order->save();

        // 更新用户余额
        DB::table('users')->where('id', $uid)->decrement('balance', $combined_order->grand_total);

        // 订单创建完毕后，执行佣金计算
        $finalOrder = Order::findOrFail($order->id);
        calculateCommissionAffilationClubPoint($finalOrder);

        return [true, '', $order->id];
    }
}
