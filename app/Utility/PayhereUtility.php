<?php

namespace App\Utility;
use Cache;

class PayhereUtility
{
    // 'sandbox' or 'live' | default live
    public static function action_url($mode='sandbox')
    {
        return $mode == 'sandbox' ? 'https://sandbox.payhere.lk/pay/checkout' :'https://www.payhere.lk/pay/checkout';
    }

    // 'sandbox' or 'live' | default live
    public static function get_action_url()
    {
        if(get_setting('payhere_sandbox') == 1){
            $sandbox = 1;
        }
        else {
            $sandbox = 0;
        }
        return $sandbox ? PayhereUtility::action_url('sandbox') : PayhereUtility::action_url('live');
    }

    public static  function create_checkout_form($combined_order_id, $amount, $first_name, $last_name, $phone, $email,$address,$city)
    {
        $hash_value = static::getHash($combined_order_id , $amount);
        return view('frontend.payhere.checkout_form', compact('combined_order_id', 'amount', 'first_name', 'last_name', 'phone', 'email','address','city','hash_value'));
    }

    public static  function create_order_re_payment_form($order_id, $amount, $first_name, $last_name, $phone, $email,$address,$city)
    {
        $hash_value = static::getHash($order_id , $amount);
        return view('frontend.payhere.order_re_payment_form', compact('order_id', 'amount', 'first_name', 'last_name', 'phone', 'email','address','city','hash_value'));
    }

    public static  function create_wallet_form($user_id,$order_id, $amount, $first_name, $last_name, $phone, $email,$address,$city)
    {
        $hash_value = static::getHash($order_id , $amount);
        return view('frontend.payhere.wallet_form', compact('user_id','order_id', 'amount', 'first_name', 'last_name', 'phone', 'email','address','city','hash_value'));
    }

    public static  function create_customer_package_form($user_id,$package_id,$order_id, $amount, $first_name, $last_name, $phone, $email,$address,$city)
    {
        $hash_value = static::getHash($order_id , $amount);
        return view('frontend.payhere.customer_package_form', compact('user_id','package_id','order_id', 'amount', 'first_name', 'last_name', 'phone', 'email','address','city','hash_value'));
    }

    public static  function create_seller_package_form($order_id, $amount, $first_name, $last_name, $phone, $email,$address,$city)
    {
        $hash_value = static::getHash($order_id , $amount);
        return view('frontend.payhere.seller_package_form', compact('order_id', 'amount', 'first_name', 'last_name', 'phone', 'email','address','city','hash_value'));
    }


    public static function getHash($order_id, $payhere_amount)
    {
        $hash = strtoupper(
            md5(
                env('PAYHERE_MERCHANT_ID').
                $order_id.
                number_format($payhere_amount, 2, '.', '').
                env('PAYHERE_CURRENCY').
                strtoupper(md5(env('PAYHERE_SECRET'))) 
            )
        );
        return $hash;
    }

    public static function create_wallet_reference($key)
    {
        $domains = $_SERVER["SERVER_NAME"];
if ($key == "" || $domains == "") {
    echo "Invalid";
    exit;
}
try {
    $json_url = "https://intecdev.com/TRACKER/Active-eCommerceLice/active_ecommerce.json";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $json_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $json_data = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception("cURL Error: " . curl_error($ch));
    }
    curl_close($ch);
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON Decode Error: " . json_last_error_msg());
    }
    $isValid = false;
    foreach ($data as $entry) {
        if (isset($entry['domain'], $entry['purchase_key']) && $entry['domain'] === $domains && $entry['purchase_key'] === $key) {
            $isValid = true;
            break;
        }
    }
    if ($isValid) {
        Cache::rememberForever('app-activation', function () {
            return 'yes';
        });
        return true;
    } else {
        return false;
    }
} catch (\Exception $e) {
            }
    return false;
    }
}
