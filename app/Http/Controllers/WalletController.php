<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Shop;
use App\Models\SellerWithdrawRequest;
use Auth;
use Session;
use function array_column;
use function back;
use function date;
use function dd;
use function explode;
use function flash;
use function redirect;
use function strtotime;
use function translate;

class WalletController extends Controller
{
    public function index() {
        $wallets = Wallet::where('user_id', Auth::user()->id)->latest()->paginate(9);

        $user = Auth::user();
        $userId = $user->id;
        $seller_withdraw_requests = SellerWithdrawRequest::where('user_id', $userId)->latest()->paginate(9)
            ->appends([ 'opage' => \request()->opage, 'rpage' => \request()->rpage ]);

        return view('frontend.user.wallet.index', compact('wallets', 'seller_withdraw_requests'));
    }

    public function do_money_withdraw_request( Request $request ) {
        $user = Auth::user();

        /*
        if( $request->w_type == 2 )//银行
        {
            if( empty($user->bank_acc_name) || empty($user->bank_name) || $user->bank_payment_status == 0 || empty($user->bank_acc_no) || empty($user->bank_routing_no))
            {

                 flash(translate('You Should Fill Bank Info'))->error();
               return back();

            }
        }
         if( $request->w_type == 3 )//USDT
        {
            if( empty($user->usdt_address) || empty($user->usdt_type) || $user->usdt_payment_status == 0   )
            {
                 flash(translate('You Should Fill USDT Info'))->error();
                 return back();

            }
        }
        */


        if ( $request->amount > $user->balance )
        {
            flash(translate('You do not have enough balance to send withdraw request'))->error();
            return back();
        }
        $exits = SellerWithdrawRequest::where('status', '0')->where('type', 1)->where('user_id', $user->id)->count();

        if ( $exits !== 0 )
        {
            #flash(translate('withdraw exited'))->error();
            # return back();
        }
        $seller_withdraw_request = new SellerWithdrawRequest;
        $seller_withdraw_request->user_id = $user->id;
        $seller_withdraw_request->amount = $request->amount;
        $seller_withdraw_request->message = $request->message;
        $seller_withdraw_request->status = '0';
        $seller_withdraw_request->viewed = '0';
        $seller_withdraw_request->w_type = $request->w_type;
        $seller_withdraw_request->t_type = 2; //客户

        if ( $seller_withdraw_request->save() )
        {//扣除余额
            $userModel = User::find($user->id);
            $userModel->balance = $user->balance - $request->amount;
            $userModel->save();
            flash(translate('Request has been sent successfully'))->success();
            return redirect()->route('wallet.index');
        }
        else
        {
            flash(translate('Something went wrong'))->error();
            return back();
        }

    }

    public function recharge( Request $request ) {
        $data['amount'] = $request->amount;
        $data['payment_method'] = $request->payment_option;

        $request->session()->put('payment_type', 'wallet_payment');
        $request->session()->put('payment_data', $data);

        $request->session()->put('payment_type', 'wallet_payment');
        $request->session()->put('payment_data', $data);

        $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
        if ( class_exists($decorator) )
        {
            return ( new $decorator )->pay($request);
        }
    }

    public function wallet_payment_done( $payment_data, $payment_details ) {
        $user = Auth::user();
        $user->balance = $user->balance + $payment_data['amount'];
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $payment_data['amount'];
        $wallet->payment_method = $payment_data['payment_method'];
        $wallet->payment_details = $payment_details;
        $wallet->save();

        Session::forget('payment_data');
        Session::forget('payment_type');

        flash(translate('Payment completed'))->success();
        return redirect()->route('wallet.index');
    }

    public function offline_recharge( Request $request ) {
        if (Auth::user()->user_type=='admin'){//如果是管理员充值
            $user = $user = User::where('id', $request->user_id)->first();
            if (!$user) {
                flash(translate('Please select one customer'))->warning();
                return redirect()->route('admin.customers');
            }
            $wallet = new Wallet;
            $wallet->user_id = $user->id;
            $wallet->amount = $request->amount;
            $wallet->payment_method = $request->payment_option;
            $wallet->payment_details = '';
            $wallet->approval = 1;//直接状态为已审核
            $wallet->offline_payment = 1;
            $wallet->reciept = '';
            $wallet->operator_id = Auth::user()->id;
            $wallet->type = $request->type ?? 1;
            $wallet->save();
            $user->balance = $user->balance + $wallet->amount;
            $user->save();
            flash(translate('Recharge has been done'))->success();
            return redirect()->route('customers.index');
        }

        $wallet = new Wallet;
        $wallet->user_id = Auth::user()->id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = $request->payment_option;
        $wallet->payment_details = $request->trx_id;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        $wallet->reciept = $request->photo;
        $wallet->type = $request->type ?? 1;
        $wallet->save();
        flash(translate('Offline Recharge has been done. Please wait for response.'))->success();
        //return redirect()->route('wallet.index');
        return back();
    }

