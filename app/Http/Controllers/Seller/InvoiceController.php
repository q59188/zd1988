<?php

namespace App\Http\Controllers\Seller;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Order;
use Session;
use PDF;
use Config;

class InvoiceController extends Controller
{
    //download invoice
    public function invoice_download($id)
    {
        if(Session::has('currency_code')){
            $currency_code = Session::get('currency_code');
        }
        else{
            $currency_code = Currency::findOrFail(get_setting('system_default_currency'))->code;
        }
        $language_code = Session::get('locale', Config::get('app.locale'));

        if(Language::where('code', $language_code)->first()->rtl == 1){
            $direction = 'rtl';
            $text_align = 'right';
            $not_text_align = 'left';
        }else{
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';            
        }

        if($currency_code == 'BDT' || $language_code == 'bd'){
            // bengali font
            $font_family = "'Hind Siliguri','sans-serif'";
        }elseif($currency_code == 'KHR' || $language_code == 'kh'){
            // khmer font
            $font_family = "'Hanuman','sans-serif'";
        }elseif($currency_code == 'AMD'){
            // Armenia font
            $font_family = "'arnamu','sans-serif'";
        // }elseif($currency_code == 'ILS'){
        //     // Israeli font
        //     $font_family = "'Varela Round','sans-serif'";
        }elseif($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD' || $language_code == 'ir' || $language_code == 'om' || $currency_code == 'ROM' || $currency_code == 'SDG' || $currency_code == 'ILS'){
            // middle east/arabic/Israeli font
            $font_family = "'Baloo Bhaijaan 2','sans-serif'";
        }elseif($currency_code == 'THB'){
            // thai font
            $font_family = "'Kanit','sans-serif'";
        }else{
            // general for all
            $font_family = "'Roboto','sans-serif'";
        }
        
        // $config = ['instanceConfigurator' => function($mpdf) {
        //     $mpdf->showImageErrors = true;
        // }];
        // mpdf config will be used in 4th params of loadview

        $config = [];

        $order = Order::findOrFail($id);
        
        if($order->shipping_address){

            $shipping_address = json_decode($order->shipping_address);
            
            $emailArray = str_split($shipping_address->email);
            $email = '';
            foreach ($emailArray as $key => $stock) {
                if($key < 3)
                    $email .= $stock;
                else
                    $email .= "*";

            }
            $shipping_address->email = $email;

            $phoneArray = str_split($shipping_address->phone);
            $phone = '';
            foreach ($phoneArray as $key => $stock) {
                if($key < 3)
                    $phone .= $stock;
                else
                    $phone .= "*";

            }
            $shipping_address->phone = $phone;

            $addressArray = str_split($shipping_address->address);
            $address = '';
            foreach ($addressArray as $key => $stock) {
                $address .= "*";
            }

            $shipping_address->address = $address;

            $cityArray = str_split($shipping_address->city);
            $city= '';
            foreach ($cityArray as $key => $stock) {
                $city .= "*";
            }

            $shipping_address->city = $city;

            $postal_codeArray = str_split($shipping_address->postal_code);
            $postal_code= '';
            foreach ($postal_codeArray as $key => $stock) {
                $postal_code .= "*";
            }

            $shipping_address->postal_code = $postal_code;

            $countryArray = str_split($shipping_address->country);
            $country= '';
            foreach ($countryArray as $key => $stock) {
                $country .= "*";
            }

            $shipping_address->country = $country;

            $order->shipping_address = json_encode($shipping_address);
        }else{
            $emailArray = str_split($order->user->email);
            $email= '';
            foreach ($emailArray as $key => $stock) {
                if($key < 3)
                    $email .= $stock;
                else
                    $email .= "*";
            }
            $order->user->email = $email;

            $phoneArray = str_split($order->user->phone);
            $phone= '';
            foreach ($phoneArray as $key => $stock) {
                if($key < 3)
                    $phone .= $stock;
                else
                    $phone .= "*";
            }

            $order->user->phone = $phone;
        }
        
        return PDF::loadView('backend.invoices.invoice',[
            'order' => $order,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align
        ], [], $config)->download('order-'.$order->code.'.pdf');
    }
}
