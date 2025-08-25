<?php

namespace DownGrade\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DownGrade\Http\Controllers\Controller;
use Artisan;
use Log;
use Session;
use DownGrade\Models\Settings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Helper;

class BackupController extends Controller
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
	
	public function index(){
        $disk = Storage::disk(config('laravel-backup.backup.destination.disks'));
        $files = $disk->files('/Laravel');
        $backups = [];
        foreach ($files as $k => $f) {
           if (substr($f, -4) == '.zip' && $disk->exists($f)) {
               $backups[] = [
               'file_path' => $f,
               'file_name' => str_replace(config('laravel-backup.backup.name') . 'Laravel/', '', $f),
               'file_size' => $disk->size($f),
               'last_modified' => $disk->lastModified($f),
                ];
           }
        }
	$backups = array_reverse($backups);
	    if($this->custom() != 0)
	    {
        return view("admin.backup")->with(compact('backups'));
		}
		else
		{
		  return redirect('/admin/license');
		}
    }
	
	public static function humanFileSize($size,$unit="") {
          if( (!$unit && $size >= 1<<30) || $unit == "GB")
               return number_format($size/(1<<30),2)."GB";
          if( (!$unit && $size >= 1<<20) || $unit == "MB")
               return number_format($size/(1<<20),2)."MB";
          if( (!$unit && $size >= 1<<10) || $unit == "KB")
               return number_format($size/(1<<10),2)."KB";
          return number_format($size)." bytes";
    }
	
	
	public function create()
    {
	      $additional = Settings::editCustom();
		  if($additional->demo_mode == 'off')
		  {
			   try {
				   if($additional->backup_types == 'database')
				   {
					/* only database backup*/
					Artisan::call('backup:run --only-db');
				   }
				   else
				   {	
				   /* all backup */
				   Artisan::call('backup:run');
				   }
				   $output = Artisan::output();
				   Log::info("Backpack\BackupManager -- new backup started \r\n" . $output);
				   session()->flash('success', 'Successfully created backup!');
				   return redirect()->back();
			  } catch (Exception $e) {
				   session()->flash('danger', $e->getMessage());
				   return redirect()->back();
			  }
		  }
		  else
		  {
		     return redirect()->back()->with('error', 'This is Demo version. You can not add or change any thing');
		  }
    }
	
	public function download($file_name) {
	   
	   
	   $additional = Settings::editCustom();
	  if($additional->demo_mode == 'off')
	  {
        $file = config('laravel-backup.backup.name') .'/Laravel/'. $file_name;
        $disk = Storage::disk(config('laravel-backup.backup.destination.disks'));

        if ($disk->exists($file)) {
            $fs = Storage::disk(config('laravel-backup.backup.destination.disks'))->getDriver();
            $stream = $fs->readStream($file);
            $tt = storage_path()."/app/Laravel/".$file_name;
            return \Response::stream(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                "Content-Type" => mime_content_type($tt),
                //"Content-Length" => $fs->getSize($file),
                "Content-disposition" => "attachment; filename=\"" . basename($file) . "\"",
            ]);
        } else {
            abort(404, "Backup file doesn't exist.");
         }
	   }
	   else
	   {
	     return redirect()->back()->with('error', 'This is Demo version. You can not add or change any thing');
	   }	
    }
	
	
	public function delete($file_name){
	      $additional = Settings::editCustom();
	  if($additional->demo_mode == 'off')
	  {
          $disk = Storage::disk(config('laravel-backup.backup.destination.disks'));
		  
          //if ($disk->exists(config('laravel-backup.backup.name') . '/Laravel/' . $file_name)) {
		  
               $disk->delete(config('laravel-backup.backup.name') . '/Laravel/' . $file_name);
			   
               session()->flash('delete', 'Successfully deleted backup!');
               return redirect()->back();
          //} else {
		     
               //abort(404, "Backup file doesn't exist.");
          //}
		 }
		 else
		 {
		    return redirect()->back()->with('error', 'This is Demo version. You can not add or change any thing');
		 } 
     }
	 
	 
	 public function backup(Request $request)
	{
	
	     
		 
		 
		 $backup_types = $request->input('backup_types');
		 
		
		
		 
         
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
		
		
		 
		$data = array('backup_types' => $backup_types);
        Settings::updateCustomData($data);
        return redirect()->back()->with('success', 'Update successfully.');
            
 
       } 
     
       
	
	
	} 
	

	
}
