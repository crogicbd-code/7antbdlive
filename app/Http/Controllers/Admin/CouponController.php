<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Coupon;
use DownGrade\Models\Members;
use DownGrade\Models\Settings;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Auth;
use Helper;


class CouponController extends Controller
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
	
	public function view_coupon()
    {
        
		$couponData['view'] = Coupon::getallCoupon();
		if($this->custom() != 0)
	    {
		return view('admin.coupons',[ 'couponData' => $couponData]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	
	public function add_coupon()
	{
	   
	   if($this->custom() != 0)
	   {
	   return view('admin.add-coupon');
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function save_coupon(Request $request)
	{
 
    
         $coupon_code = $request->input('coupon_code');
		 $user_id = $request->input('user_id');
		 $discount_type = $request->input('discount_type');
         $coupon_start_date = $request->input('coupon_start_date');
		 $coupon_end_date = $request->input('coupon_end_date');
		 $coupon_value = $request->input('coupon_value');
		 $coupon_status = $request->input('coupon_status');
		 $coupon_type = $request->input('coupon_type');	
		 
		
		 
		 
         
		 $request->validate([
							'coupon_code' => 'required',
							'discount_type' => 'required',
							'coupon_value' => 'required',
							
         ]);
		 $rules = array(
				'coupon_code' => ['required',  Rule::unique('coupon') -> where(function($sql){ $sql->where('coupon_id','!=','');})],
				
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
		
		if($discount_type == 'percentage' && $coupon_value >= 100)
		{
		  return redirect()->back()->with('error', 'Please enter coupon value below 100.');
		}
		else
		{ 
		$data = array('user_id' => $user_id, 'coupon_code' => $coupon_code, 'discount_type' => $discount_type, 'coupon_value' => $coupon_value, 'coupon_start_date' => $coupon_start_date, 'coupon_end_date' => $coupon_end_date, 'coupon_status' => $coupon_status, 'coupon_type' => $coupon_type);
        Coupon::insertCoupon($data);
        return redirect('/admin/coupons')->with('success', 'Insert successfully.');
		}
            
 
       } 
     
    
  }
  
  
    public function all_delete_coupons(Request $request)
	{
	   
	   $coupon_id = $request->input('coupon_id');
	   foreach($coupon_id as $id)
	   {
	      
		  Coupon::deleteCoupon($id);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
	
  
    public function delete_coupon($coupon_id){
     
	   $couponid = base64_decode($coupon_id);
      	  
      Coupon::deleteCoupon($couponid);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
  public function edit_coupon($coupon_id)
	{
	   $couponid = base64_decode($coupon_id);
	   $edit['value'] = Coupon::editCoupon($couponid);
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-coupon', [ 'edit' => $edit]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	public function update_coupon(Request $request)
	{
	
	     $coupon_code = $request->input('coupon_code');
		 $user_id = $request->input('user_id');
		 $discount_type = $request->input('discount_type');
         $coupon_start_date = $request->input('coupon_start_date');
		 $coupon_end_date = $request->input('coupon_end_date');
		 $coupon_value = $request->input('coupon_value');
		 $coupon_status = $request->input('coupon_status');
		 $coupon_id = base64_decode($request->input('coupon_id'));	
		 $coupon_type = $request->input('coupon_type');	
		
		 
		 
         
		 $request->validate([
							'coupon_code' => 'required',
							'discount_type' => 'required',
							'coupon_value' => 'required',
							
         ]);
		 $rules = array(
				'coupon_code' => ['required',  Rule::unique('coupon') ->ignore($coupon_id, 'coupon_id') -> where(function($sql){ $sql->where('coupon_status','!=','');})],
				
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
		
		if($discount_type == 'percentage' && $coupon_value > 100)
		{
		  return redirect()->back()->with('error', 'Please enter coupon value below 100.');
		}
		else
		{ 
		$data = array('user_id' => $user_id, 'coupon_code' => $coupon_code, 'discount_type' => $discount_type, 'coupon_value' => $coupon_value, 'coupon_start_date' => $coupon_start_date, 'coupon_end_date' => $coupon_end_date, 'coupon_status' => $coupon_status, 'coupon_type' => $coupon_type);
        Coupon::updateCoupon($coupon_id, $data);
        return redirect('/admin/coupons')->with('success', 'Update successfully.');
		}
            
 
       } 
     
       
	
	
	}
	
	
	/* coupon */
	
	
	
	
  
	
	
	
}
