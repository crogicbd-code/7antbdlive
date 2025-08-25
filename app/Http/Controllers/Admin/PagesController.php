<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Pages;
use DownGrade\Models\Settings;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Helper;

class PagesController extends Controller
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
	
	public function features()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		$featureData['view'] = Pages::getfeatureData();
		if($this->custom() != 0)
	    {
		return view('admin.features',[ 'featureData' => $featureData, 'setting' => $setting]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	public function pages()
    {
        
		
		$pageData['pages'] = Pages::getpageData();
		if($this->custom() != 0)
	    {
		return view('admin.pages',[ 'pageData' => $pageData]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
    
	
	public function add_page()
	{
	   if($this->custom() != 0)
	   {
	   return view('admin.add-page');
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function page_slug($string)
	{
		$string = preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);
		$string = str_replace(' ', '-', $string);
		$string = strtolower($string);
		return $string;
    }
	
	
	
	public function save_page(Request $request)
	{
 
    
         $page_title = $request->input('page_title');
		 $page_desc = htmlentities($request->input('page_desc'));
         $page_slug = $this->page_slug($page_title);
		 $page_status = $request->input('page_status');
		 $footer_menu = $request->input('footer_menu');
		
		 if($request->input('menu_order'))
		 {
		    $menu_order = $request->input('menu_order');
		 }
		 else
		 {
		   $menu_order = 0;
		 }
		 $main_menu = $request->input('main_menu');
		
		 
		 
         
		 $request->validate([
							'page_title' => 'required',
							'page_desc' => 'required',
							'page_status' => 'required',
							'main_menu' => 'required',
							
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
		
		
		 
		$data = array('page_title' => $page_title, 'page_desc' => $page_desc, 'page_slug' => $page_slug, 'page_status' => $page_status, 'menu_order' => $menu_order, 'footer_menu' => $footer_menu, 'main_menu' => $main_menu);
        Pages::insertpageData($data);
        return redirect('/admin/pages')->with('success', 'Insert successfully.');
            
 
       } 
     
    
  }
  
  
  public function all_delete_pages(Request $request)
	{
	   
	   $page_id = $request->input('page_id');
	   foreach($page_id as $id)
	   {
	      
		  Pages::deletePagedata($id);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
  
  
  public function delete_pages($page_id){

      
	  
      Pages::deletePagedata($page_id);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
  public function edit_page($page_id)
	{
	   
	   $edit['page'] = Pages::editadminPage($page_id);
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-page', [ 'edit' => $edit, 'page_id' => $page_id]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function edit_features($feature_id)
	{
	   
	   $edit['feature'] = Pages::editfeatureData($feature_id);
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-features', [ 'edit' => $edit, 'feature_id' => $feature_id]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function update_features(Request $request)
	{
	
	   
		 $feature_title = $request->input('feature_title');
		 $image_size = $request->input('image_size');
		 $feature_id = $request->input('feature_id');
		 $save_feature_image = $request->input('save_feature_image');
		 $feature_desc = $request->input('feature_desc');
		 if(!empty($request->input('feature_link')))
		 {
		 $feature_link = $request->input('feature_link');
		 }
		 else
		 {
		 $feature_link = "";
		 }
		 $feature_color = $request->input('feature_color');
		 
		 
         
		 $request->validate([
		                    'feature_image' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							'feature_title' => 'required',
							'feature_desc' => 'required',
							'feature_color' => 'required',
							
							
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
		
		   if ($request->hasFile('feature_image')) 
		   {
		    Pages::dropFeatures($feature_id);
			$image = $request->file('feature_image');
			$img_name = time() . '.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/features');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$feature_image = $img_name;
		  }
		  else
		  {
		     $feature_image = $save_feature_image;
		  }
		 
		$data = array('feature_title' => $feature_title, 'feature_desc' => $feature_desc, 'feature_image' => $feature_image, 'feature_link' => $feature_link, 'feature_color' => $feature_color);
        Pages::updatefeatureData($feature_id,$data);
        return redirect('/admin/features')->with('success', 'Update successfully.');
            
 
       } 
      
     
       
	
	
	}
	
	
	
	
	
	
	public function upedit_features(Request $request)
	{
	
	   
		 $site_features_display = $request->input('site_features_display');
		 
		 $sid = $request->input('sid');
		 
		 
         
		 $request->validate([
		                    
							'site_features_display' => 'required',
							
							
							
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
		
		   
		 
		$data = array('site_features_display' => $site_features_display);
        Settings::updatemailData($sid, $data);
        return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
      
     
       
	
	
	}
	
	
	
	
	public function update_page(Request $request)
	{
	
	   $page_title = $request->input('page_title');
		 $page_desc = htmlentities($request->input('page_desc'));
         $page_slug = $this->page_slug($page_title);
		 $page_status = $request->input('page_status');
		 
		 $page_id = $request->input('page_id');
		 $footer_menu = $request->input('footer_menu');
		 
		 if($request->input('menu_order'))
		 {
		    $menu_order = $request->input('menu_order');
		 }
		 else
		 {
		   $menu_order = 0;
		 }
		 $main_menu = $request->input('main_menu');
         
		 $request->validate([
							'page_title' => 'required',
							'page_desc' => 'required',
							'page_status' => 'required',
							'main_menu' => 'required', 
							
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
		
		
		$data = array('page_title' => $page_title, 'page_desc' => $page_desc, 'page_slug' => $page_slug, 'page_status' => $page_status, 'menu_order' => $menu_order, 'footer_menu' => $footer_menu, 'main_menu' => $main_menu);
        Pages::updatepageData($page_id, $data);
            return redirect('/admin/pages')->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	}
	
  
	
	
	
}