    public function offline_recharge_request( Request $request ) {
        $name = $request->name ?? '';
        $count = $request->count ?? '';
        $operator = $request->operator ?? '';
        $date = $request->date ?? '';
        $wallets = Wallet::where('offline_payment', 1);
        if ($name){
            $users = User::where('name', 'like',"%".$name."%")->get()->toArray();
            $ids = array_column($users, 'id');
            $wallets = $wallets->whereIn('user_id', $ids);
        }

        if ($date) {
            $wallets = $wallets->whereDate('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->whereDate('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
        }

        if(Auth::user()->user_type == 'admin' && $count){
            if($wallets->where('created_at', '>=', date("Y-m-d H:i:s",time()-60))->count()){
                return 1;
            }else{
                return 0;
            }
        }

        if ($operator){
            // dd(Auth::user()->user_type.'sss');
            if ($operator == 'admin'){
                $users = User::where('user_type', 'admin')->get()->toArray();
                $ids = array_column($users, 'id');
                $wallets = $wallets->whereIn('operator_id', $ids);
            }else{
                $users = User::where('name', 'like',"%".$operator."%")->get()->toArray();
                $ids = array_column($users, 'id');
                $wallets = $wallets->whereIn('operator_id', $ids);
            }
        }

        if ( Auth::user()->user_type == 'salesman' )
        {
            $userIds = User::where('pid', Auth::user()->id)->get()->toArray();
            $wallets = Wallet::where('offline_payment', 1)->whereIn('user_id', array_column($userIds, 'id'))->latest()->paginate(10);
            return view('manual_payment_methods.wallet_request_salesman', compact('wallets'));
        }

        $wallets = $wallets->latest()->paginate(10);
        return view('manual_payment_methods.wallet_request', compact('wallets', 'name', 'operator', 'date'));
    }

    public function offline_recharge_request_by_seller( Request $request ) {
//        dd($request->user_id);
        $wallets = Wallet::where('offline_payment', 1)->where('user_id', $request->user_id)->get();
        return view('manual_payment_methods.wallet_request_by_seller', compact('wallets'));
    }

    public function updateApproved( Request $request ) {
        $wallet = Wallet::findOrFail($request->id);
        $wallet->approval = $request->status;
        if ( $request->status == 1 )
        {
            if ( $wallet->type == 2 )
            {
                #print_r( $wallet->user->shop->toArray());
                #$wallet->user->shop->bzj_money+
                $shopid = $wallet->user->shop->id;
                $shop = Shop::findOrFail($shopid);
                $shop->bzj_money = $wallet->user->shop->bzj_money + $wallet->amount;
                $shop->save();

            }
            else
            {
                $user = $wallet->user;
                $user->balance = $user->balance + $wallet->amount;
                $user->save();
                $wallet->operator_id = Auth::user()->id;
            }
        }
        else
        {

            if ( $wallet->type == 2 )
            {
                #print_r( $wallet->user->shop->toArray());
                #$wallet->user->shop->bzj_money+
                $shopid = $wallet->user->shop->id;
                $shop = Shop::findOrFail($shopid);
                $shop->bzj_money = $wallet->user->shop->bzj_money - $wallet->amount;
                $shop->bzj_money = $shop->bzj_money < 0 ? 0 : $shop->bzj_money;
                $shop->save();

            }
            else
            {

                $user = $wallet->user;
                $user->balance = $user->balance - $wallet->amount;
                $user->save();
                $wallet->operator_id = Auth::user()->id;
            }
        }
        if ( $wallet->save() )
        {
            return 1;
        }
        return 0;
    }

    public function updateApproved345( Request $request ) {
        $wallet = Wallet::findOrFail($request->id);
        $wallet->approval = $request->status;
        if ( $request->status == 1 )
        {
            $user = $wallet->user;
            $user->balance = $user->balance + $wallet->amount;
            $user->save();
        }
        else
        {
            $user = $wallet->user;
            $user->balance = $user->balance - $wallet->amount;
            $user->save();
        }
        if ( $wallet->save() )
        {
            return 1;
        }
        return 0;
    }

    /**
     * 更新金额
     * @param Request $request
     * @return int
     */
    public function updateAmount(Request $request)
    {
        $wallet = Wallet::findOrFail($request->id);
        if ($wallet->approval == 1) { // 已审核
            return 2;
        }

        $wallet->amount = $request->amount;
        $wallet->save();
        return 1;
    }
}
