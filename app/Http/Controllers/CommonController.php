<?php

namespace DownGrade\Http\Controllers;

use Illuminate\Http\Request;
use DownGrade\Models\Members;
use DownGrade\Models\Settings;
use DownGrade\Models\Pages;
use DownGrade\Models\Category;
use DownGrade\Models\SubCategory;
use DownGrade\Models\Blog;
use DownGrade\Models\Product;
use DownGrade\Models\Comment;
use DownGrade\Models\Subscription;
use DownGrade\Models\EmailTemplate;
use DownGrade\Models\Attribute;
use Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use URL;
use Cookie;
use Redirect;
use Session;
use Storage;
use Currency;
use Paystack;
use Cache;
use MercadoPago;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Cashfree;
use GuzzleHttp\Client;


class CommonController extends Controller
{
    
	public function cookie_translate($id)
	{
	
	  Cookie::queue(Cookie::make('translate', $id, 3000));
      /*return Redirect::route('index')->withCookie('translate');*/
	  return redirect()->back()->withCookie('translate');
	  
	}
	
	
	public function view_verify()
	{
	   $checkverify = 0;
	   $data = array('checkverify' => $checkverify);
	   return view('verify')->with($data);
	}
	
	public function update_verify(Request $request)
	{
	   $purchase_code = $request->input('purchase_code');
	   
	   $checkverify = Product::checkVerify($purchase_code);
       
	   if($checkverify != 0)
	   {
			
		 $sold = Product::possibleVerify($purchase_code);
         $data = array('sold' => $sold, 'checkverify' => $checkverify, 'purchase_code' => $purchase_code);
		 return view('verify')->with($data);
             
			  
       }
	   else
	   {
              
			  return redirect()->back()->with('error', 'Sorry, This is not a valid purchase code or this user have not purchased any of items.');
       }
	   
	  
	}
	
	
	public function view_preview($item_slug)
	{
	   $item['item'] = Product::singleitemData($item_slug);
	   $product_url = URL::to('/item').'/'.$item['item']->product_slug;
	   $demo_url = $item['item']->product_demo_url;
	   $product_name = $item['item']->product_name;
	   $allproducts = Product::DemoProduct();
	   $data = array('product_url' => $product_url, 'demo_url' => $demo_url, 'product_name' => $product_name, 'allproducts' => $allproducts);
	   return view('preview')->withHeaders('X-Frame-Options', 'ALLOWALL')->with($data);
	}
	
	/* updates */
	public function view_updates()
	{
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $updates = Product::with('ratings')->join('category', 'category.cat_id', '=', 'product.product_category_parent')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_update', 'desc')->get();
	   $data = array('setting' => $setting, 'updates' => $updates);
	 return view('updates')->with($data);
	}
	
	/* updates */
	
	
	/* subscription */
	
	public function view_subscription()
	{
	 $subscription['view'] = Subscription::viewSubscription();
	 $data = array('subscription' => $subscription);  
	 return view('subscription')->with($data);
	}
	
	
	
	/* subscription */
	
