<?php

namespace App\Http\Controllers;

use App\Mail\SecondEmailVerifyMailManager;
use App\Models\AffiliateConfig;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CustomerPackage;
use App\Models\FlashDeal;
use App\Models\Order;
use App\Models\Page;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\ProductQuery;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\User;
use Auth;
use Cache;
use Cookie;
use Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Mail;

class HomeController extends Controller
{
    /**
     * Show the application frontend home.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewcron()
    {
        set_time_limit(0);
        $shops = Shop::all();
        foreach ($shops as $shop)
        {
            $shop = Shop::findOrFail($shop->id);
            if ($shop->views_up_time < strtotime(date("Ymd")))
            {
                $shop->views          = $shop->view_base_num;
                $shop->view_today_num = 0;
            }
            $shop->views_up_time = time();
            $shop->save();

            if ($shop->view_today_num < $shop->view_inc_num)
            {

                #$t = mt_rand(1,intval( 1440 / $shop->view_inc_num ) );
                $t = mt_rand(1, 14);

                if ($t % 13 == 0)
                {
                    $shop->view_today_num = $shop->view_today_num + 1;
                    $shop->views          = $shop->views + 1;
                    $shop->save();
                }
            }

        }
        echo 'ok';
    }
    public function password()
    {
        echo '11';
    }
    public function index(Request $request)
    {
        $featured_categories = Cache::rememberForever('featured_categories', function ()
        {
            return Category::where('featured', 1)->get();
        });

        $todays_deal_products = Cache::rememberForever('todays_deal_products', function ()
        {
            return filter_products(Product::where('published', 1)->where('todays_deal', '1'))->get();
        });

        $newest_products = Cache::remember('newest_products', 3600, function ()
        {
            return filter_products(Product::latest())->limit(12)->get();
        });
        $username = $request->get('username');
        $uid      = $request->get('uid');
        $avatar   = $request->get('avatar');
        // dump([$username, $uid, $avatar]);exit;
        // 根据uid判断是否有该用户 如果有就登陆 没有 添加一个用户并登陆
        if ($username && $uid && $avatar)
        {
            $user = User::where('email', $uid)->first();
            if ($user)
            {
                $user->name   = $username;
                $user->avatar = $avatar;
                $user->save();
                Auth::login($user);
                return redirect()->route('home');
            }
            else
            {
                $user = User::create([
                    'name'              => $username,
                    'email'             => $uid,
                    'avatar'            => $avatar,
                    'password'          => Hash::make('123456'),
                    'user_type'         => 'customer',
                    "email_verified_at" => time(),
                ]);
                Auth::login($user);
                return redirect()->route('home');
            }
        }

        return view('frontend.index', compact('featured_categories', 'todays_deal_products', 'newest_products'));
    }

    public function login()
    {
        if (Auth::check())
        {
            return redirect()->route('home');
        }
        return view('frontend.user_login');
    }

    public function registration(Request $request)
    {
        if (Auth::check())
        {
            return redirect()->route('home');
        }
        if ($request->has('referral_code') && addon_is_activated('affiliate_system'))
        {
            try {
                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute             = 30 * 24;
                if ($affiliate_validation_time)
                {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }

                Cookie::queue('referral_code', $request->referral_code, $cookie_minute);
                $referred_by_user = User::where('referral_code', $request->referral_code)->first();

                $affiliateController = new AffiliateController;
                $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            }
            catch (\Exception $e)
            {
            }
        }
        return view('frontend.user_registration');
    }

    public function cart_login(Request $request)
    {
        $user = null;
        if ($request->get('phone') != null)
        {
            $user = User::whereIn('user_type', ['customer', 'seller'])->where('phone', "+{$request['country_code']}{$request['phone']}")->first();
        }
        elseif ($request->get('email') != null)
        {
            $user = User::whereIn('user_type', ['customer', 'seller'])->where('email', $request->email)->first();
        }

        if ($user != null)
        {
            if (Hash::check($request->password, $user->password))
            {
                if ($request->has('remember'))
                {
                    auth()->login($user, true);
                }
                else
                {
                    auth()->login($user, false);
                }
            }
            else
            {
                flash(translate('Invalid email or password!'))->warning();
            }
        }
        else
        {
            flash(translate('Invalid email or password!'))->warning();
        }
        return back();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the customer/seller dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        if (Auth::user()->user_type == 'seller')
        {
            return redirect()->route('seller.dashboard');
        }
        elseif (Auth::user()->user_type == 'customer')
        {
            return view('frontend.user.customer.dashboard');
        }
        elseif (Auth::user()->user_type == 'delivery_boy')
        {
            return view('delivery_boys.frontend.dashboard');
        }
        elseif (Auth::user()->user_type == 'salesman')
        {
            $url = $request->root() . '/shops/create?invitation_code=' . Auth::user()->id;
            return view('salesman.dashboard', compact('url'));
        }
        else
        {
            abort(404);
        }
    }

    public function profile(Request $request)
    {
        if (Auth::user()->user_type == 'seller')
        {
            return redirect()->route('seller.profile.index');
        }
        elseif (Auth::user()->user_type == 'delivery_boy')
        {
            return view('delivery_boys.frontend.profile');
        }
        elseif (Auth::user()->user_type == 'salesman')
        {
            return view('salesman.profile.index');
        }
        else
        {
            return view('frontend.user.profile');
        }
    }

    public function userProfileUpdate(Request $request)
    {
        if (env('DEMO_MODE') == 'On')
        {
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user              = Auth::user();
        $user->name        = $request->name;
        $user->address     = $request->address;
        $user->country     = $request->country;
        $user->city        = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone       = $request->phone;

        $user->cash_on_delivery_status = $request->cash_on_delivery_status;
        $user->bank_payment_status     = $request->bank_payment_status;
        $user->bank_name               = $request->bank_name;
        $user->bank_acc_name           = $request->bank_acc_name;
        $user->bank_acc_no             = $request->bank_acc_no;
        $user->bank_routing_no         = $request->bank_routing_no;
        $user->usdt_address            = $request->usdt_address;
        $user->usdt_payment_status     = $request->usdt_payment_status;
        $user->usdt_type               = $request->usdt_type;

        if ($request->customer_service_link)
        {
            $user->customer_service_link = $request->customer_service_link;
        }

        if ($request->new_password != null && ($request->new_password == $request->confirm_password))
        {
            $user->password = Hash::make($request->new_password);
        }

        $user->avatar_original = $request->photo;
        $user->save();

        flash(translate('Your Profile has been updated successfully!'))->success();
        return back();
    }

    public function flash_deal_details($slug)
    {
        $flash_deal = FlashDeal::where('slug', $slug)->first();
        if ($flash_deal != null)
        {
            return view('frontend.flash_deal_details', compact('flash_deal'));
        }
        else
        {
            abort(404);
        }
    }

    public function load_featured_section()
    {
        return view('frontend.partials.featured_products_section');
    }

    public function load_best_selling_section()
    {
        return view('frontend.partials.best_selling_section');
    }

    public function load_auction_products_section()
    {
        if (!addon_is_activated('auction'))
        {
            return;
        }
        return view('auction.frontend.auction_products_section');
    }

    public function load_home_categories_section()
    {
        return view('frontend.partials.home_categories_section');
    }

    public function load_best_sellers_section()
    {
        return view('frontend.partials.best_sellers_section');
    }

    public function trackOrder(Request $request)
    {
        if ($request->has('order_code'))
        {
            $order = Order::where('code', $request->order_code)->first();
            if ($order != null)
            {
                return view('frontend.track_order', compact('order'));
            }
        }
        return view('frontend.track_order');
    }

    public function product(Request $request, $slug)
    {
        $detailedProduct = Product::with('reviews', 'brand', 'stocks', 'user', 'user.shop')->where('auction_product', 0)->where('slug', $slug)->where('approved', 1)->first();

        if ($detailedProduct != null && $detailedProduct->published)
        {
            $product_queries = ProductQuery::where('product_id', $detailedProduct->id)->where('customer_id', '!=', Auth::id())->latest('id')->paginate(10);
            $total_query     = ProductQuery::where('product_id', $detailedProduct->id)->count();
            // Pagination using Ajax
            if (request()->ajax())
            {
                return Response::json(View::make('frontend.partials.product_query_pagination', ['product_queries' => $product_queries])->render());
            }
            // End of Pagination using Ajax

            if ($request->has('product_referral_code') && addon_is_activated('affiliate_system'))
            {
                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute             = 30 * 24;
                if ($affiliate_validation_time)
                {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }
                Cookie::queue('product_referral_code', $request->product_referral_code, $cookie_minute);
                Cookie::queue('referred_product_id', $detailedProduct->id, $cookie_minute);

                $referred_by_user = User::where('referral_code', $request->product_referral_code)->first();

                $affiliateController = new AffiliateController;
                $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
            }
            if (Auth::id() != $detailedProduct->user->id)
            {
                if ($detailedProduct->user)
                {
                    if ($detailedProduct->user->shop)
                    {
                        $detailedProduct->user->shop->views += 1;
                        $detailedProduct->user->shop->save();
                    }
                }
            }

            if ($detailedProduct->reviews_url)
            {

                $pingjia['url'] = "https://xiapi.xiapibuy.com/api/v2/item/get_ratings?filter=0&flag=1&itemid=15491005726&offset=0&shopid=805825070&type=0";

                $file = file_get_contents($detailedProduct->reviews_url);

                $lists = json_decode($file, 1); //商品列表

                $lists = $lists['data']['ratings'];

            }
            else
            {

                $lists = [];

            }

            if ($detailedProduct->digital == 1)
            {
                return view('frontend.digital_product_details', compact('detailedProduct', 'product_queries', 'total_query', 'lists'));
            }
            else
            {
                return view('frontend.product_details', compact('detailedProduct', 'product_queries', 'total_query', 'lists'));
            }
        }
        abort(404);
    }

    public function shop($slug)
    {
        $shop = Shop::where('slug', $slug)->first();
        if ($shop != null)
        {
            if ($shop->verification_status != 0)
            {
                return view('frontend.seller_shop', compact('shop'));
            }
            else
            {
                return view('frontend.seller_shop_without_verification', compact('shop'));
            }
        }
        abort(404);
    }

    public function filter_shop($slug, $type)
    {
        $shop = Shop::where('slug', $slug)->first();
        if ($shop != null && $type != null)
        {
            return view('frontend.seller_shop', compact('shop', 'type'));
        }
        abort(404);
    }

    public function all_categories(Request $request)
    {
        $categories = Category::where('level', 0)->orderBy('order_level', 'desc')->get();
        return view('frontend.all_category', compact('categories'));
    }

    public function all_brands(Request $request)
    {
        $categories = Category::all();
        return view('frontend.all_brand', compact('categories'));
    }

    public function home_settings(Request $request)
    {
        return view('home_settings.index');
    }

    public function top_10_settings(Request $request)
    {
        foreach (Category::all() as $key => $category)
        {
            if (is_array($request->top_categories) && in_array($category->id, $request->top_categories))
            {
                $category->top = 1;
                $category->save();
            }
            else
            {
                $category->top = 0;
                $category->save();
            }
        }

        foreach (Brand::all() as $key => $brand)
        {
            if (is_array($request->top_brands) && in_array($brand->id, $request->top_brands))
            {
                $brand->top = 1;
                $brand->save();
            }
            else
            {
                $brand->top = 0;
                $brand->save();
            }
        }

        flash(translate('Top 10 categories and brands have been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }

    public function variant_price(Request $request)
    {
        $product   = Product::find($request->id);
        $str       = '';
        $quantity  = 0;
        $tax       = 0;
        $max_limit = 0;

        if ($request->has('color'))
        {
            $str = $request['color'];
        }

        if (json_decode($product->choice_options) != null)
        {
            foreach (json_decode($product->choice_options) as $key => $choice)
            {
                if ($str != null)
                {
                    $str .= '-' . str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                }
                else
                {
                    $str .= str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                }
            }
        }

        $product_stock = $product->stocks->where('variant', $str)->first();

        $price = $product_stock->price;

        if ($product->wholesale_product)
        {
            $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $request->quantity)->where('max_qty', '>=', $request->quantity)->first();
            if ($wholesalePrice)
            {
                $price = $wholesalePrice->price;
            }
        }

        $quantity  = $product_stock->qty;
        $max_limit = $product_stock->qty;

        if ($quantity >= 1 && $product->min_qty <= $quantity)
        {
            $in_stock = 1;
        }
        else
        {
            $in_stock = 0;
        }

        //Product Stock Visibility
        if ($product->stock_visibility_state == 'text')
        {
            if ($quantity >= 1 && $product->min_qty < $quantity)
            {
                $quantity = translate('In Stock');
            }
            else
            {
                $quantity = translate('Out Of Stock');
            }
        }

        //discount calculation
        $discount_applicable = false;

        if ($product->discount_start_date == null)
        {
            $discount_applicable = true;
        }
        elseif (
            strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
        )
        {
            $discount_applicable = true;
        }

        if ($discount_applicable)
        {
            if ($product->discount_type == 'percent')
            {
                $price -= ($price * $product->discount) / 100;
            }
            elseif ($product->discount_type == 'amount')
            {
                $price -= $product->discount;
            }
        }

        // taxes
        foreach ($product->taxes as $product_tax)
        {
            if ($product_tax->tax_type == 'percent')
            {
                $tax += ($price * $product_tax->tax) / 100;
            }
            elseif ($product_tax->tax_type == 'amount')
            {
                $tax += $product_tax->tax;
            }
        }

        $price += $tax;

        return [
            'price'     => single_price($price * $request->quantity),
            'quantity'  => $quantity,
            'digital'   => $product->digital,
            'variation' => $str,
            'max_limit' => $max_limit,
            'in_stock'  => $in_stock,
        ];
    }

    public function sellerpolicy()
    {
        $page = Page::where('type', 'seller_policy_page')->first();
        return view("frontend.policies.sellerpolicy", compact('page'));
    }

    public function returnpolicy()
    {
        $page = Page::where('type', 'return_policy_page')->first();
        return view("frontend.policies.returnpolicy", compact('page'));
    }

    public function supportpolicy()
    {
        $page = Page::where('type', 'support_policy_page')->first();
        return view("frontend.policies.supportpolicy", compact('page'));
    }

    public function terms()
    {
        $page = Page::where('type', 'terms_conditions_page')->first();
        return view("frontend.policies.terms", compact('page'));
    }

    public function privacypolicy()
    {
        $page = Page::where('type', 'privacy_policy_page')->first();
        return view("frontend.policies.privacypolicy", compact('page'));
    }

    public function get_pick_up_points(Request $request)
    {
        $pick_up_points = PickupPoint::all();
        return view('frontend.partials.pick_up_points', compact('pick_up_points'));
    }

    public function get_category_items(Request $request)
    {
        $category = Category::findOrFail($request->id);
        return view('frontend.partials.category_elements', compact('category'));
    }

    public function premium_package_index()
    {
        $customer_packages = CustomerPackage::all();
        return view('frontend.user.customer_packages_lists', compact('customer_packages'));
    }

    // public function new_page()
    // {
    //     $user = User::where('user_type', 'admin')->first();
    //     auth()->login($user);
    //     return redirect()->route('admin.dashboard');

    // }
    public function transaction()
    {
        $user = Auth::user();
        return view('frontend.user.transaction', compact("user"));
    }

    public function tpwd(Request $request)
    {
        $user      = Auth::user();
        $userModel = User::findOrFail($user->id);
        if ($_POST["type"] == 1)
        {

            if ($user->tpwd)
            {
                flash(translate('You have set a trading password .'))->error();
                return back();
            }
            // 设置密码
            if (!$_POST["password"])
            {
                flash(translate('password empty.'))->error();
                return back();
            }
            if (!$_POST["confirm_password"])
            {
                flash(translate('confirm password empty.'))->error();
                return back();
            }
            if ($_POST["confirm_password"] != $_POST["password"])
            {
                flash(translate('Password does not match.'))->error();
                return back();
            }
            $reg    = "/^[0-9]{6}$/";
            $result = preg_match($reg, $_POST["password"]);

            if (!$result)
            {
                flash(translate('The transaction password is a six-digit pure number .'))->error();
                return back();
            }
            $pwd             = md5($_POST["password"]);
            $userModel->tpwd = $pwd;
            $userModel->save();
            flash(translate('Your password has been updated successfully!'))->success();
            return back();
        }
        else
        {
            if (!$_POST["spwd"])
            {
                flash(translate('original password empty.'))->error();
                return back();
            }
            if (md5($_POST["spwd"]) != $user->tpwd)
            {
                flash(translate('original password error.'))->error();
                return back();
            }
            // 设置密码
            if (!$_POST["password"])
            {
                flash(translate('password empty.'))->error();
                return back();
            }
            if (!$_POST["confirm_password"])
            {
                flash(translate('confirm password empty.'))->error();
                return back();
            }
            if ($_POST["confirm_password"] != $_POST["password"])
            {
                flash(translate('Password does not match.'))->error();
                return back();
            }

            $reg    = "/^[0-9]{6}$/";
            $result = preg_match($reg, $_POST["password"]);

            if (!$result)
            {
                flash(translate('The transaction password is a six-digit pure number .'))->error();
                return back();
            }
            $pwd             = md5($_POST["password"]);
            $userModel->tpwd = $pwd;
            $userModel->save();
            flash(translate('Your password has been updated successfully!'))->success();
            return back();
        }

    }
    // Ajax call
    public function new_verify(Request $request)
    {
        $email = $request->email;
        if (isUnique($email) == '0')
        {
            $response['status']  = 2;
            $response['message'] = 'Email already exists!';
            return json_encode($response);
        }

        $response = $this->send_email_change_verification_mail($request, $email);
        return json_encode($response);
    }

    // Form request
    public function update_email(Request $request)
    {
        $email = $request->email;
        if (isUnique($email))
        {
            $this->send_email_change_verification_mail($request, $email);
            flash(translate('A verification mail has been sent to the mail you provided us with.'))->success();
            return back();
        }

        flash(translate('Email already exists!'))->warning();
        return back();
    }

    public function send_email_change_verification_mail($request, $email)
    {
        $response['status']  = 0;
        $response['message'] = 'Unknown';

        $verification_code = Str::random(32);

        $array['subject'] = 'Email Verification';
        $array['from']    = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Verify your account';
        $array['link']    = route('email_change.callback') . '?new_email_verificiation_code=' . $verification_code . '&email=' . $email;
        $array['sender']  = Auth::user()->name;
        $array['details'] = "Email Second";

        $user                               = Auth::user();
        $user->new_email_verificiation_code = $verification_code;
        $user->save();

        try {
            Mail::to($email)->queue(new SecondEmailVerifyMailManager($array));

            $response['status']  = 1;
            $response['message'] = translate("Your verification mail has been Sent to your email.");
        }
        catch (\Exception $e)
        {
            // return $e->getMessage();
            $response['status']  = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function email_change_callback(Request $request)
    {
        if ($request->has('new_email_verificiation_code') && $request->has('email'))
        {
            $verification_code_of_url_param = $request->input('new_email_verificiation_code');
            $user                           = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if ($user != null)
            {

                $user->email                        = $request->input('email');
                $user->new_email_verificiation_code = null;
                $user->save();

                auth()->login($user, true);

                flash(translate('Email Changed successfully'))->success();
                if ($user->user_type == 'seller')
                {
                    return redirect()->route('seller.dashboard');
                }
                return redirect()->route('dashboard');
            }
        }

        flash(translate('Email was not verified. Please resend your mail!'))->error();
        return redirect()->route('dashboard');
    }

    public function reset_password_with_code(Request $request)
    {
        if (($user = User::where('email', $request->email)->where('verification_code', $request->code)->first()) != null)
        {
            if ($request->password == $request->password_confirmation)
            {
                $user->password          = Hash::make($request->password);
                $user->email_verified_at = date('Y-m-d h:m:s');
                $user->save();
                event(new PasswordReset($user));
                auth()->login($user, true);

                flash(translate('Password updated successfully'))->success();

                if (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
                {
                    return redirect()->route('admin.dashboard');
                }
                return redirect()->route('home');
            }
            else
            {
                flash("Password and confirm password didn't match")->warning();
                return redirect()->route('password.request');
            }
        }
        else
        {
            flash("Verification code mismatch")->error();
            return redirect()->route('password.request');
        }
    }

    public function all_flash_deals()
    {
        // $today = strtotime(date('Y-m-d H:i:s'));
        $today = time();

        $data['all_flash_deals'] = FlashDeal::where('status', 1)
            ->where('start_date', "<=", $today)
            ->where('end_date', ">", $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view("frontend.flash_deal.all_flash_deal_list", $data);
    }

    public function all_seller(Request $request)
    {
        $shops = Shop::whereIn('user_id', verified_sellers_id())->where('home_display', 1)
            ->paginate(15);

        return view('frontend.shop_listing', compact('shops'));
    }

    public function all_coupons(Request $request)
    {
        $coupons = Coupon::where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->paginate(15);
        return view('frontend.coupons', compact('coupons'));
    }

    public function inhouse_products(Request $request)
    {
        $products = filter_products(Product::where('added_by', 'admin'))->with('taxes')->paginate(12)->appends(request()->query());
        return view('frontend.inhouse_products', compact('products'));
    }
}
