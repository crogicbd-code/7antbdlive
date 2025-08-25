<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Category;
use DownGrade\Models\Settings;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Helper;

class CategoryController extends Controller
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
	
	public function category()
    {
        
		
		$categoryData['category'] = Category::getcategoryData();
		if($this->custom() != 0)
	    {
		return view('admin.category',[ 'categoryData' => $categoryData]);
		}
		else
		{
		  return redirect('/admin/license');
		}
    }
    
	
	public function add_category()
	{
	   if($this->custom() != 0)
	   {
	   return view('admin.add-category');
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	public function category_slug($string)
	{
		$string=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
		$string=strtolower($string);
		return $string;	
    }
	
	
	
	public function save_category(Request $request)
	{
 
    
         $category_name = $request->input('category_name');
		 $category_slug = $this->category_slug($category_name);
		 $category_status = $request->input('category_status');
		 if(!empty($request->input('display_order')))
		 {
		 $display_order = $request->input('display_order');
		 }
		 else
		 {
		   $display_order = 0;
		 }
		 $category_meta_keywords = $request->input('category_meta_keywords');
		 $category_meta_desc = $request->input('category_meta_desc');
		 
         
		 $request->validate([
							'category_name' => 'required',
							'category_status' => 'required',
							
         ]);
		 $rules = array(
				'category_name' => ['required', 'max:255', Rule::unique('category') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				
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
		
		
		   if ($request->hasFile('category_icon')) 
		  {
		     
			$image = $request->file('category_icon');
			$img_name = time() . '9.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/category');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$category_icon = $img_name;
		  }
		  else
		  {
		     $category_icon = "";
		  } 
		  if ($request->hasFile('category_image')) 
		  {
		     
			$image = $request->file('category_image');
			$img_name = time() . '2.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/category');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$category_image = $img_name;
		  }
		  else
		  {
		     $category_image = "";
		  } 
		 
		$data = array('category_name' => $category_name, 'category_slug' => $category_slug, 'category_status' => $category_status, 'display_order' => $display_order, 'category_meta_keywords' => $category_meta_keywords, 'category_meta_desc' => $category_meta_desc, 'category_icon' => $category_icon, 'category_image' => $category_image);
        Category::insertcategoryData($data);
        return redirect('/admin/category')->with('success', 'Insert successfully.');
            
 
       } 
     
    
  }
  
  public function all_delete_category(Request $request)
	{
	   
	   $cat_id = $request->input('cat_id');
	   $data = array('drop_status'=>'yes');
	   foreach($cat_id as $id)
	   {
	      
		  Category::deleteCategorydata($id,$data);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
  
  public function delete_category($cat_id){

      $data = array('drop_status'=>'yes');
	  
       
      Category::deleteCategorydata($cat_id,$data);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
     
  
  
  public function edit_category($cat_id)
	{
	   
	   $edit['category'] = Category::editcategoryData($cat_id);
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-category', [ 'edit' => $edit, 'cat_id' => $cat_id]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	public function update_category(Request $request)
	{
	
	    $category_name = $request->input('category_name');
		 $category_slug = $this->category_slug($category_name);
		 $category_status = $request->input('category_status');
		 if(!empty($request->input('display_order')))
		 {
		 $display_order = $request->input('display_order');
		 }
		 else
		 {
		   $display_order = 0;
		 }
		 
		 $category_meta_keywords = $request->input('category_meta_keywords');
		 $category_meta_desc = $request->input('category_meta_desc');
		 
		 
		 
         $cat_id = $request->input('cat_id');
		 $request->validate([
							'category_name' => 'required',
							'category_status' => 'required',
							
         ]);
		 $rules = array(
				'category_name' => ['required', 'max:255', Rule::unique('category') ->ignore($cat_id, 'cat_id') -> where(function($sql){ $sql->where('drop_status','=','no');})],
				
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
		
		
		
		  if ($request->hasFile('category_icon')) 
		  {
		    Category::dropImage($cat_id,'category_icon');  
			$image = $request->file('category_icon');
			$img_name = time() . '9.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/category');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$category_icon = $img_name;
		  }
		  else
		  {
		     $category_icon = $request->input('save_icon');
		  } 
		  if ($request->hasFile('category_image')) 
		  {
		    Category::dropImage($cat_id,'category_image');
			$image = $request->file('category_image');
			$img_name = time() . '2.'.$image->getClientOriginalExtension();
			$destinationPath = public_path('/storage/category');
			$imagePath = $destinationPath. "/".  $img_name;
			$image->move($destinationPath, $img_name);
			$category_image = $img_name;
		  }
		  else
		  {
		     $category_image = $request->input('save_image');
		  } 
		  
		
		
		$data = array('category_name' => $category_name, 'category_slug' => $category_slug, 'category_status' => $category_status, 'display_order' => $display_order, 'category_meta_keywords' => $category_meta_keywords, 'category_meta_desc' => $category_meta_desc, 'category_icon' => $category_icon, 'category_image' => $category_image);
        Category::updatecategoryData($cat_id, $data);
            return redirect('/admin/category')->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	}
	
	
	/* category */
	
	
	
	/* subcategory */
	
	
	public function subcategory()
    {
        
		
		$subcategoryData['subcategory'] = Category::getsubcategoryData();
		if($this->custom() != 0)
	    {
		return view('admin.sub-category',[ 'subcategoryData' => $subcategoryData]);
		}
		else
		{
		  return redirect('/admin/license');
		}
    }
	
	
	public function add_subcategory()
	{
	   $categoryData['category'] = Category::allcategoryData();
	   if($this->custom() != 0)
	   {
	   return view('admin.add-subcategory',[ 'categoryData' => $categoryData]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	public function save_subcategory(Request $request)
	{
 
    
         $cat_id = $request->input('cat_id');
		 $subcategory_name = $request->input('subcategory_name');
		 $subcategory_slug = $this->category_slug($subcategory_name);
		 $subcategory_status = $request->input('subcategory_status');
		 $subcategory_order = $request->input('subcategory_order');
		 
		 
         
		 $request->validate([
							'cat_id' => 'required',
							'subcategory_name' => 'required',
							'subcategory_status' => 'required',
							
         ]);
		 $rules = array(
				/*'subcategory_name' => ['required', 'max:255', Rule::unique('subcategory') -> where(function($sql){ $sql->where('drop_status','=','no');})],*/
				
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
		
		
		 
		$data = array('cat_id' => $cat_id, 'subcategory_name' => $subcategory_name, 'subcategory_slug' => $subcategory_slug, 'subcategory_status' => $subcategory_status, 'subcategory_order' => $subcategory_order);
        Category::insertsubcategoryData($data);
        return redirect('/admin/sub-category')->with('success', 'Insert successfully.');   
 
       } 
     
    
  }
  
  
    public function all_delete_subcategory(Request $request)
	{
	   
	   $subcat_id = $request->input('subcat_id');
	   $data = array('drop_status'=>'yes');
	   foreach($subcat_id as $id)
	   {
	      
		  Category::deleteSubcategorydata($id,$data);
	   }
	   return redirect()->back()->with('success','Delete successfully.');
	
	}
  
	public function delete_subcategory($subcat_id){

      $data = array('drop_status'=>'yes');
	  
        
      Category::deleteSubcategorydata($subcat_id,$data);
	  
	  return redirect()->back()->with('success', 'Delete successfully.');

    
  }
  
  
  
  public function edit_subcategory($subcat_id)
	{
	   $categoryData['category'] = Category::allcategoryData();
	   $edit['subcategory'] = Category::editsubcategoryData($subcat_id);
	   if($this->custom() != 0)
	   {
	   return view('admin.edit-subcategory', [ 'edit' => $edit, 'subcat_id' => $subcat_id, 'categoryData' => $categoryData]);
	   }
	   else
	   {
		  return redirect('/admin/license');
	   }
	}
	
	
	
	public function update_subcategory(Request $request)
	{
	
	    $cat_id = $request->input('cat_id');
		 $subcategory_name = $request->input('subcategory_name');
		 $subcategory_slug = $this->category_slug($subcategory_name);
		 $subcategory_status = $request->input('subcategory_status');
		 $subcategory_order = $request->input('subcategory_order');
		 
		 $subcat_id = $request->input('subcat_id');
         
		 $request->validate([
							'cat_id' => 'required',
							'subcategory_name' => 'required',
							'subcategory_status' => 'required',
							
         ]);
		 $rules = array(
				/*'subcategory_name' => ['required', 'max:255', Rule::unique('subcategory') ->ignore($subcat_id, 'subcat_id') -> where(function($sql){ $sql->where('drop_status','=','no');})],*/
				
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
		
		$data = array('cat_id' => $cat_id, 'subcategory_name' => $subcategory_name, 'subcategory_slug' => $subcategory_slug, 'subcategory_status' => $subcategory_status, 'subcategory_order' => $subcategory_order);
		
        Category::updatesubcategoryData($subcat_id, $data);
        return redirect('/admin/sub-category')->with('success', 'Update successfully.');    
 
       } 
     
       
	
	
	}
	
  
	/* subcategory */
	
	
	
		
	
}
