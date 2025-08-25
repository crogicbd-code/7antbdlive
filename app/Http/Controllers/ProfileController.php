<?php

namespace DownGrade\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use DownGrade\Models\Members;
use DownGrade\Models\Settings;
use DownGrade\Models\Subscription;
use DownGrade\Models\Category;
use DownGrade\Models\Causes;
use DownGrade\Models\EmailTemplate;
use Auth;
use Mail;
use Paystack;
use Currency;
use Razorpay\Api\Api;
use CoinGate\CoinGate;
use Cache;
use MercadoPago;
use Cashfree;
use GuzzleHttp\Client;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function __construct()
    {
        $this->middleware('auth');
    }
    
	
	public function view_myreferral()
	{
	
	  
	  return view('my-referral');
	  
	
	}	
    	
	public function view_myprofile()
	{
	
	  $countries = Settings::allCountry();
	  return view('my-profile', [ 'countries' => $countries ]);
	  
	
	}
	
	
	
	
	
	public function view_withdrawal_request()
	{
	  $withdraw_option = array('paypal','stripe');
	  $user_id = Auth::user()->id;
	  $withdrawData['view'] = Causes::getdrawalData($user_id);
	  $data = array('withdraw_option' => $withdraw_option, 'withdrawData' => $withdrawData);
	  return view('withdrawal-request')->with($data);
	}
	
	
	
	public function withdrawal_request(Request $request)
	{
	   $withdrawal = $request->input('withdrawal');
	   $paypal_email = $request->input('paypal_email');
	   $stripe_email = $request->input('stripe_email');
	   $available_balance = base64_decode($request->input('available_balance'));
	   $get_amount = $request->input('get_amount');
	   $user_id = $request->input('user_id');
	   $token = $request->input('user_token');
	   $wd_data = date('Y-m-d');
	   $wd_status = "pending";
	   
	   $drawal_data = array('wd_user_id' => $user_id, 'withdraw_type' => $withdrawal, 'paypal_email' => $paypal_email, 'stripe_email' => $stripe_email, 'wd_amount' => $get_amount, 'wd_status' => $wd_status, 'wd_date' => $wd_data);
	   if($available_balance > $get_amount)
	   {
	     Causes::savedrawalData($drawal_data);
		 $less_amount = $available_balance - $get_amount;
		 $data = array('earnings' => $less_amount);
		 Members::updateData($token,$data);
		 $check_email_status = Members::getuserSubscription($user_id);
		 if($check_email_status == 1)
		 {
			 $sid = 1;
			 $setting['setting'] = Settings::editGeneral($sid);
			 $admin_name = $setting['setting']->sender_name;
			 $admin_email = $setting['setting']->sender_email;
			 $currency = $setting['setting']->site_currency_symbol;
			 $user['details'] = Members::singlebuyerData($user_id);
			 $from_name = $user['details']->name;
			 $from_email = $user['details']->email;
			 $record = array('from_name' => $from_name, 'from_email' => $from_email, 'withdrawal' => $withdrawal, 'paypal_email' => $paypal_email, 'stripe_email' => $stripe_email, 'get_amount' => $get_amount, 'currency' => $currency);
			 Mail::send('withdrawal_mail', $record, function($message) use ($admin_name, $admin_email, $from_name, $from_email) {
					$message->to($admin_email, $admin_name)
							->subject('Withdrawal Request');
					$message->from($from_email,$from_name);
				});
		 }	 
		 
		 return redirect()->back()->with('success', 'Your withdrawal request has been sent');
	   }
	   else
	   {
	     return redirect()->back()->with('error', 'Sorry Please check your available balance');
	   }
	   
	   
	   
	}
	
	
	
	public function my_raised_funds_delete($id)
	{
	   $donor_id = base64_decode($id);
	   Causes::deleteDonor($donor_id);
	   return redirect()->back()->with('success','Delete successfully.');
	}
	
	
	public function view_donate_details($id)
	{
	  $donor_id = base64_decode($id);
	  $single['view'] = Causes::singleDonor($donor_id);
	  return view('fund-details', ['single' => $single]);
	}
	
	
	
	public function view_addcauses()
	{
	  $category['view'] = Category::quickbookData();
	  return view('add-causes',['category' => $category]);
	  
	}
	
	public function delete_mycauses($id)
	{
	  $data = array('cause_drop_status' => 'yes');
	  $user_id = Auth::user()->id;
	  Causes::dropCausesphoto($id,$user_id,$data);
	  return redirect()->back()->with('success', 'Delete successfully.'); 
	
	}
	
	
	
	public function view_editcauses($id)
	{
	  $user_id = Auth::user()->id; 
	  $category['view'] = Category::quickbookData();
	  $edit['view'] = Causes::singleCauses($user_id,$id);
	  return view('edit-causes',['category' => $category, 'edit' => $edit]);
	}
	
	
	public function update_edit_causes(Request $request)
	{
	
	   $cause_title = $request->input('cause_title');
	   $cause_slug = $this->cause_slug($cause_title);
	   $cause_short_desc = $request->input('cause_short_desc');
	   $cause_desc = $request->input('cause_desc');
	   $cause_goal = $request->input('cause_goal');
	   $cat_id = $request->input('cat_id');
	   $image_size = $request->input('image_size');
	   $user_id = $request->input('user_id');
	   $cause_token = $this->generateRandomString();
	   $allsettings = Settings::allSettings();
	   $causes_approval = $allsettings->causes_approval;
	   $cause_raised = 0;
	   $save_cause_image = $request->input('save_cause_image');
	   $cause_token = $request->input('cause_token'); 
	   
	   if($causes_approval == 1)
	   {
	      $cause_status = 1;
		  $cause_approve_status = "Thanks for your submission. Your cause updated successfully.";
	   }
	   else
	   {
	      $cause_status = 0;
		  $cause_approve_status = "Thanks for your submission. Once admin will approved your cause. will publish on our website.";
	   }
	   
	   
	   $request->validate([
							'cause_title' => 'required',
							'cause_short_desc' => 'required',
							'cause_desc' => 'required',
							'cause_goal' => 'required',
							'cause_image' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							
							
         ]);
		 $rules = array(
				
				'cause_title' => ['required',  Rule::unique('causes') ->ignore($cause_token, 'cause_token') -> where(function($sql){ $sql->where('cause_drop_status','=','no');})],
				
				
				
	     );
		 
		 $messsages = array(
		      
	    );
		 
		$validator = Validator::make($request->all(), $rules,$messsages);
		
		if ($validator->fails()) 
		{
		 $failedRules = $validator->failed();
		 return back()->withErrors($validator);
		} 
		else
		{
	        
		  
			   if ($request->hasFile('cause_image')) 
				  {
				    Causes::dropCauseimage($cause_token);
					$image = $request->file('cause_image');
					$img_name = time() . '.'.$image->getClientOriginalExtension();
					$destinationPath = public_path('/storage/causes');
					$imagePath = $destinationPath. "/".  $img_name;
					$image->move($destinationPath, $img_name);
					$cause_image = $img_name;
				  }
				  else
				  {
					 $cause_image = $save_cause_image;
				  }
			   
			   $data = array('cat_id' => $cat_id, 'cause_token' => $cause_token, 'cause_title' => $cause_title, 'cause_slug' => $cause_slug, 'cause_short_desc' => $cause_short_desc,'cause_desc' => $cause_desc, 'cause_goal' => $cause_goal, 'cause_image' => $cause_image, 'cause_status' => $cause_status, 'cause_raised' => $cause_raised);
			   
			   Causes::updatecausesData($cause_token,$user_id,$data);
			   
			   return redirect('/my-causes')->with('success', $cause_approve_status);
			 
			   
			   
		}
		
		
		
		
			   
	
	}
	
	
	public function view_subscription_coupon(Request $request)
	{
	   $user_id = Auth::user()->id;
	   $allsettings = Settings::allSettings();
	   $coupon = $request->input('coupon');
	   $subscr_id = base64_decode($request->input('id'));
	   $subscr_details = Subscription::getSubscription($subscr_id);
	   $coupon_usage_type = "subscription";
	   $check_coupon = Members::checkCoupon($coupon,$coupon_usage_type);
	   if($check_coupon == 1)
	   {
	      $single = Members::singleCoupon($coupon,$coupon_usage_type);
	      $coupondata['get'] = Members::getSubCoupon($coupon,$coupon_usage_type);
		  $coupon_id = $single->coupon_id;
		  $coupon_code = $single->coupon_code;
		  $coupon_type = $single->discount_type;
		  $coupon_value = $single->coupon_value;
		  $price = $subscr_details->subscr_price;
		  if($coupon_type == 'percentage')
		  {
			 $discount = ($coupon_value * $price) / 100;
			 $discount_price = $price - $discount;
			 $data = array('user_coupon_id' => $coupon_id, 'user_coupon_code' => $coupon_code, 'user_coupon_type' => $coupon_type, 'user_coupon_value' => $coupon_value, 'user_discount_price' => $discount_price);
			 Members::updateSubCoupon($user_id,$data);
		   }
		   else
		   {
			    if($coupon_value < $price)
				{
			     $discount = $coupon_value;
				 $discount_price = $price - $discount;
				 $data = array('user_coupon_id' => $coupon_id, 'user_coupon_code' => $coupon_code, 'user_coupon_type' => $coupon_type, 'user_coupon_value' => $coupon_value, 'user_discount_price' => $discount_price);
			 Members::updateSubCoupon($user_id,$data);
				}
				else
				{
				 $discount = 0; 
				 return redirect()->back()->with('error', 'Invalid Coupon Code or Expired');
				}
		    }
			
		  
		  return redirect()->back()->with('success', 'Coupon Added Successfully.');
	   }
	   else
	   {
	      return redirect()->back()->with('error', 'Invalid Coupon Code or Expired');
	   }
	
	}
	
	public function remove_coupon($id)
	{  
	   $rid = base64_decode($id);
	   $data = array('user_coupon_id' => '', 'user_coupon_code' => '', 'user_coupon_type' => '', 'user_coupon_value' => '', 'user_discount_price' => 0);
	   Members::updateprofileData($rid,$data);
	   return redirect()->back()->with('success', 'Coupon Removed Successfully.');
	}
	
	public function upgrade_subscription($id)
	{
	  $subscr_id = base64_decode($id);
	  $subscr['view'] = Subscription::getSubscription($subscr_id);
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  $user_id = Auth::user()->id;
	  $user_details = Members::singlebuyerData($user_id);
	  $get_payment = explode(',', $setting['setting']->payment_option);
	  $stripe_type = $setting['setting']->stripe_type;
	  $stripe_mode = $setting['setting']->stripe_mode;
	  if($stripe_mode == 0)
	  {
	     $stripe_publish = $setting['setting']->test_publish_key;
		 $stripe_secret = $setting['setting']->test_secret_key;
	  }
	  else
	  {
	     $stripe_publish = $setting['setting']->live_publish_key;
		 $stripe_secret = $setting['setting']->live_secret_key;
	  }
	   return view('confirm-subscription', ['id' => $id, 'subscr' => $subscr, 'get_payment' => $get_payment, 'user_details' => $user_details, 'stripe_publish' => $stripe_publish, 'stripe_secret' => $stripe_secret, 'stripe_type' => $stripe_type]);
	}
	
	
	
	public function cause_slug($string){
		   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
		   return $slug;
    }
	
	
	public function generateRandomString($length = 25) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
    }
	
	public function save_add_causes(Request $request)
	{
	
	   $cause_title = $request->input('cause_title');
	   $cause_slug = $this->cause_slug($cause_title);
	   $cause_short_desc = $request->input('cause_short_desc');
	   $cause_desc = $request->input('cause_desc');
	   $cause_goal = $request->input('cause_goal');
	   $cat_id = $request->input('cat_id');
	   $image_size = $request->input('image_size');
	   $user_id = $request->input('user_id');
	   $cause_token = $this->generateRandomString();
	   $allsettings = Settings::allSettings();
	   $causes_approval = $allsettings->causes_approval;
	   $cause_raised = 0;
	   $user_subscr_causes = $request->input('user_subscr_causes');
	   $count_causes = Causes::countCauses($user_id);
	   
	   if($causes_approval == 1)
	   {
	      $cause_status = 1;
		  $cause_approve_status = "Thanks for your submission. Your cause updated successfully.";
	   }
	   else
	   {
	      $cause_status = 0;
		  $cause_approve_status = "Thanks for your submission. Once admin will approved your cause. will publish on our website.";
	   }
	   
	   
	   $request->validate([
							'cause_title' => 'required',
							'cause_short_desc' => 'required',
							'cause_desc' => 'required',
							'cause_goal' => 'required',
							'cause_image' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							
							
         ]);
		 $rules = array(
				
				'cause_title' => ['required',  Rule::unique('causes') -> where(function($sql){ $sql->where('cause_drop_status','=','no');})],
				
				
	     );
		 
		 $messsages = array(
		      
	    );
		 
		$validator = Validator::make($request->all(), $rules,$messsages);
		
		if ($validator->fails()) 
		{
		 $failedRules = $validator->failed();
		 return back()->withErrors($validator);
		} 
		else
		{
	        if($user_subscr_causes > $count_causes)
			{
		  
			   if ($request->hasFile('cause_image')) 
				  {
					$image = $request->file('cause_image');
					$img_name = time() . '.'.$image->getClientOriginalExtension();
					$destinationPath = public_path('/storage/causes');
					$imagePath = $destinationPath. "/".  $img_name;
					$image->move($destinationPath, $img_name);
					$cause_image = $img_name;
				  }
				  else
				  {
					 $cause_image = "";
				  }
			   
			   $data = array('cat_id' => $cat_id, 'cause_token' => $cause_token, 'cause_title' => $cause_title, 'cause_slug' => $cause_slug, 'cause_short_desc' => $cause_short_desc,'cause_desc' => $cause_desc, 'cause_goal' => $cause_goal, 'cause_image' => $cause_image, 'cause_status' => $cause_status, 'cause_raised' => $cause_raised, 'cause_user_id' => $user_id);
			   
			   Causes::savecausesData($data);
			   
			   return redirect('/my-causes')->with('success', $cause_approve_status);
			}
			else
			{
			   return redirect('/my-causes')->with('error', 'Sorry!! Your causes limit reached.');
			} 
			   
			   
		}
		
		
		
		
			   
	
	}
	
	
	
	public function update_subscription(Request $request)
	{
	   $encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	   $custom_settings = Settings::editCustom();
	   $user_subscr_id = $request->input('user_subscr_id');
	   $subscription_details = Subscription::editsubData($user_subscr_id);
	   $token = $request->input('token');
	   if(Auth::user()->user_coupon_id != "")
	   {
	   $price = round(Auth::user()->user_discount_price,2);
	   
	   
	   }
	   else
	   {
	   $price = round($subscription_details->subscr_price,2);
	   }
	   $user_id = Auth::user()->id;
	   $user_name = Auth::user()->name;
	   $order_email = Auth::user()->email;
	   $purchase_token = rand(111111,999999);
	   $payment_method = $request->input('payment_method');
	   $user_subscr_type = $request->input('user_subscr_type');
	   $user_subscr_date = $request->input('user_subscr_date');
	   $user_subscr_item_level = $request->input('user_subscr_item_level');
	   $user_subscr_item = $request->input('user_subscr_item');
	   $website_url = $request->input('website_url');
	   $subscr_value = "+".$user_subscr_date;
	   $subscr_date = date('Y-m-d', strtotime($subscr_value));
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $admin_amount = $price;
	   $payment_status = 'pending';
	   if($payment_method == 'localbank')
	   {
	   $updatedata = array('user_subscr_price' => $price, 'user_subscr_id' => $user_subscr_id, 'user_purchase_token' => $purchase_token, 'user_subscr_payment_type' => $payment_method, 'user_subscr_payment_status' => $payment_status, 'user_coupon_id' => '');
	   }
	   else
	   {
	   $updatedata = array('user_subscr_price' => $price, 'user_subscr_id' => $user_subscr_id, 'user_subscr_payment_type' => $payment_method, 'user_subscr_payment_status' => $payment_status, 'user_coupon_id' => '');
	   }
	   
	   
	   
	   /* settings */
	   
	   $paypal_email = $setting['setting']->paypal_email;
	   $paypal_mode = $setting['setting']->paypal_mode;
	   $site_currency = $setting['setting']->site_currency_code;
	   if($paypal_mode == 1)
	   {
	     $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
	   }
	   else
	   {
	     $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	   }
	   $success_url = $website_url.'/subscription-success/'.$purchase_token;
	   $cancel_url = $website_url.'/cancel';
	   
	   $stripe_mode = $setting['setting']->stripe_mode;
	   if($stripe_mode == 0)
	   {
	     $stripe_publish_key = $setting['setting']->test_publish_key;
		 $stripe_secret_key = $setting['setting']->test_secret_key;
	   }
	   else
	   {
	     $stripe_publish_key = $setting['setting']->live_publish_key;
		 $stripe_secret_key = $setting['setting']->live_secret_key;
	   }
	   
	   $payhere_success_url = $website_url.'/subscription-payhere/'.$purchase_token;
	   
	   /* flutterwave */
	   $flutterwave_public_key = $setting['setting']->flutterwave_public_key;
	   $flutterwave_secret_key = $setting['setting']->flutterwave_secret_key;
	   $flutterwave_callback = $website_url.'/subscription-flutterwave';
	   /* flutterwave */
	   
	   /* coingate */
	   $coingate_mode = $setting['setting']->coingate_mode;
	   if($coingate_mode == 0)
	   {
	      $coingate_mode_status = "sandbox";
	   }
	   else
	   {
	      $coingate_mode_status = "live";
	   }
	   $coingate_auth_token = $setting['setting']->coingate_auth_token;
	   $coingate_callback = $website_url.'/subscription-coingate';
	   /* coingate */
	   
	   /* payfast */
	   $payfast_mode = $setting['setting']->payfast_mode;
	   $payfast_merchant_id = $setting['setting']->payfast_merchant_id;
	   $payfast_merchant_key = $setting['setting']->payfast_merchant_key;
	   if($payfast_mode == 1)
	   {
		   $payfast_url = "https://www.payfast.co.za/eng/process";
	   }
	   else
	   {
		   $payfast_url = "https://sandbox.payfast.co.za/eng/process";
	   }
	   $payfast_success_url = $website_url.'/subscription-payfast/'.$purchase_token;
	   /* payfast */
	   
	   /* coinpayments */
	   $coinpayments_merchant_id = $setting['setting']->coinpayments_merchant_id;
	   $coinpayments_success_url = $website_url.'/subscription-coinpayments/'.$purchase_token;
	   /* coinpayments */
	   
	   /* mercadopago */
	   $mercadopago_client_id = $custom_settings->mercadopago_client_id;
	   $mercadopago_client_secret = $custom_settings->mercadopago_client_secret;
	   $mercadopago_mode = $custom_settings->mercadopago_mode;
	   $mercadopago_success = $website_url.'/subscription-mercadopago/'.$purchase_token;
	   $mercadopago_failure = $website_url.'/failure/';
	   $mercadopago_pending = $website_url.'/pending/';	
	   /* mercadopago */
	   
	   /* coinbase */
	   $coinbase_api_key = $custom_settings->coinbase_api_key;
	   $coinbase_success = $website_url.'/subscription-coinbase/'.$encrypter->encrypt($purchase_token);
	   $coinbase_webhooks = $website_url.'/webhooks/coinbase-subscription';
	   /* coinbase */
	   
	   /* cashfree */
	   $cashfree_api_key = $custom_settings->cashfree_api_key;
	   $cashfree_api_secret = $custom_settings->cashfree_api_secret;
	   $cashfree_mode = $custom_settings->cashfree_mode;
	   $cashfree_success = $website_url.'/subscription-cashfree/';
	   /* cashfree */
	   
	   /* nowpayments */
	   $nowpayments_api_key = $custom_settings->nowpayments_api_key;
	   $nowpayments_ipn_secret = $custom_settings->nowpayments_ipn_secret;
	   $nowpayments_mode = $custom_settings->nowpayments_mode;
	   $nowpayments_success = $website_url.'/subscription-nowpayments/'.$encrypter->encrypt($purchase_token);
	   /* nowpayments */
	   
	   
	   $bank_details = $setting['setting']->local_bank_details;
	   /* settings */
	   Subscription::upsubscribeData($user_id,$updatedata);
	   if($payment_method == 'paypal')
		  {
		     
			 $paypal = '<form method="post" id="paypal_form" action="'.$paypal_url.'">
			  <input type="hidden" value="_xclick" name="cmd">
			  <input type="hidden" value="'.$paypal_email.'" name="business">
			  <input type="hidden" value="'.$user_subscr_type.'" name="item_name">
			  <input type="hidden" value="'.$purchase_token.'" name="item_number">
			  <input type="hidden" value="'.$price.'" name="amount">
			  <input type="hidden" value="'.$site_currency.'" name="currency_code">
			  <input type="hidden" value="'.$success_url.'" name="return">
			  <input type="hidden" value="'.$cancel_url.'" name="cancel_return">
			  		  
			</form>';
			$paypal .= '<script>window.paypal_form.submit();</script>';
			echo $paypal;
					 
			 
		  }
		  else if($payment_method == 'nowpayments')
		  {
			     // Specify the accepted cryptocurrencies
					// Validate the form input
				$validator = Validator::make($request->all(), [
					//'amount' => 'digits_between:1,99999999999999',
					/*'currency' => 'required|in:BTC,ETH,LTC', */
				]);
		
				if ($validator->fails()) {
					return redirect()->back()->withErrors($validator)->withInput();
				}
		
				// Prepare data for the payment request
				if($nowpayments_mode == 0)
				{
				   $nowpayments_payment_url = 'https://api-sandbox.nowpayments.io/v1/payment/';
				   $nowpayments_invoice_url = 'https://api-sandbox.nowpayments.io/v1/invoice';
				   $nowpayment_redirect = 'https://sandbox.nowpayments.io/payment?iid=';
				}
				else
				{
				   $nowpayments_payment_url = 'https://api.nowpayments.io/v1/payment/';
				   $nowpayments_invoice_url = 'https://api.nowpayments.io/v1/invoice';
				   $nowpayment_redirect = 'https://nowpayments.io/payment?iid=';
				}
		        
				// Make a request to NowPayments API using GuzzleHTTP // https://api-sandbox.nowpayments.io/v1 //https://api.nowpayments.io/v1/payment
				$client = new Client();
				$response = $client->post($nowpayments_payment_url, [
					'json' => [
						'price_amount' => $price,
						'price_currency' => $site_currency,
						'pay_currency' => "BTC", 
						//'ipn_callback_url' => "https://nowpayments.io",
  						'order_id'=> $purchase_token,
  					    'order_description' => $user_subscr_type,
						// Add other parameters as needed
					],
					'headers' => [
						'x-api-key' => $nowpayments_api_key,
					],
				]);
		
				$responseBody = json_decode($response->getBody(), true);
				 //dd($responseBody);
				 
				 $response2 = $client->post($nowpayments_invoice_url, [
					'json' => [
						
						'price_amount' => $responseBody['price_amount'],
						'price_currency' => $site_currency, 
						'order_id' => $responseBody['order_id'],
	                    'order_description' => $user_subscr_type,
						'ipn_callback_url' => "https://nowpayments.io",
  						'success_url'=> $nowpayments_success,
                        'cancel_url' => $cancel_url,
						// Add other parameters as needed
					],
					'headers' => [
						'x-api-key' => $nowpayments_api_key,
					],
				]);
		
				$responseBody2 = json_decode($response2->getBody(), true);
				 
				//dd($responseBody2);
				 
				//$paymentLink = $responseBody['payment_url'];
				
				return redirect($nowpayment_redirect.$responseBody2['id'].'&paymentId='.$responseBody['payment_id']);
		       
				//return view('payment-success', ['paymentLink' => $paymentLink]);
						
		  }
		  else if($payment_method == 'cashfree')
		  {
		  
		      if($site_currency != 'INR')
			 {
			   $convert = Currency::convert($site_currency,'INR',$price);
			   $price_amount = $convert['convertedAmount'];
			   
			 }
			 else
			 {
			   $price_amount = $price;
			 }
			 $phone = "9999999999";  
			\Cashfree\Cashfree::$XClientId = $cashfree_api_key;
			\Cashfree\Cashfree::$XClientSecret = $cashfree_api_secret;
			if($cashfree_mode == 0)
			{
			\Cashfree\Cashfree::$XEnvironment = Cashfree\Cashfree::$SANDBOX; //$SANDBOX $PRODUCTION
			$keymode = "sandbox";
			}
			else
			{
			\Cashfree\Cashfree::$XEnvironment = Cashfree\Cashfree::$PRODUCTION;
			$keymode = "production";
			}
			$order_id = "order_".$purchase_token;
			$cashfree = new \Cashfree\Cashfree();
			$x_api_version = "2023-08-01";
			$create_order_request = new \Cashfree\Model\CreateOrderRequest();
			$create_order_request->setOrderAmount($price_amount);
			$create_order_request->setOrderCurrency("INR");
			$create_order_request->setOrderId($order_id);
			$customer_details = new \Cashfree\Model\CustomerDetails();
			$order_meta = new \Cashfree\Model\OrderMeta();
			$customer_details->setCustomerId('customer_'.$user_id);
			$customer_details->setCustomerPhone($phone);
			$customer_details->setCustomerName($user_name);
			$customer_details->setCustomerEmail($order_email);
			$order_meta->setReturnUrl($cashfree_success.'?order_id='.$order_id);
			$create_order_request->setCustomerDetails($customer_details);
			$create_order_request->setOrderMeta($order_meta);
			$create_order_request->setOrderNote($user_subscr_type);
			try {
				$result = $cashfree->PGCreateOrder($x_api_version, $create_order_request);
				//dd($result[0]['order_id']);
				
				//dd($result[0]['payment_session_id']);
				
				$cashfree ='<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>';
		    $cashfree .= '<script>
		    window.onload = function(){
            const cashfree = Cashfree({
                mode: "'.$keymode.'", 
            });
            
                let checkoutOptions = {
                    paymentSessionId: "'.$result[0]['payment_session_id'].'",
                    redirectTarget: "_self",
                };
                cashfree.checkout(checkoutOptions);
            
			}
           </script>'; 
		    echo $cashfree;
				
				
			} catch (Exception $e) {
				echo 'Exception when calling PGCreateOrder: ', $e->getMessage(), PHP_EOL;
			}
		  

		  
		     /*if($site_currency != 'INR')
			 {
			   $convert = Currency::convert($site_currency,'INR',$price);
			   $price_amount = $convert['convertedAmount'];
			   
			 }
			 else
			 {
			   $price_amount = $price;
			 }
		      if($cashfree_mode == 0)
			  {
		      $url = "https://sandbox.cashfree.com/pg/orders";
			  }
			  else
			  {
			  $url = "https://api.cashfree.com/pg/orders";
			  }
			  $phone = "9999999999";
              $headers = array(
               "Content-Type: application/json",
               "x-api-version: 2022-01-01",
               "x-client-id: ".$cashfree_api_key,
               "x-client-secret: ".$cashfree_api_secret
               );
              $data = json_encode([
               'order_id' =>  'order_'.$purchase_token,
               'order_amount' => $price_amount,
               "order_currency" => "INR",
               "customer_details" => [
                    "customer_id" => 'customer_'.$user_id,
                    "customer_name" => $user_name,
                    "customer_email" => $order_email,
                    "customer_phone" => $phone,
               ],
               "order_meta" => [
                    "return_url" => $cashfree_success.'?order_id={order_id}&order_token={order_token}'
               ]
          ]);

          $curl = curl_init($url);

          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

          $resp = curl_exec($curl);

          curl_close($curl);
          dd(json_decode($resp));
          return redirect()->to(json_decode($resp)->payment_link);
		  */
		  
		  }
		  else if($payment_method == 'coinbase')
		  {
		      
			    $url = 'https://api.commerce.coinbase.com/charges';
				$array = [
					'name' => $user_subscr_type,
					'description' => $user_subscr_type,
					'local_price' => [
						'amount' => $price,
						'currency' => $site_currency
					],
					'metadata' => [
						'trx' => $purchase_token
					],
					'pricing_type' => "fixed_price",
					'notification_url' => $coinbase_webhooks,
					'redirect_url' => $coinbase_success,
					'cancel_url' => $cancel_url
				];
		
				$yourjson = json_encode($array);
				$ch = curl_init();
				$apiKey = $coinbase_api_key;
				$header = array();
				$header[] = 'Content-Type: application/json';
				$header[] = 'X-CC-Api-Key: ' . "$apiKey";
				$header[] = 'X-CC-Version: 2018-03-22';
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $yourjson);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);
		        $result = json_decode($result);
                if ($result->data->id != '') 
				{
				   return redirect($result->data->hosted_url);
				}
				else
				{
				   return redirect($cancel_url);
				}
		  
		  }
		  else if($payment_method == 'mercadopago')
		  {
		    
		    if($site_currency != 'BRL')
			 {
			   
			   $convert = Currency::convert($site_currency,'BRL',$price);
			   $price_amount = $convert['convertedAmount'];
			   
			 }
			 else
			 {
			   $price_amount = $price;
			 }
			include(app_path() . '/mercadopago/autoload.php');
			 MercadoPago\SDK::setAccessToken($mercadopago_client_secret);
			 $preference = new MercadoPago\Preference();
             $item = new MercadoPago\Item();
             $item->title = $user_subscr_type;
             $item->quantity = 1;
             $item->unit_price = $price_amount;
		     $item->id = $purchase_token;
             $item->currency_id = "BRL";
             $preference->items = array($item);
             $preference->back_urls = array(
				"success" => $mercadopago_success,
				"failure" => $mercadopago_failure,
				"pending" => $mercadopago_pending
			);
            $preference->payment_methods = array(
				"excluded_payment_types" => array(
				array("id" => "ticket")   
				) );
            $preference->auto_return = "approved";
            $preference->save();
			if($mercadopago_mode == 1)
			{
			return redirect($preference->init_point);
			}
			else
			{
			return redirect($preference->sandbox_init_point);
			}
			
		  }
		  else if($payment_method == 'localbank')
		  {
			$bank_data = array('purchase_token' => $purchase_token, 'bank_details' => $bank_details);
			return view('upgrade-bank-details')->with($bank_data);
		  }
		  else if($payment_method == 'wallet')
		  {
		    if(Auth::user()->earnings >= $price)
			{
			        $user_token = Auth::user()->user_token;
			        $earn_wallet = Auth::user()->earnings - $price;
					$walet_data = array('earnings' => $earn_wallet); 
					Members::updateData($user_token,$walet_data);
					$payment_gateway_status = 'completed';
					$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_gateway_status);
					Subscription::confirmsubscriData($user_id,$checkoutdata);
					/* subscription email */
					$subscr['view'] = Subscription::editsubData($user_subscr_id);
					$subscri_date = $subscr['view']->subscr_duration;
					$sid = 1;
					$setting['setting'] = Settings::editGeneral($sid);
					$currency = $setting['setting']->site_currency_code;
					$subscr_price = $subscr['view']->subscr_price;
					$admin_name = $setting['setting']->sender_name;
					$admin_email = $setting['setting']->sender_email;
					$buyer_name = Auth::user()->name;
					$buyer_email = Auth::user()->email;
					$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
					/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */
					Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
						$message->to($buyer_email, $buyer_name)
						->subject($template_subject);
						$message->from($admin_email,$admin_name);
					});
					/* subscription email */
					return view('success');
					
			} 
			else
			{
			    return redirect()->back()->with('error', 'Please check your wallet balance amount');
			}
		 
		 }
		 
		  /* coinpayments */
		  else if($payment_method == 'coinpayments')
		  {
		     $coinpayments = '<form action="https://www.coinpayments.net/index.php" method="post" id="coinpayments_form">
								<input type="hidden" name="cmd" value="_pay">
								<input type="hidden" name="reset" value="1">
								<input type="hidden" name="merchant" value="'.$coinpayments_merchant_id.'">
								<input type="hidden" name="item_name" value="'.$user_subscr_type.'">	
								<input type="hidden" name="item_desc" value="'.$user_subscr_type.'">
								<input type="hidden" name="item_number" value="'.$purchase_token.'">
								<input type="hidden" name="currency" value="'.$site_currency.'">
								<input type="hidden" name="amountf" value="'.$price.'">
								<input type="hidden" name="want_shipping" value="0">
								<input type="hidden" name="success_url" value="'.$coinpayments_success_url.'">	
								<input type="hidden" name="cancel_url" value="'.$cancel_url.'">	
							</form>';
			$coinpayments .= '<script>window.coinpayments_form.submit();</script>';
			echo $coinpayments;				
		  }
		  /* coinpayments */
		  /* payfast */
		  else if($payment_method == 'payfast')
		  {
		       if($site_currency != 'ZAR')
			   {
			   $convert = Currency::convert($site_currency,'ZAR',$price);
			   $price_amount = $convert['convertedAmount'];
			   }
			   else
			   {
			   $price_amount = $price;
			   }
			 $payfast = '<form method="post" id="payfast_form" action="'.$payfast_url.'">
			  <input type="hidden" name="merchant_id" value="'.$payfast_merchant_id.'">
   			  <input type="hidden" name="merchant_key" value="'.$payfast_merchant_key.'">
   			  <input type="hidden" name="amount" value="'.$price_amount.'">
   			  <input type="hidden" name="item_name" value="'.$user_subscr_type.'">
			  <input type="hidden" name="item_description" value="'.$user_subscr_type.'">
			  <input type="hidden" name="name_first" value="'.$user_name.'">
			  <input type="hidden" name="name_last" value="'.$user_name.'">
			  <input type="hidden" name="email_address" value="'.$order_email.'">
			  <input type="hidden" name="m_payment_id" value="'.$purchase_token.'">
              <input type="hidden" name="email_confirmation" value="1">
              <input type="hidden" name="confirmation_address" value="'.$order_email.'"> 
              <input type="hidden" name="return_url" value="'.$payfast_success_url.'">
			  <input type="hidden" name="cancel_url" value="'.$cancel_url.'">
			  <input type="hidden" name="notify_url" value="'.$cancel_url.'">
			</form>';
			$payfast .= '<script>window.payfast_form.submit();</script>';
			echo $payfast;
					 
			 
		  }
		  
		  /* payfast */
		  else if($payment_method == 'coingate')
		  {
		  
		     \CoinGate\CoinGate::config(array(
					'environment'               => $coingate_mode_status, // sandbox OR live
					'auth_token'                => $coingate_auth_token,
					'curlopt_ssl_verifypeer'    => TRUE // default is false
					 ));
					 
			  $post_params = array(
			       'id'                => $purchase_token,
                   'order_id'          => $purchase_token,
                   'price_amount'      => $price,
                   'price_currency'    => $site_currency,
                   'receive_currency'  => $site_currency,
                   'callback_url'      => $coingate_callback,
                   'cancel_url'        => $cancel_url,
                   'success_url'       => $coingate_callback,
                   'title'             => $user_subscr_type,
                   'description'       => $user_subscr_type
				   
               );
                
				$order = \CoinGate\Merchant\Order::create($post_params);
				
				if ($order) {
					//echo $order->status;
					
					Cache::put('coingate_id', $order->id, now()->addDays(1));
					Cache::put('purchase_id', $order->order_id, now()->addDays(1));
					//echo $order->id;
					return redirect($order->payment_url);
					
					
				} else {
					return redirect($cancel_url);
				}
					  //return view('test');
	  		 
			 
		  }
		  else if($payment_method == 'flutterwave')
		  {
		  
		       if($site_currency != 'NGN')
			   {
		       $convert = Currency::convert($site_currency,'NGN',$price);
			   $price_amount = $convert['convertedAmount'];
			   }
			   else
			   {
			   $price_amount = $price;
			   }
		       $phone_number = "";
			   $csf_token = csrf_token();
			   $flutterwave = '<form method="post" id="flutterwave_form" action="https://checkout.flutterwave.com/v3/hosted/pay">
	          <input type="hidden" name="public_key" value="'.$flutterwave_public_key.'" />
	          <input type="hidden" name="customer[email]" value="'.Auth::user()->email.'" >
			  <input type="hidden" name="customer[phone_number]" value="'.$phone_number.'" />
			  <input type="hidden" name="customer[name]" value="'.Auth::user()->name.'" />
			  <input type="hidden" name="tx_ref" value="'.$purchase_token.'" />
			  <input type="hidden" name="amount" value="'.$price_amount.'">
			  <input type="hidden" name="currency" value="'.$setting['setting']->flutterwave_default_currency.'">
			  <input type="hidden" name="meta[token]" value="'.$csf_token.'">
			  <input type="hidden" name="redirect_url" value="'.$flutterwave_callback.'">
			</form>';
			$flutterwave .= '<script>window.flutterwave_form.submit();</script>';
			echo $flutterwave;
			  
		  
		  }
		  else if($payment_method == 'payhere')
		  {
		     
		     $payhere_mode = $setting['setting']->payhere_mode;
			 if($payhere_mode == 1)
			 {
				$payhere_url = 'https://www.payhere.lk/pay/checkout';
			 }
			 else
			 {
				$payhere_url = 'https://sandbox.payhere.lk/pay/checkout';
			 }
			 $payhere_merchant_id = $setting['setting']->payhere_merchant_id;
			 if($site_currency != 'LKR')
			   {
		       $convert = Currency::convert($site_currency,'LKR',$price);
			   $price_amount = $convert['convertedAmount'];
			   }
			   else
			   {
			   $price_amount = $price;
			   }
		      $payhere = '<form method="post" action="'.$payhere_url.'" id="payhere_form">   
							<input type="hidden" name="merchant_id" value="'.$payhere_merchant_id.'">
							<input type="hidden" name="return_url" value="'.$payhere_success_url.'">
							<input type="hidden" name="cancel_url" value="'.$cancel_url.'">
							<input type="hidden" name="notify_url" value="'.$cancel_url.'">  
							<input type="hidden" name="order_id" value="'.$purchase_token.'">
							<input type="hidden" name="items" value="'.$user_subscr_type.'"><br>
							<input type="hidden" name="currency" value="LKR">
							<input type="hidden" name="amount" value="'.$price_amount.'">  
							
							<input type="hidden" name="first_name" value="'.$user_name.'">
							<input type="hidden" name="last_name" value="'.$user_name.'"><br>
							<input type="hidden" name="email" value="'.$order_email.'">
							<input type="hidden" name="phone" value="'.$order_email.'"><br>
							<input type="hidden" name="address" value="'.$user_subscr_type.'">
							<input type="hidden" name="city" value="'.$user_name.'">
							<input type="hidden" name="country" value="'.$user_name.'">
							  
						</form>'; 
						$payhere .= '<script>window.payhere_form.submit();</script>';
			            echo $payhere;
		  
		  }
		 else if($payment_method == 'razorpay')
		  {
		       
		       if($site_currency != 'INR')
			   {
		       $convert = Currency::convert($site_currency,'INR',$price);
			   $price_amount = $convert['convertedAmount'] * 100;
			   }
			   else
			   {
			   $price_amount = $price * 100;
			   }
			   
			   $csf_token = csrf_token();
			   
			   $logo_url = $website_url.'/public/storage/settings/'.$setting['setting']->site_logo;
			   $script_url = $website_url.'/resources/views/theme/js/vendor.min.js';
			   $callback = $website_url.'/subscription-razorpay';
			   $razorpay = '
			   <script type="text/javascript" src="'.$script_url.'"></script>
			   <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
			   <script>
				var options = {
					"key": "'.$setting['setting']->razorpay_key.'",
					"amount": "'.$price_amount.'", 
					"currency": "INR",
					"name": "'.$user_subscr_type.'",
					"description": "'.$purchase_token.'",
					"image": "'.$logo_url.'",
					"callback_url": "'.$callback.'",
					"prefill": {
						"name": "'.$user_name.'",
						"email": "'.$order_email.'"
						
					},
					"notes": {
						"address": "'.$user_subscr_type.'"
						
						
					},
					"theme": {
						"color": "'.$setting['setting']->site_theme_color.'"
					}
				};
				var rzp1 = new Razorpay(options);
				rzp1.on("payment.failed", function (response){
						alert(response.error.code);
						alert(response.error.description);
						alert(response.error.source);
						alert(response.error.step);
						alert(response.error.reason);
						alert(response.error.metadata);
				});
				
				$(window).on("load", function() {
					 rzp1.open();
					e.preventDefault();
					});
				</script>';
				echo $razorpay;
					
					
		  }
		   
		 else if($payment_method == 'paystack')
		  {
		       if($site_currency != 'NGN')
			   {
		       $convert = Currency::convert($site_currency,'NGN',$price);
			   $price_amount = $convert['convertedAmount'] * 100;
			   }
			   else
			   {
			   $price_amount = $price * 100;
			   }
		       
			   
		       $callback = $website_url.'/subscription-paystack';
			   $csf_token = csrf_token();
			   
			   $reference = $request->input('reference');
			   $paystack = '<form method="post" id="stack_form" action="'.route('paystack').'">
					  <input type="hidden" name="_token" value="'.$csf_token.'">
					  <input type="hidden" name="email" value="'.$order_email.'" >
					  <input type="hidden" name="order_id" value="'.$purchase_token.'">
					  <input type="hidden" name="amount" value="'.$price_amount.'">
					  <input type="hidden" name="quantity" value="1">
					  <input type="hidden" name="currency" value="'.$setting['setting']->paystack_default_currency.'">
					  <input type="hidden" name="reference" value="'.$reference.'">
					  <input type="hidden" name="callback_url" value="'.$callback.'">
					  <input type="hidden" name="metadata" value="'.$purchase_token.'">
					  <input type="hidden" name="key" value="'.$setting['setting']->paystack_secret_key.'">
					</form>';
					$paystack .= '<script>window.stack_form.submit();</script>';
					echo $paystack;
			 
		  }
		 
		 
		  
		  /* stripe code */
		  
		  if($payment_method == 'stripe')
		  {
		     
			 
			 if($setting['setting']->stripe_type == "intents") // Intents API
			 {       
			 
			       if($site_currency == 'INR')
					{
						$finpr = round($price,2);
						$partamt = $finpr * 100;
						$myamount = str_replace([',', '.'], ['', ''], $partamt);
					}
					else
					{
					    $finpr = round($price,2);
						$myamount = $finpr * 100;
					}	      
					\Stripe\Stripe::setApiKey($stripe_secret_key);
					$customer = \Stripe\Customer::create(array( 
					'name' => $user_name,
					'description' => $user_subscr_type,        
					'email' => $order_email,
					"address" => ["city" => "", "country" => "", "line1" => $order_email, "line2" => "", "postal_code" => "", "state" => ""],
					'shipping' => [
						  'name' => $user_name,
						  'address' => [
							'country' => 'us',
							'state' => '',
							'city' => '',
							'line1' => $order_email,
							'line2' => '',
							'postal_code' => ''
						  ]
						]
					));
        		    $payment_intent = \Stripe\PaymentIntent::create([
						'description' => $user_subscr_type,
						'amount' => $myamount,
						'currency' => $site_currency,
						'customer' => $customer->id,
						'metadata' => [
						'order_id' => $purchase_token
					    ],
						'shipping' => [
							'name' => $user_name,
							'address' => [
							  'line1' => $order_email,
							  'postal_code' => '',
							  'city' => '',
							  'state' => '',
							  'country' => 'us',
							],
						  ],
						'payment_method_types' => ['card'],
					]);
		            $intent = $payment_intent->client_secret;
				  $final_amount = $price;
			       $data = array('stripe_publish' => $stripe_publish_key, 'stripe_secret' => $stripe_secret_key, 'intent' => $intent, 'myamount' => $myamount, 'final_amount' => $final_amount, 'site_currency' => $site_currency, 'purchase_token' => $purchase_token);
	   
	   
	              return view('stripe-subscription')->with($data); 

             
						
			}
			else  // Charges API
			{
			   
			   $stripe = array(
					"secret_key"      => $stripe_secret_key,
					"publishable_key" => $stripe_publish_key
				);
			 
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
			 
				$customer = \Stripe\Customer::create(array( 
					'name' => $user_name,
					'description' => $user_subscr_type,        
					'email' => $order_email, 
					'source'  => $token,
					'customer' => $order_email, 
					"address" => ["city" => "", "country" => "", "line1" => $order_email, "line2" => "", "postal_code" => "", "state" => ""],
					'shipping' => [
						  'name' => $user_name,
						  'address' => [
							'country' => 'us',
							'state' => '',
							'city' => '',
							'line1' => $order_email,
							'line2' => '',
							'postal_code' => ''
						  ]
						]
	
                ));
			    
				if($site_currency == 'INR')
				{
				$finpr = round($price,2);
				$partamt = $finpr * 100;
				$myamount = str_replace([',', '.'], ['', ''], $partamt);
				}
				else
				{
				$finpr = round($price,2);
				$myamount = $finpr * 100;
				}
			 
				
				$subscribe_name = $user_subscr_type;
				$subscribe_price = $myamount;
				$currency = $site_currency;
				$book_id = $purchase_token;
			 
				
				$charge = \Stripe\Charge::create(array(
					'customer' => $customer->id,
					'amount'   => $subscribe_price,
					'currency' => $currency,
					'description' => $subscribe_name,
					'metadata' => array(
						'order_id' => $book_id
					)
				));
			 
				
				$chargeResponse = $charge->jsonSerialize();
			 
				
				if($chargeResponse['paid'] == 1 && $chargeResponse['captured'] == 1) 
				{
			 
					$payment_token = $chargeResponse['balance_transaction'];
					$purchased_token = $book_id;
					$payment_gateway_status = 'completed';
					$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_gateway_status);
					Subscription::confirmsubscriData($user_id,$checkoutdata);
					/* subscription email */
					$subscr['view'] = Subscription::editsubData($user_subscr_id);
					$subscri_date = $subscr['view']->subscr_duration;
					$sid = 1;
					$setting['setting'] = Settings::editGeneral($sid);
					$currency = $setting['setting']->site_currency_code;
					$subscr_price = $subscr['view']->subscr_price;
					$admin_name = $setting['setting']->sender_name;
					$admin_email = $setting['setting']->sender_email;
					$buyer_name = Auth::user()->name;
					$buyer_email = Auth::user()->email;
					$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
					/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */
					Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
						$message->to($buyer_email, $buyer_name)
						->subject($template_subject);
						$message->from($admin_email,$admin_name);
					});
					/* subscription email */
					$data_record = array('payment_token' => $payment_token);
					return view('success')->with($data_record);
					
					
				}
			   
			}
			 
			
			   
		  /* stripe code */
		  
	   }
	   $subscr_id = $user_subscr_id;
	   $subscr['view'] = Subscription::getSubscription($subscr_id);
	   $get_payment = explode(',', $setting['setting']->payment_option);
	   
	   $user_details = Members::singlebuyerData($user_id);
	   $totaldata = array('subscr' => $subscr, 'get_payment' => $get_payment, 'id' => $request->input('id'), 'user_details' => $user_details);
	   return view('confirm-subscription')->with($totaldata);
	
	
	}
	
	public function redirectToGateway()
    {
        return Paystack::getAuthorizationUrl()->redirectNow();
    }
	
	public function update_myprofile(Request $request)
	{
	
	   $name = $request->input('name');
	   $username = $request->input('username');
         $email = $request->input('email');
		 
		 
		 if(!empty($request->input('password')))
		 {
		 $password = bcrypt($request->input('password'));
		 $pass = $password;
		 }
		 else
		 {
		 $pass = $request->input('save_password');
		 }
		 
		 		 
		  $token = $request->input('user_token');
		  $image_size = $request->input('image_size');
		 $country = $request->input('country');
         
		 $request->validate([
							'name' => 'required',
							'username' => 'required',
							'password' => 'min:6',
							'email' => 'required|email',
							'user_photo' => 'mimes:jpeg,jpg,png,gif,svg|max:'.$image_size,
							
         ]);
		 $rules = array(
				'username' => ['required', 'regex:/^[\w-]*$/', 'max:255', Rule::unique('users') ->ignore($token, 'user_token') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				'email' => ['required', 'email', 'max:255', Rule::unique('users') ->ignore($token, 'user_token') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				
	     );
		 
		 $messsages = array(
		      
	    );
		 
		$validator = Validator::make($request->all(), $rules,$messsages);
		
		if ($validator->fails()) 
		{
		 $failedRules = $validator->failed();
		 return back()->withErrors($validator);
		} 
		else
		{
		
		if ($request->hasFile('user_photo')) {
		     
			Members::droPhoto($token); 
		   
			$image = $request->file('user_photo');
			$img_name = time() . '.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/users');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$user_image = $img_name;
		  }
		  else
		  {
		     $user_image = $request->input('save_photo');
		  }
		  
		 
		 
		$data = array('name' => $name, 'username' => $username, 'email' => $email, 'password' => $pass, 'user_photo' => $user_image, 'updated_at' => date('Y-m-d H:i:s'), 'user_country' => $country);
        Members::updateData($token, $data);
        return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	}
	
	
	
	
	public function paypal_success($ord_token, Request $request)
	{
	
	$payment_token = $request->input('tx');
	$purchased_token = $ord_token;
	$subscr_id = Auth::user()->user_subscr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	$user_id = Auth::user()->id;
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
	/* subscription email */
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$currency = $setting['setting']->site_currency_code;
	$subscr_price = $subscr['view']->subscr_price;
	$admin_name = $setting['setting']->sender_name;
	$admin_email = $setting['setting']->sender_email;
	$buyer_name = Auth::user()->name;
	$buyer_email = Auth::user()->email;
	$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
	/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */
	Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
		$message->to($buyer_email, $buyer_name)
		->subject($template_subject);
		$message->from($admin_email,$admin_name);
	});
	/* subscription email */
	$result_data = array('payment_token' => $payment_token);
	return view('success')->with($result_data);
	
	}
	
	public function flutterwaveCallback(Request $request)
	{
	   	$payment_token = $request->input('transaction_id');
		$ord_token = $request->input('tx_ref');
		$pay_status = $request->input('status');
		if ($pay_status == 'successful') 
		{
			
			$purchased_token = $ord_token;
			$subscr_id = Auth::user()->user_subscr_id;
			$subscr['view'] = Subscription::editsubData($subscr_id);
			$subscri_date = $subscr['view']->subscr_duration;
			$user_subscr_item_level = $subscr['view']->subscr_item_level;
			$user_subscr_item = $subscr['view']->subscr_item;
			$user_subscr_type = $subscr['view']->subscr_name;
			$subscr_value = "+".$subscri_date;
			$subscr_date = date('Y-m-d', strtotime($subscr_value));
			$user_id = Auth::user()->id;
			$payment_status = 'completed';
			
			$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
			Subscription::confirmsubscriData($user_id,$checkoutdata);
			/* subscription email */
			$sid = 1;
			$setting['setting'] = Settings::editGeneral($sid);
			$currency = $setting['setting']->site_currency_code;
			$subscr_price = $subscr['view']->subscr_price;
			$admin_name = $setting['setting']->sender_name;
			$admin_email = $setting['setting']->sender_email;
			$buyer_name = Auth::user()->name;
			$buyer_email = Auth::user()->email;
			$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
			/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */
            Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
				$message->to($buyer_email, $buyer_name)
				->subject($template_subject);
				$message->from($admin_email,$admin_name);
			});
			/* subscription email */
            $result_data = array('payment_token' => $payment_token);
			return view('success')->with($result_data);
		}
		else
		{
		   return view('cancel');
		}	
			
	
	}
	
	
	public function cashfree_success(Request $request)
	{
	  
	$custom_settings = Settings::editCustom();
	$cashfree_api_key = $custom_settings->cashfree_api_key;
	$cashfree_api_secret = $custom_settings->cashfree_api_secret;
	$cashfree_mode = $custom_settings->cashfree_mode;
	\Cashfree\Cashfree::$XClientId = $cashfree_api_key;
	\Cashfree\Cashfree::$XClientSecret = $cashfree_api_secret;
	if($cashfree_mode == 0)
	{
	\Cashfree\Cashfree::$XEnvironment = Cashfree\Cashfree::$SANDBOX; //$SANDBOX $PRODUCTION
	}
	else
	{
	\Cashfree\Cashfree::$XEnvironment = Cashfree\Cashfree::$PRODUCTION;
	}
	$cashfree = new \Cashfree\Cashfree();
	$x_api_version = "2023-08-01";
	try {
		$response = $cashfree->PGFetchOrder($x_api_version, $request->get('order_id'));
		$pieces = explode("_", $request->get('order_id'));
	    $purchased_token = $pieces[1];
		$payment_token = '';
	    if($response[0]['order_status'] == 'PAID')
		{
			
			
			$subscr_id = Auth::user()->user_subscr_id;
			$subscr['view'] = Subscription::editsubData($subscr_id);
			$subscri_date = $subscr['view']->subscr_duration;
			$user_subscr_item_level = $subscr['view']->subscr_item_level;
			$user_subscr_item = $subscr['view']->subscr_item;
			$user_subscr_type = $subscr['view']->subscr_name;
			$subscr_value = "+".$subscri_date;
			$subscr_date = date('Y-m-d', strtotime($subscr_value));
			$user_id = Auth::user()->id;
			$payment_status = 'completed';
			
			$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
			Subscription::confirmsubscriData($user_id,$checkoutdata);
			/* subscription email */
			$sid = 1;
			$setting['setting'] = Settings::editGeneral($sid);
			$currency = $setting['setting']->site_currency_code;
			$subscr_price = $subscr['view']->subscr_price;
			$admin_name = $setting['setting']->sender_name;
			$admin_email = $setting['setting']->sender_email;
			$buyer_name = Auth::user()->name;
			$buyer_email = Auth::user()->email;
			$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
			/* email template code */
							$checktemp = EmailTemplate::checkTemplate(20);
							if($checktemp != 0)
							{
								$template_view['mind'] = EmailTemplate::viewTemplate(20);
								$template_subject = $template_view['mind']->et_subject;
							}
							else
							{
								$template_subject = "Subscription Upgrade";
							}
							/* email template code */
					
			Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
				$message->to($buyer_email, $buyer_name)
				->subject($template_subject);
				$message->from($admin_email,$admin_name);
			});
			/* subscription email */
			$result_data = array('payment_token' => $payment_token);
			return view('success')->with($result_data);
		}
		else
		{
		   return view('cancel');
		}
		
		
	  } catch (Exception $e) {
		echo 'Exception when calling PGFetchOrder: ', $e->getMessage(), PHP_EOL;
	 }	
	
		
	}
	
	
	public function mercadopago_success($ord_token, Request $request)
	{
	  
		$pay_status = $request->input('status');
		if($pay_status == 'approved')
		{
			$payment_token = $request->input('payment_id');
			$purchased_token = $ord_token;
			$subscr_id = Auth::user()->user_subscr_id;
			$subscr['view'] = Subscription::editsubData($subscr_id);
			$subscri_date = $subscr['view']->subscr_duration;
			$user_subscr_item_level = $subscr['view']->subscr_item_level;
			$user_subscr_item = $subscr['view']->subscr_item;
			$user_subscr_type = $subscr['view']->subscr_name;
			$subscr_value = "+".$subscri_date;
			$subscr_date = date('Y-m-d', strtotime($subscr_value));
			$user_id = Auth::user()->id;
			$payment_status = 'completed';
			
			$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
			Subscription::confirmsubscriData($user_id,$checkoutdata);
			/* subscription email */
			$sid = 1;
			$setting['setting'] = Settings::editGeneral($sid);
			$currency = $setting['setting']->site_currency_code;
			$subscr_price = $subscr['view']->subscr_price;
			$admin_name = $setting['setting']->sender_name;
			$admin_email = $setting['setting']->sender_email;
			$buyer_name = Auth::user()->name;
			$buyer_email = Auth::user()->email;
			$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
			/* email template code */
							$checktemp = EmailTemplate::checkTemplate(20);
							if($checktemp != 0)
							{
								$template_view['mind'] = EmailTemplate::viewTemplate(20);
								$template_subject = $template_view['mind']->et_subject;
							}
							else
							{
								$template_subject = "Subscription Upgrade";
							}
							/* email template code */
					
			Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
				$message->to($buyer_email, $buyer_name)
				->subject($template_subject);
				$message->from($admin_email,$admin_name);
			});
			/* subscription email */
			$result_data = array('payment_token' => $payment_token);
			return view('success')->with($result_data);
		}
		else
		{
		   return view('failure');
		}
	
		
	}	
	
	
	public function payfast_success($ord_token, Request $request)
	{
	
	$payment_token = "";
	$purchased_token = $ord_token;
	$subscr_id = Auth::user()->user_subscr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	$user_id = Auth::user()->id;
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
	/* subscription email */
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$currency = $setting['setting']->site_currency_code;
	$subscr_price = $subscr['view']->subscr_price;
	$admin_name = $setting['setting']->sender_name;
	$admin_email = $setting['setting']->sender_email;
	$buyer_name = Auth::user()->name;
	$buyer_email = Auth::user()->email;
	$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
	/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */
            
	Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
		$message->to($buyer_email, $buyer_name)
		->subject($template_subject);
		$message->from($admin_email,$admin_name);
	});
	/* subscription email */
    $result_data = array('payment_token' => $payment_token);
	return view('success')->with($result_data);
	
	}
	
	
	
	public function payhere_success($ord_token, Request $request)
	{
	
	$payment_token = "";
	$purchased_token = $ord_token;
	$subscr_id = Auth::user()->user_subscr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	$user_id = Auth::user()->id;
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
	/* subscription email */
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$currency = $setting['setting']->site_currency_code;
	$subscr_price = $subscr['view']->subscr_price;
	$admin_name = $setting['setting']->sender_name;
	$admin_email = $setting['setting']->sender_email;
	$buyer_name = Auth::user()->name;
	$buyer_email = Auth::user()->email;
	$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency);
	/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
	Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
		$message->to($buyer_email, $buyer_name)
		->subject($template_subject);
		$message->from($admin_email,$admin_name);
	});
	/* subscription email */
    $result_data = array('payment_token' => $payment_token);
	return view('success')->with($result_data);
	
	}
	
	
	/* coinbase */
	
	public function coinbase_subscription(Request $request)
    {   
	    $encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	    $custom_settings = Settings::editCustom();
        $postdata = file_get_contents("php://input");
        $res = json_decode($postdata);
        $ord_token = $res->event->data->metadata->trx;
		$coinbase_secret_key = $custom_settings->coinbase_secret_key;
		$headers = apache_request_headers();
        $sentSign = $headers['x-cc-webhook-signature'];
        $sig = hash_hmac('sha256', $postdata, $coinbase_secret_key);
        if ($sentSign == $sig) {
            if ($res->event->type == 'charge:confirmed') 
			{
			    
				return redirect('/subscription-coinbase/'.$encrypter->encrypt($ord_token));
                
            }
        }
    }
	
	
	public function stripe_success($ordtoken, Request $request)
	{
	$encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	$ord_token   = $encrypter->decrypt($ordtoken);
	$payment_token = '';
	$purchased_token = $ord_token;
	$subscr_id = Auth::user()->user_subscr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	$user_id = Auth::user()->id;
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
	/* subscription email */
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$currency = $setting['setting']->site_currency_code;
	$subscr_price = $subscr['view']->subscr_price;
	$admin_name = $setting['setting']->sender_name;
	$admin_email = $setting['setting']->sender_email;
	$buyer_name = Auth::user()->name;
	$buyer_email = Auth::user()->email;
	$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
	/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
	Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
		$message->to($buyer_email, $buyer_name)
		->subject($template_subject);
		$message->from($admin_email,$admin_name);
	});
	/* subscription email */
	$result_data = array('payment_token' => $payment_token);
	return view('success')->with($result_data);
	
	}
	
	
	
	/* nowpayments */
	
	public function nowpayments_success($ordtoken, Request $request)
	{
	$encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	$ord_token   = $encrypter->decrypt($ordtoken);
	$payment_token = '';
	$purchased_token = $ord_token;
	$subscr_id = Auth::user()->user_subscr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	$user_id = Auth::user()->id;
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
	/* subscription email */
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$currency = $setting['setting']->site_currency_code;
	$subscr_price = $subscr['view']->subscr_price;
	$admin_name = $setting['setting']->sender_name;
	$admin_email = $setting['setting']->sender_email;
	$buyer_name = Auth::user()->name;
	$buyer_email = Auth::user()->email;
	$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
	/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
	Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
		$message->to($buyer_email, $buyer_name)
		->subject($template_subject);
		$message->from($admin_email,$admin_name);
	});
	/* subscription email */
	$result_data = array('payment_token' => $payment_token);
	return view('success')->with($result_data);
	
	}
	
	
	/* nowpayments */
	
	
	public function coinbase_success($ordtoken, Request $request)
	{
	$encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	$ord_token   = $encrypter->decrypt($ordtoken);
	$payment_token = '';
	$purchased_token = $ord_token;
	$subscr_id = Auth::user()->user_subscr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	$user_id = Auth::user()->id;
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
	/* subscription email */
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$currency = $setting['setting']->site_currency_code;
	$subscr_price = $subscr['view']->subscr_price;
	$admin_name = $setting['setting']->sender_name;
	$admin_email = $setting['setting']->sender_email;
	$buyer_name = Auth::user()->name;
	$buyer_email = Auth::user()->email;
	$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
	/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
	Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
		$message->to($buyer_email, $buyer_name)
		->subject($template_subject);
		$message->from($admin_email,$admin_name);
	});
	/* subscription email */
	$result_data = array('payment_token' => $payment_token);
	return view('success')->with($result_data);
	
	}
	
	
	/* coinbase */
	
	
	
	public function coinpayments_success($ord_token, Request $request)
	{
	
	$payment_token = '';
	$purchased_token = $ord_token;
	$subscr_id = Auth::user()->user_subscr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	$user_id = Auth::user()->id;
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
	/* subscription email */
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$currency = $setting['setting']->site_currency_code;
	$subscr_price = $subscr['view']->subscr_price;
	$admin_name = $setting['setting']->sender_name;
	$admin_email = $setting['setting']->sender_email;
	$buyer_name = Auth::user()->name;
	$buyer_email = Auth::user()->email;
	$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
	/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
	Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
		$message->to($buyer_email, $buyer_name)
		->subject($template_subject);
		$message->from($admin_email,$admin_name);
	});
	/* subscription email */
	$result_data = array('payment_token' => $payment_token);
	return view('success')->with($result_data);
	
	}
	
	
	public function coingateCallback(Request $request)
	{
	
	   $ord_token = Cache::get('coingate_id');
	   $purchase_id = Cache::get('purchase_id');
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $coingate_mode = $setting['setting']->coingate_mode;
	   if($coingate_mode == 0)
	   {
	      $coingate_mode_status = "sandbox";
	   }
	   else
	   {
	      $coingate_mode_status = "live";
	   }
	   $coingate_auth_token = $setting['setting']->coingate_auth_token;
	   \CoinGate\CoinGate::config(array(
					'environment'               => $coingate_mode_status, // sandbox OR live
					'auth_token'                => $coingate_auth_token,
					'curlopt_ssl_verifypeer'    => TRUE // default is false
					 ));
	   try 
	   {
         $order = \CoinGate\Merchant\Order::find($ord_token);
     
		if ($order) 
		{
		  //echo $order->status;
		 /*var_dump($order); 
		 echo $order->status;
		 echo $order->id;
		 echo $order->order_id;
		 echo $order->payment_address;*/
		 //dd($order); //sara
			$payment_token = $order->payment_address;
			$ord_token = $order->order_id;
			if($order->status == 'paid')
			{
		
				$purchased_token = $ord_token;
				$subscr_id = Auth::user()->user_subscr_id;
				$subscr['view'] = Subscription::editsubData($subscr_id);
				$subscri_date = $subscr['view']->subscr_duration;
				$user_subscr_item_level = $subscr['view']->subscr_item_level;
				$user_subscr_item = $subscr['view']->subscr_item;
				$user_subscr_type = $subscr['view']->subscr_name;
				$subscr_value = "+".$subscri_date;
				$subscr_date = date('Y-m-d', strtotime($subscr_value));
				$user_id = Auth::user()->id;
				$payment_status = 'completed';
				
				$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
				Subscription::confirmsubscriData($user_id,$checkoutdata);
				/* subscription email */
				$sid = 1;
				$setting['setting'] = Settings::editGeneral($sid);
				$currency = $setting['setting']->site_currency_code;
				$subscr_price = $subscr['view']->subscr_price;
				$admin_name = $setting['setting']->sender_name;
				$admin_email = $setting['setting']->sender_email;
				$buyer_name = Auth::user()->name;
				$buyer_email = Auth::user()->email;
				$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
				/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
				Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
					$message->to($buyer_email, $buyer_name)
					->subject($template_subject);
					$message->from($admin_email,$admin_name);
				});
				/* subscription email */
                $result_data = array('payment_token' => $payment_token);
				return view('success')->with($result_data);
				
			 }
			 else
			 {
			   return view('cancel');
			 }
	
			 
			
			}
			else 
			{
			  echo 'Order not found';
			}
	   } catch (Exception $e) 
		 {
		  echo $e->getMessage(); // BadCredentials Not found App by Access-Key
		}
	
	
	
	}
	
	
	
	public function razorpay_payment(Request $request)
    {
	    $sid = 1;
	    $setting['setting'] = Settings::editGeneral($sid);
		
        $input = $request->all();

        $api = new Api($setting['setting']->razorpay_key, $setting['setting']->razorpay_secret);

        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        
        $user_id = Auth::user()->id;

        //dd($paymentDetails);
         //print_r($paymentDetails);
		if(count($input)  && !empty($input['razorpay_payment_id'])) 
		{
		
		 $payment_token = $input['razorpay_payment_id'];
		 $purchased_token = $payment->description;
		 $subscr_id = Auth::user()->user_subscr_id;
		 $subscr['view'] = Subscription::editsubData($subscr_id);
		 $subscri_date = $subscr['view']->subscr_duration;
		 $user_subscr_item_level = $subscr['view']->subscr_item_level;
		 $user_subscr_item = $subscr['view']->subscr_item;
		 $user_subscr_type = $subscr['view']->subscr_name;
		 $subscr_value = "+".$subscri_date;
		 $subscr_date = date('Y-m-d', strtotime($subscr_value));
		 $user_id = Auth::user()->id;
		 $payment_status = 'completed';
			 
		 $checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
		/* subscription email */
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		$currency = $setting['setting']->site_currency_code;
		$subscr_price = $subscr['view']->subscr_price;
		$admin_name = $setting['setting']->sender_name;
		$admin_email = $setting['setting']->sender_email;
		$buyer_name = Auth::user()->name;
		$buyer_email = Auth::user()->email;
		$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
		/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
		Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
			$message->to($buyer_email, $buyer_name)
			->subject($template_subject);
			$message->from($admin_email,$admin_name);
		});
		/* subscription email */
        $result_data = array('payment_token' => $payment_token);
		return view('success')->with($result_data);
	    } 
		else
		{
		  return redirect('/cancel');
		}
		
        
    }
	
	
	public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();
		$sid = 1;
	    $setting['setting'] = Settings::editGeneral($sid);

        
		if (array_key_exists('data', $paymentDetails) && array_key_exists('status', $paymentDetails['data']) && ($paymentDetails['data']['status'] === 'success')) 
		{
		 
		$payment_token = $paymentDetails['data']['reference'];
		$purchased_token = $paymentDetails['data']['metadata'];
		$subscr_id = Auth::user()->user_subscr_id;
		$subscr['view'] = Subscription::editsubData($subscr_id);
		$subscri_date = $subscr['view']->subscr_duration;
		$user_subscr_item_level = $subscr['view']->subscr_item_level;
		$user_subscr_item = $subscr['view']->subscr_item;
		$user_subscr_type = $subscr['view']->subscr_name;
		$subscr_value = "+".$subscri_date;
		$subscr_date = date('Y-m-d', strtotime($subscr_value));
		$user_id = Auth::user()->id;
		$payment_status = 'completed';
        $checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmsubscriData($user_id,$checkoutdata);
		/* subscription email */
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		$currency = $setting['setting']->site_currency_code;
		$subscr_price = $subscr['view']->subscr_price;
		$admin_name = $setting['setting']->sender_name;
		$admin_email = $setting['setting']->sender_email;
		$buyer_name = Auth::user()->name;
		$buyer_email = Auth::user()->email;
		$buyer_data = array('user_subscr_type' => $user_subscr_type, 'subscr_date' => $subscr_date, 'subscri_date' =>  $subscri_date, 'subscr_price' => $subscr_price, 'currency' => $currency); 
		/* email template code */
					$checktemp = EmailTemplate::checkTemplate(20);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(20);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Subscription Upgrade";
					}
					/* email template code */ 
		Mail::send('subscription_mail', $buyer_data , function($message) use ($admin_name, $admin_email, $buyer_name, $buyer_email, $template_subject) {
			$message->to($buyer_email, $buyer_name)
			->subject($template_subject);
			$message->from($admin_email,$admin_name);
		});
		/* subscription email */	
		$result_data = array('payment_token' => $payment_token);
		return view('success')->with($result_data);
		 
			
		}
		else
		{
		  return redirect('/cancel');
		}
		
    }
	
		
	public function payment_cancel()
	{
	  return view('cancel');
	}
	
	
	
	
	
}
