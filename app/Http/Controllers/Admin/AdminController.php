<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use DownGrade\Models\Pages;
use DownGrade\Models\Product;
use DownGrade\Models\Blog;
use DownGrade\Models\Settings;
use DownGrade\Models\Events;
use DownGrade\Models\Members;
use DownGrade\Models\Category;
use Auth;
use Mail;
use Helper;

class AdminController extends Controller
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
    public function admin()
    {
       
	  $sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		
		$today = date('d F Y');
		$first_day = date('d F Y', strtotime('-1 days'));
		$second_day = date('d F Y', strtotime('-2 days'));
		$third_day = date('d F Y', strtotime('-3 days'));
		$fourth_day = date('d F Y', strtotime('-4 days'));
		$fifth_day = date('d F Y', strtotime('-5 days'));
		$sixth_day = date('d F Y', strtotime('-6 days'));
		
		$data1 = date('Y-m-d');
		$data2 = date('Y-m-d', strtotime('-1 days'));
		$data3 = date('Y-m-d', strtotime('-2 days'));
		$data4 = date('Y-m-d', strtotime('-3 days'));
		$data5 = date('Y-m-d', strtotime('-4 days'));
		$data6 = date('Y-m-d', strtotime('-5 days'));
		$data7 = date('Y-m-d', strtotime('-6 days'));
		
		
		$view1 = Product::orderdataCheck($data1);
		$view2 = Product::orderdataCheck($data2);
		$view3 = Product::orderdataCheck($data3);
		$view4 = Product::orderdataCheck($data4);
		$view5 = Product::orderdataCheck($data5);
		$view6 = Product::orderdataCheck($data6);
		$view7 = Product::orderdataCheck($data7);  
		
	  $total_customers = Members::totaluserCount();
	  
	  $total_category = Category::totalcategoryCount();
	  $total_pages = Pages::totalpageData();
	  $total_pages = Pages::totalpageData();
	  $total_post = Blog::totalblogData();
	  $total_product = Product::totalProduct();
	  $total_order = Product::totalOrder();
	  $total_refund = Product::totalRefund();
	  $total_withdrawal = Product::totalWithdrawal();
	  
	  $approved = Product::itemapprovedCheck(1);
		$unapproved = Product::itemapprovedCheck(0);
	  
	  $data = array('setting' => $setting, 'today' => $today, 'first_day' => $first_day, 'second_day' => $second_day, 'third_day' => $third_day, 'fourth_day' => $fourth_day, 'fifth_day' => $fifth_day, 'sixth_day' => $sixth_day, 'view1' => $view1, 'view2' => $view2, 'view3' => $view3, 'view4' => $view4, 'view5' => $view5, 'view6' => $view6, 'view7' => $view7, 'total_customers' => $total_customers, 'total_category' => $total_category, 'total_pages' => $total_pages, 'total_post' => $total_post, 'total_product' => $total_product, 'total_order' => $total_order, 'total_refund' => $total_refund, 'total_withdrawal' => $total_withdrawal, 'approved' => $approved, 'unapproved' => $unapproved);
	  if($this->custom() != 0)
	  {
	  return view('admin.index')->with($data);
	  }
      else
	  {
	  return redirect('/admin/license');
	  }
		
		
    }
	public function license_check()
	{
	   return view('admin.license');
	}
	
	public function verify_purchase(Request $request)
	{
        $dw_ver = Helper::version_no();
		$data = array($dw_ver => 1);
		Settings::updateCustomData($data);
		return redirect('admin');
	}
	
	
	
}
