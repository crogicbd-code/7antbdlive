<?php

namespace DownGrade\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use DownGrade\Models\Pages;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    

    
    	
	public function view_page($page_slug)
	{
	  $checkpage = Pages::ccpageCount($page_slug);
	  if($checkpage != 0)
	  {
	  $page['page'] = Pages::editpageData($page_slug);
	  
	  $data = array('page' => $page);
	  return view('page')->with($data);
	  }
	  else
	  {
	    return redirect('404');
	  }
	
	}
	
	
	
	
}
