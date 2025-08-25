<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Settings;
use DownGrade\Models\Pages;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Image;
use Helper;

class SettingsController extends Controller
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
	
	public function vat_update(Request $request)
	{
	
	  $default_vat_price = $request->input('default_vat_price');
	  
	  
	  
	  
	  $request->validate([
		                    
							
							
							
							
							
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
		
		  $data = array('default_vat_price' => $default_vat_price);
 
			Settings::updateCustomData($data);
            return redirect()->back()->with('success', 'Update successfully.');
		
		
	    }
	  
	
	}
	
	
	/* pwa settings */
	
	public function pwa_settings()
    {
        
		
		$additional = Settings::pwaSettings();
		if($this->custom() != 0)
	    {
		return view('admin.pwa-settings', [ 'additional' => $additional]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
		
    }
	
	public function update_pwa_settings(Request $request)
	{
	
	   
	   $app_name = $request->input('app_name');
	   $background_color = $request->input('background_color');
	   $short_name = $request->input('short_name');
	   $theme_color = $request->input('theme_color');
	   
	   $sid = $request->input('sid');
	   
	   $request->validate([
							
							
							'pwa_icon1' => 'mimes:png',
							'pwa_icon2' => 'mimes:png',
							'pwa_icon3' => 'mimes:png',
							'pwa_icon4' => 'mimes:png',
							'pwa_icon5' => 'mimes:png',
							'pwa_icon6' => 'mimes:png',
							'pwa_icon7' => 'mimes:png',
							'pwa_icon8' => 'mimes:png',
							'pwa_splash1' => 'mimes:png',
							'pwa_splash2' => 'mimes:png',
							'pwa_splash3' => 'mimes:png',
							'pwa_splash4' => 'mimes:png',
							'pwa_splash5' => 'mimes:png',
							'pwa_splash6' => 'mimes:png',
							'pwa_splash7' => 'mimes:png',
							'pwa_splash8' => 'mimes:png',
							'pwa_splash9' => 'mimes:png',
							'pwa_splash10' => 'mimes:png',
                            
							
							
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
		
		  if ($request->hasFile('pwa_icon1')) 
		  {
		    $column = 'pwa_icon1'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon1');
			$img_name = time() . '1.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon1 = $img_name;
		  }
		  else
		  {
		     $pwa_icon1 = $request->input('save_pwa_icon1');
		  }
		  
		  if ($request->hasFile('pwa_icon2')) 
		  {
		    $column = 'pwa_icon2'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon2');
			$img_name = time() . '2.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon2 = $img_name;
		  }
		  else
		  {
		     $pwa_icon2 = $request->input('save_pwa_icon2');
		  }
		  
		  if ($request->hasFile('pwa_icon3')) 
		  {
		    $column = 'pwa_icon3'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon3');
			$img_name = time() . '3.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon3 = $img_name;
		  }
		  else
		  {
		     $pwa_icon3 = $request->input('save_pwa_icon3');
		  }
		  
		  if ($request->hasFile('pwa_icon4')) 
		  {
		    $column = 'pwa_icon4'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon4');
			$img_name = time() . '4.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon4 = $img_name;
		  }
		  else
		  {
		     $pwa_icon4 = $request->input('save_pwa_icon4');
		  }
		  
		  if ($request->hasFile('pwa_icon5')) 
		  {
		    $column = 'pwa_icon5'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon5');
			$img_name = time() . '5.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon5 = $img_name;
		  }
		  else
		  {
		     $pwa_icon5 = $request->input('save_pwa_icon5');
		  }
		  
		  if ($request->hasFile('pwa_icon6')) 
		  {
		    $column = 'pwa_icon6'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon6');
			$img_name = time() . '6.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon6 = $img_name;
		  }
		  else
		  {
		     $pwa_icon6 = $request->input('save_pwa_icon6');
		  }
		  
		  if ($request->hasFile('pwa_icon7')) 
		  {
		    $column = 'pwa_icon7'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon7');
			$img_name = time() . '7.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon7 = $img_name;
		  }
		  else
		  {
		     $pwa_icon7 = $request->input('save_pwa_icon7');
		  }
		  
		  if ($request->hasFile('pwa_icon8')) 
		  {
		    $column = 'pwa_icon8'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_icon8');
			$img_name = time() . '8.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_icon8 = $img_name;
		  }
		  else
		  {
		     $pwa_icon8 = $request->input('save_pwa_icon8');
		  }
		  
		  
		  if ($request->hasFile('pwa_splash1')) 
		  {
		    $column = 'pwa_splash1'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash1');
			$img_name = time() . '9.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash1 = $img_name;
		  }
		  else
		  {
		     $pwa_splash1 = $request->input('save_pwa_splash1');
		  }
		  
		  if ($request->hasFile('pwa_splash2')) 
		  {
		    $column = 'pwa_splash2'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash2');
			$img_name = time() . '10.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash2 = $img_name;
		  }
		  else
		  {
		     $pwa_splash2 = $request->input('save_pwa_splash2');
		  }
		  
		  if ($request->hasFile('pwa_splash3')) 
		  {
		    $column = 'pwa_splash3'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash3');
			$img_name = time() . '11.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash3 = $img_name;
		  }
		  else
		  {
		     $pwa_splash3 = $request->input('save_pwa_splash3');
		  }
		  
		  if ($request->hasFile('pwa_splash4')) 
		  {
		    $column = 'pwa_splash4'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash4');
			$img_name = time() . '12.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash4 = $img_name;
		  }
		  else
		  {
		     $pwa_splash4 = $request->input('save_pwa_splash4');
		  }
		  
		  if ($request->hasFile('pwa_splash5')) 
		  {
		    $column = 'pwa_splash5'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash5');
			$img_name = time() . '13.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash5 = $img_name;
		  }
		  else
		  {
		     $pwa_splash5 = $request->input('save_pwa_splash5');
		  }
		  
		  

		  if ($request->hasFile('pwa_splash6')) 
		  {
		    $column = 'pwa_splash6'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash6');
			$img_name = time() . '14.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash6 = $img_name;
		  }
		  else
		  {
		     $pwa_splash6 = $request->input('save_pwa_splash6');
		  }
		  
		  if ($request->hasFile('pwa_splash7')) 
		  {
		    $column = 'pwa_splash7'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash7');
			$img_name = time() . '15.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash7 = $img_name;
		  }
		  else
		  {
		     $pwa_splash7 = $request->input('save_pwa_splash7');
		  }
		  
		  if ($request->hasFile('pwa_splash8')) 
		  {
		    $column = 'pwa_splash8'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash8');
			$img_name = time() . '16.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash8 = $img_name;
		  }
		  else
		  {
		     $pwa_splash8 = $request->input('save_pwa_splash8');
		  }
		  
		  if ($request->hasFile('pwa_splash9')) 
		  {
		    $column = 'pwa_splash9'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash9');
			$img_name = time() . '17.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash9 = $img_name;
		  }
		  else
		  {
		     $pwa_splash9 = $request->input('save_pwa_splash9');
		  }
		  
		  if ($request->hasFile('pwa_splash10')) 
		  {
		    $column = 'pwa_splash10'; 
			Settings::dropPWA($column); 
		    $image = $request->file('pwa_splash10');
			$img_name = time() . '18.'.$image->getClientOriginalExtension();
			$destinationPath = base_path('/images/icons');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$pwa_splash10 = $img_name;
		  }
		  else
		  {
		     $pwa_splash10 = $request->input('save_pwa_splash10');
		  }
		  
		  
		  
		$data = array('app_name' => $app_name, 'background_color' => $background_color, 'short_name' => $short_name, 'theme_color' => $theme_color, 'pwa_icon1' => $pwa_icon1, 'pwa_icon2' => $pwa_icon2, 'pwa_icon3' => $pwa_icon3, 'pwa_icon4' => $pwa_icon4, 'pwa_icon5' => $pwa_icon5, 'pwa_icon6' => $pwa_icon6, 'pwa_icon7' => $pwa_icon7, 'pwa_icon8' => $pwa_icon8, 'pwa_splash1' => $pwa_splash1, 'pwa_splash2' => $pwa_splash2, 'pwa_splash3' => $pwa_splash3, 'pwa_splash4' => $pwa_splash4, 'pwa_splash5' => $pwa_splash5, 'pwa_splash6' => $pwa_splash6, 'pwa_splash7' => $pwa_splash7, 'pwa_splash8' => $pwa_splash8, 'pwa_splash9' => $pwa_splash9, 'pwa_splash10' => $pwa_splash10);
        Settings::updatePWAData($data);
        return redirect()->back()->with('success', 'Update successfully.');
		
		}
	   
	   
	   
	   
	 
	}
	
	
	
  /* pwa settings */
  
  
	 
	/* Website Maintenance */
	public function website_maintenance()
    {
	   if($this->custom() != 0)
	   {
	   return view('admin.website-maintenance');
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	public function update_maintenance(Request $request)
	{
	   $sid = 1;
	   $maintenance_mode = $request->input('maintenance_mode');
	   $m_mode_content = $request->input('m_mode_content');
	   $m_mode_title = $request->input('m_mode_title');
	   $m_mode_social_label = $request->input('m_mode_social_label');
	   $m_mode_background = $request->input('m_mode_background');
	   $m_mode_bgcolor = $request->input('m_mode_bgcolor');
	   
	   
	   $request->validate([
							
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
		  
		  if ($request->hasFile('m_mode_bgimage')) 
		  {
		     
			Settings::dropWebsite($sid); 
		   
			$image = $request->file('m_mode_bgimage');
			$img_name = time() . '11.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$m_mode_bgimage = $img_name;
		  }
		  else
		  {
		     $m_mode_bgimage = $request->input('save_bgimage');
		  }
		  
		  $data = array('maintenance_mode' => $maintenance_mode, 'm_mode_content' => $m_mode_content, 'm_mode_title' => $m_mode_title, 'm_mode_social_label' => $m_mode_social_label, 'm_mode_bgcolor' => $m_mode_bgcolor, 'm_mode_background' => $m_mode_background, 'm_mode_bgimage' => $m_mode_bgimage);
          Settings::updatemailData($sid, $data);
          return redirect()->back()->with('success', 'Update successfully.');
		  
		}
	
	}
	/* Website Maintenance */ 
	    
	
	/* settings */
	public function view_ads()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		$top_ads_pages = explode(',', $setting['setting']->top_ads_pages);
		$sidebar_ads_pages = explode(',', $setting['setting']->sidebar_ads_pages);
		$bottom_ads_pages = explode(',', $setting['setting']->bottom_ads_pages);
		$ads_pages = array('home' => 'Home', 'shop' => 'Shop', 'item-details' => 'Item Details', 'featured-items' => 'Featured Items', 'free-items' => 'Free Items', 'new-releases' => 'New Releases', 'popular-items' => 'Popular Items, Subscriber Downloads', 'blog' => 'Blog', 'post-details' => 'Post Details', 'contact' => 'Contact', 'flash-sale' => 'Flash Sale', 'tags' => 'Tags', 'pages' => 'Dynamic Pages');
		if($this->custom() != 0)
	    {
		return view('admin.ads', [ 'setting' => $setting, 'sid' => $sid, 'top_ads_pages' => $top_ads_pages, 'sidebar_ads_pages' => $sidebar_ads_pages, 'bottom_ads_pages' => $bottom_ads_pages, 'ads_pages' => $ads_pages]);
		}
	    else
	    { 
		  return redirect('/admin/license');
	    }
		
    }
	
	public function update_ads(Request $request)
	{
	
	   if(!empty($request->input('top_ads_pages')))
	   {
	     $payment = "";
		 foreach($request->input('top_ads_pages') as $payment_option)
		 {
		    $payment .= $payment_option.',';
		 }
		 $top_ads_pages = rtrim($payment,',');
	   }
	   else
	   {
	   $top_ads_pages = "";
	   }
	   
	   
	   if(!empty($request->input('sidebar_ads_pages')))
	   {
	     $payment = "";
		 foreach($request->input('sidebar_ads_pages') as $payment_option)
		 {
		    $payment .= $payment_option.',';
		 }
		 $sidebar_ads_pages = rtrim($payment,',');
	   }
	   else
	   {
	   $sidebar_ads_pages = "";
	   }
	   
	   if(!empty($request->input('bottom_ads_pages')))
	   {
	     $payment = "";
		 foreach($request->input('bottom_ads_pages') as $payment_option)
		 {
		    $payment .= $payment_option.',';
		 }
		 $bottom_ads_pages = rtrim($payment,',');
	   }
	   else
	   {
	   $bottom_ads_pages = "";
	   }
	   $top_ads = $request->input('top_ads');
	   $sidebar_ads = $request->input('sidebar_ads');
	   $bottom_ads = $request->input('bottom_ads');
	    $sid = $request->input('sid');
	   
	   $request->validate([
							
							
							
							
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
		
			  
		 
		 
		$data = array('top_ads_pages' => $top_ads_pages, 'sidebar_ads_pages' => $sidebar_ads_pages, 'bottom_ads_pages' => $bottom_ads_pages, 'top_ads' => $top_ads, 'sidebar_ads' => $sidebar_ads, 'bottom_ads' => $bottom_ads);
 
        Settings::updatemailData($sid, $data);
        return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
	   
	
	
	}
	
	public function general_settings()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		$page['view'] = Pages::pagelinkData();
		if($this->custom() != 0)
	    {
		return view('admin.general-settings', [ 'setting' => $setting, 'sid' => $sid, 'page' => $page]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
		
    }
	
	public function demo_mode()
	{
	   return redirect()->back()->with('error', 'This is Demo version. You can not add or change any thing');
	}
	
	
	
	public function update_demo_mode(Request $request)
	{
	   return redirect()->back()->with('error', 'This is Demo version. You can not add or change any thing');
	}
	
	
	public function color_settings()
	{
	
	    $sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.color-settings', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	
	}
	
	
	public function update_color_settings(Request $request)
	{
	  	$site_theme_color = $request->input('site_theme_color');
		$site_button_color = $request->input('site_button_color');
		$site_header_color = $request->input('site_header_color');
		$site_footer_color = $request->input('site_footer_color');
		$site_button_hover = $request->input('site_button_hover');
	  	$sid = $request->input('sid');
	     
         
		 $request->validate([
		 
					'site_theme_color' => 'required',
					'site_button_color' => 'required',
					'site_header_color' => 'required',
					'site_footer_color' => 'required',			
							
							
							
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
		
			  
		 
		 
		$data = array('site_theme_color' => $site_theme_color, 'site_button_color' => $site_button_color, 'site_header_color' => $site_header_color,  'site_footer_color' => $site_footer_color, 'site_button_hover' => $site_button_hover);
 
			Settings::updatemailData($sid, $data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
	
	
	
	}
	
	
	public function counter_section()
	{
	  
	    $sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		
		if($this->custom() != 0)
	    {
		return view('admin.counter-section', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	
	}
	
	
	public function update_counter_section(Request $request)
	{
	   
	   
	   $site_counter_icon1 = $request->input('site_counter_icon1');
	   $site_counter_icon2 = $request->input('site_counter_icon2');
	   $site_counter_icon3 = $request->input('site_counter_icon3');
	   $site_counter_icon4 = $request->input('site_counter_icon4');
	   
	   $site_counter_count1 = $request->input('site_counter_count1');
	   $site_counter_count2 = $request->input('site_counter_count2');
	   $site_counter_count3 = $request->input('site_counter_count3');
	   $site_counter_count4 = $request->input('site_counter_count4');
	   
	   $site_counter_title1 = $request->input('site_counter_title1');
	   $site_counter_title2 = $request->input('site_counter_title2');
	   $site_counter_title3 = $request->input('site_counter_title3');
	   $site_counter_title4 = $request->input('site_counter_title4');
	   
	   $site_counter_display = $request->input('site_counter_display');
	   
	   
	   $sid = $request->input('sid');
	     
         
		 $request->validate([
		 
							
							
							
							
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
		
			  
		 
		 
		$data = array('site_counter_icon1' => $site_counter_icon1, 'site_counter_icon2' => $site_counter_icon2, 'site_counter_icon3' => $site_counter_icon3, 'site_counter_icon4' => $site_counter_icon4, 'site_counter_count1' => $site_counter_count1, 'site_counter_count2' => $site_counter_count2, 'site_counter_count3' => $site_counter_count3, 'site_counter_count4' => $site_counter_count4, 'site_counter_title1' => $site_counter_title1, 'site_counter_title2' => $site_counter_title2, 'site_counter_title3' => $site_counter_title3, 'site_counter_title4' => $site_counter_title4, 'site_counter_display' => $site_counter_display);
 
			Settings::updatemailData($sid, $data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
	
	
	}
	
	
	
	
	 public function update_general_settings(Request $request)
	{
	
	     $site_title = $request->input('site_title');
		 $site_home_title = $request->input('site_home_title');
	     $site_desc = $request->input('site_desc');
         $site_keywords = $request->input('site_keywords');
		 $sid = $request->input('sid');
		 $office_email = $request->input('office_email');
		 $office_phone = $request->input('office_phone');
		 $office_address = $request->input('office_address');
		 $image_size = $request->input('image_size');
		 $site_copyright = $request->input('site_copyright');
		 $save_footer_logo = $request->input('save_footer_logo');
		 $site_loader_display = $request->input('site_loader_display');
		 $save_loader_image = $request->input('save_loader_image');
		 $site_banner_heading = $request->input('site_banner_heading');
		 $site_banner_sub_heading = $request->input('site_banner_sub_heading');
         $save_footer_payment = $request->input('save_footer_payment');
		 $site_flash_end_date = $request->input('site_flash_end_date');
		 $product_support_link = $request->input('product_support_link');
		 $cookie_popup = $request->input('cookie_popup');
		 $cookie_popup_text = $request->input('cookie_popup_text');
		 $cookie_popup_button = $request->input('cookie_popup_button');
		 
		 
		 $site_header_top_bar = $request->input('site_header_top_bar');
		 
		 
		 
		 $google_analytics = $request->input('google_analytics');
		 
		 
		 $email_verification = $request->input('email_verification');
		 
		 
		 $site_tawk_chat = $request->input('site_tawk_chat');
		 
		 $reminder_renewal_before_days = $request->input('reminder_renewal_before_days');
		 
		 $redeem_voucher_terms = $request->input('redeem_voucher_terms');
		 $site_google_recaptcha = $request->input('site_google_recaptcha');
		 
		 $product_updates_tabs = $request->input('product_updates_tabs');
		 
		 $product_reporting_url = $request->input('product_reporting_url');
		 
		 $google_recaptcha_site_key = $request->input('google_recaptcha_site_key');
		 $google_recaptcha_secret_key = $request->input('google_recaptcha_secret_key');
		 
		 $demo_url_preview = $request->input('demo_url_preview');
		 
		 $request->validate([
							'site_title' => 'required',
							'site_favicon' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							'site_logo' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							'site_footer_payment' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							
							
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
		
		if ($request->hasFile('site_favicon')) {
		     
			Settings::dropFavicon($sid); 
		   
			$image = $request->file('site_favicon');
			$img_name = time() . '.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$fav_image = $img_name;
		  }
		  else
		  {
		     $fav_image = $request->input('save_favicon');
		  }
		  
		  
		  
		  if ($request->hasFile('site_logo')) {
		     
			Settings::dropLogo($sid); 
		   
			$image = $request->file('site_logo');
			$img_name = time() . '11.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$logo_image = $img_name;
		  }
		  else
		  {
		     $logo_image = $request->input('save_logo');
		  }
		  
		  
		  if ($request->hasFile('site_loader_image')) {
		     
			Settings::dropLoader($sid); 
		   
			$image = $request->file('site_loader_image');
			$img_name = time() . '6713.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$site_loader_image = $img_name;
		  }
		  else
		  {
		     $site_loader_image = $save_loader_image;
		  }
		   
		   
		  if ($request->hasFile('site_banner')) {
		     
			Settings::dropBanner($sid); 
		   
			$image = $request->file('site_banner');
			$img_name = time() . '2321.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$site_banner = $img_name;
		  }
		  else
		  {
		     $site_banner = $request->input('save_banner');
		  } 
		  
		  if ($request->hasFile('site_other_banner')) {
		     
			Settings::dropPhoto('site_other_banner'); 
		   
			$image = $request->file('site_other_banner');
			$img_name = time() . '039.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$site_other_banner = $img_name;
		  }
		  else
		  {
		     $site_other_banner = $request->input('save_other_banner');
		  } 
		  
		  
		  if ($request->hasFile('site_footer_payment')) {
		     
			Settings::dropPaymentbanner($sid); 
		   
			$image = $request->file('site_footer_payment');
			$img_name = time().'133.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$site_footer_payment = $img_name;
		  }
		  else
		  {
		     $site_footer_payment = $save_footer_payment;
		  }
		 
		 
		$data = array('site_title' => $site_title, 'site_home_title' => $site_home_title, 'site_desc' => $site_desc, 'site_keywords' => $site_keywords,  'site_favicon' => $fav_image, 'site_logo' => $logo_image, 'office_address' => $office_address, 'office_email' => $office_email, 'office_phone' => $office_phone, 'site_copyright' => $site_copyright, 'site_loader_image' => $site_loader_image, 'site_loader_display' => $site_loader_display, 'site_banner' => $site_banner, 'site_banner_heading' => $site_banner_heading, 'site_banner_sub_heading' => $site_banner_sub_heading, 'site_footer_payment' => $site_footer_payment, 'site_flash_end_date' => $site_flash_end_date, 'product_support_link' => $product_support_link, 'cookie_popup' => $cookie_popup, 'cookie_popup_text' => $cookie_popup_text, 'cookie_popup_button' => $cookie_popup_button, 'site_header_top_bar' => $site_header_top_bar,  'google_analytics' => $google_analytics, 'email_verification' => $email_verification,  'site_tawk_chat' => $site_tawk_chat, 'reminder_renewal_before_days' => $reminder_renewal_before_days, 'redeem_voucher_terms' => $redeem_voucher_terms, 'site_google_recaptcha' => $site_google_recaptcha, 'product_updates_tabs' => $product_updates_tabs, 'product_reporting_url' => $product_reporting_url, 'site_other_banner' => $site_other_banner);
 
            
			Settings::updategeneralData($sid, $data);
			$custom_data = array('google_recaptcha_site_key' => $google_recaptcha_site_key, 'google_recaptcha_secret_key' => $google_recaptcha_secret_key, 'demo_url_preview' => $demo_url_preview);
			Settings::updateCustomData($custom_data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	} 
	
	
	
	
	public function limitation_settings()
	{
	
	    $sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.limitation-settings', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	
	}
	
	public function update_limitation_settings(Request $request)
	{
	   	   
	      
	   $product_per_page = $request->input('product_per_page');
	   $post_per_page = $request->input('post_per_page');
	   $home_featured_items = $request->input('home_featured_items');
	   $home_flash_items = $request->input('home_flash_items');
	   $home_popular_items = $request->input('home_popular_items');
	   $home_new_items = $request->input('home_new_items');
	   $home_blog_post = $request->input('home_blog_post');
	   $comment_per_page = $request->input('comment_per_page');
	   $review_per_page = $request->input('review_per_page');
	   $site_range_min_price = $request->input('site_range_min_price');
	   $site_range_max_price = $request->input('site_range_max_price');
	   $menu_display_categories = $request->input('menu_display_categories');
	   $menu_categories_order = $request->input('menu_categories_order');
	   $home_free_items = $request->input('home_free_items');
	   
	   $footer_menu_display_categories = $request->input('footer_menu_display_categories');
	   $footer_menu_categories_order = $request->input('footer_menu_categories_order');
	   
	   $item_sold_count = $request->input('item_sold_count');
	    $members_count = $request->input('members_count');
		
		$home_subscriber_items = $request->input('home_subscriber_items');
		$shop_search_type = $request->input('shop_search_type');
		
		$item_sold_display = $request->input('item_sold_display');
		$members_count_display = $request->input('members_count_display');
		
		$app_store_url = $request->input('app_store_url');
		$google_play_url = $request->input('google_play_url');
		
		$home_categories_icon = $request->input('home_categories_icon');
	   
	   $sid = $request->input('sid');
	         
         
		 $request->validate([
		 
							
							
							
							
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
		
		   if ($request->hasFile('available_payment_methods')) {
		     
			Settings::dropImage('available_payment_methods'); 
		   
			$image = $request->file('available_payment_methods');
			$img_name = time() . '11.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$available_payment_methods = $img_name;
		  }
		  else
		  {
		     $available_payment_methods = $request->input('save_payment_methods');
		  }
		   
		   
		   $data = array('product_per_page' => $product_per_page, 'post_per_page' => $post_per_page, 'home_featured_items' => $home_featured_items, 'home_flash_items' => $home_flash_items, 'home_popular_items' => $home_popular_items, 'home_new_items' => $home_new_items, 'home_blog_post' => $home_blog_post, 'comment_per_page' => $comment_per_page, 'review_per_page' => $review_per_page, 'site_range_min_price' => $site_range_min_price, 'site_range_max_price' => $site_range_max_price, 'menu_display_categories' => $menu_display_categories, 'menu_categories_order' => $menu_categories_order, 'home_free_items' => $home_free_items, 'footer_menu_display_categories' => $footer_menu_display_categories, 'footer_menu_categories_order' => $footer_menu_categories_order, 'item_sold_count' => $item_sold_count, 'members_count' => $members_count, 'home_subscriber_items' => $home_subscriber_items, 'home_categories_icon' => $home_categories_icon);
  
			Settings::updategeneralData($sid, $data);
			$custom_data = array('shop_search_type' => $shop_search_type, 'item_sold_display' => $item_sold_display, 'members_count_display' => $members_count_display, 'app_store_url' => $app_store_url, 'google_play_url' => $google_play_url, 'available_payment_methods' => $available_payment_methods);
			Settings::updateCustomData($custom_data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
	
	
	}
	
	
	
	
	
	
	public function custom_section()
	{
	  
	    $sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.custom-section', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	
	}
	
	
	public function update_custom_section(Request $request)
	{
	   	   
	      
	   $site_custom_display = $request->input('site_custom_display');
	   $site_custom_title = $request->input('site_custom_title');
	   $site_custom_content = $request->input('site_custom_content');
	   $sid = $request->input('sid');
	         
         
		 $request->validate([
		 
							
							
							
							
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
		
		 
		   $data = array('site_custom_display' => $site_custom_display, 'site_custom_title' => $site_custom_title, 'site_custom_content' => $site_custom_content);
 
			Settings::updatemailData($sid, $data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
	
	
	}
	
	
	
	
	public function about_section()
	{
	
	    $sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.about-section', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	
	}
	
	
	public function update_about_section(Request $request)
	{
	   if(!empty($request->input('site_about_heading')))
	   {
	   $site_about_heading = $request->input('site_about_heading');
	   }
	   else
	   {
	   $site_about_heading = "";
	   }
	   if(!empty($request->input('site_about_desc')))
	   {
	   $site_about_desc = $request->input('site_about_desc');
	   }
	   else 
	   {
	   $site_about_desc = "";
	   }
	   
	   if(!empty($request->input('site_about_btntext')))
	   {
	   $site_about_btntext = $request->input('site_about_btntext');
	   }
	   else 
	   {
	   $site_about_btntext = "";
	   }
	   if(!empty($request->input('site_about_btnlink')))
	   {
	   $site_about_btnlink = $request->input('site_about_btnlink');
	   }
	   else 
	   {
	   $site_about_btnlink = "";
	   }
	   
	   
	   
	   if(!empty($request->input('site_about_videolink')))
	   {
	   $site_about_videolink = $request->input('site_about_videolink');
	   }
	   else 
	   {
	   $site_about_videolink = "";
	   }
	   
	   $save_about_image = $request->input('save_about_image');
	   $sid = $request->input('sid');
	   $image_size = $request->input('image_size');
	   $site_about_display = $request->input('site_about_display');
	            
         
		 $request->validate([
		 
							'site_about_image' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							
							
							
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
		
			  
		 if ($request->hasFile('site_about_image')) {
		     
			Settings::dropAboutbanner($sid); 
		   
			$image = $request->file('site_about_image');
			$img_name = time().'.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$site_about_image = $img_name;
		  }
		  else
		  {
		     $site_about_image = $request->input('save_about_image');
		  }
		 
		$data = array('site_about_heading' => $site_about_heading, 'site_about_desc' => $site_about_desc, 'site_about_btntext' => $site_about_btntext, 'site_about_btnlink' => $site_about_btnlink, 'site_about_image' => $site_about_image, 'site_about_videolink' => $site_about_videolink, 'site_about_display' => $site_about_display);
 
            
            
			Settings::updatemailData($sid, $data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
	
	
	}
	
	
	
	
	public function media_settings()
	{
	
	    $sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.media-settings', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	
	}
	
	
	public function update_media_settings(Request $request)
	{
	
	   $site_max_image_size = $request->input('site_max_image_size');
	   $site_max_zip_size = $request->input('site_max_zip_size');
		$watermark_option = $request->input('watermark_option');         
         $site_s3_storage = $request->input('site_s3_storage');
		  $wasabi_access_key_id = $request->input('wasabi_access_key_id'); 
		$wasabi_secret_access_key = $request->input('wasabi_secret_access_key');
		$wasabi_default_region = $request->input('wasabi_default_region');
		$wasabi_bucket = $request->input('wasabi_bucket');
		
		$dropbox_api = $request->input('dropbox_api');
		$dropbox_token = $request->input('dropbox_token');
		
		$google_drive_client_id = $request->input('google_drive_client_id');
		$google_drive_client_secret = $request->input('google_drive_client_secret');
		$google_drive_refresh_token = $request->input('google_drive_refresh_token');
		$google_drive_folder_id = $request->input('google_drive_folder_id');
		
		
		$watermark_repeat = $request->input('watermark_repeat');
		$watermark_position = $request->input('watermark_position');
		
		$aws_access_key_id = $request->input('aws_access_key_id');
		$aws_secret_access_key = $request->input('aws_secret_access_key');
		$aws_default_region = $request->input('aws_default_region');
		$aws_bucket = $request->input('aws_bucket');
		
		
		
		 $request->validate([
							'site_max_image_size' => 'required',
							'site_max_zip_size' => 'required',
							'site_watermark' => 'mimes:jpeg,jpg,png|max:2000',
							
							
         ]);
		 
		  $sid = $request->input('sid');
		 
         
		 
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
		
			  
		 if ($request->hasFile('site_watermark')) {
		     
			Settings::dropWatermark($sid); 
		   
			$image = $request->file('site_watermark');
			$img_name = time() . '141.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/settings');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$site_watermark = $img_name;
		  }
		  else
		  {
		     $site_watermark = $request->input('save_watermark');
		  }
		 
		$data = array('site_max_image_size' => $site_max_image_size, 'site_max_zip_size' => $site_max_zip_size, 'site_watermark' => $site_watermark, 'watermark_option' => $watermark_option, 'site_s3_storage' => $site_s3_storage, 'wasabi_access_key_id' => $wasabi_access_key_id, 'wasabi_secret_access_key' => $wasabi_secret_access_key, 'wasabi_default_region' => $wasabi_default_region, 'wasabi_bucket' => $wasabi_bucket, 'dropbox_api' => $dropbox_api, 'dropbox_token' => $dropbox_token, 'google_drive_client_id' => $google_drive_client_id, 'google_drive_client_secret' => $google_drive_client_secret, 'google_drive_refresh_token' => $google_drive_refresh_token, 'google_drive_folder_id' => $google_drive_folder_id, 'watermark_repeat' => $watermark_repeat, 'watermark_position' => $watermark_position);
 
            
            
			Settings::updatemailData($sid, $data);
			$custom_data = array('aws_access_key_id' => $aws_access_key_id, 'aws_secret_access_key' => $aws_secret_access_key, 'aws_default_region' => $aws_default_region, 'aws_bucket' => $aws_bucket);
			
			Settings::updateCustomData($custom_data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
	
	
	}
	
	
	public function email_settings()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.email-settings', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
		
    }
	
	
	
	public function update_email_settings(Request $request)
	{
	
	   $sender_name = $request->input('sender_name');
	   $sender_email = $request->input('sender_email');
	   $mail_driver = $request->input('mail_driver');
	   $mail_port = $request->input('mail_port');
	   $mail_password = $request->input('mail_password');
	   $mail_host = $request->input('mail_host');
	   $mail_username = $request->input('mail_username');
	   $mail_encryption = $request->input('mail_encryption');
		         
         
		 $request->validate([
							'sender_name' => 'required',
							'sender_email' => 'required',
							'mail_driver' => 'required',
							'mail_port' => 'required',
							'mail_host' => 'required',
							
							
							
							
         ]);
		 
		  $sid = $request->input('sid');
		 
         
		 
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
		
			  
		 
		 
		$data = array('sender_name' => $sender_name, 'sender_email' => $sender_email, 'mail_driver' => $mail_driver, 'mail_host' => $mail_host, 'mail_port' => $mail_port, 'mail_username' => $mail_username, 'mail_password' => $mail_password, 'mail_encryption' => $mail_encryption);
 
            
            
			Settings::updatemailData($sid, $data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
      
	
	
	}
	
	
	
	public function social_settings()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.social-settings', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
		
    }
	
	
	public function update_social_settings(Request $request)
	{
	    if(!empty($request->input('facebook_url')))
		{
	    $facebook = $request->input('facebook_url');
		}
		else
		{
		 $facebook = ""; 
		}
		
		if(!empty($request->input('twitter_url')))
		{
	    $twitter = $request->input('twitter_url');
		}
		else
		{
		$twitter = "";
		}
		
		if(!empty($request->input('gplus_url')))
		{
		$gplus = $request->input('gplus_url');
		}
		else
		{
		$gplus = "";
		}
		
		if(!empty($request->input('pinterest_url')))
		{
		$pinterest = $request->input('pinterest_url');
		}
		else
		{
		$pinterest = "";
		}
		
		if(!empty($request->input('instagram_url')))
		{
		$instagram = $request->input('instagram_url');
		}
		else
		{
		$instagram = "";
		}
		
		$facebook_client_id = $request->input('facebook_client_id');
		$facebook_client_secret = $request->input('facebook_client_secret');
		$facebook_callback_url = $request->input('facebook_callback_url');
		$google_client_id = $request->input('google_client_id');
		$google_client_secret = $request->input('google_client_secret');
		$google_callback_url = $request->input('google_callback_url');
		$display_social_login = $request->input('display_social_login');
		 
		$sid = $request->input('sid');
			 
		$data = array('facebook_url' => $facebook, 'twitter_url' => $twitter, 'gplus_url' => $gplus, 'pinterest_url' => $pinterest, 'instagram_url' => $instagram, 'facebook_client_id' => $facebook_client_id, 'facebook_client_secret' => $facebook_client_secret, 'facebook_callback_url' => $facebook_callback_url, 'google_client_id' => $google_client_id, 'google_client_secret' => $google_client_secret, 'google_callback_url' => $google_callback_url, 'display_social_login' => $display_social_login);
  		Settings::updatemailData($sid, $data);
        return redirect()->back()->with('success', 'Update successfully.');
       
	
		
	
	}
	
	
	
	public function preferred_settings()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.preferred-settings', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	
	public function update_preferred_settings(Request $request)
	{
	
	     
		 $sid = $request->input('sid');
		 
		 
		 $site_blog_display = $request->input('site_blog_display');
		 $google_ads = $request->input('google_ads');
		 $site_newsletter_display = $request->input('site_newsletter_display');
		 $site_refund_display = $request->input('site_refund_display');
		 $site_withdrawal_display = $request->input('site_withdrawal_display');
		 $site_google_translate = $request->input('site_google_translate');
		 $subscription_mode = $request->input('subscription_mode');
		 $verify_mode = $request->input('verify_mode');
		 $product_sale_count = $request->input('product_sale_count');
         $disable_view_source = $request->input('disable_view_source');
         
		 
		 
		 $request->validate([
							
							
							
							'site_blog_display' => 'required',
							
							
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
		
		
		 
		$data = array('site_blog_display' => $site_blog_display, 'google_ads' => $google_ads, 'site_newsletter_display' => $site_newsletter_display, 'site_refund_display' => $site_refund_display, 'site_withdrawal_display' => $site_withdrawal_display, 'site_google_translate' => $site_google_translate, 'subscription_mode' => $subscription_mode);
        Settings::updategeneralData($sid, $data);
		$custom_data = array('verify_mode' => $verify_mode, 'product_sale_count' => $product_sale_count, 'disable_view_source' => $disable_view_source);
		Settings::updateCustomData($custom_data);
        return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	} 
	
	
	
	
	
	
	public function currency_settings()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		if($this->custom() != 0)
	    {
		return view('admin.currency-settings', [ 'setting' => $setting, 'sid' => $sid]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	
	
	public function update_currency_settings(Request $request)
	{
	
	     
		 $sid = $request->input('sid');
		 
		 $site_currency_code = $request->input('site_currency_code');
		 $site_currency_symbol = $request->input('site_currency_symbol');
		
		
		 
         
		 $request->validate([
							
							'site_currency_code' => 'required',
							'site_currency_symbol' => 'required',
							
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
		
		
		 
		$data = array('site_currency_code' => $site_currency_code, 'site_currency_symbol' => $site_currency_symbol);
        Settings::updategeneralData($sid, $data);
        return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	} 
	
	
	
	
	public function payment_settings()
    {
        
		$sid = 1;
		$setting['setting'] = Settings::editGeneral($sid);
		$payment_option = array('paypal','wallet','paystack','localbank','razorpay', 'coingate','coinpayments','payhere','payfast','flutterwave','mercadopago','coinbase','cashfree','nowpayments','stripe');
		$withdraw_option = array('paypal','stripe','paystack','localbank');
		$get_payment = explode(',', $setting['setting']->payment_option);
		$get_withdraw = explode(',', $setting['setting']->withdraw_option);
		if($this->custom() != 0)
	    {
		return view('admin.payment-settings', [ 'setting' => $setting, 'sid' => $sid, 'payment_option' => $payment_option, 'withdraw_option' => $withdraw_option, 'get_payment' => $get_payment, 'get_withdraw' => $get_withdraw]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
		
    }
	
	
	public function update_payment_settings(Request $request)
	{
	
	   $site_extra_fee = $request->input('site_extra_fee');
	   
	   if(!empty($request->input('payment_option')))
	   {
	     $payment = "";
		 foreach($request->input('payment_option') as $payment_option)
		 {
		    $payment .= $payment_option.',';
		 }
		 $payment_method = rtrim($payment,',');
	   }
	   else
	   {
	   $payment_method = "";
	   }
	   
	   if(!empty($request->input('withdraw_option')))
	   {
	     $withdraw = "";
		 foreach($request->input('withdraw_option') as $withdraw_option)
		 {
		    $withdraw .= $withdraw_option.',';
		 }
		 $withdraw_method = rtrim($withdraw,',');
	   }
	   else
	   {
	   $withdraw_method = "";
	   }
	   $paypal_email = $request->input('paypal_email');
	   $paypal_mode = $request->input('paypal_mode');
	   $stripe_mode = $request->input('stripe_mode');
	   $test_publish_key = $request->input('test_publish_key');
	   $live_publish_key = $request->input('live_publish_key');
	   $test_secret_key = $request->input('test_secret_key');
	   $live_secret_key = $request->input('live_secret_key');
	   $site_minimum_withdrawal = $request->input('site_minimum_withdrawal');
	   $paystack_public_key = $request->input('paystack_public_key');
	   $paystack_secret_key = $request->input('paystack_secret_key');
	   $paystack_merchant_email = $request->input('paystack_merchant_email');
	   $razorpay_key = $request->input('razorpay_key');
	   $razorpay_secret = $request->input('razorpay_secret');
	   
	   $coingate_mode = $request->input('coingate_mode');
	   $coingate_auth_token = $request->input('coingate_auth_token');
	   
	   $coinpayments_merchant_id = $request->input('coinpayments_merchant_id');
	   
	   $payhere_mode = $request->input('payhere_mode');
	   $payhere_merchant_id = $request->input('payhere_merchant_id');
	   $payfast_merchant_id = $request->input('payfast_merchant_id');
	   $payfast_merchant_key = $request->input('payfast_merchant_key');
	   $payfast_mode = $request->input('payfast_mode');
	   $flutterwave_public_key = $request->input('flutterwave_public_key');
	   $flutterwave_secret_key = $request->input('flutterwave_secret_key');
	   
	   $site_referral_commission = $request->input('site_referral_commission');
	   $per_sale_referral_commission = $request->input('per_sale_referral_commission');
	   
	   $site_flash_sale_discount = $request->input('site_flash_sale_discount');
	   
	   $local_bank_details = $request->input('local_bank_details');
	   
	   $mercadopago_mode = $request->input('mercadopago_mode');
	   $mercadopago_client_id = $request->input('mercadopago_client_id');
	   $mercadopago_client_secret = $request->input('mercadopago_client_secret');
	   
	   $coinbase_api_key = $request->input('coinbase_api_key');
	   $coinbase_secret_key = $request->input('coinbase_secret_key');
	   
	   $cashfree_api_key = $request->input('cashfree_api_key');
	   $cashfree_api_secret = $request->input('cashfree_api_secret');
	   $cashfree_mode = $request->input('cashfree_mode');
	   
	   
	   $nowpayments_mode = $request->input('nowpayments_mode');
	   $nowpayments_api_key = $request->input('nowpayments_api_key');
	   $nowpayments_ipn_secret = $request->input('nowpayments_ipn_secret');
	   
	   $per_sale_referral_commission_type = $request->input('per_sale_referral_commission_type');
	   
	   $affiliate_referral = $request->input('affiliate_referral');
	   $stripe_type = $request->input('stripe_type');
	   $flutterwave_default_currency = $request->input('flutterwave_default_currency');
	   
	   $paystack_default_currency = $request->input('paystack_default_currency');
	   
	   $request->validate([
							'site_extra_fee' => 'required',
							
							
							
         ]);
		 
		  $sid = $request->input('sid');
		 
         
		 
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
		
			  
		 
		 
		$data = array('site_extra_fee' => $site_extra_fee, 'payment_option' => $payment_method, 'withdraw_option' => $withdraw_method, 'paypal_email' => $paypal_email, 'paypal_mode' => $paypal_mode, 'stripe_mode' => $stripe_mode, 'test_publish_key' => $test_publish_key, 'test_secret_key' => $test_secret_key, 'live_publish_key' => $live_publish_key, 'live_secret_key' => $live_secret_key, 'site_minimum_withdrawal' => $site_minimum_withdrawal, 'paystack_public_key' => $paystack_public_key, 'paystack_secret_key' => $paystack_secret_key, 'paystack_merchant_email' => $paystack_merchant_email, 'razorpay_key' => $razorpay_key, 'razorpay_secret' => $razorpay_secret, 'coingate_mode' => $coingate_mode, 'coingate_auth_token' => $coingate_auth_token, 'coinpayments_merchant_id' => $coinpayments_merchant_id, 'payhere_mode' => $payhere_mode, 'payhere_merchant_id' => $payhere_merchant_id, 'payfast_merchant_id' => $payfast_merchant_id, 'payfast_merchant_key' => $payfast_merchant_key, 'payfast_mode' => $payfast_mode, 'flutterwave_public_key' => $flutterwave_public_key, 'flutterwave_secret_key' => $flutterwave_secret_key, 'site_referral_commission' => $site_referral_commission, 'per_sale_referral_commission' => $per_sale_referral_commission, 'site_flash_sale_discount' => $site_flash_sale_discount, 'local_bank_details' => $local_bank_details, 'stripe_type' => $stripe_type, 'flutterwave_default_currency' => $flutterwave_default_currency, 'paystack_default_currency' => $paystack_default_currency);
 
            
            
			Settings::updatemailData($sid, $data);
			
			$custom_data = array ('mercadopago_mode' => $mercadopago_mode, 'mercadopago_client_id' => $mercadopago_client_id, 'mercadopago_client_secret' => $mercadopago_client_secret, 'coinbase_api_key' => $coinbase_api_key, 'coinbase_secret_key' => $coinbase_secret_key, 'cashfree_api_key' => $cashfree_api_key, 'cashfree_api_secret' => $cashfree_api_secret, 'cashfree_mode' => $cashfree_mode, 'nowpayments_mode' => $nowpayments_mode, 'nowpayments_api_key' => $nowpayments_api_key, 'nowpayments_ipn_secret' => $nowpayments_ipn_secret, 'per_sale_referral_commission_type' => $per_sale_referral_commission_type, 'affiliate_referral' => $affiliate_referral);
			Settings::updateCustomData($custom_data);
            return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
       
	   
		     
	}
	
	
	
	
	/* settings */
	
	
	
	/* country settings */
  
  
  public function country_settings()
    {
        
		
		$country['data'] = Settings::getcountryData();
		if($this->custom() != 0)
	    {
		return view('admin.country-settings',[ 'country' => $country]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	
	public function add_country()
	{
	   if($this->custom() != 0)
	   {
	   return view('admin.add-country');
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function save_country(Request $request)
	{
 
    
         $country_name = $request->input('country_name');
		 $vat_price = $request->input('vat_price');        
         
		 $request->validate([
							'country_name' => 'required',
							
							
         ]);
		 $rules = array(
				
				'country_name' => ['required', 'max:255', Rule::unique('country') -> where(function($sql){ $sql->where('country_name','!=','');})],
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
		
				 
		$data = array('country_name' => $country_name, 'vat_price' => $vat_price);
 
            
            Settings::savecountryData($data);
            return redirect('/admin/country-settings')->with('success', 'Insert successfully.');
            
 
       } 
     
    
  }
  
  
  public function all_delete_country(Request $request)
	{
	   
	   $country_id = $request->input('country_id');
	   foreach($country_id as $id)
	   {
	      
		  Settings::deleteCountrydata($id);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
  
  
  public function delete_country($cid){

      
	  
      Settings::deleteCountrydata($cid);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
  public function edit_country($cid)
	{
	   
	   $edit['country'] = Settings::editCountry($cid);
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-country', [ 'edit' => $edit, 'cid' => $cid]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	public function update_country(Request $request)
	{
	
	   $country_name = $request->input('country_name');
	   $vat_price = $request->input('vat_price'); 	         
         
		 $request->validate([
							'country_name' => 'required',
							
							
         ]);
		 
		  $cid = $request->input('cid');
		 
         
		 
		 $rules = array(
				'country_name' => ['required', 'max:255', Rule::unique('country') ->ignore($cid, 'country_id') -> where(function($sql){ $sql->where('country_name','!=','');})],
				
				
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
		
			  
		 
		 
		$data = array('country_name' => $country_name, 'vat_price' => $vat_price);
 
            
            
			Settings::updatecountryData($cid, $data);
            return redirect('/admin/country-settings')->with('success', 'Update successfully.');
            
 
       } 
     
      
	
	
	}
	
	
	
	
  /* country settings */	
  
  
	
	
	
}
