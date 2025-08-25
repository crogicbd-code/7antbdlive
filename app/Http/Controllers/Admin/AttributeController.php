<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Attribute;
use DownGrade\Models\Items;
use DownGrade\Models\Category;
use DownGrade\Models\Settings;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Helper;

class AttributeController extends Controller
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
	
	/* category */
	
	public function attribute()
    {
        
		
		$attributeData['attribute'] = Attribute::getattributeData();
		if($this->custom() != 0)
	    {
		return view('admin.attributes',[ 'attributeData' => $attributeData]);
		}
		else
		{
		  return redirect('/admin/license');
		}
    }
    
	
	public function add_attribute()
	{
	   $attr_field_type = array('multi-select' => 'Multi Select', 'single-select' => 'Single Select');
	   if($this->custom() != 0)
	   {
	   return view('admin.add-attribute',[ 'attr_field_type' => $attr_field_type]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	
	
	
	public function save_attribute(Request $request)
	{
 
    
         
		 $attr_label = $request->input('attr_label');
		 if(!empty($request->input('attr_field_order')))
		 {
		 $attr_field_order = $request->input('attr_field_order');
		 }
		 else
		 {
		   $attr_field_order = 0;
		 }
		 $attr_field_type = $request->input('attr_field_type');
		 $attr_field_value = $request->input('attr_field_value');
		 $attr_field_status = $request->input('attr_field_status');
		 
         
		 $request->validate([
							'attr_label' => 'required',
							
							
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
		
		
		 
		$data = array('attr_label' => $attr_label, 'attr_field_type' => $attr_field_type, 'attr_field_value' => $attr_field_value, 'attr_field_order' => $attr_field_order, 'attr_field_status' => $attr_field_status);
        Attribute::insertattributeData($data);
        return redirect('/admin/attributes')->with('success', 'Insert successfully.');
            
 
       } 
     
    
  }
  
  public function all_delete_attribute(Request $request)
	{
	   
	   $attr_id = $request->input('attr_id');
	   $data = array('attr_drop_status'=>'yes');
	   foreach($attr_id as $id)
	   {
	      Attribute::deleteAttributedata($id,$data);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
  
  public function delete_attribute($attr_id){

      $data = array('attr_drop_status'=>'yes');
	  
        
      Attribute::deleteAttributedata($attr_id,$data);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
  public function edit_attribute($attr_id)
	{
	   
	   $edit['attribute'] = Attribute::editattributeData($attr_id);
	   $attr_field_type = array('multi-select' => 'Multi Select', 'single-select' => 'Single Select');
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-attribute', [ 'edit' => $edit, 'attr_id' => $attr_id, 'attr_field_type' => $attr_field_type]);
	   }
       else
	   {
	   return redirect('/admin/license');
	   }
	}
	
	
	
	public function update_attribute(Request $request)
	{
	
	     
		 $attr_label = $request->input('attr_label');
		 if(!empty($request->input('attr_field_order')))
		 {
		 $attr_field_order = $request->input('attr_field_order');
		 }
		 else
		 {
		   $attr_field_order = 0;
		 }
		 $attr_field_type = $request->input('attr_field_type');
		 $attr_field_value = $request->input('attr_field_value');
		 $attr_field_status = $request->input('attr_field_status');
		  
         $attr_id = $request->input('attr_id');
		 $request->validate([
							'attr_label' => 'required',
							
							
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
		
		
		$data = array('attr_label' => $attr_label, 'attr_field_type' => $attr_field_type, 'attr_field_value' => $attr_field_value, 'attr_field_order' => $attr_field_order, 'attr_field_status' => $attr_field_status);
        Attribute::updateattributeData($attr_id, $data);
        return redirect('/admin/attributes')->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	}
	
	
	/* category */
	
	
	
	
	
}
