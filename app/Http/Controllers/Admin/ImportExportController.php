<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Session;
use DownGrade\Models\Product;
use DownGrade\Models\Settings;
use DownGrade\Models\Members;
use DownGrade\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Mail;
use Excel;
use DownGrade\Models\ExportProduct;
use DownGrade\Models\ImportProduct;
use Helper;


class ImportExportController extends Controller
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
	
	public function view_products_import_export()
    {
        if($this->custom() != 0)
	    {
		return view('admin.products-import-export');
		}
	    else
	    {
		  return redirect('/admin/license');
	    }
		
    }
	
	public function download_products_export($type)
    {
	   $filename = "products_".uniqid().'.'.$type;
	   return Excel::download(new ExportProduct, $filename);
		
    }
	
	public function products_import(Request $request){
        /*if($request->hasFile('import_file')){
            $path = $request->file('import_file')->getRealPath();
            $data = Excel::import($path)->get();
            if($data->count()){
                foreach ($data as $key => $value) {
                    $arr[] = ['name' => $value->name, 'details' => $value->details];
                }
                if(!empty($arr)){
                    DB::table('product')->insert($arr);
                    dd('Insert Record successfully.');
                }
            }
        }
		
        dd('Request data does not have any files to import.');*/ 
		Excel::import(new ImportProduct, $request->file('import_file'));
		return redirect()->back()->with('success', 'Imported successfully.');   
    } 
	
	
	
}
