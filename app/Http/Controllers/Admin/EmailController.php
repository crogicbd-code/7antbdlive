<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\EmailTemplate;
use DownGrade\Models\Settings;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Helper;


class EmailController extends Controller
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
	
	public function email_template()
    {
        
		
		$templateData['view'] = EmailTemplate::gettemplate();
		if($this->custom() != 0)
	    {
		return view('admin.email-template',[ 'templateData' => $templateData]);
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
    }
	
	
	
	public function add_email_template()
	{
	   if($this->custom() != 0)
	   {
	   return view('admin.add-email-template');
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function page_slug($string){
		   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
		   return $slug;
    }
	
	
	
	public function save_email_template(Request $request)
	{
         $et_heading = $request->input('et_heading');
		 $et_status = $request->input('et_status');
		 $request->validate([
							'et_heading' => 'required',
							'et_status' => 'required',
							
							
							
         ]);
		 $rules = array(
				
				'et_heading' => ['required',  Rule::unique('email_template') -> where(function($sql){ $sql->where('et_status','!=','');})],
				
				
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
		 	
			$data = array('et_heading' => $et_heading, 'et_status' => $et_status);
			EmailTemplate::savetemplate($data);
			return redirect('/admin/email-template')->with('success', 'Insert Successfully');	
				
		}
			
		
     
    
    }
  
  
  
  public function delete_pages($page_id){

      
	  
      Pages::deletePagedata($page_id);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
  public function edit_email_template($et_id)
	{
	   
	   $edit['template'] = EmailTemplate::edittemplate($et_id);
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-email-template', [ 'edit' => $edit, 'et_id' => $et_id]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	
	
	
	public function update_email_template(Request $request)
	{
	
	   
		 $et_subject = $request->input('et_subject');
		 $et_status = $request->input('et_status');
		 $et_content = htmlentities($request->input('et_content'));
		 
		 $et_id = $request->input('et_id');
		 
		 
         
		 $request->validate([
		                    
							'et_content' => 'required',
							
							
							
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
		
		   
		 
		$data = array('et_subject' => $et_subject, 'et_content' => $et_content, 'et_status' => $et_status);
        EmailTemplate::updateTemplate($et_id, $data);
        return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
      
     
       
	
	
	}
	
	
	
	
	
	
  
	
	
	
}
