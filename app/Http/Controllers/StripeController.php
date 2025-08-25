<?php

namespace DownGrade\Http\Controllers;

use Illuminate\Http\Request;
use DownGrade\Models\Members;
use DownGrade\Models\Settings;
use DownGrade\Models\Items;
use DownGrade\Models\EmailTemplate;
use Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Auth;
use Illuminate\Validation\Rule;
use URL;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use Redirect;
use Storage;
use Cache;
use Illuminate\Support\Facades\DB;
use Session;
use AmrShawky\LaravelCurrency\Facade\Currency;
use GuzzleHttp\Client;
use Carbon\Carbon;
use PDF;
use Stripe;

class StripeController extends Controller
{
    
	
	
	
	public function stripe_index()
	{
	  
	 return view('stripe');

    }

    
	
	public function afterPayment()
    {
        echo 'Payment Has been Received';
    }

	
	public function stripe_subscription_index()
	{
	  
	 return view('stripe-subscription');

    }
}
