<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Models\CombinedOrder;
use App\Models\PaymentMethod;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\SellerPackage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Session;
use PaytmWallet;
use Auth;

class PaytmController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:asian_payment_gateway_configuration'])->only('credentials_index');
    }

    public function pay(){
        $user = Auth::user();

        if ($user->phone == null) {
            flash('Please add phone number to your profile')->warning();
            return redirect()->route('profile');
        }
        if(Session::has('payment_type')){
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');
            
            $transaction = new Transaction;
            $transaction->user_id = $user->id;
            $transaction->gateway = 'paytm';
            $transaction->payment_type = $paymentType;
            $transaction->additional_content = json_encode($paymentData);
            $transaction->save();

            if($paymentType == 'cart_payment'){
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = $combined_order->grand_total;
                $payment = PaytmWallet::with('receive');
                $payment->prepare([
					'order' => $transaction->id,
					'user' => $combined_order->id,
					'mobile_number' => $user->phone,
					'email' => $user->email != null ? $user->email : 'customer@example.com',
					'amount' => $amount,
					'callback_url' => route('paytm.callback')
                ]);
                return $payment->receive();
            }
            elseif($paymentType == 'order_re_payment'){
                $order = Order::findOrFail($paymentData['order_id']);
                $amount = $order->grand_total;
                $payment = PaytmWallet::with('receive');
                $payment->prepare([
					'order' => $transaction->id,
					'user' => $user->id,
					'mobile_number' => $user->phone,
					'email' => $user->email != null ? $user->email : 'customer@example.com',
					'amount' => $amount,
					'callback_url' => route('paytm.callback')
                ]);
                return $payment->receive();
            }
            elseif ($paymentType == 'wallet_payment') {
                if($user->phone != null){
                    $amount= $paymentData['amount'];
                    $payment = PaytmWallet::with('receive');
                    $payment->prepare([
                      'order' => $transaction->id,
                      'user' => $user->id,
                      'mobile_number' => $user->phone,
                      'email' => $user->email != null ? $user->email : 'customer@example.com',
                      'amount' => $amount,
                      'callback_url' => route('paytm.callback')
                    ]);
                    return $payment->receive();
                }
                else {
                    flash('Please add phone number to your profile')->warning();
                    return back();
                }
            }
            elseif($paymentType == 'customer_package_payment'){
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $amount = $customer_package->amount;
                $payment = PaytmWallet::with('receive');
                $payment->prepare([
					'order' => $transaction->id,
					'user' => $user->id,
					'mobile_number' => $user->phone,
					'email' => $user->email != null ? $user->email : 'customer@example.com',
					'amount' => $amount,
					'callback_url' => route('paytm.callback')
                ]);
                return $payment->receive();
            }
            elseif($paymentType == 'seller_package_payment'){
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);
                $amount = $seller_package->amount;
                $payment = PaytmWallet::with('receive');
                $payment->prepare([
					'order' => $transaction->id,
					'user' => $user->id,
					'mobile_number' => $user->phone,
					'email' => $user->email != null ? $user->email : 'customer@example.com',
					'amount' => $amount,
					'callback_url' => route('paytm.callback')
                ]);
                return $payment->receive();
            }
        }
    }

    public function callback(Request $request){
        $transaction = PaytmWallet::with('receive');

        $response = $transaction->response(); // To get raw response as array
        //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=interpreting-response-sent-by-paytm

        if($transaction->isSuccessful()){
            $transaction = Transaction::findOrFail($response['ORDERID']);
            if($transaction->payment_type == 'cart_payment'){
                Auth::login(User::findOrFail($transaction->user_id));
                return (new CheckoutController)->checkout_done(json_decode($transaction->additional_content)->combined_order_id, json_encode($response));
            }
            elseif($transaction->payment_type == 'order_re_payment'){
                Auth::login(User::findOrFail($transaction->user_id));
                return (new CheckoutController)->orderRePaymentDone(json_decode($transaction->additional_content, true), json_encode($response));
            }
            elseif ($transaction->payment_type == 'wallet_payment') {
                Auth::login(User::findOrFail($transaction->user_id));
                return (new WalletController)->wallet_payment_done(json_decode($transaction->additional_content, true), json_encode($response));
            }
            elseif ($transaction->payment_type == 'customer_package_payment') {
                Auth::login(User::findOrFail($transaction->user_id));
                return (new CustomerPackageController)->purchase_payment_done(json_decode($transaction->additional_content, true), json_encode($response));
            }
            elseif ($transaction->payment_type == 'seller_package_payment') {
                Auth::login(User::findOrFail($transaction->user_id));
                return (new SellerPackageController)->purchase_payment_done(json_decode($transaction->additional_content, true), json_encode($response));
            }
        }else if($transaction->isFailed()){
            $request->session()->forget('combined_order_id');
            $request->session()->forget('payment_data');
            flash(translate('Payment cancelled'))->error();
        	return back();
        }else if($transaction->isOpen()){
          //Transaction Open/Processing
        }
        $transaction->getResponseMessage(); //Get Response Message If Available
        //get important parameters via public methods
        $transaction->getOrderId(); // Get order id
        $transaction->getTransactionId(); // Get transaction id
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function credentials_index()
    {
        $payment_methods = PaymentMethod::where('addon_identifier', 'paytm')->get();
        return view('paytm.index', compact('payment_methods'));
    }

    /**
     * Update the specified resource in .env
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_credentials(Request $request)
    {
        foreach ($request->types as $key => $type) {
                $this->overWriteEnvFile($type, $request[$type]);
        }

        flash("Settings updated successfully")->success();
        return back();
    }

    /**
    *.env file overwrite
    */
    public function overWriteEnvFile($type, $val)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $val = '"'.trim($val).'"';
            if(is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0){
                file_put_contents($path, str_replace(
                    $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
                ));
            }
            else{
                file_put_contents($path, file_get_contents($path)."\r\n".$type.'='.$val);
            }
        }
    }
}
