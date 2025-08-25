<?php

namespace DownGrade\Http\Controllers;

use Illuminate\Http\Request;
use DownGrade\Models\Product;
use DownGrade\Models\Category;
use DownGrade\Models\SubCategory;
use DownGrade\Models\Pages;
use DownGrade\Models\Blog;
use DownGrade\Models\Members;
use DownGrade\Http\Requests;

class SitemapController extends Controller
{
    
	public function index()
    {
      $items = Product::all()->first();
      $category = Category::all()->first();
      $subcategory = SubCategory::all()->first();
      $pages = Pages::all()->first();
	  $blog = Blog::all()->first();
	  

      return response()->view('sitemap.index', [
          'items' => $items,
          'category' => $category,
          'subcategory' => $subcategory,
          'pages' => $pages,
		  'blog' => $blog,
		])->header('Content-Type', 'text/xml');
    }
	
	public function items()
    {
        $items = Product::getallItems();
        return response()->view('sitemap.items', [
            'items' => $items,
        ])->header('Content-Type', 'text/xml');
    }

    public function category()
    {
        $category = Category::getcategoryData();
        return response()->view('sitemap.category', [
            'category' => $category,
        ])->header('Content-Type', 'text/xml');
    }

    public function subcategory()
    {
        $subcategory = SubCategory::getsubcategoryData();
        return response()->view('sitemap.subcategory', [
            'subcategory' => $subcategory,
        ])->header('Content-Type', 'text/xml');
    }

    public function pages()
    {
        $pages = Pages::getpageViews();
        return response()->view('sitemap.pages', [
            'pages' => $pages,
        ])->header('Content-Type', 'text/xml');
    }
	
	 public function blog()
    {
        $blog = Blog::allpostData();
        return response()->view('sitemap.blog', [
            'blog' => $blog,
        ])->header('Content-Type', 'text/xml');
    }
	
	
	
}