	public function issue_report(Request $request)
	{
	     $report_fullname = $request->input('report_fullname');
		 $report_email = $request->input('report_email');
		 $report_issue_type = $request->input('report_issue_type');
		 $report_subject = $request->input('report_subject');
		 $report_message = $request->input('report_message');
		 $report_product_token = $request->input('report_product_token');
		 $check = Product::checkReport($report_email,$report_product_token);
		 $today = date("Y-m-d h:i a");
		 if ($check != 0) 
		 {
		 
		  return redirect()->back()->with('error', 'This email address already report this item');
		 
		 } 
		 else
		 { 
		 
		  $savedata = array('report_product_token' => $report_product_token, 'report_fullname' => $report_fullname, 'report_email' => $report_email, 'report_issue_type' => $report_issue_type, 'report_subject' => $report_subject, 'report_message' => $report_message, 'report_times' => $today);
		  Product::savereport($savedata);
		  $sid = 1;
		  $setting['setting'] = Settings::editGeneral($sid);
		  $to_name = $setting['setting']->sender_name;
		  $to_email = $setting['setting']->sender_email;
		  $from_name = $report_fullname;
		  $from_email = $report_email;
		  $product_data = Product::editproductData($report_product_token);
		  $product_slug = $product_data->product_slug;
		  $product_name = $product_data->product_name;
		  $item_url = URL::to('/item').'/'.$product_slug;
		  
		  $data = array('to_name' => $to_name, 'to_email' => $to_email, 'product_name' => $product_name, 'product_slug' => $product_slug, 'report_subject' => $report_subject, 'report_message' => $report_message, 'report_issue_type' => $report_issue_type, 'from_name' => $from_name, 'from_email' => $from_email);
					/* email template code */
					$checktemp = EmailTemplate::checkTemplate(24);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(24);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Item Report Notifications";
					}
					/* email template code */
					Mail::send('report_mail', $data , function($message) use ($from_name, $from_email, $to_name, $to_email, $product_name, $product_slug, $report_subject, $report_message, $report_issue_type, $template_subject) {
							$message->to($to_email, $to_name)
									->subject($template_subject);
							$message->from($from_email,$from_name);
						});
		  
		 
		  return redirect()->back()->with('success','Thank You! Report has been sent successfully'); 
		
		}
	
	
	}
	
	
	public function view_tags($type,$slug)
	{
	$nslug = str_replace("-"," ",$slug); 
	$tagproduct['view'] = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_drop_status','=','no')->where('product.product_status','=',1)->where('product.product_tags', 'LIKE', "%$nslug%")->orderBy('product.product_id', 'desc')->get();
	$data = array('tagproduct' => $tagproduct, 'nslug' => $nslug);
	return view('tag')->with($data);
	
	}
	
	/* cart */
	
	public static function price_info($flash_var,$price_var) 
    {
	    $sid = 1;
	    $setting['setting'] = Settings::editGeneral($sid);
	    if($flash_var == 1)
        {
		/*$varprice = ($setting['setting']->site_flash_sale_discount * $price_var) / 100;
        $price = round($varprice,2);*/
		
		$varprice = ($price_var / 100) * $setting['setting']->site_flash_sale_discount;
        $pricess = $price_var - $varprice;
        $price = round($pricess,2);
		
        }
        else
        {
        $price = $price_var;
        }
		return $price;
	}
	
	public function add_to_cart($slug)
	{
	  
	  $checkitem = Product::singleitemCount($slug);
	  if($checkitem != 0)
	  {
	  $item['view'] = Product::singleitemData($slug);
	  $price = $this->price_info($item['view']->product_flash_sale,$item['view']->regular_price);
	  $product_id = $item['view']->product_id;
	  $product_name = $item['view']->product_name;
	  $product_user_id = $item['view']->user_id;
	  $product_token = $item['view']->product_token;
	  $session_id = Session::getId();
	  $license = 'regular';
	  $start_date = date('Y-m-d');
	  $end_date = date('Y-m-d', strtotime('+6 month'));
	  $order_status = 'pending';
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  $extra_fee = $setting['setting']->site_extra_fee;
	  $admin_amount = $price;
	  $getcount  = Product::getorderCount($product_id,$session_id,$order_status);
	  $savedata = array('session_id' => $session_id, 'product_id' => $product_id, 'product_name' => $product_name, 'product_user_id' => $product_user_id, 'product_token' => $product_token, 'license' => $license, 'start_date' => $start_date, 'end_date' => $end_date, 'product_price' => $price, 'admin_amount' => $admin_amount, 'total_price' => $price, 'order_status' => $order_status);
	  
	  $updatedata = array('license' => $license, 'start_date' => $start_date, 'end_date' => $end_date, 'product_price' => $price, 'total_price' => $price);
	   
	   if($getcount == 0)
	   {
	      Product::savecartData($savedata);
		 
		  return redirect('cart')->with('success','Product has been added to cart'); 
	   }
	   else
	   {
	      Product::updatecartData($product_id,$session_id,$order_status,$updatedata);
		  
		  return redirect('cart')->with('success','Product has been updated to cart'); 
	   }
	   
	  
	  }
	  else
	  {
	     return redirect()->back()->with('error', 'Invalid Product Data');
	  }
	  
	
	}
	
	public function show_checkout()
	{
	 $custom_settings = Settings::editCustom();
	 $cart['item'] = Product::getcartData();
	 $cart_mobile['item'] = Product::getcartData();
	  $cart_count = Product::getcartCount();
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
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
	  /* VAT */
	  if (Auth::check())
	  {
		  $country_id = Auth::user()->user_country;
		  $country_details = Members::countryCheck($country_id);
		  
		  if($country_details != 0)
		  {  
		      
			  $data_views = Members::countryDATA($country_id);
			  $country_percent = $data_views->vat_price;
			 
		  }
		  else
		  {
			$country_percent = $custom_settings->default_vat_price;
		  } 
	   }
	   else
	   {
	      $country_percent = $custom_settings->default_vat_price;
	   }	  
	   /* VAT */
	    
	   $data = array('cart' => $cart, 'cart_count' => $cart_count, 'get_payment' => $get_payment, 'cart_mobile' => $cart_mobile, 'country_percent' => $country_percent, 'stripe_publish' => $stripe_publish, 'stripe_secret' => $stripe_secret, 'stripe_type' => $stripe_type);
	   
	   return view('checkout')->with($data);
	 
	
	}
	
	public function payment_failure()
	{
	  return view('failure');
	} 
    
	public function payment_pending()
	{
	  return view('pending');
	}
	
	
	public function coinbase_checkout(Request $request)
    {   
	    $encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	    $custom_settings = Settings::editCustom();
        $postdata = file_get_contents("php://input");
        $res = json_decode($postdata);
        $ord_token = $res->event->data->metadata->trx;
		$check_details = Product::getcheckoutData($ord_token);
		$coinbase_secret_key = $custom_settings->coinbase_secret_key;
		$headers = apache_request_headers();
        $sentSign = $headers['x-cc-webhook-signature'];
        $sig = hash_hmac('sha256', $postdata, $coinbase_secret_key);
        if ($sentSign == $sig) {
            if ($res->event->type == 'charge:confirmed' && $check_details->payment_status == 'pending') 
			{
			    
				return redirect('/coinbase/'.$encrypter->encrypt($ord_token));
                
            }
        }
    }
	
	public function generate_license($suffix = null) 
	{
    // Default tokens contain no "ambiguous" characters: 1,i,0,o
    if(isset($suffix)){
        // Fewer segments if appending suffix
        $num_segments = 3;
        $segment_chars = 6;
    }else{
        $num_segments = 4;
        $segment_chars = 5;
    }
    $tokens = '0123456789abcdefghijklmnopqrstuvwxyz';
    $license_string = '';
    // Build Default License String
    for ($i = 0; $i < $num_segments; $i++) {
        $segment = '';
        for ($j = 0; $j < $segment_chars; $j++) {
            $segment .= $tokens[rand(0, strlen($tokens)-1)];
        }
        $license_string .= $segment;
        if ($i < ($num_segments - 1)) {
            $license_string .= '-';
        }
    }
    // If provided, convert Suffix
    if(isset($suffix)){
        if(is_numeric($suffix)) {   // Userid provided
            $license_string .= '-'.strtoupper(base_convert($suffix,10,36));
        }else{
            $long = sprintf("%u\n", ip2long($suffix),true);
            if($suffix === long2ip($long) ) {
                $license_string .= '-'.strtoupper(base_convert($long,10,36));
            }else{
                $license_string .= '-'.strtoupper(str_ireplace(' ','-',$suffix));
            }
        }
    }
    return $license_string;
   }
	
	
	public function view_checkout(Request $request)
	{
	   $encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $custom_settings = Settings::editCustom();
	   $cart['item'] = Product::getcartData();
	   $cart_mobile['item'] = Product::getcartData();
	   $cart_count = Product::getcartCount();
	   $purchase_code = $this->generate_license();
	   $get_payment = explode(',', $setting['setting']->payment_option);
	   $stripe_type = $setting['setting']->stripe_type;
	   /* VAT */
	  if (Auth::check())
	  {
		  $country_id = Auth::user()->user_country;
		  $country_details = Members::countryCheck($country_id);
		  
		  if($country_details != 0)
		  {  
		      
			  $data_views = Members::countryDATA($country_id);
			  $country_percent = $data_views->vat_price;
			 
		  }
		  else
		  {
			$country_percent = $custom_settings->default_vat_price;
		  } 
	   }
	   else
	   {
	      $country_percent = $custom_settings->default_vat_price;
	   }	  
	   /* VAT */
	   $totaldata = array('cart' => $cart, 'cart_count' => $cart_count, 'get_payment' => $get_payment, 'cart_mobile' => $cart_mobile, 'country_percent' => $country_percent, 'stripe_type' => $stripe_type);
       $session_id = Session::getId();
	   if(Auth::guest())
	   {
	     $email = $request->input('email');
	     $password = bcrypt($request->input('password'));
		 $pass = trim($request->input('password'));
	     $request->validate([
							
							'email' => 'required|email',
							'password' => ['required', 'min:6'],
							
							
							
         ]);
		 $rules = array(
				
				'email' => ['required', 'email', 'max:255', Rule::unique('users') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				
	     );
		 
		 $messsages = array(
		      
	    );
		 
		$validator = Validator::make($request->all(), $rules,$messsages);
		
		if ($validator->fails()) 
		{
		 //$failedRules = $validator->failed();
		 //return back()->withErrors($validator);
		 
		 return redirect()->back()->with('error', 'Email address already exists');
		 
		} 
		else
		{ 
		   $user_token = $this->generateRandomString();
		   $name = strstr($email, '@', true);
		   $username = strstr($email, '@', true);
		   $user_type = 'customer';
		   $earnings = 0;
		   $verified = 1;
		   $data = array('name' => $name, 'username' => $username, 'email' => $email, 'user_type' => $user_type, 'password' => $password, 'earnings' => $earnings, 'verified' => $verified, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_token' => $user_token);
		   Members::insertData($data);
		   $field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
		   if (Auth::attempt(array($field => $email, 'password' =>  $pass, 'verified' => 1, 'drop_status' => 'no' )))
		   {
		    Session::setId($session_id);
			$updata = array('user_id' => auth()->user()->id); 
			Product::changeOrder($session_id,$updata);
			$user_id = auth()->user()->id;
			$userdata = Members::logindataUser($user_id);
			$order_firstname = $userdata->name;
			$order_lastname = $userdata->name;
			$order_email = $userdata->email;
			$buyer_wallet_amount = $userdata->earnings;
			$user_key_token = $userdata->user_token;
		   }
		   else
	   	   {
	         return redirect()->back()->with('error', 'These credentials do not match our records.');
	   	   }
		   
		}
		 
	   
	   }
	   else
	   {
	   $user_id = Auth::user()->id;
	   $userdata = Members::logindataUser($user_id);
	   $order_firstname = $userdata->name;
	   $order_lastname = $userdata->name;
	   $order_email = $userdata->email;
	   $buyer_wallet_amount = $userdata->earnings;
	   $user_key_token = $userdata->user_token;
	   }
	   
	   $token = $request->input('token');
	   $purchase_token = rand(111111,999999);
	   $order_id = $request->input('order_id');
	   $product_prices = base64_decode($request->input('product_prices'));
	   $product_user_id = $request->input('product_user_id');
	   
	   $amount = base64_decode($request->input('amount'));
	   $processing_fee = base64_decode($request->input('processing_fee'));
	   /*$final_amount = $amount + $processing_fee;*/
	   $final_amount = round($amount,2);
	   $payment_method = $request->input('payment_method');
	   $website_url = $request->input('website_url');
	   $payment_date = date('Y-m-d');
	   $payment_status = 'pending';
	   
	   $vat_price = base64_decode($request->input('vat_price')); 
	   $default_vat_price = base64_decode($request->input('default_vat_price')); 
	   
	   
	   $getcount  = Product::getcheckoutCount($purchase_token,$user_id,$payment_status);
	   
	   $savedata = array('purchase_token' => $purchase_token, 'order_ids' => $order_id, 'product_prices' => $product_prices, 'product_user_id' => $product_user_id, 'user_id' => $user_id, 'total' => $final_amount, 'subtotal' => $amount, 'processing_fee' => $processing_fee, 'payment_type' => $payment_method, 'payment_date' => $payment_date, 'order_firstname' => $order_firstname, 'order_lastname' => $order_lastname, 'order_email' => $order_email, 'payment_status' => $payment_status, 'vat_price' => $vat_price);
	   
	   $updatedata = array('order_ids' => $order_id, 'product_prices' => $product_prices, 'product_user_id' => $product_user_id, 'total' => $final_amount, 'subtotal' => $amount, 'processing_fee' => $processing_fee, 'payment_type' => $payment_method, 'payment_date' => $payment_date, 'order_firstname' => $order_firstname, 'order_lastname' => $order_lastname, 'order_email' => $order_email, 'vat_price' => $vat_price);
	   
	   
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
	   $success_url = $website_url.'/success/'.$purchase_token;
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
	   $coingate_success = $website_url.'/coingate';
	   /* settings */
	   
	   /* coinpayments */
	   $coinpayments_merchant_id = $setting['setting']->coinpayments_merchant_id;
	   $coinpayments_success_url = $website_url.'/coinpayments-success/'.$purchase_token;
	   /* coinpayments */
	   
	   /* payhere */
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
	   $payhere_success_url = $website_url.'/payhere-success/'.$purchase_token;
	   /* payhere */
	   
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
	   $payfast_success_url = $website_url.'/payfast-success/'.$purchase_token;
	   /* payfast */
	   
	   /* flutterwave */
	   $flutterwave_public_key = $setting['setting']->flutterwave_public_key;
	   $flutterwave_secret_key = $setting['setting']->flutterwave_secret_key;
	   /* flutterwave */
	   
	   /* mercadopago */
	   $mercadopago_client_id = $custom_settings->mercadopago_client_id;
	   $mercadopago_client_secret = $custom_settings->mercadopago_client_secret;
	   $mercadopago_mode = $custom_settings->mercadopago_mode;
	   $mercadopago_success = $website_url.'/mercadopago-success/'.$purchase_token;
	   $mercadopago_failure = $website_url.'/failure/';
	   $mercadopago_pending = $website_url.'/pending/';	
	   /* mercadopago */
	   
	   /* coinbase */
	   $coinbase_api_key = $custom_settings->coinbase_api_key;
	   $coinbase_success = $website_url.'/coinbase/'.$encrypter->encrypt($purchase_token);
	   $coinbase_webhooks = $website_url.'/webhooks/coinbase-checkout';
	   /* coinbase */
	   
	   /* cashfree */
	   $cashfree_api_key = $custom_settings->cashfree_api_key;
	   $cashfree_api_secret = $custom_settings->cashfree_api_secret;
	   $cashfree_mode = $custom_settings->cashfree_mode;
	   $cashfree_success = $website_url.'/cashfree/';
	   /* cashfree */
	   
	   /* nowpayments */
	   $nowpayments_api_key = $custom_settings->nowpayments_api_key;
	   $nowpayments_ipn_secret = $custom_settings->nowpayments_ipn_secret;
	   $nowpayments_mode = $custom_settings->nowpayments_mode;
	   $nowpayments_success = $website_url.'/nowpayments/'.$encrypter->encrypt($purchase_token);
	   /* nowpayments */
	   
	   
	   if($getcount == 0)
	   {
	      Product::savecheckoutData($savedata);
		  
		  
		  $order_loop = explode(',',$order_id);
		  $item_names = "";
		  foreach($order_loop as $order)
		  {
		    $orderdata = array('purchase_code' => $purchase_code, 'purchase_token' => $purchase_token, 'payment_type' => $payment_method);
			Product::singleorderupData($order,$orderdata);
			$item['name'] = Product::singleorderData($order);
			$item_names .= $item['name']->product_name;
		   
		  }
		  $item_names_data = rtrim($item_names,',');
		  
		  
		  if($payment_method == 'paypal')
		  {
		     
			 $paypal = '<form method="post" id="paypal_form" action="'.$paypal_url.'">
			  <input type="hidden" value="_xclick" name="cmd">
			  <input type="hidden" value="'.$paypal_email.'" name="business">
			  <input type="hidden" value="'.$item_names_data.'" name="item_name">
			  <input type="hidden" value="'.$purchase_token.'" name="item_number">
			  <input type="hidden" value="'.$final_amount.'" name="amount">
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
						'price_amount' => $final_amount,
						'price_currency' => $site_currency,
						'pay_currency' => "BTC", 
						//'ipn_callback_url' => "https://nowpayments.io",
  						'order_id'=> $purchase_token,
  					    'order_description' => $item_names_data,
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
	                    'order_description' => $item_names_data,
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
			   $convert = Currency::convert($site_currency,'INR',$final_amount);
			   $price_amount = $convert['convertedAmount'];
			   
			 }
			 else
			 {
			   $price_amount = $final_amount;
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
			$customer_details->setCustomerName($order_firstname);
			$customer_details->setCustomerEmail($order_email);
			$order_meta->setReturnUrl($cashfree_success.'?order_id='.$order_id);
			$create_order_request->setCustomerDetails($customer_details);
			$create_order_request->setOrderMeta($order_meta);
			$create_order_request->setOrderNote($item_names_data);
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
		  
		    
		    /* 
		      
			  
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
                    "customer_name" => $order_firstname,
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
					'name' => $item_names_data,
					'description' => $item_names_data,
					'local_price' => [
						'amount' => $final_amount,
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
			   $convert = Currency::convert($site_currency,'BRL',$final_amount);
			   $price_amount = $convert['convertedAmount'];
			   
			 }
			 else
			 {
			   $price_amount = $final_amount;
			 }
			include(app_path() . '/mercadopago/autoload.php');
			 MercadoPago\SDK::setAccessToken($mercadopago_client_secret);
			 $preference = new MercadoPago\Preference();
             $item = new MercadoPago\Item();
             $item->title = $item_names_data;
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
		    $bank_details = $setting['setting']->local_bank_details;
		    $bank_data = array('purchase_token' => $purchase_token, 'bank_details' => $bank_details);
	        return view('bank-details')->with($bank_data);
		  }
		  else if($payment_method == 'wallet')
		  {
		       if($buyer_wallet_amount >= $final_amount)
			   { 
			        $payment_token = ""; 
				    $payment_status = 'completed';
					$purchased_token = $purchase_token;
					$orderdata = array('order_status' => $payment_status);
					$checkoutdata = array('payment_status' => $payment_status);
					Product::singleordupdateData($purchased_token,$orderdata);
					Product::singlecheckoutData($purchased_token,$checkoutdata);
					
					$token = $purchased_token;
					$check['display'] = Product::getcheckoutData($token);
					$order_id = $check['display']->order_ids;
					$order_loop = explode(',',$order_id);
					
					$earn_wallet = $buyer_wallet_amount - $final_amount;
					$walet_data = array('earnings' => $earn_wallet); 
					Members::updateData($user_key_token,$walet_data); 
					/* download file */
					$download_file = "";
					/* download file */
					foreach($order_loop as $order)
					{
										
						$getitem['item'] = Product::getorderData($order);
						$token = $getitem['item']->product_token;
						$item['display'] = Product::solditemData($token);
						$product_sold = $item['display']->product_sold + 1;
						$item_token = $token; 
						$data = array('product_sold' => $product_sold);
						Product::updateitemData($item_token,$data);
						
						$orderdata = array('approval_status' => 'payment released to admin');
						Product::singleorderupData($order,$orderdata);
						/* download file */
						$encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
						$download_file .= URL::to('/download-file').'/'.$encrypter->encrypt($item['display']->product_token).'<br/><br/>';
						/* download file */
						
					}
					
					$checkout['details'] = Product::getcheckoutData($purchased_token);
					$final_amount = $checkout['details']->total;
					$user_id = $checkout['details']->user_id;
					$admin['info'] = Members::adminData();
					$admin_token = $admin['info']->user_token;
					$admin_earning = $admin['info']->earnings + $final_amount;
					$admin_record = array('earnings' => $admin_earning);
					Members::updateadminData($admin_token, $admin_record);
					
									 
					$sid = 1;
					$setting['setting'] = Settings::editGeneral($sid);
					$to_name = $setting['setting']->sender_name;
					$to_email = $setting['setting']->sender_email;
					$currency = $setting['setting']->site_currency_code;
					$from['info'] = Members::singlevendorData($user_id);
					$from_name = $from['info']->name;
					$from_email = $from['info']->email;
					$data = array('to_name' => $to_name, 'to_email' => $to_email, 'final_amount' => $final_amount, 'currency' => $currency, 'from_name' => $from_name, 'from_email' => $from_email, 'purchased_token' => $purchased_token, 'download_file' => $download_file);
					/* email template code */
									$checktemp = EmailTemplate::checkTemplate(21);
									if($checktemp != 0)
									{
										$template_view['mind'] = EmailTemplate::viewTemplate(21);
										$template_subject = $template_view['mind']->et_subject;
									}
									else
									{
										$template_subject = "Item Purchase Notifications";
									}
									/* email template code */
									Mail::send('admin_payment_mail', $data , function($message) use ($from_name, $from_email, $to_name, $to_email, $purchased_token, $template_subject) {
											$message->to($to_email, $to_name)
													->subject($template_subject);
											$message->from($from_email,$from_name);
										});
									 
									if($purchased_token != "")
									{
									Mail::send('admin_payment_mail', $data , function($message) use ($from_name, $from_email, $to_name, $to_email, $purchased_token, $template_subject) {
											$message->to($from_email,$from_name)
													->subject($template_subject);
											$message->from($to_email, $to_name);
										});
									}
									 
					
					    /* referral per sale earning */
						if($custom_settings->affiliate_referral == 1)
						{
							$logged_id = Auth::user()->id;
							$buyer_details = Members::singlebuyerData($logged_id);
							$referral_by = $buyer_details->referral_by;
							
							/* new code */
							if($custom_settings->per_sale_referral_commission_type == 'fixed')
							{
							$per_sale_commission = $setting['setting']->per_sale_referral_commission;
							}
							else
							{
							$per_sale_commission = ($setting['setting']->per_sale_referral_commission * $final_amount) / 100;
							}
							$referral_commission = $per_sale_commission;
							/* new code */
							$check_referral = Members::referralCheck($referral_by);
							  if($check_referral != 0)
							  {
								  $referred['display'] = Members::referralUser($referral_by);
								  $wallet_amount = $referred['display']->earnings + $referral_commission;
								  $referral_amount = $referred['display']->referral_amount + $referral_commission;
								  $update_data = array('earnings' => $wallet_amount, 'referral_amount' => $referral_amount);
								  Members::updateReferral($referral_by,$update_data);
							   } 
					   }	   
					/* referral per sale earning */	
					$result_data = array('payment_token' => $payment_token);
					return view('success')->with($result_data);
					
			   
			   }
			   else
			   {
			      return redirect()->back()->with('error', 'Please check your wallet balance amount');
			   }
		  
		  }
		  else if($payment_method == 'flutterwave')
		  {
		       if($site_currency != 'NGN')
			   {
		       $convert = Currency::convert($site_currency,'NGN',$final_amount);
			   $price_amount = $convert['convertedAmount'];
			   }
			   else
			   {
			   $price_amount = $final_amount;
			   }
		       $flutterwave_callback = $website_url.'/flutterwave';
			   $phone_number = "";
			   $csf_token = csrf_token();
			   $flutterwave = '<form method="post" id="flutterwave_form" action="https://checkout.flutterwave.com/v3/hosted/pay">
	          <input type="hidden" name="public_key" value="'.$flutterwave_public_key.'" />
	          <input type="hidden" name="customer[email]" value="'.$order_email.'" >
			  <input type="hidden" name="customer[phone_number]" value="'.$order_firstname.'" />
			  <input type="hidden" name="customer[name]" value="'.$order_firstname.'" />
			  <input type="hidden" name="tx_ref" value="'.$purchase_token.'" />
			  <input type="hidden" name="amount" value="'.$price_amount.'">
			  <input type="hidden" name="currency" value="'.$setting['setting']->flutterwave_default_currency.'">
			  <input type="hidden" name="meta[token]" value="'.$csf_token.'">
			  <input type="hidden" name="redirect_url" value="'.$flutterwave_callback.'">
			</form>';
			$flutterwave .= '<script>window.flutterwave_form.submit();</script>';
			echo $flutterwave;
			   
		  }
		  /* payfast */
		  else if($payment_method == 'payfast')
		  {
		     if($site_currency != 'ZAR')
			   {
			   $convert = Currency::convert($site_currency,'ZAR',$final_amount);
			   $price_amount = $convert['convertedAmount'];
			   }
			   else
			   {
			   $price_amount = $final_amount;
			   }
			  $payfast = '<form method="post" id="payfast_form" action="'.$payfast_url.'">
			  <input type="hidden" name="merchant_id" value="'.$payfast_merchant_id.'">
   			  <input type="hidden" name="merchant_key" value="'.$payfast_merchant_key.'">
   			  <input type="hidden" name="amount" value="'.$price_amount.'">
   			  <input type="hidden" name="item_name" value="'.$item_names_data.'">
			  <input type="hidden" name="item_description" value="'.$item_names_data.'">
			  <input type="hidden" name="name_first" value="'.$order_firstname.'">
			  <input type="hidden" name="name_last" value="'.$order_firstname.'">
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
		  /* payhere */
		  else if($payment_method == 'payhere')
		  {
		      if($site_currency != 'LKR')
			   {
		       $convert = Currency::convert($site_currency,'LKR',$final_amount);
			   $price_amount = $convert['convertedAmount'];
			   }
			   else
			   {
			   $price_amount = $final_amount;
			   }
		      $payhere = '<form method="post" action="'.$payhere_url.'" id="payhere_form">   
							<input type="hidden" name="merchant_id" value="'.$payhere_merchant_id.'">
							<input type="hidden" name="return_url" value="'.$payhere_success_url.'">
							<input type="hidden" name="cancel_url" value="'.$cancel_url.'">
							<input type="hidden" name="notify_url" value="'.$cancel_url.'">  
							<input type="hidden" name="order_id" value="'.$purchase_token.'">
							<input type="hidden" name="items" value="'.$item_names_data.'"><br>
							<input type="hidden" name="currency" value="LKR">
							<input type="hidden" name="amount" value="'.$price_amount.'">  
							
							<input type="hidden" name="first_name" value="'.$order_firstname.'">
							<input type="hidden" name="last_name" value="'.$order_lastname.'"><br>
							<input type="hidden" name="email" value="'.$order_email.'">
							<input type="hidden" name="phone" value="'.$order_lastname.'"><br>
							<input type="hidden" name="address" value="'.$order_lastname.'">
							<input type="hidden" name="city" value="'.$order_lastname.'">
							<input type="hidden" name="country" value="'.$order_lastname.'">
							  
						</form>'; 
						$payhere .= '<script>window.payhere_form.submit();</script>';
			            echo $payhere;
		  }
		  /* payhere */
		  /* coinpayments */
		  else if($payment_method == 'coinpayments')
		  {
		     $coinpayments = '<form action="https://www.coinpayments.net/index.php" method="post" id="coinpayments_form">
								<input type="hidden" name="cmd" value="_pay">
								<input type="hidden" name="reset" value="1">
								<input type="hidden" name="merchant" value="'.$coinpayments_merchant_id.'">
								<input type="hidden" name="item_name" value="'.$item_names_data.'">	
								<input type="hidden" name="item_desc" value="'.$item_names_data.'">
								<input type="hidden" name="item_number" value="'.$purchase_token.'">
								<input type="hidden" name="currency" value="'.$site_currency.'">
								<input type="hidden" name="amountf" value="'.$final_amount.'">
								<input type="hidden" name="want_shipping" value="0">
								<input type="hidden" name="success_url" value="'.$coinpayments_success_url.'">	
								<input type="hidden" name="cancel_url" value="'.$cancel_url.'">	
							</form>';
			$coinpayments .= '<script>window.coinpayments_form.submit();</script>';
			echo $coinpayments;				
		  }
		  /* coinpayments */
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
                   'price_amount'      => $final_amount,
                   'price_currency'    => $site_currency,
                   'receive_currency'  => $site_currency,
                   'callback_url'      => $coingate_success,
                   'cancel_url'        => $cancel_url,
                   'success_url'       => $coingate_success,
                   'title'             => $item_names_data,
                   'description'       => $item_names_data
               );
                
				$order = \CoinGate\Merchant\Order::create($post_params);
				
				if ($order) {
					//echo $order->status;
					
					Cache::put('coingate_id', $order->id, now()->addDays(1));
					
					//echo $order->id;
					return redirect($order->payment_url);
					
				} else {
					return redirect($cancel_url);
				}
					  //return view('test');
	  		 
		  
		  
		  }
		  else if($payment_method == 'paystack')
		  {
		       if($site_currency != 'NGN')
			   {
		       $convert = Currency::convert($site_currency,'NGN',$final_amount);
			   $price_amount = $convert['convertedAmount'] * 100;
			   }
			   else
			   {
			   $price_amount = $final_amount * 100;
			   }
			   $callback = $website_url.'/paystack';
			   $csf_token = csrf_token();
			   $reference = Paystack::genTranxRef();
			   
			   $paystack = '<form method="post" id="stack_form" action="'.route('paystack').'">
					  <input type="hidden" name="_token" value="'.$csf_token.'">
					  <input type="hidden" name="email" value="'.$order_email.'" >
					  <input type="hidden" name="purchase_token" value="'.$purchase_token.'">
					  <input type="hidden" name="amount" value="'.$price_amount.'">
					  <input type="hidden" name="site_currency" value="'.$setting['setting']->paystack_default_currency.'">
					  <input type="hidden" name="reference" value="'.$reference.'">
					  <input type="hidden" name="callback_url" value="'.$callback.'">
					  <input type="hidden" name="metadata" value="'.$purchase_token.'">
					  <input type="hidden" name="key" value="'.$setting['setting']->paystack_secret_key.'">
					</form>';
					$paystack .= '<script>window.stack_form.submit();</script>';
					echo $paystack;
		  }
		  else if($payment_method == 'razorpay')
		  {
		       if($site_currency != 'INR')
			   {
		       $convert = Currency::convert($site_currency,'INR',$final_amount);
			   $price_amount = $convert['convertedAmount'] * 100;
			   }
			   else
			   {
			   $price_amount = $final_amount * 100;
			   }
			   $csf_token = csrf_token();
			   $logo_url = $website_url.'/public/storage/settings/'.$setting['setting']->site_logo;
			   $script_url = $website_url.'/resources/views/theme/js/jquery.min.js';
			   $callback = $website_url.'/razorpay';
			   $razorpay = '
			   <script type="text/javascript" src="'.$script_url.'"></script>
			   <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
			   <script>
				var options = {
					"key": "'.$setting['setting']->razorpay_key.'",
					"amount": "'.$price_amount.'", 
					"currency": "INR",
					"name": "'.$item_names_data.'",
					"description": "'.$purchase_token.'",
					"image": "'.$logo_url.'",
					"callback_url": "'.$callback.'",
					"prefill": {
						"name": "'.$order_firstname.'",
						"email": "'.$order_email.'"
						
					},
					"notes": {
						"address": "'.$order_firstname.'"
						
						
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
		  /* stripe code */
		  else if($payment_method == 'stripe')
		  {
		     
			 
			    if($setting['setting']->stripe_type == "intents") // Intents API
			    {       
			 
			       if($site_currency == 'INR')
					{
						$finpr = round($final_amount,2);
						$partamt = $finpr * 100;
						$myamount = str_replace([',', '.'], ['', ''], $partamt);
					}
					else
					{
					    $finpr = round($final_amount,2);
						$myamount = $finpr * 100;
					}	      
					\Stripe\Stripe::setApiKey($stripe_secret_key);
					$customer = \Stripe\Customer::create(array( 
					'name' => $order_firstname,
					'description' => $item_names_data,        
					'email' => $order_email,
					"address" => ["city" => "", "country" => "", "line1" => $order_email, "line2" => "", "postal_code" => "", "state" => ""],
					'shipping' => [
						  'name' => $order_firstname,
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
						'description' => $item_names_data,
						'amount' => $myamount,
						'currency' => $site_currency,
						'customer' => $customer->id,
						'metadata' => [
						'order_id' => $purchase_token
					    ],
						'shipping' => [
							'name' => $order_firstname,
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
				  
			       $data = array('stripe_publish' => $stripe_publish_key, 'stripe_secret' => $stripe_secret_key, 'intent' => $intent, 'myamount' => $myamount, 'final_amount' => $final_amount, 'site_currency' => $site_currency, 'purchase_token' => $purchase_token);
	   
	   
	              return view('stripe-checkout')->with($data); 

             
						
			}
			else  // Charges API
			{
			   
			   $stripe = array(
					"secret_key"      => $stripe_secret_key,
					"publishable_key" => $stripe_publish_key
				);
			 
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
			 
				$customer = \Stripe\Customer::create(array( 
					'name' => $order_firstname,
					'description' => $item_names_data,        
					'email' => $order_email, 
					'source'  => $token,
					'customer' => $order_email, 
					"address" => ["city" => "", "country" => "", "line1" => $order_email, "line2" => "", "postal_code" => "", "state" => ""],
					'shipping' => [
						  'name' => $order_firstname,
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
				$finpr = round($final_amount,2);
				$partamt = $finpr * 100;
				$myamount = str_replace([',', '.'], ['', ''], $partamt);
				}
				else
				{
				$finpr = round($final_amount,2);
				$myamount = $finpr * 100;
				}
			    
				
				$item_name = $item_names_data;
				$item_price = $myamount;
				$currency = $site_currency;
				$order_id = $purchase_token;
			 
				
				$charge = \Stripe\Charge::create(array(
					'customer' => $customer->id,
					'amount'   => $item_price,
					'currency' => $currency,
					'description' => $item_name,
					'metadata' => array(

						'order_id' => $order_id
					)
				));
				
				
				
				
				
				$chargeResponse = $charge->jsonSerialize();
			 
				
				if($chargeResponse['paid'] == 1 && $chargeResponse['captured'] == 1) 
				{
			 
					
					$payment_token = $chargeResponse['balance_transaction'];
					$payment_status = 'completed';
					$purchased_token = $order_id;
					$orderdata = array('payment_token' => $payment_token, 'order_status' => $payment_status);
					$checkoutdata = array('payment_token' => $payment_token, 'payment_status' => $payment_status);
					Product::singleordupdateData($purchased_token,$orderdata);
					Product::singlecheckoutData($purchased_token,$checkoutdata);
					
					$token = $purchased_token;
					$check['display'] = Product::getcheckoutData($token);
					$order_id = $check['display']->order_ids;
					$order_loop = explode(',',$order_id);
					  /* download file */
					  $download_file = "";
					  /* download file */
					  foreach($order_loop as $order)
					  {
						
						$getitem['item'] = Product::getorderData($order);
						$token = $getitem['item']->product_token;
						$item['display'] = Product::solditemData($token);
						$product_sold = $item['display']->product_sold + 1;
						$item_token = $token; 
						$data = array('product_sold' => $product_sold);
					    Product::updateitemData($item_token,$data);
						
						$orderdata = array('approval_status' => 'payment released to admin');
		                Product::singleorderupData($order,$orderdata);
						/* download file */
						$encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
						$download_file .= URL::to('/download-file').'/'.$encrypter->encrypt($item['display']->product_token).'<br/><br/>';
						/* download file */
						
						
					  }
					
					
					 $admin['info'] = Members::adminData();
					 $admin_token = $admin['info']->user_token;
					 $admin_earning = $admin['info']->earnings + $final_amount;
					 $admin_record = array('earnings' => $admin_earning);
					 Members::updateadminData($admin_token, $admin_record);
		             
					 
					 $sid = 1;
					$setting['setting'] = Settings::editGeneral($sid);
					$to_name = $setting['setting']->sender_name;
					$to_email = $setting['setting']->sender_email;
					$currency = $setting['setting']->site_currency_code;
					$from['info'] = Members::singlevendorData($user_id);
					$from_name = $from['info']->name;
					$from_email = $from['info']->email;
					$data = array('to_name' => $to_name, 'to_email' => $to_email, 'final_amount' => $final_amount, 'currency' => $currency, 'from_name' => $from_name, 'from_email' => $from_email, 'purchased_token' => $purchased_token, 'download_file' => $download_file);
					/* email template code */
					$checktemp = EmailTemplate::checkTemplate(21);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(21);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Item Purchase Notifications";
					}
					/* email template code */
					Mail::send('admin_payment_mail', $data , function($message) use ($from_name, $from_email, $to_name, $to_email, $purchased_token, $template_subject) {
							$message->to($to_email, $to_name)
									->subject($template_subject);
							$message->from($from_email,$from_name);
						});
					 
					if($purchased_token != "")
					{
					Mail::send('admin_payment_mail', $data , function($message) use ($from_name, $from_email, $to_name, $to_email, $purchased_token, $template_subject) {
							$message->to($from_email,$from_name)
									->subject($template_subject);
							$message->from($to_email, $to_name);
						});
					}
					 
					/* referral per sale earning */
					if($custom_settings->affiliate_referral == 1)
					{
						$logged_id = Auth::user()->id;
						$buyer_details = Members::singlebuyerData($logged_id);
						$referral_by = $buyer_details->referral_by;
						
						/* new code */
						if($custom_settings->per_sale_referral_commission_type == 'fixed')
						{
							$per_sale_commission = $setting['setting']->per_sale_referral_commission;
						}
						else
						{
							$per_sale_commission = ($setting['setting']->per_sale_referral_commission * $final_amount) / 100;
						}
						$referral_commission = $per_sale_commission;
						/* new code */
						$check_referral = Members::referralCheck($referral_by);
						if($check_referral != 0)
						{
							$referred['display'] = Members::referralUser($referral_by);
							$wallet_amount = $referred['display']->earnings + $referral_commission;
							$referral_amount = $referred['display']->referral_amount + $referral_commission;
							$update_data = array('earnings' => $wallet_amount, 'referral_amount' => $referral_amount);
							Members::updateReferral($referral_by,$update_data);
						} 
					}
					/* referral per sale earning */
					$data_record = array('payment_token' => $payment_token);
					return view('success')->with($data_record);
					
					
					
				}
			 
			 
			 }
		     
		  
		  }
		  /* stripe code */
		  
		 
		  
	   }
	   else
	   {
	   
	      Product::updatecheckoutData($purchase_token,$user_id,$payment_status,$updatedata);
		  $order_loop = explode(',',$order_id);
		  $item_names = "";
		  foreach($order_loop as $order)
		  {
		    $orderdata = array('purchase_token' => $purchase_token, 'payment_type' => $payment_method);
			Product::singleorderupData($order,$orderdata);
			$item['name'] = Product::singleorderData($order);
			$item_names .= $item['name']->product_name;
		   
		  }
		  $item_names_data = rtrim($item_names,',');
		  
		  
		  if($payment_method == 'paypal')
		  {
		     
			 $paypal = '<form method="post" id="paypal_form" action="'.$paypal_url.'">
			  <input type="hidden" value="_xclick" name="cmd">
			  <input type="hidden" value="'.$paypal_email.'" name="business">
			  <input type="hidden" value="'.$item_names_data.'" name="item_name">
			  <input type="hidden" value="'.$purchase_token.'" name="item_number">
			  <input type="hidden" value="'.$final_amount.'" name="amount">
			  <input type="hidden" value="USD" name="'.$site_currency.'">
			  <input type="hidden" value="'.$success_url.'" name="return">
			  <input type="hidden" value="'.$cancel_url.'" name="cancel_return">
			  		  
			</form>';
			$paypal .= '<script>window.paypal_form.submit();</script>';
			echo $paypal;
					 
			 
		  }
		  else if($payment_method == 'paystack')
		  {
		       $callback = $website_url.'/paystack';
			   $csf_token = csrf_token();
			   $reference = Paystack::genTranxRef();
			   $price_amount = $final_amount * 100;
			   $paystack = '<form method="post" id="stack_form" action="'.route('paystack').'">
					  <input type="hidden" name="_token" value="'.$csf_token.'">
					  <input type="hidden" name="email" value="'.$order_email.'" >
					  <input type="hidden" name="purchase_token" value="'.$purchase_token.'">
					  <input type="hidden" name="amount" value="'.$price_amount.'">
					  <input type="hidden" name="site_currency" value="'.$site_currency.'">
					  <input type="hidden" name="reference" value="'.$reference.'">
					  <input type="hidden" name="callback_url" value="'.$callback.'">
					  <input type="hidden" name="metadata" value="'.$purchase_token.'">
					  <input type="hidden" name="key" value="'.$setting['setting']->paystack_secret_key.'">
					</form>';
					$paystack .= '<script>window.stack_form.submit();</script>';
					echo $paystack;
		  }
		  /* stripe code */
		  else if($payment_method == 'stripe')
		  {
		     
			 			 
				$stripe = array(
					"secret_key"      => $stripe_secret_key,
					"publishable_key" => $stripe_publish_key
				);
			 
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
			 
				
				$customer = \Stripe\Customer::create(array(
					'email' => $order_email,
					'source'  => $token
				));
			 
				
				$item_name = $item_names_data;
				$item_price = $final_amount * 100;
				$currency = $site_currency;
				$order_id = $purchase_token;
			 
				
				$charge = \Stripe\Charge::create(array(
					'customer' => $customer->id,
					'amount'   => $item_price,
					'currency' => $currency,
					'description' => $item_name,
					'metadata' => array(
						'order_id' => $order_id
					)
				));
			 
				
				$chargeResponse = $charge->jsonSerialize();
			 
				
				if($chargeResponse['paid'] == 1 && $chargeResponse['captured'] == 1) 
				{
			 
					
										
					$payment_token = $chargeResponse['balance_transaction'];
					$payment_status = 'completed';
					$purchased_token = $order_id;
					$orderdata = array('payment_token' => $payment_token, 'order_status' => $payment_status);
					$checkoutdata = array('payment_token' => $payment_token, 'payment_status' => $payment_status);
					Product::singleordupdateData($purchased_token,$orderdata);
					Product::singlecheckoutData($purchased_token,$checkoutdata);
					
					$token = $purchased_token;
					$check['display'] = Product::getcheckoutData($token);
					$order_id = $check['display']->order_ids;
					$order_loop = explode(',',$order_id);
					  
					  foreach($order_loop as $order)
					  {
						
						$getitem['item'] = Product::getorderData($order);
						$token = $getitem['item']->product_token;
						$item['display'] = Product::solditemData($token);
						$product_sold = $item['display']->product_sold + 1;
						$item_token = $token; 
						$data = array('product_sold' => $product_sold);
					    Product::updateitemData($item_token,$data);
						
					 $orderdata = array('approval_status' => 'payment released to admin');
		             Product::singleorderupData($order,$orderdata);
						
					  }
					
					
					
					$admin['info'] = Members::adminData();
					 $admin_token = $admin['info']->user_token;
					 $admin_earning = $admin['info']->earnings + $final_amount;
					 $admin_record = array('earnings' => $admin_earning);
					 Members::updateadminData($admin_token, $admin_record);
		             
					 
					 $sid = 1;
					$setting['setting'] = Settings::editGeneral($sid);
					$to_name = $setting['setting']->sender_name;
					$to_email = $setting['setting']->sender_email;
					$currency = $setting['setting']->site_currency_code;
					$from['info'] = Members::singlevendorData($user_id);
					$from_name = $from['info']->name;
					$from_email = $from['info']->email;
					$data = array('to_name' => $to_name, 'to_email' => $to_email, 'final_amount' => $final_amount, 'currency' => $currency, 'from_name' => $from_name, 'from_email' => $from_email, 'purchased_token' => $purchased_token);
					/* email template code */
					$checktemp = EmailTemplate::checkTemplate(21);
					if($checktemp != 0)
					{
						$template_view['mind'] = EmailTemplate::viewTemplate(21);
						$template_subject = $template_view['mind']->et_subject;
					}
					else
					{
						$template_subject = "Item Purchase Notifications";
					}
					/* email template code */
					Mail::send('admin_payment_mail', $data , function($message) use ($from_name, $from_email, $to_name, $to_email, $purchased_token, $template_subject) {
							$message->to($to_email, $to_name)
									->subject($template_subject);
							$message->from($from_email,$from_name);
						});
					 
					if($purchased_token != "")
					{
					Mail::send('admin_payment_mail', $data , function($message) use ($from_name, $from_email, $to_name, $to_email, $purchased_token, $template_subject) {
							$message->to($from_email,$from_name)
									->subject($template_subject);
							$message->from($to_email, $to_name);
						});
					}
					 
					/* referral per sale earning */
					if($custom_settings->affiliate_referral == 1)
					{
						$logged_id = Auth::user()->id;
						$buyer_details = Members::singlebuyerData($logged_id);
						$referral_by = $buyer_details->referral_by;
						
						/* new code */
						if($custom_settings->per_sale_referral_commission_type == 'fixed')
						{
							$per_sale_commission = $setting['setting']->per_sale_referral_commission;
						}
						else
						{
							$per_sale_commission = ($setting['setting']->per_sale_referral_commission * $final_amount) / 100;
						}
						$referral_commission = $per_sale_commission;
						/* new code */
						$check_referral = Members::referralCheck($referral_by);
						if($check_referral != 0)
						{
							$referred['display'] = Members::referralUser($referral_by);
							$wallet_amount = $referred['display']->earnings + $referral_commission;
							$referral_amount = $referred['display']->referral_amount + $referral_commission;
							$update_data = array('earnings' => $wallet_amount, 'referral_amount' => $referral_amount);
							Members::updateReferral($referral_by,$update_data);
						} 
					}
					/* referral per sale earning */
					$data_record = array('payment_token' => $payment_token);
					return view('success')->with($data_record);
					
					
				}
		     
		  
		  }
		  /* stripe code */

		  
		  
	   }
	   return view('checkout')->with($totaldata);
	
	
	}
	
	
	public function view_cart(Request $request)
	{
	  $item_price = $request->input('item_price');
	  $user_id = $request->input('user_id');
	  $product_id = $request->input('product_id');
	  $product_name = $request->input('product_name');
	  $product_user_id = $request->input('product_user_id');
	  $product_token = $request->input('product_token');
	  $session_id = Session::getId();
	  
	  $split = explode("_", $item_price);
         
       $price = base64_decode($split[0]);
	   
	   $license = $split[1];
	   if($license == 'regular')
	   {
	     $start_date = date('Y-m-d');
		 $end_date = date('Y-m-d', strtotime('+6 month'));
	   }
	   else if($license == 'extended')
	   {
	     $start_date = date('Y-m-d');
		 $end_date = date('Y-m-d', strtotime('+12 month'));
	   }
	   
	   $order_status = 'pending';
	   
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   
	   $extra_fee = $setting['setting']->site_extra_fee;
	   $admin_amount = $price;
	   
	   
	   $getcount  = Product::getorderCount($product_id,$session_id,$order_status);
	   
	   $savedata = array('session_id' => $session_id, 'product_id' => $product_id, 'product_name' => $product_name, 'product_user_id' => $product_user_id, 'product_token' => $product_token, 'license' => $license, 'start_date' => $start_date, 'end_date' => $end_date, 'product_price' => $price, 'admin_amount' => $admin_amount, 'total_price' => $price, 'order_status' => $order_status);
	   
	   
	   $updatedata = array('license' => $license, 'start_date' => $start_date, 'end_date' => $end_date, 'product_price' => $price, 'total_price' => $price);
	   
	   if($getcount == 0)
	   {
	      Product::savecartData($savedata);
		 
		  return redirect('cart')->with('success','Product has been added to cart'); 
	   }
	   else
	   {
	      Product::updatecartData($product_id,$session_id,$order_status,$updatedata);
		  
		  return redirect('cart')->with('success','Product has been updated to cart'); 
	   }
	   
	   
	  
	
	}
	
	public function remove_coupon($remove,$coupon)
	{  
	   $session_id = Session::getId();
	   $data = array('coupon_id' => '', 'coupon_code' => '', 'coupon_type' => '', 'coupon_value' => '', 'discount_price' => 0);
	   Product::removeCoupon($coupon,$session_id,$data);
	   return redirect()->back()->with('success', 'Coupon Removed Successfully.');
	}
	
	
		
	public function view_coupon(Request $request)
	{
	   $allsettings = Settings::allSettings();
	   $coupon = $request->input('coupon');
	   $session_id = Session::getId();
	   $coupon_key = uniqid();
	   $coupon_usage_type = "product";
	   $check_coupon = Product::checkCoupon($coupon,$coupon_usage_type);
	   if($check_coupon == 1)
	   {
	      $single = Product::singleCoupon($coupon,$coupon_usage_type);
	      $coupondata['get'] = Product::getCoupon($coupon,$coupon_usage_type,$session_id);
		  foreach($coupondata['get'] as $couponview)
		  {
		     $order_id = $couponview->ord_id;
			 $coupon_id = $single->coupon_id;
			 $coupon_code = $single->coupon_code;
			 $coupon_type = $single->discount_type;
			 $coupon_value = $single->coupon_value;
			 $price = $couponview->product_price;
			/* $price = $couponview->item_price;*/
			 if($coupon_type == 'percentage')
			 {
			 $discount = ($coupon_value * $price) / 100;
			 $discount_price = $price - $discount;
			 $data = array('coupon_key' => $coupon_key, 'coupon_id' => $coupon_id, 'coupon_code' => $coupon_code, 'coupon_type' => $coupon_type, 'coupon_value' => $coupon_value, 'discount_price' => $discount_price);
			 Product::updateCoupon($order_id,$data);
			 }
			 else
			 {
			    if($coupon_value < $price)
				{
			     $discount = $coupon_value;
				 $discount_price = $price - $discount;
				 $data = array('coupon_key' => $coupon_key, 'coupon_id' => $coupon_id, 'coupon_code' => $coupon_code, 'coupon_type' => $coupon_type, 'coupon_value' => $coupon_value, 'discount_price' => $discount_price);
			 Product::updateCoupon($order_id,$data);
				}
				else
				{
				 $discount = 0; 
				 return redirect()->back()->with('error', 'Invalid Coupon Code or Expired');
				}
			 }
			
		  }
		  return redirect()->back()->with('success', 'Coupon Added Successfully.');
	   }
	   else
	   {
	      return redirect()->back()->with('error', 'Invalid Coupon Code or Expired');
	   }
	
	}
	
	public function show_cart()
	{
	  $session_id = Session::getId();
	  if(Auth::check())
	  { 
	  $user_id = Auth::user()->id;
	  $update_data = array('user_id' => $user_id); 
	  Product::changeOrder($session_id,$update_data);
	  }
	  $cart['item'] = Product::getcartData();
	  $cart_count = Product::getcartCount();
	   $data = array('cart' => $cart, 'cart_count' => $cart_count);
	   
	   return view('cart')->with($data);
	}
	
	public function clear_cart()
	{
	   $session_id = Session::getId();
	   Product::clearcartdata($session_id);
	  
	  return redirect()->back()->with('success', 'Your cart has been cleared');
	}
	
	public function remove_cart_item($ordid)
	{
	  
	   $ord_id = base64_decode($ordid); 
	   Product::deletecartdata($ord_id);
	  
	  return redirect()->back()->with('success', 'Cart product has been removed');
	  
	}
	
	
	
	/* cart */
	
	public function payment_cancel()
	{
	  return view('cancel');
	}
	
	public function not_found()
	{
	  return view('404');
	}
	
	
	public function autoComplete(Request $request) {
	    
        $query = $request->get('term','');
        
        $products=Product::autoSearch($query);
        
        $data=array();
        foreach ($products as $product) {
                $data[]=array('value'=>$product->product_name,'id'=>$product->product_id);
        }
        if(count($data))
             return $data;
        else
            return ['value'=>'No Result Found','id'=>''];
    }
	
	public function view_free_item($download,$item_token)
	{
	  
	  if(Auth::check())
	  {
	  $today_date = date('Y-m-d');
	  $download_count_checks = Members::checkdownloadDate(Auth::user()->id,$today_date);
	  
	  if($download_count_checks->user_subscr_item_level == 'limited')
	  {
	  
			  if(Auth::user()->user_subscr_item > $download_count_checks->user_today_download_limit)
			  {
			     
				  $token = base64_decode($item_token);
				  $allsettings = Settings::allSettings();
				  $item['data'] = Product::editproductData($token);
				  $tempsplit= explode('.',$item['data']->product_file);
				  $extension = end($tempsplit);
				  $item_count = $item['data']->download_count + 1;
				  $data = array('download_count' => $item_count);
				  Product::updateproductData($token,$data);
				  
				  $downoad_count = Auth::user()->user_today_download_limit + 1;
				  $up_level_download = array('user_today_download_limit' => $downoad_count);
				  Members::updateReferral(Auth::user()->id,$up_level_download);
				  /* wasabi */
				  $wasabi_access_key_id = $allsettings->wasabi_access_key_id;
				  $wasabi_secret_access_key = $allsettings->wasabi_secret_access_key;
				  $wasabi_default_region = $allsettings->wasabi_default_region;
				  $wasabi_bucket = $allsettings->wasabi_bucket;
				  $wasabi_endpoint = 'https://s3.'.$wasabi_default_region.'.wasabisys.com';
				  $raw_credentials = array(
												'credentials' => [
													'key' => $wasabi_access_key_id,
													'secret' => $wasabi_secret_access_key
												],
												'endpoint' => $wasabi_endpoint, 
												'region' => $wasabi_default_region, 
												'version' => 'latest',
												'use_path_style_endpoint' => true
											);
				  $s3 = S3Client::factory($raw_credentials);
				  /* wasabi */
				  if($item['data']->product_file_type == 'file')
				  {
						  if($allsettings->site_s3_storage == 1)
						  {
									$result = $s3->getObject(['Bucket' => $wasabi_bucket,'Key' => $item['data']->product_file]);
									$myFile = $result["@metadata"]["effectiveUri"];
									$newName = uniqid().time().'.'.$extension;
									header("Cache-Control: public");
									header("Content-Description: File Transfer");
									header("Content-Disposition: attachment; filename=" . basename($newName));
									header("Content-Type: application/octet-stream");
									return readfile($myFile);
								
						  }
						  else if($allsettings->site_s3_storage == 2)
						  {
								$myFile = Storage::disk('dropbox')->url($item['data']->product_file);
								$newName = uniqid().time().'.zip';
								header("Cache-Control: public");
								header("Content-Description: File Transfer");
								header("Content-Disposition: attachment; filename=" . basename($newName));
								header("Content-Type: application/octet-stream");
								return readfile($myFile);
						  }
						  else if($allsettings->site_s3_storage == 3)
						  {
											$filename = $item['data']->product_file;
											$dir = '/';
											$recursive = false; 
											$contents = collect(Storage::disk('google')->listContents($dir, $recursive));
											$file = $contents
											->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
											->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
											->first(); 
											$display_product_file = Storage::disk('google')->download($file['path']);
											return $display_product_file;
						  }
						  else if($allsettings->site_s3_storage == 4)
							{
									$myFile = Storage::disk('s3')->url($item['data']->product_file);
									$newName = uniqid().time().'.'.$extension;
									header("Cache-Control: public");
									header("Content-Description: File Transfer");
									header("Content-Disposition: attachment; filename=" . basename($newName));
									header("Content-Type: application/octet-stream");
									return readfile($myFile);
							   
							}
						  else
						  {
						  $filename = public_path().'/storage/product/'.$item['data']->product_file;
						  $headers = ['Content-Type: application/octet-stream'];
						  $new_name = uniqid().time().'.zip';
						  return response()->download($filename,$new_name,$headers);
						  
						  }
					
					}
					else
					{
					   return redirect($item['data']->product_file_link);
					}
					
				}
				else
				{
				   return redirect()->back()->with('error', 'Sorry! Today your download limit reached. please check your profile page');
				}
			
			
			}  // limited download
			
			
			
			else  // unlimited download
			{
			   
			    $token = base64_decode($item_token);
				  $allsettings = Settings::allSettings();
				  $item['data'] = Product::editproductData($token);
				  $tempsplit= explode('.',$item['data']->product_file);
				  $extension = end($tempsplit);
				  $item_count = $item['data']->download_count + 1;
				  $data = array('download_count' => $item_count);
				  Product::updateproductData($token,$data);
				  
				  $downoad_count = Auth::user()->user_today_download_limit + 1;
				  $up_level_download = array('user_today_download_limit' => $downoad_count);
				  Members::updateReferral(Auth::user()->id,$up_level_download);
				  /* wasabi */
				  $wasabi_access_key_id = $allsettings->wasabi_access_key_id;
				  $wasabi_secret_access_key = $allsettings->wasabi_secret_access_key;
				  $wasabi_default_region = $allsettings->wasabi_default_region;
				  $wasabi_bucket = $allsettings->wasabi_bucket;
				  $wasabi_endpoint = 'https://s3.'.$wasabi_default_region.'.wasabisys.com';
				  $raw_credentials = array(
												'credentials' => [
													'key' => $wasabi_access_key_id,
													'secret' => $wasabi_secret_access_key
												],
												'endpoint' => $wasabi_endpoint, 
												'region' => $wasabi_default_region, 
												'version' => 'latest',
												'use_path_style_endpoint' => true
											);
				  $s3 = S3Client::factory($raw_credentials);
				  /* wasabi */
				  if($item['data']->product_file_type == 'file')
				  {
						  if($allsettings->site_s3_storage == 1)
						  {
									$result = $s3->getObject(['Bucket' => $wasabi_bucket,'Key' => $item['data']->product_file]);
									$myFile = $result["@metadata"]["effectiveUri"];
									$newName = uniqid().time().'.'.$extension;
									header("Cache-Control: public");
									header("Content-Description: File Transfer");
									header("Content-Disposition: attachment; filename=" . basename($newName));
									header("Content-Type: application/octet-stream");
									return readfile($myFile);
								
						  }
						  else if($allsettings->site_s3_storage == 2)
						  {
								$myFile = Storage::disk('dropbox')->url($item['data']->product_file);
								$newName = uniqid().time().'.zip';
								header("Cache-Control: public");
								header("Content-Description: File Transfer");
								header("Content-Disposition: attachment; filename=" . basename($newName));
								header("Content-Type: application/octet-stream");
								return readfile($myFile);
						  }
						  else if($allsettings->site_s3_storage == 3)
						  {
											$filename = $item['data']->product_file;
											$dir = '/';
											$recursive = false; 
											$contents = collect(Storage::disk('google')->listContents($dir, $recursive));
											$file = $contents
											->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
											->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
											->first(); 
											$display_product_file = Storage::disk('google')->download($file['path']);
											return $display_product_file;
						  }
						  else if($allsettings->site_s3_storage == 4)
							{
									$myFile = Storage::disk('s3')->url($item['data']->product_file);
									$newName = uniqid().time().'.'.$extension;
									header("Cache-Control: public");
									header("Content-Description: File Transfer");
									header("Content-Disposition: attachment; filename=" . basename($newName));
									header("Content-Type: application/octet-stream");
									return readfile($myFile);
							   
							}
						  else
						  {
						  $filename = public_path().'/storage/product/'.$item['data']->product_file;
						  $headers = ['Content-Type: application/octet-stream'];
						  $new_name = uniqid().time().'.zip';
						  return response()->download($filename,$new_name,$headers);
						  }
					
					}
					else
					{
					   return redirect($item['data']->product_file_link);
					}
			   
			   
			
			
			} // unlimited download
			
				
	 
	  }
	  else
	  {
	     return redirect('/404');
	  }  		
				  
	
	}
	
	public function view_item($slug)
	{
	  $checkitem = Product::singleitemCount($slug);
	  if($checkitem != 0)
	  {
	  $item['view'] = Product::singleitemData($slug);
	  $view_count = $item['view']->product_views + 1;
	  $product_token = $item['view']->product_token;
	  $product_id = $item['view']->product_id;
	  $count_data = array('product_views' => $view_count);
	  Product::updatefavouriteData($product_id,$count_data);
	  $getcount = Product::getimagesCount($product_token);
	  $getfirst['image'] = Product::getimagesFirst($product_token);
	  $getall['image'] = Product::getimagesAll($product_token);
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  $page_slug = $setting['setting']->product_support_link;
	  $page['view'] = Pages::editpageData($page_slug);
	  if (Auth::check()) 
	  {
	  $checkif_purchased = Product::ifpurchaseCount($product_token);
	  }
	  else
	  {
	    $checkif_purchased = 0;
	  }
	    
	   
	   
	   $comment['view'] = Comment::with('ReplyComment')->leftjoin('users', 'users.id', '=', 'product_comments.comm_user_id')->where('product_comments.comm_product_id','=',$product_id)->orderBy('comm_id', 'asc')->get();
	  
	  $comment_count = $comment['view']->count();
	  
	  $getreviewdata['view']  = Product::getreviewItems($product_id);
	  $review_count = Product::getreviewCount($product_id);
	  
	  
	  $getreview  = Product::getreviewRecord($product_id);
	  if($getreview !=0)
	  {
	      $review['view'] = Product::getreviewView($product_id);
		  $top = 0;
		  $bottom = 0;
		  foreach($review['view'] as $review)
		  {
		     if($review->rating == 1) { $value1 = $review->rating*1; } else { $value1 = 0; }
			 if($review->rating == 2) { $value2 = $review->rating*2; } else { $value2 = 0; }
			 if($review->rating == 3) { $value3 = $review->rating*3; } else { $value3 = 0; }
			 if($review->rating == 4) { $value4 = $review->rating*4; } else { $value4 = 0; }
			 if($review->rating == 5) { $value5 = $review->rating*5; } else { $value5 = 0; }
			 
			 $top += $value1 + $value2 + $value3 + $value4 + $value5;
			 $bottom += $review->rating;
			 
		  }
		  if(!empty(round($top/$bottom)))
		  {
		    $count_rating = round($top/$bottom);
		  }
		  else
		  {
		    $count_rating = 0;
		  }
		  
		  
		  
	  }
	  else
	  {
	    $count_rating = 0;
	  }
	  $viewattribute['details'] = Attribute::getattributeViews($product_token);
	  $related['product'] = Product::with('ratings')->join('category', 'category.cat_id', '=', 'product.product_category_parent')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_token','!=',$product_token)->inRandomOrder()->take(6)->get();
	  
	  $data = array('item' => $item, 'getcount' => $getcount, 'getfirst' => $getfirst, 'getall' => $getall, 'page' => $page, 'checkif_purchased' => $checkif_purchased,   'comment_count' => $comment_count, 'comment' => $comment, 'getreviewdata' => $getreviewdata, 'review_count' => $review_count, 'getreview' => $getreview, 'count_rating' => $count_rating, 'slug' => $slug, 'related' => $related, 'viewattribute' => $viewattribute);
	  return view('item')->with($data);
	  }
	  else
	  {
	     return redirect('404');
	  }
	
	}
	
	
	public function view_free_items()
	{
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $free['product'] = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_free','=',1)->orderBy('product.product_date', 'desc')->get();
	   $data = array('setting' => $setting, 'free' => $free);
	   return view('free-items')->with($data);
	}
	
	
	public function view_featured_items()
	{
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $featured['product'] = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_featured','=',1)->orderBy('product.product_date', 'desc')->get();
	   $data = array('setting' => $setting, 'featured' => $featured);
	   return view('featured-items')->with($data);
	}
    
	public function view_sale_items()
	{
	
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $end_sale = $setting['setting']->site_flash_end_date;
	   $today_date = date('Y-m-d');
	   if($end_sale <= $today_date)
	   {
	     $off_flash = array('product_flash_sale' => 0);
		 Product::offFlash($off_flash);
	   }
	   $flash['product'] = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_flash_sale','=',1)->orderBy('product.product_date', 'desc')->get();
	   $data = array('setting' => $setting, 'flash' => $flash);
	   return view('sale')->with($data);
	
	}
	
	public function view_popular_items()
	{
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $popular['product'] = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_views', 'desc')->get();
	   $data = array('setting' => $setting, 'popular' => $popular);
	   return view('popular-items')->with($data);
	}
	
	
	public function view_subscriber_items()
	{
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $subscriber_downloads = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.subscription_item','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_id', 'desc')->get();
	   $data = array('setting' => $setting, 'subscriber_downloads' => $subscriber_downloads);
	   return view('subscriber-downloads')->with($data);
	}
	
	
	public function view_new_items()
	{
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $newest['product'] = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_id', 'desc')->get();
	   $data = array('setting' => $setting, 'newest' => $newest);
	   return view('new-releases')->with($data);
	}
	
    public function view_index()
	{
	   
	   
	   
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $end_sale = $setting['setting']->site_flash_end_date;
	   $today_date = date('Y-m-d');
	   if($end_sale <= $today_date)
	   {
	     $off_flash = array('product_flash_sale' => 0);
		 Product::offFlash($off_flash);
	   }
	   $take_featured = $setting['setting']->home_featured_items;
	   $take_flash = $setting['setting']->home_flash_items;
	   $take_popular = $setting['setting']->home_popular_items;
	   $take_newest = $setting['setting']->home_new_items;
	   $take_free = $setting['setting']->home_free_items;
	   $take_subscribes = $setting['setting']->home_subscriber_items;
	   
	   $featured_products = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_featured','=',1)->orderBy('product.product_date', 'desc')->take($take_featured)->get();
	   $popular_product = Product::with('ratings')->leftjoin('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_views', 'desc')->take($take_popular)->get();
	   $subscribe_downloads = Product::with('ratings')->join('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.subscription_item','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_id', 'desc')->take($take_subscribes)->get();
	   $flash_product = Product::with('ratings')->join('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_flash_sale','=',1)->orderBy('product.product_date', 'desc')->take($take_flash)->get();
	   $free_product = Product::with('ratings')->join('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','category.category_slug','category.category_name','product.product_sold','product.product_id','product.product_liked')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_free','=',1)->orderBy('product.product_date', 'desc')->take($take_free)->get();
	   $newest_product = Product::with('ratings')->join('category', 'category.cat_id', '=', 'product.product_category_parent')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_id', 'desc')->take($take_newest)->get();
	   
	   
	   $latestpost['view'] = Blog::homepostData($setting['setting']->home_blog_post);
	   $viewlogo['display'] = Blog::homelogoData();
	   $comments = Blog::getgroupcommentData();
	   $category_box = Category::mainmenuCategoryData($setting['setting']->home_categories_icon,'asc');
	   
	  return view('index',['free_product' => $free_product, 'setting' => $setting, 'featured_products' => $featured_products, 'flash_product' => $flash_product, 'popular_product' => $popular_product, 'newest_product' => $newest_product, 'latestpost' => $latestpost, 'viewlogo' => $viewlogo, 'comments' => $comments, 'subscribe_downloads' => $subscribe_downloads, 'category_box' => $category_box]); 
	}
	
	
	
	public function view_shop_items(Request $request)
	{
	  $method = "get";
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  $product_item = $request->input('product_item');
	  if(!empty($request->input('category_names')))
	   {
	      
		  $category_no = "";
		  foreach($request->input('category_names') as $category_value)
		  {
		     $category_no .= $category_value.',';
		  }
		  $category_names = rtrim($category_no,",");
		  
	   }
	   else
	   {
	     $category_names = "";
	   }
	  if(!empty($request->input('orderby')))
	  { 
	  $orderby = $request->input('orderby');
	  }
	  else
	  {
	  $orderby = "desc";
	  }
	  $min_price = $request->input('min_price');
	  $max_price = $request->input('max_price'); 
	  if($product_item != "" ||  $orderby != "" || $min_price != "" || $max_price != "")
	  {
	  $itemData['item'] = Product::with('ratings')
	                      ->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')
						  ->leftjoin('users', 'users.id', '=', 'product.user_id')
	                      ->where('product.product_status','=',1)
						  ->where('product.product_drop_status','=','no')
						  ->where(function ($query) use ($product_item,$category_names,$orderby,$min_price,$max_price) { 
						  $query->where('product.product_name', 'LIKE', "%$product_item%");
						  if ($min_price != "" || $max_price != "")
						  {
						  $query->where('product.regular_price', '>', $min_price);
						  $query->where('product.regular_price', '<', $max_price);
						  }
						  if ($category_names != "")
						  {
						  $query->whereRaw('FIND_IN_SET(product.product_type_cat_id,"'.$category_names.'")');
						  }
						  })->orderBy('product.regular_price', $orderby)->paginate($setting['setting']->product_per_page);
						  
						  
	  }
	  else
	  {
	   $itemData['item'] = Product::with('ratings')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')->leftjoin('users', 'users.id', '=', 'product.user_id')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_id', 'desc')->paginate($setting['setting']->product_per_page); 
	   
	   
	  }
	 	 
	 $catData['item'] = Product::getitemcatData();
	
	$browser['view'] = Product::browserData();
	$package['view'] = Product::packData();
	/*$category['view'] = Category::categorydisplayOrder();*/
	$type = "";
	$meta_keyword = "";
	$meta_desc = "";
	$count_item = Product::getgroupitemData();
	$category['view'] = Category::with('SubCategory')->where('category_status','=','1')->where('drop_status','=','no')->orderBy('display_order','asc')->get();
	$minprice['price'] = Product::minpriceData();
	  $maxprice['price'] = Product::maxpriceData();
	return view('shop',[ 'itemData' => $itemData, 'catData' => $catData, 'browser' => $browser, 'package' => $package, 'category' => $category, 'type' => $type, 'meta_keyword' => $meta_keyword, 'meta_desc' => $meta_desc, 'count_item' => $count_item, 'method' => $method, 'minprice' => $minprice, 'maxprice' => $maxprice]);
	}
	
	
	
	public function view_all_items()
	{
	  $method = "get";
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  $custom_settings = Settings::editCustom();
	  if($custom_settings->shop_search_type == 'normal')
	  {
	  $itemData['item'] = Product::with('ratings')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')->leftjoin('users', 'users.id', '=', 'product.user_id')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_id', 'asc')->paginate($setting['setting']->product_per_page);
	  }
	  else
	  {
	  $itemData['item'] = Product::with('ratings')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')->leftjoin('users', 'users.id', '=', 'product.user_id')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->orderBy('product.product_id', 'asc')->get();
	  }
	  
	  $catData['item'] = Product::getitemcatData();
	  
	  $browser['view'] = Product::browserData();
	   $package['view'] = Product::packData();
	  /*$category['view'] = Category::categorydisplayOrder();*/
	  $type = "";
	  $meta_keyword = "";
	  $meta_desc = "";
	  $count_item = Product::getgroupitemData();
	  
	  $category['view'] = Category::with('SubCategory')->where('category_status','=','1')->where('drop_status','=','no')->orderBy('display_order','asc')->get();
	  $minprice['price'] = Product::minpriceData();
	  $maxprice['price'] = Product::maxpriceData();
	  
	  return view('shop',[ 'itemData' => $itemData, 'catData' => $catData, 'browser' => $browser, 'package' => $package, 'category' => $category, 'type' => $type, 'meta_keyword' => $meta_keyword, 'meta_desc' => $meta_desc, 'count_item' => $count_item, 'method' => $method, 'minprice' => $minprice, 'maxprice' => $maxprice]);
	  
	}
	
	
	public function view_category_items($type,$slug)
	{
	           $custom_settings = Settings::editCustom();
	           $method = "get";
			   $sid = 1;
	           $setting['setting'] = Settings::editGeneral($sid);
	           if($type == 'category')
	           {
				   $check_data = Category::getcategoryCheck($slug);
				   if($check_data != 0)
				   {
				   $category_data = Category::getcategorysingle($slug);
				   $cat_id = $category_data->cat_id;
				   $meta_keyword = $category_data->category_meta_keywords;
	               $meta_desc = $category_data->category_meta_desc;
					   if($custom_settings->shop_search_type == 'normal')
					   {
					   $itemData['item'] = Product::with('ratings')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')->leftjoin('users', 'users.id', '=', 'product.user_id')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_category_parent','=',$cat_id)->orderBy('product.product_id', 'desc')->paginate($setting['setting']->product_per_page);
					   }
					   else
					   {
					   $itemData['item'] = Product::with('ratings')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')->leftjoin('users', 'users.id', '=', 'product.user_id')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_category_parent','=',$cat_id)->orderBy('product.product_id', 'desc')->get();
					   }
				   }
				   else
				   {
				     return view('/404');
				   }
				}
				else
				{
				  $check_data = Category::getsubcategoryCheck($slug);
				  if($check_data != 0)
				  {
				  $category_data = Category::getsubcategorysingle($slug);
				  $cat_id = $category_data->subcat_id;
				  $meta_keyword = "";
				  $meta_desc = "";
					  if($custom_settings->shop_search_type == 'normal')
					  {
					  $itemData['item'] = Product::with('ratings')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')->leftjoin('users', 'users.id', '=', 'product.user_id')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_category_type','=',$type)->where('product.product_category','=',$cat_id)->orderBy('product.product_id', 'desc')->paginate($setting['setting']->product_per_page);
					  }
					  else
					  {
					  $itemData['item'] = Product::with('ratings')->select('product.product_name','product.product_slug','product.product_flash_sale','product.regular_price','product.user_id','product.product_token','product.product_image','product.product_sold','product.product_id','product.product_liked','product.product_type_cat_id','product.product_category_parent','product.product_free')->leftjoin('users', 'users.id', '=', 'product.user_id')->where('product.product_status','=',1)->where('product.product_drop_status','=','no')->where('product.product_category_type','=',$type)->where('product.product_category','=',$cat_id)->orderBy('product.product_id', 'desc')->paginate($setting['setting']->product_per_page);
					  }
				  }
				  else
				  {
				     return view('/404');
				  }
				}
	   
	  $catData['item'] = Product::getitemcatData();
	  
	  $browser['view'] = Product::browserData();
	   $package['view'] = Product::packData();
	  /*$category['view'] = Category::categorydisplayOrder();*/
	  $category['view'] = Category::with('SubCategory')->where('category_status','=','1')->where('drop_status','=','no')->orderBy('display_order','asc')->get();
	  
	  $count_item = Product::getgroupitemData();
	  $minprice['price'] = Product::minpriceData();
	  $maxprice['price'] = Product::maxpriceData();
	  return view('shop',[ 'itemData' => $itemData, 'catData' => $catData, 'browser' => $browser, 'package' => $package, 'category' => $category, 'type' => $type, 'meta_keyword' => $meta_keyword, 'meta_desc' => $meta_desc, 'count_item' => $count_item, 'method' => $method, 'minprice' => $minprice, 'maxprice' => $maxprice]);
	
	}
	
	
	
	public function all_categories()
	{
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  $category['view'] = Category::quickbookData();
	  $count_cause = Category::getgroupcauseData();
	  $data = array('setting' => $setting, 'category' => $category, 'count_cause' => $count_cause);
	  return view('categories')->with($data); 
	}
	
	
	public function all_gallery()
	{
	  $gallery['view'] = Events::viewallGallery(); 
	  $data = array('gallery' => $gallery);
	  return view('gallery')->with($data);
	
	}
	
	
	
	public function donor_paypal_success($ord_token, Request $request)
	{
	
	$payment_token = $request->input('tx');
	$purchased_token = $ord_token;
	$donor['details'] = Causes::getDonor($purchased_token);
	$user_id = $donor['details']->donor_cause_user_id;
	$checkcount = Causes::checkuserSubscription($user_id);
	$sid = 1;
	$setting['setting'] = Settings::editGeneral($sid);
	$user_data['view'] = Members::singlebuyerData($user_id);
	if($checkcount == 0)
	{
		$commission = ($setting['setting']->site_admin_commission * $donor['details']->donor_amount) / 100;
		$user_amount = $donor['details']->donor_amount - $commission;
		$admin_amount = $commission;
		$user_old_amount = $user_data['view']->earnings + $user_amount;
		$admin_details['view'] = Members::adminData();
		$admin_old_amount = $admin_details['view']->earnings + $admin_amount;
		$user_record = array('earnings' => $user_old_amount);
		Members::updateuserPrice($user_id, $user_record);
		$admin_data = array('earnings' => $admin_old_amount);
		Members::updateuserPrice(1, $admin_data);			   
				  
	}
	$cause_id = $donor['details']->donor_cause_id;
	$cause['details'] = Causes::singleCausesdetails($cause_id);
	$raised_price = $cause['details']->cause_raised + $donor['details']->donor_amount;
	$pricedata = array('cause_raised' => $raised_price);
	Causes::updatecausePrice($cause_id,$pricedata);
	
	$checkoutdata = array('donor_payment_token' => $payment_token, 'donor_payment_status' => 'completed');
	Causes::updatedonorData($purchased_token,$checkoutdata);
	$result_data = array('payment_token' => $payment_token);
	
	$check_email_support = Members::getuserSubscription($user_id);
	if($check_email_support == 1)
	{   
	    $donor_payment_amount = $donor['details']->donor_amount;
		$admin_name = $setting['setting']->sender_name;
		$admin_email = $setting['setting']->sender_email;
		$currency_symbol = $setting['setting']->site_currency_symbol;
		$cause_url = URL::to('/cause/').$cause['details']->cause_slug;
		$record = array('donor_payment_amount' => $donor_payment_amount, 'currency_symbol' => $currency_symbol, 'cause_url' => $cause_url);
		$to_name = $user_data['view']->name;
		$to_email = $user_data['view']->email;
		Mail::send('donation_mail', $record, function($message) use ($admin_name, $admin_email, $to_email, $to_name) {
		$message->to($to_email, $to_name)
			->subject('Donation payment received');
			$message->from($admin_email,$admin_name);
			});
	}
	return view('donor-success')->with($result_data);
	
	}
	
	
	
	public function confirm_donation(Request $request)
	{
	   
	   $token = $request->input('token');
	   $donor_name = $request->input('donor_name');
	   $donor_email = $request->input('donor_email'); 
	   $donor_phone = $request->input('donor_phone');
	   $donor_amount = $request->input('donor_amount');
	   $donor_note = $request->input('donor_note'); 
	   $cause_title = $request->input('cause_title');
	   $cause_slug = $request->input('cause_slug');
	   $image_size = $request->input('image_size');   
	   $purchase_token = rand(111111,999999);
	   $payment_method = $request->input('payment_method');
	   $website_url = $request->input('website_url');
	   $donor_purchase_date = date('Y-m-d');
	   $donor_cause_id = $request->input('donor_cause_id');
	   $cause_raised = base64_decode($request->input('cause_raised'));
	   $donor_cause_token = $request->input('donor_cause_token');
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $user_id = $request->input('cause_user_id');
	   $raised_price = $cause_raised + $donor_amount;
	   $miniumum_amount = $setting['setting']->site_minimum_donate;
	   
	   
	   $request->validate([
							'donor_name' => 'required',
							'donor_email' => 'required',
							'donor_phone' => 'required',
							'donor_amount' => 'required|numeric|min:'.$miniumum_amount,
							'donor_photo' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							
							
         ]);
		 $rules = array(
								
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
	        
			
	   
	   
			   if ($request->hasFile('donor_photo')) 
			   {
							
							$image = $request->file('donor_photo');
							$img_name = time() . '.'.$image->getClientOriginalExtension();
							$destinationPath = public_path('/storage/donors');
							$imagePath = $destinationPath. "/".  $img_name;
							$image->move($destinationPath, $img_name);
							$donor_photo = $img_name;
			  }
			  else
			  {
				$donor_photo = "";
			  }
			   
	   
	   
			   $savedata = array('donor_cause_id' => $donor_cause_id, 'donor_cause_user_id' => $user_id, 'donor_cause_token' => $donor_cause_token, 'donor_name' => $donor_name, 'donor_email' => $donor_email, 'donor_phone' => $donor_phone, 'donor_amount' => $donor_amount, 'donor_note' => $donor_note, 'donor_payment_type' => $payment_method, 'donor_purchase_token' => $purchase_token, 'donor_purchase_date' => $donor_purchase_date, 'donor_photo' => $donor_photo, 'donor_payment_status' => 'pending');
			   
			   
			   $checkcount = Causes::checkuserSubscription($user_id);
			   $user_data['view'] = Members::singlebuyerData($user_id);
			   /* settings */
			   $site_currency = $setting['setting']->site_currency_code;
			   $success_url = $website_url.'/donor-success/'.$purchase_token;
			   $cancel_url = $website_url.'/cancel';
			   
			   if($checkcount == 1)
			   {
				   $paypal_email = $user_data['view']->user_paypal_email;
				   $paypal_mode = $user_data['view']->user_paypal_mode;
				   if($paypal_mode == 1)
				   {
					 $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
				   }
				   else
				   {
					 $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
				   }
				  
				   $stripe_mode = $user_data['view']->user_stripe_mode;
				   if($stripe_mode == 0)
				   {
					 $stripe_publish_key = $user_data['view']->user_test_publish_key;
					 $stripe_secret_key = $user_data['view']->user_test_secret_key;
				   }
				   else
				   {
					 $stripe_publish_key = $user_data['view']->user_live_publish_key;
					 $stripe_secret_key = $user_data['view']->user_live_secret_key;
				   }
			   
			   }
			   else
			   {
				  
				   $paypal_email = $setting['setting']->paypal_email;
				   $paypal_mode = $setting['setting']->paypal_mode;
				   if($paypal_mode == 1)
				   {
					 $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
				   }
				   else
				   {
					 $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
				   }
				  
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
				   
						  
			   }
			   
				   /* settings */
				   Causes::insertdonorData($savedata);
				   
				   if($payment_method == 'paypal')
					  {
						 
						 $paypal = '<form method="post" id="paypal_form" action="'.$paypal_url.'">
						  <input type="hidden" value="_xclick" name="cmd">
						  <input type="hidden" value="'.$paypal_email.'" name="business">
						  <input type="hidden" value="'.$cause_title.'" name="item_name">
						  <input type="hidden" value="'.$purchase_token.'" name="item_number">
						  <input type="hidden" value="'.$donor_amount.'" name="amount">
						  <input type="hidden" value="'.$site_currency.'" name="currency_code">
						  <input type="hidden" value="'.$success_url.'" name="return">
						  <input type="hidden" value="'.$cancel_url.'" name="cancel_return">
								  
						</form>';
						$paypal .= '<script>window.paypal_form.submit();</script>';
						echo $paypal;
								 
						 
					  }
					  /* stripe code */
					  else if($payment_method == 'stripe')
					  {
						 
									 
							$stripe = array(
								"secret_key"      => $stripe_secret_key,
								"publishable_key" => $stripe_publish_key
							);
						 
							\Stripe\Stripe::setApiKey($stripe['secret_key']);
						 
							
							$customer = \Stripe\Customer::create(array(
								'email' => $donor_email,
								'source'  => $token
							));
						 
							
							$cause_name = $cause_title;
							$donor_price = $donor_amount * 100;
							$currency = $site_currency;
							$book_id = $purchase_token;
						 
							
							$charge = \Stripe\Charge::create(array(
								'customer' => $customer->id,
								'amount'   => $donor_price,
								'currency' => $currency,
								'description' => $cause_name,
								'metadata' => array(
									'order_id' => $book_id
								)
							));
						 
							
							$chargeResponse = $charge->jsonSerialize();
						 
							
							if($chargeResponse['paid'] == 1 && $chargeResponse['captured'] == 1) 
							{
						 
								if($checkcount == 0)
								{
								   
								   $commission = ($setting['setting']->site_admin_commission * $donor_amount) / 100;
								   $user_amount = $donor_amount - $commission;
								   $admin_amount = $commission;
								   $user_old_amount = $user_data['view']->earnings + $user_amount;
								   $admin_details['view'] = Members::adminData();
								   $admin_old_amount = $admin_details['view']->earnings + $admin_amount;
								   $user_record = array('earnings' => $user_old_amount);
								   Members::updateuserPrice($user_id, $user_record);
								   $admin_data = array('earnings' => $admin_old_amount);
								   Members::updateuserPrice(1, $admin_data);
			
								  
								}
								$pricedata = array('cause_raised' => $raised_price);
								Causes::updatecausePrice($donor_cause_id,$pricedata);
													
								$payment_token = $chargeResponse['balance_transaction'];
								$purchased_token = $book_id;
								$checkoutdata = array('donor_payment_token' => $payment_token, 'donor_payment_status' => 'completed');
								Causes::updatedonorData($purchased_token,$checkoutdata);
								$data_record = array('payment_token' => $payment_token);
								
								
								$check_email_support = Members::getuserSubscription($user_id);
								if($check_email_support == 1)
								{   
									$donor_payment_amount = $donor_amount;
									$admin_name = $setting['setting']->sender_name;
									$admin_email = $setting['setting']->sender_email;
									$currency_symbol = $setting['setting']->site_currency_symbol;
									$cause_url = URL::to('/cause/').$cause_slug;
									$record = array('donor_payment_amount' => $donor_payment_amount, 'currency_symbol' => $currency_symbol, 'cause_url' => $cause_url);
									$to_name = $user_data['view']->name;
									$to_email = $user_data['view']->email;
									Mail::send('donation_mail', $record, function($message) use ($admin_name, $admin_email, $to_email, $to_name) {
									$message->to($to_email, $to_name)
										->subject('Donation payment received');
										$message->from($admin_email,$admin_name);
										});
								}
								return view('success')->with($data_record);
								
								
							}
						 
					  
					  }
					  /* stripe code */
					  
	     }
	
	
	}
	
	
	public function activate_newsletter($token)
	{
	   
	   $check = Members::checkNewsletter($token);
	   if($check == 1)
	   {
	      
		  $data = array('news_status' => 1);
		
		  Members::updateNewsletter($token,$data);
		  
		  return redirect('/newsletter')->with('success', 'Thank You! Your subscription has been confirmed!');
		  
	   }
	   else
	   {
	       return redirect('/newsletter')->with('error', 'This email address already subscribed');
	   }
	
	}
	
	
	public function view_newsletter()
	{
	 
	  return view('newsletter');
	
	}
	
	
	public function update_newsletter(Request $request)
	{
	
	   $news_email = $request->input('news_email');
	   $news_status = 0;
	   $news_token = $this->generateRandomString();
	   
	   $request->validate([
							
							'news_email' => 'required|email',
							
							
							
         ]);
		 $rules = array(
		 
		      'news_email' => ['required',  Rule::unique('newsletter') -> where(function($sql){ $sql->where('news_status','=',0);})],
								
	     );
		 
		 $messsages = array(
		      
	    );
		 
		$validator = Validator::make($request->all(), $rules,$messsages);
		
		if ($validator->fails()) 
		{
		 $failedRules = $validator->failed();
		 /*return back()->withErrors($validator);*/
		 return redirect()->back()->with('error', 'This email address already subscribed.');
		} 
		else
		{
		
		
		$data = array('news_email' => $news_email, 'news_token' => $news_token, 'news_status' => $news_status);
		
		Members::savenewsletterData($data);
		
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		
		$from_name = $setting['setting']->sender_name;
        $from_email = $setting['setting']->sender_email;
		$activate_url = URL::to('/newsletter').'/'.$news_token;
		
		$record = array('activate_url' => $activate_url);
		/* email template code */
	          $checktemp = EmailTemplate::checkTemplate(6);
			  if($checktemp != 0)
			  {
			  $template_view['mind'] = EmailTemplate::viewTemplate(6);
			  $template_subject = $template_view['mind']->et_subject;
			  }
			  else
			  {
			  $template_subject = "Newsletter Signup";
			  }
			  /* email template code */
		Mail::send('newsletter_mail', $record, function($message) use ($from_name, $from_email, $news_email, $template_subject) {
			$message->to($news_email)
					->subject($template_subject);
			$message->from($from_email,$from_name);
		});
		
			   
		return redirect()->back()->with('success', 'Your email address subscribed. You will receive a confirmation email.');
		
		}
	   
	
	}
	
	
	public function view_allcauses()
	{
	   $causes['view'] = Causes::viewallCauses();
	   $slug = '';
	   $data = array('causes' => $causes, 'slug' => $slug); 
	   return view('causes')->with($data);
	
	}
	
	
	public function view_category_causes($slug)
	{
	  $causes['view'] = Causes::viewcategoryCauses($slug);
	   $data = array('causes' => $causes, 'slug' => $slug); 
	   return view('causes')->with($data);
	}
	
	
	public function single_cause($slug)
	{
	  $single['view'] = Causes::singleCause($slug);
	  $user_id = $single['view']->cause_user_id;
	  $checkcount = Causes::checkuserSubscription($user_id);
	  if($checkcount == 0)
	  {
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  $get_payment = explode(',', $setting['setting']->payment_option);
	  }
	  else
	  {
	      $user['details'] = Members::singlebuyerData($user_id);
		  $get_payment = explode(',', $user['details']->user_payment_option);
	  }
	  
	    $x = $single['view']->cause_raised;
        $y = $single['view']->cause_goal;
        $percent = $x/$y;
        $percent_value = number_format( $percent * 100);
        if($percent_value >= 100)
        {
          $percent_val = 100;
        }
        else
        {
          $percent_val = $percent_value;
        }
		
		$donor['details'] = Causes::recentDonation($single['view']->cause_id);
        $data = array('single' => $single, 'percent_val' => $percent_val, 'get_payment' => $get_payment, 'donor' => $donor); 
	  
	   return view('cause')->with($data);
	}
	
	
	public function view_became_volunteer()
	{
	   return view('became-volunteer');
	}

    public function user_verify($user_token)
    {
	    
	    $allsettings = Settings::allSettings();
        $data = array('verified'=>'1');
		$user['user'] = Members::verifyuserData($user_token, $data);
		$custom_settings = Settings::editCustom();
		if($custom_settings->affiliate_referral == 1)
		{
			$check_ref = Members::refCount($user_token);
			if($check_ref != 0)
			{
				$user_data = Members::editData($user_token);
				$referral_by = $user_data->referral_by;
				
				  $referral_commission = $allsettings->site_referral_commission;
				  $check_referral = Members::referralCheck($referral_by);
				  if($check_referral != 0)
				  {
					  $referred['display'] = Members::referralUser($referral_by);
					  $wallet_amount = $referred['display']->earnings + $referral_commission;
					  $referral_amount = $referred['display']->referral_amount + $referral_commission;
					  $referral_count = $referred['display']->referral_count + 1;
					  
					  $update_data = array('earnings' => $wallet_amount, 'referral_amount' => $referral_amount, 'referral_count' => $referral_count);
					  Members::updateReferral($referral_by,$update_data);
				   }
				   $again_data = array('referral_payout' => 'completed');
				   Members::updateData($user_token,$again_data);
				
			}	
	    }		
		
		return redirect('login')->with('success','Your e-mail is verified. You can now login.');
    }
	
	
	public function single_volunteer($slug)
	{
	   $single['view'] = Volunteers::slugVolunteers($slug);
	   $data = array('single' => $single); 
	   return view('volunteer')->with($data);
	}
	
	
	public function all_volunteer()
	{
	
	  $display['view'] = Volunteers::allVolunteers();
	   $data = array('display' => $display); 
	   return view('volunteers')->with($data);
	
	}
	
	public function all_events()
	{
	
	  $display['view'] = Events::allEvents();
	  $category['view'] = Category::eventCategoryData();
	  $count_category = Category::getgroupeventData();
	  $slug = "";
	   $data = array('display' => $display, 'category' => $category, 'count_category' => $count_category, 'slug' => $slug); 
	   return view('events')->with($data);
	
	}
	
	
	public function single_event($slug)
	{
	   $single['view'] = Events::singleEvent($slug);
	   $category['view'] = Category::eventCategoryData();
	   $count_category = Category::getgroupeventData();
	   $recent['view'] = Events::recentEvent($slug);
	   $event_start_time = date('F d, Y H:i:s', strtotime($single['view']->event_start_date_time));
	   $data = array('single' => $single, 'category' => $category, 'count_category' => $count_category, 'slug' => $slug, 'recent' => $recent, 'event_start_time' => $event_start_time); 
	   return view('event')->with($data);
	
	}
	
	
	public function view_category_events($cat_id,$slug)
	{
	
	$display['view'] = Events::categoryEvents($cat_id);
	  $category['view'] = Category::eventCategoryData();
	  $count_category = Category::getgroupeventData();
	   $data = array('display' => $display, 'category' => $category, 'count_category' => $count_category, 'slug' => $slug); 
	   return view('events')->with($data);
	
	}
	
	
	
	
	
	public function view_forgot()
	{
	   return view('forgot');
	}
	
	public function view_contact()
	{
	   return view('contact');
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
	
	public function view_reset($token)
	{
	  $data = array('token' => $token);
	  return view('reset')->with($data);
	}
	
	
	
	public function update_reset(Request $request)
	{
	
	   $user_token = $request->input('user_token');
	   $password = bcrypt($request->input('password'));
	   $password_confirmation = $request->input('password_confirmation');
	   $data = array("user_token" => $user_token);
	   $value = Members::verifytokenData($data);
	   $user['user'] = Members::gettokenData($user_token);
	   if($value)
	   {
	   
	      $request->validate([
							'password' => 'required|confirmed|min:6',
							
           ]);
		 $rules = array(
				
				
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
		   
		   $record = array('password' => $password);
           Members::updatepasswordData($user_token, $record);
           return redirect('login')->with('success','Your new password updated successfully. Please login now.');
		
		}
	   
	   
	   }
	   else
	   {
              
			  return redirect()->back()->with('error', 'These credentials do not match our records.');
       }
	   
	   
	
	}
	
	
	
	public function update_forgot(Request $request)
	{
	   $email = $request->input('email');
	   
	   $data = array("email"=>$email);
 
       $value = Members::verifycheckData($data);
	   $user['user'] = Members::getemailData($email);
       
	   if($value == 1)
	   {
			
		$user_token = $user['user']->user_token;
		$name = $user['user']->name;
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		
		$from_name = $setting['setting']->sender_name;
        $from_email = $setting['setting']->sender_email;
		$forgot_url = URL::to('/reset/').'/'.$user_token;
		$record = array('user_token' => $user_token, 'forgot_url' => $forgot_url);
		/* email template code */
	    $checktemp = EmailTemplate::checkTemplate(4);
		if($checktemp != 0)
		{
			$template_view['mind'] = EmailTemplate::viewTemplate(4);
			$template_subject = $template_view['mind']->et_subject;
		}
		else
		{
		   $template_subject = "Forgot Password";
		}
		/* email template code */
		Mail::send('forgot_mail', $record, function($message) use ($from_name, $from_email, $email, $name, $user_token, $forgot_url, $template_subject) {
			$message->to($email, $name)
					->subject($template_subject);
			$message->from($from_email,$from_name);
		});
 
         return redirect('forgot')->with('success','We have e-mailed your password reset link!');     
			  
       }
	   else
	   {
              
			  return redirect()->back()->with('error', 'These credentials do not match our records.');
       }
	   
	  
	   
	   
	   
	}
	
	
	
	/* contact */
	
	public function update_contact(Request $request)
	{
	
	  $from_name = $request->input('from_name');
	  $from_email = $request->input('from_email');
	  $message_text = $request->input('message_text');
	  $sid = 1;
	  $setting['setting'] = Settings::editGeneral($sid);
	  	  
	  $admin_name = $setting['setting']->sender_name;
        $admin_email = $setting['setting']->sender_email;
	   if($setting['setting']->site_google_recaptcha == 1)
		{	
		   $request->validate([
								'from_name' => 'required',
								'from_email' => 'required|email',
								'message_text' => 'required',
								'g-recaptcha-response' => 'required|recaptchav3:register,0.5'
								
								
			 ]);
		 }
		 else
		 {
		    $request->validate([
							'from_name' => 'required',
							'from_email' => 'required|email',
							'message_text' => 'required',
							
							
							
             ]);
		 }	 
		 $rules = array(
				
				
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
		
	  
		  $record = array('from_name' => $from_name, 'from_email' => $from_email, 'message_text' => $message_text, 'contact_date' => date('Y-m-d'));
		  $contact_count = Members::getcontactCount($from_email);
		  if($contact_count == 0)
		  {
		  Members::saveContact($record);
		  /* email template code */
	      $checktemp = EmailTemplate::checkTemplate(3);
		  if($checktemp != 0)
		  {
			  $template_view['mind'] = EmailTemplate::viewTemplate(3);
			  $template_subject = $template_view['mind']->et_subject;
		  }
		  else
		  {
			  $template_subject = "Contact Us";
		  }
		  /* email template code */
		  Mail::send('contact_mail', $record, function($message) use ($admin_name, $admin_email, $from_email, $from_name, $template_subject) {
					$message->to($admin_email, $admin_name)
							->subject($template_subject);
					$message->from($from_email,$from_name);
				});
		  return redirect('contact')->with('success','Your message has been sent successfully');
		  }
		  else
		  {
		  return redirect('contact')->with('error','Sorry! Your message already sent');
		  }
	  
	  
	  }
	  
	  
	
	}
	
	/* contact */
	
	
	
	
}
