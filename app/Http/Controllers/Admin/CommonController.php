<?php

namespace DownGrade\Http\Controllers\Admin;


use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use DownGrade\Models\Pages;
use DownGrade\Models\Settings;
use DownGrade\Models\EmailTemplate;
use DownGrade\Models\Events;
use DownGrade\Models\Members;
use Auth;
use Mail;
use Artisan;
use Helper;

class CommonController extends Controller
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
	
	public function logout(Request $request) {
	  Auth::logout();
	  return redirect('/login');
    }
	
	
	public function delete_cache()
	{
	    Artisan::call('cache:clear');
		Artisan::call('view:clear');
		Artisan::call('config:cache');
		Artisan::call('optimize:clear');
		/*return redirect(admin/contact)->with('success','All cache data has been cleared');*/
		return redirect()->back()->with('success','All cache data has been cleared');
		
	}
	
	public function view_contact()
	{
	  
	  $contactData['view'] = Pages::getcontactData();
	  $data = array('contactData' => $contactData);
	  if($this->custom() != 0)
	  {
	  return view('admin.contact')->with($data);
	  }
	  else
	  {
		  return redirect('/admin/license');
	  }
	}
	
	
	public function view_newsletter()
	{
	  
	  $newsData['view'] = Pages::getnewsletterData();
	  $data = array('newsData' => $newsData);
	  if($this->custom() != 0)
	  {
	  return view('admin.newsletter')->with($data);
	  }
	  else
	  {
		  return redirect('/admin/license');
	  }
	
	}
	
	
	public function view_add_contact()
	{
	    if($this->custom() != 0)
	    {   
		return view('admin.add-contact');
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
	}
	
	
	public function update_contact(Request $request)
	{
	
	  $from_name = $request->input('from_name');
	  $from_email = $request->input('from_email');
	  $message_text = $request->input('message_text');
	  
	  $contact_count = Members::getcontactCount($from_email);
	  if($contact_count == 0)
	  {
	  $record = array('from_name' => $from_name, 'from_email' => $from_email, 'message_text' => $message_text, 'contact_date' => date('Y-m-d'));
	  Members::saveContact($record);
	  
	  return redirect('admin/add-contact')->with('success','Added successfully');
	  }
	  else
	  {
	  return redirect('admin/add-contact')->with('error','Sorry! Contact details already added');
	  }
	  
	  
	
	}
	
	
	public function view_contact_delete($id)
	{
	   Pages::deleteContact($id);
	   return redirect()->back()->with('success','Delete successfully.');
	}
	
	
	public function all_delete_contact(Request $request)
	{
	   
	   $cid = $request->input('cid');
	   foreach($cid as $id)
	   {
	      
		  Pages::deleteContact($id);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
	
	
	public function all_delete_newsletter(Request $request)
	{
	   
	   $news_id = $request->input('news_id');
	   foreach($news_id as $id)
	   {
	      
		  Pages::deleteNewsletter($id);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
	
	public function view_newsletter_delete($id)
	{
	   Pages::deleteNewsletter($id);
	   return redirect()->back()->with('success','Delete successfully.');
	}
	
	public function view_send_updates()
	{
	  $newsData['view'] = Pages::getactiveNewsletter();
	  $data = array('newsData' => $newsData);
	  if($this->custom() != 0)
	  {  
	  return view('admin.send-updates')->with($data);
	  }
	  else
	  {
		  return redirect('/admin/license');
	  }
	}
	
	
	public function send_updates(Request $request)
	{
	   
	   
	   $news_heading = $request->input('news_heading');
	   $news_content = $request->input('news_content');
	   $news_email = $request->input('news_email');
	   
	     
         
		 $request->validate([
		 
							
					'news_heading' => 'required',
					'news_content' => 'required',		
							
							
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
		   
		   foreach($news_email as $to_email)
		   {
		     
			    $sid = 1;
				$setting['setting'] = Settings::editGeneral($sid);
				$from_name = $setting['setting']->sender_name;
				$from_email = $setting['setting']->sender_email;
				$record = array('news_heading' => $news_heading, 'news_content' => $news_content);
				/* email template code */
	              $checktemp = EmailTemplate::checkTemplate(16);
				  if($checktemp != 0)
				  {
				  $template_view['mind'] = EmailTemplate::viewTemplate(16);
				  $template_subject = $template_view['mind']->et_subject;
				  }
				  else
				  {
				  $template_subject = "Newsletter Updates";
				  }
			     /* email template code */
				Mail::send('admin.newsletter_update_mail', $record, function($message) use ($from_name, $from_email, $to_email, $template_subject) {
					$message->to($to_email)
							->subject($template_subject);
					$message->from($from_email,$from_name);
				});
		
		   
		   }
		
			
           return redirect()->back()->with('success', 'Your message has been sent successfully.');
            
 
        } 
     
	
	
	}
	
	
	public function view_gallery()
	{
	  
	  $get['gallery'] = Events::getadminGallery();
	  if($this->custom() != 0)
	  {
	  return view('admin.gallery',[ 'get' => $get]);
	  }
	  else
	  {
		  return redirect('/admin/license');
	  } 
	 
	}
	
	public function view_add_gallery()
	{
	  
	  if($this->custom() != 0)
	  {
	  return view('admin.add-gallery');
	  }
	  else
	  {
		  return redirect('/admin/license');
	  }  
	  
	}
	
	
	public function update_add_gallery(Request $request)
	{
	
	   
	   $gallery_status = $request->input('gallery_status');
	   $image_size = $request->input('image_size');
	   
	   
	   
	   $request->validate([
							
							'gallery_status' => 'required',
							'gallery_image' => 'required|mimes:jpeg,jpg,png,svg|max:'.$image_size,
							
							
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
	   
	      if ($request->hasFile('gallery_image')) {
		     
				   
			$image = $request->file('gallery_image');
			$img_name = time() . '.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/gallery');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$gallery_image = $img_name;
		  }
		  else
		  {
		     $gallery_image = "";
		  }
	      
		   $data = array('gallery_image' => $gallery_image, 'gallery_status' => $gallery_status);
 
            
            
			Events::savegalleryData($data);
            return redirect('/admin/gallery')->with('success', 'Insert successfully.');
	   
	   
	   }
	   
	   
	
	}
	
	public function delete_gallery($token)
	{
	  $gallery_id = base64_decode($token);
	  Events::dropGallery($gallery_id);
	  return redirect('/admin/gallery')->with('success', 'Delete successfully.');
	}
	
	
	public function edit_gallery($token)
	{
	  $gallery_id = base64_decode($token);
	  $edit['gallery'] = Events::editsingleGallery($gallery_id);
	  if($this->custom() != 0)
	  {
	  return view('admin.edit-gallery',['edit' => $edit]);
	  }
	  else
	  {
		  return redirect('/admin/license');
	  }
	}
	
	
	public function update_gallery(Request $request)
	{
	
	   
	   $gallery_status = $request->input('gallery_status');
	   $save_gallery_image = $request->input('save_gallery_image');
	   $gallery_id = base64_decode($request->input('gallery_id'));
	   $image_size = $request->input('image_size');
	   
	   $request->validate([
							
							'gallery_status' => 'required',
							'gallery_image' => 'mimes:jpeg,jpg,png,svg|max:'.$image_size,
							
							
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
	   
	      if ($request->hasFile('gallery_image')) {
		     Events::droGalleryPhoto($gallery_id);
				   
			$image = $request->file('gallery_image');
			$img_name = time() . '.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/gallery');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$gallery_image = $img_name;
		  }
		  else
		  {
		     $gallery_image = $save_gallery_image;
		  }
	      
		   $data = array('gallery_image' => $gallery_image, 'gallery_status' => $gallery_status);
 
            
            
			Events::upgalleryData($gallery_id,$data);
            return redirect('/admin/gallery')->with('success', 'Update successfully.');
	   
	   
	   }
	   
	
	
	}
	
	
	
	
	
}
