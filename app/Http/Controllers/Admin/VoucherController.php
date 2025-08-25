<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Voucher;
use DownGrade\Models\ExportVoucher;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Auth;
use DownGrade\Models\Members;
use DownGrade\Models\Settings;
use DownGrade\Models\EmailTemplate;
use Mail;
use Excel;
use DownGrade\Models\ExportProduct;
use Helper;

class VoucherController extends Controller
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
	
	public function download_export($type)
    {
	   $filename = "voucher_".uniqid().'.'.$type;
	   return Excel::download(new ExportVoucher, $filename);
		
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
	
	public function englishRandom($length = 1) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
    }
	
	public function numberRandom($length = 1) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
    }
	
	public function bothRandom($length = 1) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
    }
	
	public function drop_voucher_code(Request $request)
	{
	   if(!empty($request->input('vid')))
	   {
	   $vid = $request->input('vid');
	   $id = "";
	   foreach($vid as $ids)
	   {
			  $id .= $ids.',';
	   }
	   $id = rtrim($id,",");
	   }
	   else
	   {
	   $id = "";
	   }
	   if($request->input('action') == 'Delete All')
	   {
		   
		   foreach($vid as $id)
		   {
			  Voucher::deleteVoucher($id);
		   }
		   return redirect()->back()->with('success','Delete successfully.');
	  }
	  else
	  {
	     $filename = "voucher_".uniqid().'.xlsx';
	     return Excel::download(new ExportVoucher($id), $filename);
	  }	   
	
	}
	
	
	public function voucher_code()
    {
        
		
		$voucher = Voucher::VoucherData();
		if($this->custom() != 0)
	    {
		return view('admin.voucher-code',[ 'voucher' => $voucher]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
    
	public function voucher_purchases()
	{
	   $voucher = Voucher::getPurchasesData();
	   if($this->custom() != 0)
	   {
	   return view('admin.purchases',[ 'voucher' => $voucher]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function add_voucher_code()
	{
	   if($this->custom() != 0)
	   {
	   return view('admin.add-voucher-code');
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
		
	
	public function save_voucher_code(Request $request)
	{
         $import_codes = $request->input('import_codes');
		 $logged_id = Auth::user()->id;
		 
		 $voucher_create_date  = date('d-M-Y h:i a');
		 if($request->input('voucher_expiry_date'))
		 {
		    $voucher_expiry_date = date("Y-m-d h:i:s", strtotime($request->input('voucher_expiry_date')));
		 }
		 else
		 {
		   $voucher_expiry_date = date('Y-m-d h:i:s',strtotime('1 year'));
		 }
		 if($request->input('voucher_notes'))
		 {
		  $voucher_notes = $request->input('voucher_notes');
		 }
		 else
		 {
		 $voucher_notes = "";
		 }
		 
		 if(!empty($import_codes))
		 {
		    $spilit_value=preg_split('/\r\n|[\r\n]/', $import_codes);
			$voucher_price = explode(",", $request->input('voucher_price'));
			$voucher_bonus = explode(",", $request->input('voucher_bonus'));
			if(count($voucher_price) == count($spilit_value) && count($voucher_bonus) == count($spilit_value))
			{
			    
				foreach($spilit_value as $index => $code)
				{
				   $voucher_token = $this->generateRandomString();
				   $voucher_total = $voucher_price[$index] + $voucher_bonus[$index];	
				   
				   
				   $already = Voucher::existsVoucher($code);
				    
				   $data = array('voucher_code' => $code, 'voucher_token' => $voucher_token, 'voucher_user_id' => $logged_id, 'voucher_price' => $voucher_price[$index], 'voucher_bonus' => $voucher_bonus[$index], 'voucher_total' => $voucher_total, 'voucher_create_date' => $voucher_create_date, 'voucher_expiry_date' => $voucher_expiry_date, 'voucher_notes' => $voucher_notes);
				   
				   if($already == 0)
				   {
				   Voucher::insertvoucherData($data);
				   }
				   else
				   {
				     return redirect()->back()->with('error',$code.' - Voucher code already exists, Please input new voucher code'); 
				   }
				   
				}
				return redirect('/admin/voucher-code')->with('success', 'Added successfully.');
			}
			else
			{
			   return redirect()->back()->with('error', 'You have input '.count($spilit_value).' voucher codes, So every fields (Voucher Value & Bonus) input '.count($spilit_value).' values');
			}	
		 }
		 else
		 {
		     
			$voucher_code = $request->input('voucher_code');
			$total_voucher = $request->input('total_voucher');
			$voucher_price = explode(",", $request->input('voucher_price'));
			$voucher_bonus = explode(",", $request->input('voucher_bonus'));
		    $strcount = strlen($voucher_code);
			if(!empty($total_voucher))
			{
			   
			   
			   if(count($voucher_price) == $total_voucher && count($voucher_bonus) == $total_voucher)
			   {
					//for($i=1; $i<=$total_voucher; $i++)
					//{//
						foreach($voucher_price as $index => $code)
						{
						   
						   
						  if(!empty($voucher_code))
						  { 
						  /*$new_str = str_replace(str_split('@'), $this->generateRandomString($strcount), $voucher_code);
						  $new_str2 = str_replace(str_split('#'), $this->generateRandomString($strcount), $new_str); 
						  $voucherkey = str_replace(str_split('*'), $this->generateRandomString($strcount), $new_str2);*/
						  $vowels = array("@", "#", "*");
						  $voucherkey = str_replace($vowels, $this->generateRandomString($strcount), $voucher_code);
						  $lineword = substr($voucherkey, 0, $strcount); 
						  }
						  else
						  {
						  $lineword = $this->generateRandomString(16);
						  }
						  $voucher_token = $this->generateRandomString();
						  $voucher_total = $voucher_price[$index] + $voucher_bonus[$index]; 
						  $data = array('voucher_code' => $lineword, 'voucher_token' => $voucher_token, 'voucher_user_id' => $logged_id, 'voucher_price' => $voucher_price[$index], 'voucher_bonus' => $voucher_bonus[$index], 'voucher_total' => $voucher_total, 'voucher_create_date' => $voucher_create_date, 'voucher_expiry_date' => $voucher_expiry_date, 'voucher_notes' => $voucher_notes);
				           Voucher::insertvoucherData($data);
						   
						} // foreach
						
						
						
						
					  
					//} // for 
					return redirect('/admin/voucher-code')->with('success', 'Added successfully.');
					
				}
				else
				{
				   return redirect()->back()->with('error', 'You have input "Total Vouchers" count is '.$total_voucher.', So "Voucher Value & Bonus" fields input '.$total_voucher.' values');
				}
				
					
			}	
			
		 }
         /*
		 
		 $logged_id = Auth::user()->id; 
		 $voucher_token = $this->generateRandomString();
         $voucher_code = $request->input('voucher_code');
		 $voucher_price = $request->input('voucher_price');
		 $voucher_bonus = $request->input('voucher_bonus');
		 $voucher_total = $voucher_price + $voucher_bonus;
		 $voucher_create_date  = date('d-M-Y h:i a');
		 if($request->input('voucher_expiry_date'))
		 {
		    $voucher_expiry_date = date("Y-m-d h:i:s", strtotime($request->input('voucher_expiry_date')));
		 }
		 else
		 {
		   $voucher_expiry_date = date('Y-m-d h:i:s',strtotime('1 year'));
		 }
		 if($request->input('voucher_notes'))
		 {
		  $voucher_notes = $request->input('voucher_notes');
		 }
		 else
		 {
		 $voucher_notes = "";
		 }
		 
		 
         
		 $request->validate([
							'voucher_code' => 'required',
							'voucher_price' => 'required',
							'voucher_bonus' => 'required',
							
         ]);
		 $rules = array(
				
				'voucher_code' => ['required', 'regex:/^[\w-]*$/', 'max:20', Rule::unique('voucher') -> where(function($sql){ $sql->where('vid','!=','');})],
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
		
		
		 
		$data = array('voucher_code' => $voucher_code, 'voucher_token' => $voucher_token, 'voucher_user_id' => $logged_id, 'voucher_price' => $voucher_price, 'voucher_bonus' => $voucher_bonus, 'voucher_total' => $voucher_total, 'voucher_create_date' => $voucher_create_date, 'voucher_expiry_date' => $voucher_expiry_date, 'voucher_notes' => $voucher_notes);
        Voucher::insertvoucherData($data);
		return redirect('/admin/voucher-code')->with('success', 'Added successfully.');
        
            
 
       } 
	   */
     
    
  }
  
  public function single_voucher_code($vid)
	{
	   
	   $voucher = Voucher::singleVoucher($vid);
	   $count = Voucher::viewUserCount($voucher->voucher_redeem_user_id);
	   if($count != 0)
	   {
	   $redeem_user_data = Voucher::viewUser($voucher->voucher_redeem_user_id);
	   $redeem_user = $redeem_user_data->username;
	   }
	   else
	   {
	   $redeem_user = "";
	   }
	   if($this->custom() != 0)
	   {
	   return view('admin.voucher-info', [ 'voucher' => $voucher, 'redeem_user' => $redeem_user]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
  
  public function delete_voucher_code($vid){

      
	  
      Voucher::deleteVoucher($vid);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
  public function complete_orders($ord_id)
	{
			 $sid = 1;
	         $setting['setting'] = Settings::editGeneral($sid);
			 $payment_token = "";
			 $purchase_token = base64_decode($ord_id);
			 $payment_status = 'completed';
		     $orderdata = array('payment_token' => $payment_token, 'payment_status' => $payment_status);
			 Voucher::singleordupdateData($purchase_token,$orderdata);
			 $getpurchase = Voucher::singlePurchase($purchase_token);
			 $voucher_redeem_user_id = $getpurchase->voucher_redeem_user_id;
			 $voucher_token = $getpurchase->voucher_token;
			 $voucher_redeem_date = date('d-M-Y h:i a');
			 $checkoutdata = array('voucher_redeem_user_id' => $voucher_redeem_user_id, 'voucher_redeem_date' => $voucher_redeem_date);
			 Voucher::singlecheckoutData($purchase_token,$voucher_token,$checkoutdata);
			
			 $againpurchase = Voucher::againPurchase($purchase_token);
			 $voucher_code = $againpurchase->voucher_code;
			 $voucher_price = $againpurchase->voucher_price;
			 $voucher_bonus = $againpurchase->voucher_bonus;
			
			 $voucher_expiry_date = date("d-M-Y h:i a", strtotime($againpurchase->voucher_expiry_date));
			
			
			 $vendor['info'] = Members::singlevendorData($voucher_redeem_user_id);
			 $user_token = $vendor['info']->user_token;
			 $to_name = $vendor['info']->name;
			 $to_email = $vendor['info']->email;
			 		  
			 $admin_name = $setting['setting']->sender_name;
			 $admin_email = $setting['setting']->sender_email;
			 $currency = $setting['setting']->site_currency;
					  
			 $record_data = array('to_name' => $to_name, 'to_email' => $to_email, 'voucher_code' => $voucher_code, 'voucher_redeem_date' => $voucher_redeem_date, 'voucher_expiry_date' => $voucher_expiry_date, 'voucher_price' => $voucher_price, 'voucher_bonus' => $voucher_bonus, 'currency' => $currency);
			/* email template code */
			 $checktemp = EmailTemplate::checkTemplate(22);
			 if($checktemp != 0)
			 {
				 $template_view['mind'] = EmailTemplate::viewTemplate(22);
				 $template_subject = $template_view['mind']->et_subject;
			 }
			 else
			 {
				 $template_subject = "Recharge Voucher Notifications";
			 }
			 /* email template code */
			 Mail::send('recharge_voucher_mail', $record_data , function($message) use ($admin_name, $admin_email, $to_name, $to_email, $template_subject) {
				$message->to($to_email, $to_name)
				->subject($template_subject);
				$message->from($admin_email,$admin_name);
			 });
						  
			 
		return redirect()->back()->with('success', 'Payment details has been completed');			  
	
	}
	
    
	public function delete_orders($delete,$ord_id)
	{
	   $encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
	   $order_id   = $encrypter->decrypt($ord_id);
	   Voucher::deleteEntire($order_id);
	   return redirect()->back()->with('success','Order has been deleted'); 
	
	}
	
	
}
