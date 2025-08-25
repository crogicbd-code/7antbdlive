<?php

namespace DownGrade\Helpers;
use Cookie;
use DownGrade\Models\Members;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use DownGrade\Models\Product;
use DownGrade\Models\Settings;
use DownGrade\Models\EmailTemplate;
use DownGrade\Models\Attribute;
use DownGrade\Models\Category;
use Auth;

class Helper 
{

    public static function Current_Version()
	{
	    $version = 'Version 6.5';
		return $version;
	}
	
	public static function version_no()
	{
	    $version = 'user_license_6_5';
		return $version;
	}
    
    public static function count_rating($rate_var) 
    {
	   
	    if(count($rate_var) != 0)
        {
           $top = 0;
           $bottom = 0;
           foreach($rate_var as $view)
           { 
              if($view->rating == 1){ $value1 = $view->rating*1; } else { $value1 = 0; }
              if($view->rating == 2){ $value2 = $view->rating*2; } else { $value2 = 0; }
              if($view->rating == 3){ $value3 = $view->rating*3; } else { $value3 = 0; }
              if($view->rating == 4){ $value4 = $view->rating*4; } else { $value4 = 0; }
              if($view->rating == 5){ $value5 = $view->rating*5; } else { $value5 = 0; }
              $top += $value1 + $value2 + $value3 + $value4 + $value5;
              $bottom += $view->rating;
           }
           if(!empty(round($top/$bottom)))
           {
             $count_rating = round($top/$bottom);
           }
           else
           {
              $count_rating = 0;
            }
        }
        else
        {
            $count_rating = 0;
        }  
	    
	    
		return $count_rating;
        
    }
	
	public static function price_info($flash_var,$price_var) 
    {
	    $sid = 1;
	    $setting['setting'] = Settings::editGeneral($sid);
	    if($flash_var == 1)
        {
		
			/*$varprice = ($setting['setting']->site_flash_sale_discount * $price_var) / 100;
			$price = round($varprice,2);*/
			$varprice = ($price_var / 100) * $setting['setting']->site_flash_sale_discount;
            $pricess = $price_var - $varprice;
            $price = round($pricess,2);
			
			/*}*/
        }
        else
        {
        $price = $price_var;
        }
		return $price;
	}
	
	public static function if_purchased($product_token)
	{
	   if (Auth::check()) 
	  {
	  $checkif_purchased = Product::ifpurchaseCount($product_token);
	  }
	  else
	  {
	    $checkif_purchased = 0;
	  }
	  return $checkif_purchased;
	   
	}
	
	public static function id_toget_category($id,$data)
	{
	    $category_data = Category::getsinglecatData($id);
		return $category_data->$data;
	}
	
	public static function lifeTime($user_id)
	{
       $getdata = Members::GetLifetime($user_id);
	   return $getdata;
    }
	
	public static function Email_Subject($id)
	{
	   $checktemp = EmailTemplate::checkTemplate($id);
	   if($checktemp != 0)
	   { 
	      $template_view = EmailTemplate::viewTemplate($id);
		  return $template_view->et_subject;
	   } 
	   
	}
	
	public static function Email_Content($id,$search,$replace)
	{
	   $checktemp = EmailTemplate::checkTemplate($id);
	   if($checktemp != 0)
	   { 
	      $template_view = EmailTemplate::viewTemplate($id);
		  return str_replace($search,$replace,$template_view->et_content);
	   } 
	   
	}
	
	public static function SelectedButes($product_token,$attribute_id)
	{
	   $get_data['values'] = Attribute::SingleAttributes($product_token,$attribute_id);
	   
	   return $get_data['values'];
	}
	
	
	public static function Redeem_User($user_id)
	{
	   $checkout = Members::referralCheck($user_id);
	   if($checkout != 0)
	   {
	   $single = Members::logindataUser($user_id);
	   return $single->username;
	   }
	   else
	   {
	   return "---";
	   }
	   
	}
	
	public static function Get_User_Name($user_id)
	{
	  $single = Members::logindataUser($user_id);
	   return $single->username;
	}
	
	public static function Get_User_Photo($user_id)
	{
	  $single = Members::logindataUser($user_id);
	   return $single->user_photo;
	}
	
}