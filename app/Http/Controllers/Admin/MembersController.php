<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Members;
use DownGrade\Models\Settings;
use DownGrade\Models\Volunteers;
use DownGrade\Models\Subscription;
use DownGrade\Models\EmailTemplate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Auth;
use URL;
use Mail;
use Helper;


class MembersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
	
	public function custom()
	{
	    $dw_v = Helper::version_no();
		$custom = Settings::editCustom();
		return $custom->$dw_v;
	} 
	
	public function subscription_payment_details($token)
	{
	    $userData['data'] = Members::getJoinData($token);
		if($this->custom() != 0)
	    {
		return view('admin.subscription-payment-details',[ 'userData' => $userData]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	    
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
	
	
  
	/* edit profile */
		
	public function upgrade_customer($token,$subcr_id)
	{
	$purchased_token = $token;
	$subscr_id = $subcr_id;
	$subscr['view'] = Subscription::editsubData($subscr_id);
	$subscri_date = $subscr['view']->subscr_duration;
	$user_subscr_item_level = $subscr['view']->subscr_item_level;
	$user_subscr_item = $subscr['view']->subscr_item;
	$user_subscr_type = $subscr['view']->subscr_name;
	$subscr_value = "+".$subscri_date;
	$subscr_date = date('Y-m-d', strtotime($subscr_value));
	
	$payment_status = 'completed';
	
	$checkoutdata = array('user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item, 'user_subscr_payment_status' => $payment_status);
	Subscription::confirmupgradeData($token,$checkoutdata);
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
	
    return redirect()->back()->with('success', 'Membership has been upgrade');
	   
	}
	
	
	
	public function edit_profile()
    {
        $token = Auth::user()->id;
		$edit['userdata'] = Members::editprofileData($token);
		if($this->custom() != 0)
	    {
		return view('admin.edit-profile', [ 'edit' => $edit, 'token' => $token]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    } 
		
    }
	
	
	
	public function update_profile(Request $request)
	{
	
	   $name = $request->input('name');
	   $username = $request->input('username');
         $email = $request->input('email');
		 $user_type = $request->input('user_type');
		 
		 if(!empty($request->input('password')))
		 {
		 $password = bcrypt($request->input('password'));
		 $pass = $password;
		 }
		 else
		 {
		 $pass = $request->input('save_password');
		 }
		 $earnings = $request->input('earnings');
		 
		 
		  $token = $request->input('edit_id');
		 
         
		 $request->validate([
							'name' => 'required',
							'username' => 'required',
							'email' => 'required|email',
							'user_photo' => 'mimes:jpeg,jpg,png,gif,svg|max:3000',
							
         ]);
		 $rules = array(
				'username' => ['required', 'regex:/^[\w-]*$/', 'max:255', Rule::unique('users') ->ignore($token, 'id') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				'email' => ['required', 'email', 'max:255', Rule::unique('users') ->ignore($token, 'id') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				
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
		     
			Members::droprofilePhoto($token); 
		   
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
		  
		 
		 
		$data = array('name' => $name, 'username' => $username, 'email' => $email,'user_type' => $user_type, 'password' => $pass, 'user_photo' => $user_image, 'updated_at' => date('Y-m-d H:i:s'), 'earnings' => $earnings);
 
            
            
			Members::updateprofileData($token, $data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	}
	
	/* edit profile */
	
	
	/* administrator */
	
	public function administrator()
    {
        
		
		$userData['data'] = Members::getadminData();
		if($this->custom() != 0)
	    {
		return view('admin.administrator',[ 'userData' => $userData]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	public function add_administrator()
	{
	   $permission = array('dashboard' => 'Dashboard', 'settings' => 'Settings', 'country' => 'Country', 'customers' => 'Customers', 'subscription' => 'Subscription',  'manage-products' => 'Manage Products', 'orders' => 'Orders', 'refund-request' => 'Refund Request', 'withdrawal' => 'Withdrawal Request', 'blog' => 'Blog',  'ads' => 'Ads', 'voucher' => 'Prepaid Vouchers','coupons'=>'Discount Coupons', 'pages' => 'Pages', 'contact' => 'Contact', 'etemplate' => 'Email Template', 'maintenance' => 'Website Maintenance', 'newsletter' => 'Newsletter', 'clear-cache' => 'Clear Cache', 'backups' => 'Backups');
	   if($this->custom() != 0)
	   {
	   return view('admin.add-administrator',[ 'permission' => $permission]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function save_administrator(Request $request)
	{
 
         $sid = 1;
		 $setting['setting'] = Settings::editGeneral($sid);
		 $site_max_image_size = $setting['setting']->site_max_image_size;
		 $name = $request->input('name');
		 $username = $request->input('username');
         $email = $request->input('email');
		 $user_type = $request->input('user_type');
		 $password = bcrypt($request->input('password'));
		 if(!empty($request->input('earnings')))
		 {
		 $earnings = $request->input('earnings');
         }
		 else
		 {
		   $earnings = 0;
		 }
		 $page_url = '/admin/administrator';
		 if(!empty($request->input('user_permission')))
	     {
	      
		  $user_permission = "";
		  foreach($request->input('user_permission') as $permission)
		  {
		     $user_permission .= $permission.',';
		  }
		  $user_permissions = rtrim($user_permission,",");
		  
	     }
	     else
	     {
	     $user_permissions = "";
	     }
		 
         
		 $request->validate([
							'name' => 'required',
							'username' => 'required',
							'password' => 'min:6',
							'email' => 'required|email',
							'user_photo' => 'mimes:jpeg,jpg,png,svg|max:'.$site_max_image_size,
							
         ]);
		 $rules = array(
				'username' => ['required', 'regex:/^[\w-]*$/', 'max:255', Rule::unique('users') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				'email' => ['required', 'email', 'max:255', Rule::unique('users') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				
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
			$image = $request->file('user_photo');
			$img_name = time() . '.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/users');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$user_image = $img_name;
		  }
		  else
		  {
		     $user_image = "";
		  }
		  $verified = 1;
		  $token = $this->generateRandomString();
		 
		$data = array('name' => $name, 'username' => $username, 'email' => $email, 'user_type' => $user_type, 'password' => $password, 'earnings' => $earnings, 'user_photo' => $user_image, 'verified' => $verified, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_token' => $token, 'user_permission' => $user_permissions);
 
            
            Members::insertData($data);
            return redirect($page_url)->with('success', 'Insert successfully.');
            
 
       } 
     
    
  }
  
  
  public function all_delete_administrator(Request $request)
	{
	   $data = array('drop_status'=>'yes');
	   $user_token = $request->input('user_token');
	   foreach($user_token as $id)
	   {
	      
		  Members::deleteData($id,$data);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
  
  public function delete_administrator($token){

      $data = array('drop_status'=>'yes');
	  
      Members::deleteData($token,$data);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  public function edit_administrator($token)
	{
	   
	   $edit['userdata'] = Members::editData($token);
	   $permission = array('dashboard' => 'Dashboard', 'settings' => 'Settings', 'country' => 'Country', 'customers' => 'Customers', 'subscription' => 'Subscription',  'manage-products' => 'Manage Products', 'orders' => 'Orders', 'refund-request' => 'Refund Request',  'withdrawal' => 'Withdrawal Request', 'blog' => 'Blog',  'ads' => 'Ads', 'voucher' => 'Prepaid Vouchers', 'coupons'=>'Discount Coupons', 'pages' => 'Pages', 'contact' => 'Contact', 'etemplate' => 'Email Template', 'maintenance' => 'Website Maintenance', 'newsletter' => 'Newsletter', 'clear-cache' => 'Clear Cache', 'backups' => 'Backups');
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-administrator', [ 'edit' => $edit, 'token' => $token, 'permission' => $permission]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function update_administrator(Request $request)
	{
	
	   $sid = 1;
		 $setting['setting'] = Settings::editGeneral($sid);
		 $site_max_image_size = $setting['setting']->site_max_image_size;
		 $name = $request->input('name');
		 $username = $request->input('username');
         $email = $request->input('email');
		 $user_type = $request->input('user_type');
		 if(!empty($request->input('password')))
		 {
		 $password = bcrypt($request->input('password'));
		 $pass = $password;
		 }
		 else
		 {
		 $pass = $request->input('save_password');
		 }
		 if(!empty($request->input('earnings')))
		 {
		 $earnings = $request->input('earnings');
         }
		 else
		 {
		   $earnings = 0;
		 }
		 $page_url = '/admin/administrator';
		 if(!empty($request->input('user_permission')))
	     {
	      
		  $user_permission = "";
		  foreach($request->input('user_permission') as $permission)
		  {
		     $user_permission .= $permission.',';
		  }
		  $user_permissions = rtrim($user_permission,",");
		  
	     }
	     else
	     {
	     $user_permissions = "";
	     }
		 $token = $request->input('user_token');
         
		 $request->validate([
							'name' => 'required',
							'username' => 'required',
							'password' => 'min:6',
							'email' => 'required|email',
							'user_photo' => 'mimes:jpeg,jpg,png,svg|max:'.$site_max_image_size,
							
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
		  $data = array('name' => $name, 'username' => $username, 'email' => $email, 'user_type' => $user_type, 'password' => $pass, 'earnings' => $earnings, 'user_photo' => $user_image, 'updated_at' => date('Y-m-d H:i:s'), 'user_permission' => $user_permissions);
          Members::updateData($token, $data);
          return redirect($page_url)->with('success', 'Update successfully.');
            
 
       } 
	
	
	}
  
	
	/* administrator */
	
	
	
	/* customer */
	
    public function customer()
    {
        
		
		$userData['data'] = Members::getuserData();
		if($this->custom() != 0)
	    {
		return view('admin.customer',[ 'userData' => $userData]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	public function add_customer()
	{
	   $subscribe['userdata'] = Subscription::viewSubscription();
	   if($this->custom() != 0)
	   {
	   return view('admin.add-customer', [ 'subscribe' => $subscribe]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	
	public function save_customer(Request $request)
	{
 
         $sid = 1;
		 $setting['setting'] = Settings::editGeneral($sid);
		 $site_max_image_size = $setting['setting']->site_max_image_size;
		 $name = $request->input('name');
		 $username = $request->input('username');
         $email = $request->input('email');
		 $user_type = "customer";
		 $password = bcrypt($request->input('password'));
		 if(!empty($request->input('earnings')))
		 {
		 $earnings = $request->input('earnings');
         }
		 else
		 {
		   $earnings = 0;
		 }
		 
		 if($user_type == 'customer')
		 {
		    $page_url = '/admin/customer';
		 }
		 else
		 {
		   $page_url = '/admin/vendor';
		 }
		 
		 if($request->input('subscription_type'))
		 {
		 $user_subscr_id = $request->input('subscription_type');
		 $subscr['view'] = Subscription::editsubData($user_subscr_id);
		 $user_subscr_type = $subscr['view']->subscr_name;
		 $user_subscr_price = $subscr['view']->subscr_price;
		 $subscr_duration = $subscr['view']->subscr_duration;
		 $subscr_value = "+".$subscr_duration;
		 $user_subscr_date = date('Y-m-d', strtotime($subscr_value));
		 $user_subscr_item = $subscr['view']->subscr_item;
		 $user_subscr_item_level = $subscr['view']->subscr_item_level;
		 }
		 else
		 {
		 $user_subscr_id = "";
		 $user_subscr_type = "";
		 $user_subscr_price = "";
		 $user_subscr_date = "";
		 $user_subscr_item = "";
		 $user_subscr_item_level = "";
		 }	
		 if($request->input('user_subscr_payment_status'))
		 {
		   $user_subscr_payment_status = $request->input('user_subscr_payment_status');
		 }
		 else
		 {
		   $user_subscr_payment_status = "";
		 }
		 	
				
		$request->validate([
							'name' => 'required',
							'username' => 'required',
							'password' => 'min:6',
							'email' => 'required|email',
							'user_photo' => 'mimes:jpeg,jpg,png,svg|max:'.$site_max_image_size,
							
         ]);
		 $rules = array(
				'username' => ['required', 'regex:/^[\w-]*$/', 'max:255', Rule::unique('users') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				'email' => ['required', 'email', 'max:255', Rule::unique('users') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				
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
			$image = $request->file('user_photo');
			$img_name = time() . '.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/users');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$user_image = $img_name;
		  }
		  else
		  {
		     $user_image = "";
		  }
		  $verified = 1;
		  $token = $this->generateRandomString();
		 
		$data = array('name' => $name, 'username' => $username, 'email' => $email, 'user_type' => $user_type, 'password' => $password, 'earnings' => $earnings, 'user_photo' => $user_image, 'verified' => $verified, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'user_token' => $token, 'user_subscr_id' => $user_subscr_id, 'user_subscr_price' => $user_subscr_price,	'user_subscr_payment_status' => $user_subscr_payment_status, 'user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $user_subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item);
 
            
            Members::insertData($data);
            return redirect($page_url)->with('success', 'Insert successfully.');
            
 
       } 
     
    
  }
  
  
  public function all_delete_customer(Request $request)
	{
	   $data = array('drop_status'=>'yes');
	   $user_token = $request->input('user_token');
	   foreach($user_token as $id)
	   {
	      
		  Members::deleteData($id,$data);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
  
  public function delete_customer($token){

      $data = array('drop_status'=>'yes');
	  
      Members::deleteData($token,$data);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  public function edit_customer($token)
	{
	   
	   $edit['userdata'] = Members::editData($token);
	   $subscribe['userdata'] = Subscription::viewSubscription();
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-customer', [ 'edit' => $edit, 'token' => $token, 'subscribe' => $subscribe]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function update_customer(Request $request)
	{
	
	   $sid = 1;
	   $setting['setting'] = Settings::editGeneral($sid);
	   $site_max_image_size = $setting['setting']->site_max_image_size;
	   $name = $request->input('name');
	   $username = $request->input('username');
         $email = $request->input('email');
		 $user_type = "customer";
		 
		 if(!empty($request->input('password')))
		 {
		 $password = bcrypt($request->input('password'));
		 $pass = $password;
		 }
		 else
		 {
		 $pass = $request->input('save_password');
		 }
		 
		 if(!empty($request->input('earnings')))
		 {
		 $earnings = $request->input('earnings');
         }
		 else
		 {
		   $earnings = 0;
		 }
		 
		 if($user_type == 'customer')
		 {
		    $page_url = '/admin/customer';
		 }
		 else
		 {
		   $page_url = '/admin/vendor';
		 }
		 
		 if($request->input('subscription_type'))
		 {
		 $user_subscr_id = $request->input('subscription_type');
		 $subscr['view'] = Subscription::editsubData($user_subscr_id);
		 $user_subscr_type = $subscr['view']->subscr_name;
		 $user_subscr_price = $subscr['view']->subscr_price;
		 $subscr_duration = $subscr['view']->subscr_duration;
		 $subscr_value = "+".$subscr_duration;
		 $user_subscr_date = date('Y-m-d', strtotime($subscr_value));
		 $user_subscr_item = $subscr['view']->subscr_item;
		 $user_subscr_item_level = $subscr['view']->subscr_item_level;
		 }
		 else
		 {
		 $user_subscr_id = "";
		 $user_subscr_type = "";
		 $user_subscr_price = "";
		 $user_subscr_date = "";
		 $user_subscr_item = "";
		 $user_subscr_item_level = "";
		 }	
		 if($request->input('user_subscr_payment_status'))
		 {
		   $user_subscr_payment_status = $request->input('user_subscr_payment_status');
		 }
		 else
		 {
		   $user_subscr_payment_status = "";
		 }
		 
		 
		  $token = $request->input('edit_id');
		 
         
		 $request->validate([
							'name' => 'required',
							'username' => 'required',
							'password' => 'min:6',
							'email' => 'required|email',
							'user_photo' => 'mimes:jpeg,jpg,png,gif,svg|max:'.$site_max_image_size,
							
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
		  
		 
		 
		$data = array('name' => $name, 'username' => $username, 'email' => $email, 'user_type' => $user_type, 'password' => $pass, 'earnings' => $earnings, 'user_photo' => $user_image, 'updated_at' => date('Y-m-d H:i:s'), 'user_subscr_id' => $user_subscr_id, 'user_subscr_price' => $user_subscr_price,	'user_subscr_payment_status' => $user_subscr_payment_status, 'user_subscr_type' => $user_subscr_type, 'user_subscr_date' => $user_subscr_date, 'user_subscr_item_level' => $user_subscr_item_level, 'user_subscr_item' => $user_subscr_item);
 
            
            
			Members::updateData($token, $data);
            return redirect($page_url)->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	}
	
	/* customer */
	
	
	
	
	
}
