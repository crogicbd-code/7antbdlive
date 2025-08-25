<?php



/* admin panel */


    Route::group(['middleware' => ['is_admin', 'XSS', 'HtmlMinifier']], function () {
    Route::get('/admin', 'Admin\AdminController@admin')->middleware('cacheable:5');
	
	
	/* customer */
	Route::get('/admin/customer', 'Admin\MembersController@customer')->middleware('cacheable:5');
	Route::get('/admin/add-customer', 'Admin\MembersController@add_customer')->name('admin.add-customer');
	Route::post('/admin/add-customer', 'Admin\MembersController@save_customer');
	Route::get('/admin/customer/{token}', 'Admin\MembersController@delete_customer');
	Route::get('/admin/edit-customer/{token}', 'Admin\MembersController@edit_customer')->name('admin.edit-customer');
	Route::post('/admin/edit-customer', ['as' => 'admin.edit-customer','uses'=>'Admin\MembersController@update_customer']);
	Route::get('/admin/subscription-payment-details/{token}', 'Admin\MembersController@subscription_payment_details');
	Route::get('/admin/customer/{token}/{subcr_id}', 'Admin\MembersController@upgrade_customer');
	Route::get('/admin/license', 'Admin\AdminController@license_check')->middleware('cacheable:5');
	Route::post('/admin/license', ['as' => 'admin.license','uses'=>'Admin\AdminController@verify_purchase']);
	Route::post('/admin/customer', ['as' => 'admin.customer','uses'=>'Admin\MembersController@all_delete_customer']);
	/* customer */
	
	
	/* administrator */
	Route::get('/admin/administrator', 'Admin\MembersController@administrator')->middleware('cacheable:5');
	Route::get('/admin/add-administrator', 'Admin\MembersController@add_administrator')->name('admin.add-administrator');
	Route::post('/admin/add-administrator', 'Admin\MembersController@save_administrator');
	Route::get('/admin/administrator/{token}', 'Admin\MembersController@delete_administrator');
	Route::get('/admin/edit-administrator/{token}', 'Admin\MembersController@edit_administrator')->name('admin.edit-administrator');
	Route::post('/admin/edit-administrator', ['as' => 'admin.edit-administrator','uses'=>'Admin\MembersController@update_administrator']);
	Route::post('/admin/administrator', ['as' => 'admin.administrator','uses'=>'Admin\MembersController@all_delete_administrator']);
	/* administrator */
	
	/* country */
	Route::get('/admin/country-settings', 'Admin\SettingsController@country_settings')->middleware('cacheable:5');
	Route::get('/admin/add-country', 'Admin\SettingsController@add_country')->name('admin.add-country');
	Route::post('/admin/add-country', 'Admin\SettingsController@save_country');
	Route::get('/admin/country-settings/{cid}', 'Admin\SettingsController@delete_country');
	Route::get('/admin/edit-country/{cid}', 'Admin\SettingsController@edit_country')->name('admin.edit-country');
	Route::post('/admin/edit-country', ['as' => 'admin.edit-country','uses'=>'Admin\SettingsController@update_country']);
	Route::post('/admin/country-settings', ['as' => 'admin.country-settings','uses'=>'Admin\SettingsController@all_delete_country']);
    /* country */

		
	/* edit profile */
	
	Route::get('/admin/edit-profile', 'Admin\MembersController@edit_profile');
	Route::post('/admin/edit-profile', ['as' => 'admin.edit-profile','uses'=>'Admin\MembersController@update_profile']);
	/* edit profile */
	
	
	/* general settings */
	Route::post('/admin/vat', ['as' => 'admin.vat','uses'=>'Admin\SettingsController@vat_update']);
	Route::get('/admin/general-settings', 'Admin\SettingsController@general_settings')->middleware('cacheable:5');
	Route::post('/admin/general-settings', ['as' => 'admin.general-settings','uses'=>'Admin\SettingsController@update_general_settings']);
		
	/* general settings */
	
	
	/* media settings */
	
	Route::get('/admin/media-settings', 'Admin\SettingsController@media_settings')->middleware('cacheable:5');
	Route::post('/admin/media-settings', ['as' => 'admin.media-settings','uses'=>'Admin\SettingsController@update_media_settings']);
		
	/* media settings */
	
	
	/* email settings */
	
	Route::get('/admin/email-settings', 'Admin\SettingsController@email_settings')->middleware('cacheable:5');
	Route::post('/admin/email-settings', ['as' => 'admin.email-settings','uses'=>'Admin\SettingsController@update_email_settings']);
	
	/* email settings */
	
	/* currency settings */
	Route::get('/admin/currency-settings', 'Admin\SettingsController@currency_settings')->middleware('cacheable:5');
	Route::post('/admin/currency-settings', ['as' => 'admin.currency-settings','uses'=>'Admin\SettingsController@update_currency_settings']);
	/* currency settings */
	
	
	/* preferred settings */
	Route::get('/admin/preferred-settings', 'Admin\SettingsController@preferred_settings')->middleware('cacheable:5');
	Route::post('/admin/preferred-settings', ['as' => 'admin.preferred-settings','uses'=>'Admin\SettingsController@update_preferred_settings']);
	/* preferred settings */
	
	
	/* limitation settings */
	Route::get('/admin/limitation-settings', 'Admin\SettingsController@limitation_settings')->middleware('cacheable:5');
	Route::post('/admin/limitation-settings', ['as' => 'admin.limitation-settings','uses'=>'Admin\SettingsController@update_limitation_settings']);
	/* limitation settings */
	
	
	/* social settings */
	
	Route::get('/admin/social-settings', 'Admin\SettingsController@social_settings')->middleware('cacheable:5');
	Route::post('/admin/social-settings', ['as' => 'admin.social-settings','uses'=>'Admin\SettingsController@update_social_settings']);
	
	/* social settings */
	
	
	/* color settings */
	
	Route::get('/admin/color-settings', 'Admin\SettingsController@color_settings')->middleware('cacheable:5');
	Route::post('/admin/color-settings', ['as' => 'admin.color-settings','uses'=>'Admin\SettingsController@update_color_settings']);
	
	/* color settings */
	
	/* pwa settings */
	Route::get('/admin/pwa-settings', 'Admin\SettingsController@pwa_settings');
	Route::post('/admin/pwa-settings', ['as' => 'admin.pwa-settings','uses'=>'Admin\SettingsController@update_pwa_settings']);
	/* pwa settings */
	
	
	/* payment settings */
	
	Route::get('/admin/payment-settings', 'Admin\SettingsController@payment_settings')->middleware('cacheable:5');
	Route::post('/admin/payment-settings', ['as' => 'admin.payment-settings','uses'=>'Admin\SettingsController@update_payment_settings']);
	
	/* payment settings */
	
	
	
	/* demo mode */
	Route::post('/admin/demo-mode', ['as' => 'admin.demo-mode','uses'=>'Admin\SettingsController@update_demo_mode']);
	Route::get('/admin/demo-mode', 'Admin\SettingsController@demo_mode');
	/* demo mode */
	
	/* subscription */
	
	Route::get('/admin/subscription', 'Admin\SubscriptionController@subscription');
	Route::get('/admin/add-subscription', 'Admin\SubscriptionController@add_subscription')->name('admin.add-subscription');
	Route::post('/admin/add-subscription', 'Admin\SubscriptionController@save_subscription');
	Route::get('/admin/subscription/{subscr_id}', 'Admin\SubscriptionController@delete_subscription');
	Route::get('/admin/edit-subscription/{subscr_id}', 'Admin\SubscriptionController@edit_subscription')->name('admin.edit-subscription');
	Route::post('/admin/edit-subscription', ['as' => 'admin.edit-subscription','uses'=>'Admin\SubscriptionController@update_subscription']);
	Route::post('/admin/free-subscription', ['as' => 'admin.free-subscription','uses'=>'Admin\SubscriptionController@save_free_subscription']);
	Route::post('/admin/subscription', ['as' => 'admin.subscription','uses'=>'Admin\SubscriptionController@subscription_content']);
	Route::post('/admin/subscription', ['as' => 'admin.subscription','uses'=>'Admin\SubscriptionController@all_delete_subscription']);
	/* subscription */
	
	
	/* category */
	
	Route::get('/admin/category', 'Admin\CategoryController@category')->middleware('cacheable:5');
	Route::get('/admin/add-category', 'Admin\CategoryController@add_category')->name('admin.add-category');
	Route::post('/admin/add-category', 'Admin\CategoryController@save_category');
	Route::get('/admin/category/{cat_id}', 'Admin\CategoryController@delete_category');
	Route::get('/admin/edit-category/{cat_id}', 'Admin\CategoryController@edit_category')->name('admin.edit-category');
	Route::post('/admin/edit-category', ['as' => 'admin.edit-category','uses'=>'Admin\CategoryController@update_category']);
	Route::post('/admin/category', ['as' => 'admin.category','uses'=>'Admin\CategoryController@all_delete_category']);
	/* category */
	
	/* subcategory */
	
	Route::get('/admin/sub-category', 'Admin\CategoryController@subcategory')->middleware('cacheable:5');
	Route::get('/admin/add-subcategory', 'Admin\CategoryController@add_subcategory')->name('admin.add-subcategory');
	Route::post('/admin/add-subcategory', 'Admin\CategoryController@save_subcategory');
	Route::get('/admin/sub-category/{subcat_id}', 'Admin\CategoryController@delete_subcategory');
	Route::get('/admin/edit-subcategory/{cat_id}', 'Admin\CategoryController@edit_subcategory')->name('admin.edit-subcategory');
	Route::post('/admin/edit-subcategory', ['as' => 'admin.edit-subcategory','uses'=>'Admin\CategoryController@update_subcategory']);
	Route::post('/admin/sub-category', ['as' => 'admin.sub-category','uses'=>'Admin\CategoryController@all_delete_subcategory']);
	/* subcategory */
	
	/* products */
	
	Route::get('/admin/products', 'Admin\ProductController@view_products')->middleware('cacheable:5');
	Route::post('/admin/products', ['as' => 'admin.products','uses'=>'Admin\ProductController@search_products']);
	Route::get('/admin/add-product', 'Admin\ProductController@add_product')->name('admin.add-product');
	Route::post('/admin/add-product', 'Admin\ProductController@save_product');
	Route::get('/admin/products/{product_token}', 'Admin\ProductController@delete_product');
	Route::get('/admin/edit-product/{product_token}', 'Admin\ProductController@edit_product')->name('admin.edit-product');
	Route::post('/admin/edit-product', ['as' => 'admin.edit-product','uses'=>'Admin\ProductController@update_product']);
	Route::get('/admin/edit-product/{dropimg}/{token}', 'Admin\ProductController@drop_image_product');
	Route::get('/admin/download/{token}', 'Admin\ProductController@file_download');
	
	Route::post('/admin/fileupload/','Admin\ProductController@fileupload')->name('fileupload');
	Route::post('/admin/products', ['as' => 'admin.products','uses'=>'Admin\ProductController@all_delete_product']);
	/* products */
	
	
	/* product import & export */
	Route::get('/admin/products-import-export', 'Admin\ImportExportController@view_products_import_export')->middleware('cacheable:5');
	Route::post('/admin/products-import-export', ['as' => 'admin.products-import-export','uses'=>'Admin\ImportExportController@products_import']);
	Route::get('/admin/products-import-export/{type}', 'Admin\ImportExportController@download_products_export');
	/* product import & export */
	
	
	/* attributes */
    Route::get('/admin/attributes', 'Admin\AttributeController@attribute')->middleware('cacheable:5');
    Route::get('/admin/add-attribute', 'Admin\AttributeController@add_attribute')->name('admin.add-attribute');
    Route::post('/admin/add-attribute', 'Admin\AttributeController@save_attribute');
    Route::get('/admin/attributes/{attr_id}', 'Admin\AttributeController@delete_attribute');
    Route::get('/admin/edit-attribute/{attr_id}', 'Admin\AttributeController@edit_attribute')->name('admin.edit-attribute');
    Route::post('/admin/edit-attribute', ['as' => 'admin.edit-attribute','uses'=>'Admin\AttributeController@update_attribute']);
	Route::post('/admin/attributes', ['as' => 'admin.attributes','uses'=>'Admin\AttributeController@all_delete_attribute']);
    /* attributes */
	
	
	/* orders */
	
	Route::get('/admin/orders', 'Admin\ProductController@view_orders')->middleware('cacheable:5');
	Route::post('/admin/orders', ['as' => 'admin.orders','uses'=>'Admin\ProductController@search_orders']);
	Route::get('/admin/order-details/{token}', 'Admin\ProductController@view_order_single');
	Route::get('/admin/more-info/{token}', 'Admin\ProductController@view_more_info');
	Route::get('/admin/orders/{delete}/{purchase_id}', 'Admin\ProductController@view_orders_delete');
	Route::get('/admin/orders/{ord_id}', 'Admin\ProductController@complete_orders');
	Route::post('/admin/orders', ['as' => 'admin.orders','uses'=>'Admin\ProductController@all_delete_orders']);
	/* orders */
	
	
	/* rating */
	
	/*Route::get('/admin/rating', 'Admin\ProductController@view_rating')->middleware('cacheable:5');*/
	Route::get('/admin/dropreviews/{rating_id}', 'Admin\ProductController@rating_delete');
	
	
	Route::get('/admin/reviews/{product_token}', 'Admin\ProductController@selected_rating')->middleware('cacheable:5');
	Route::get('/admin/add-reviews/{product_token}', 'Admin\ProductController@add_rating')->middleware('cacheable:5');
	Route::post('/admin/add-reviews', ['as' => 'admin.add-reviews','uses'=>'Admin\ProductController@save_reviews']);
	Route::get('/admin/edit-reviews/{rating_id}', 'Admin\ProductController@edit_rating');
	Route::post('/admin/edit-reviews', ['as' => 'admin.edit-reviews','uses'=>'Admin\ProductController@update_rating']);
	/* rating */
	
	
	/* report */
	Route::get('/admin/reports', 'Admin\ProductController@view_report')->middleware('cacheable:5');
	Route::get('/admin/reports/{report_id}', 'Admin\ProductController@report_delete');
	
	/* report */
	
	
	/* refund request */
	
	Route::get('/admin/refund', 'Admin\ProductController@view_refund')->middleware('cacheable:5');
	Route::get('/admin/refund/{ord_id}/{refund_id}/{user_type}', 'Admin\ProductController@view_payment_refund');
	Route::get('/admin/refund/{refund_id}', 'Admin\ProductController@delete_refund');
	Route::post('/admin/refund', ['as' => 'admin.refund','uses'=>'Admin\ProductController@all_delete_refund']);
	/* refund request */
	
	
	/* package includes */
	
	Route::get('/admin/package-includes', 'Admin\ProductController@view_package_includes')->middleware('cacheable:5');
	Route::get('/admin/add-package-includes', 'Admin\ProductController@add_package_includes')->name('admin.add-package-includes');
	Route::post('/admin/add-package-includes', 'Admin\ProductController@save_package_includes');
	Route::get('/admin/package-includes/{package_id}', 'Admin\ProductController@delete_package_includes');
	Route::get('/admin/edit-package-includes/{package_id}', 'Admin\ProductController@edit_package_includes')->name('admin.edit-package-includes');
	Route::post('/admin/edit-package-includes', ['as' => 'admin.edit-package-includes','uses'=>'Admin\ProductController@update_package_includes']);
	
	/* package includes */
	
	
	
	/* Compatible Browsers */
	
	Route::get('/admin/compatible-browsers', 'Admin\ProductController@view_compatible_browsers')->middleware('cacheable:5');
	Route::get('/admin/add-compatible-browsers', 'Admin\ProductController@add_compatible_browsers')->name('admin.add-compatible-browsers');
	Route::post('/admin/add-compatible-browsers', 'Admin\ProductController@save_compatible_browsers');
	Route::get('/admin/compatible-browsers/{browser_id}', 'Admin\ProductController@delete_compatible_browsers');
	Route::get('/admin/edit-compatible-browsers/{browser_id}', 'Admin\ProductController@edit_compatible_browsers')->name('admin.edit-compatible-browsers');
	Route::post('/admin/edit-compatible-browsers', ['as' => 'admin.edit-compatible-browsers','uses'=>'Admin\ProductController@update_compatible_browsers']);
	
	/* Compatible Browsers */
	
	
	/* blog */
	
	Route::get('/admin/blog-category', 'Admin\BlogController@blog_category')->middleware('cacheable:5');
	Route::get('/admin/add-blog-category', 'Admin\BlogController@add_blog_category')->name('admin.add-blog-category');
	Route::post('/admin/add-blog-category', 'Admin\BlogController@save_blog_category');
	Route::get('/admin/blog-category/{blog_cat_id}', 'Admin\BlogController@delete_blog_category');
	Route::get('/admin/edit-blog-category/{blog_cat_id}', 'Admin\BlogController@edit_blog_category')->name('admin.edit-blog-category');
	Route::post('/admin/edit-blog-category', ['as' => 'admin.edit-blog-category','uses'=>'Admin\BlogController@update_blog_category']);
	Route::post('/admin/blog-category', ['as' => 'admin.blog-category','uses'=>'Admin\BlogController@all_delete_blog_category']);
	/* blog */
	
	
	
	/* post */
	
	Route::get('/admin/post', 'Admin\BlogController@posts')->middleware('cacheable:5');
	Route::get('/admin/add-post', 'Admin\BlogController@add_post')->name('admin.add-post');
	Route::post('/admin/add-post', 'Admin\BlogController@save_post');
	Route::get('/admin/post/{post_id}', 'Admin\BlogController@delete_post');
	Route::get('/admin/edit-post/{post_id}', 'Admin\BlogController@edit_post')->name('admin.edit-post');
	Route::post('/admin/edit-post', ['as' => 'admin.edit-post','uses'=>'Admin\BlogController@update_post']);
	Route::post('/admin/post', ['as' => 'admin.post','uses'=>'Admin\BlogController@all_delete_post']);
	
	/* post */
	
	
	/* comment */
	Route::get('/admin/comment/{post_id}', 'Admin\BlogController@comments');
	Route::get('/admin/comment/{delete}/{comment_id}', 'Admin\BlogController@delete_comment');
	Route::get('/admin/comment/update-status/{status}/{comment_id}', 'Admin\BlogController@comment_status');
	/* comment */
	
	
	
	
	/* pages */
	
	Route::get('/admin/pages', 'Admin\PagesController@pages')->middleware('cacheable:5');
	Route::get('/admin/add-page', 'Admin\PagesController@add_page')->name('admin.add-page');
	Route::post('/admin/add-page', 'Admin\PagesController@save_page');
	Route::get('/admin/pages/{page_id}', 'Admin\PagesController@delete_pages');
	Route::get('/admin/edit-page/{page_id}', 'Admin\PagesController@edit_page')->name('admin.edit-page');
	Route::post('/admin/edit-page', ['as' => 'admin.edit-page','uses'=>'Admin\PagesController@update_page']);
	Route::post('/admin/pages', ['as' => 'admin.pages','uses'=>'Admin\PagesController@all_delete_pages']);
	/* pages */
	
	
	/* Voucher Code */
	Route::get('/admin/voucher-code', 'Admin\VoucherController@voucher_code')->middleware('cacheable:5');
	Route::get('/admin/add-voucher-code', 'Admin\VoucherController@add_voucher_code')->name('admin.add-voucher-code');
	Route::post('/admin/add-voucher-code', 'Admin\VoucherController@save_voucher_code');
	Route::get('/admin/voucher-info/{vid}', 'Admin\VoucherController@single_voucher_code');
	Route::get('/admin/voucher-code/{vid}', 'Admin\VoucherController@delete_voucher_code');
	Route::get('/admin/purchases', 'Admin\VoucherController@voucher_purchases');
	Route::get('/admin/purchases/{ord_id}', 'Admin\VoucherController@complete_orders');
	Route::get('/admin/purchases/{delete}/{ord_id}', 'Admin\VoucherController@delete_orders');
	Route::get('/admin/export-voucher-code/{type}', 'Admin\VoucherController@download_export');
	Route::post('/admin/voucher-code', ['as' => 'admin.voucher-code','uses'=>'Admin\VoucherController@drop_voucher_code']);
	/* Voucher Code */
	
	
	
	/* coupon */
	Route::get('/admin/coupons', 'Admin\CouponController@view_coupon')->middleware('cacheable:5');
	Route::get('/admin/add-coupon', 'Admin\CouponController@add_coupon')->name('admin.add-coupon');
	Route::post('/admin/add-coupon', 'Admin\CouponController@save_coupon');
	Route::get('/admin/coupons/{coupon_id}', 'Admin\CouponController@delete_coupon');
	Route::get('/admin/edit-coupon/{coupon_id}', 'Admin\CouponController@edit_coupon')->name('admin.edit-coupon');
	Route::post('/admin/edit-coupon', ['as' => 'admin.edit-coupon','uses'=>'Admin\CouponController@update_coupon']);
	Route::post('/admin/coupons', ['as' => 'admin.coupons','uses'=>'Admin\CouponController@all_delete_coupons']);
	/* coupon */
	
	
	/* withdrawal */
	
	Route::get('/admin/withdrawal', 'Admin\ProductController@view_withdrawal')->middleware('cacheable:5');
	Route::get('/admin/withdrawal/{wd_id}/{wd_user_id}', 'Admin\ProductController@view_withdrawal_update');
	Route::get('/admin/withdrawal/{wd_id}', 'Admin\ProductController@delete_withdrawal');
	Route::post('/admin/withdrawal', ['as' => 'admin.withdrawal','uses'=>'Admin\ProductController@all_delete_withdrawal']);
	/* withdrawal */
	
			
	/* contact */
	Route::get('/admin/contact', 'Admin\CommonController@view_contact')->middleware('cacheable:5');
	Route::get('/admin/contact/{id}', 'Admin\CommonController@view_contact_delete');
	Route::get('/admin/add-contact', 'Admin\CommonController@view_add_contact');
	Route::post('/admin/add-contact', ['as' => 'admin.add-contact','uses'=>'Admin\CommonController@update_contact']);
	Route::post('/admin/contact', ['as' => 'admin.contact','uses'=>'Admin\CommonController@all_delete_contact']);
	/* contact */
	
	
	/* newsletter */
	Route::get('/admin/newsletter', 'Admin\CommonController@view_newsletter')->middleware('cacheable:5');
	Route::get('/admin/newsletter/{id}', 'Admin\CommonController@view_newsletter_delete');
	Route::get('/admin/send-updates', 'Admin\CommonController@view_send_updates');
	Route::post('/admin/send-updates', ['as' => 'admin.send-updates','uses'=>'Admin\CommonController@send_updates']);
	Route::post('/admin/newsletter', ['as' => 'admin.newsletter','uses'=>'Admin\CommonController@all_delete_newsletter']);
	/* newsletter */
	
	/* ads */
	Route::get('/admin/ads', 'Admin\SettingsController@view_ads')->middleware('cacheable:5');
	Route::post('/admin/ads', ['as' => 'admin.ads','uses'=>'Admin\SettingsController@update_ads']);
	/* ads */
	
	
	/* email template */
	Route::get('/admin/email-template', 'Admin\EmailController@email_template')->middleware('cacheable:5');
	Route::get('/admin/add-email-template', 'Admin\EmailController@add_email_template')->name('admin.add-email-template');
	Route::post('/admin/add-email-template', 'Admin\EmailController@save_email_template');
	Route::get('/admin/edit-email-template/{et_id}', 'Admin\EmailController@edit_email_template')->name('admin.edit-email-template');
	Route::post('/admin/edit-email-template', ['as' => 'admin.edit-email-template','uses'=>'Admin\EmailController@update_email_template']);
	/* email template */
	
	
	/* Website Maintenance */
	Route::get('/admin/website-maintenance', 'Admin\SettingsController@website_maintenance')->middleware('cacheable:5');
	Route::post('/admin/website-maintenance', ['as' => 'admin.website-maintenance','uses'=>'Admin\SettingsController@update_maintenance']);
	/* Website Maintenance */
	
	
	/* clear cache */
	Route::get('/admin/clear-cache', 'Admin\CommonController@delete_cache');
	
	/* clear cache */
	
	Route::get('/admin/backup', 'Admin\BackupController@index');
	Route::get('/admin/backup/create', 'Admin\BackupController@create');
	Route::get('/admin/backup/download/{file_name}', 'Admin\BackupController@download');
	Route::get('/admin/backup/delete/{file_name}', 'Admin\BackupController@delete');
	Route::post('/admin/backup', ['as' => 'admin.backup','uses'=>'Admin\BackupController@backup']);
	
	
	
});


/* admin panel */

Route::group(['middleware' => ['XSS', 'HtmlMinifier']], function () {

Route::get('/language/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()->put('locale', $locale);

    return redirect()->back();
});
/* 

Route::get('lang/{lang}', function ($locale){
    Session::put('lang', $locale);
	App::setLocale($locale);
    return redirect()->back();
});


 */
/* preview */
Route::get('/preview/{slug}', 'CommonController@view_preview');
/* preview */ 
Route::get('/translate/{translate}', 'CommonController@cookie_translate');

Route::get('/', 'CommonController@view_index')->middleware('cacheable:5');
Route::get('/index', 'CommonController@view_index')->middleware('cacheable:5');
Route::post('/index', ['as' => 'index','uses'=>'CommonController@update_video']);
Route::get('/download/{url}/{title}/{mime}/{ext}/{size}', 'CommonController@view_download');

Route::get('login/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('login/{provider}/callback', 'Auth\LoginController@handleProviderCallback');
Route::get('searchajax',array('as'=>'searchajax','uses'=>'CommonController@autoComplete'));
Auth::routes();

Route::get('/logout', 'Admin\CommonController@logout');



/* email verification */

Route::get('/user-verify/{user_token}', 'CommonController@user_verify');

/* email verification */


/* my profile */

Route::get('/my-profile', 'ProfileController@view_myprofile')->middleware('cacheable:5');
Route::post('/my-profile', ['as' => 'my-profile','uses'=>'ProfileController@update_myprofile']);

/* my profile */


/* my referral */
Route::get('/my-referral', 'ProfileController@view_myreferral')->middleware('cacheable:5');
/* my referral */


/* blog */
Route::get('/blog', 'BlogController@view_blog')->middleware('cacheable:5');
Route::get('/single/{slug}', 'BlogController@view_single');
Route::get('/blog/{category}/{id}/{slug}', 'BlogController@view_category_blog');
Route::post('/single', ['as' => 'single','uses'=>'BlogController@insert_comment']);
Route::get('/blog/{tag}', 'BlogController@view_tags');
/* blog */



/* withdrawal request */
Route::get('/withdrawal', 'ProductController@view_withdrawal')->middleware('cacheable:5');
Route::post('/withdrawal', ['as' => 'withdrawal','uses'=>'ProductController@withdrawal_request']);

/* withdrawal request */



/* forgot */

Route::get('/forgot', 'CommonController@view_forgot')->middleware('cacheable:5');
Route::post('/forgot', ['as' => 'forgot','uses'=>'CommonController@update_forgot']);
Route::get('/reset/{user_token}', 'CommonController@view_reset');
Route::post('/reset', ['as' => 'reset','uses'=>'CommonController@update_reset']);

/* forgot */


/* homepage pages */
Route::get('/free-items', 'CommonController@view_free_items')->middleware('cacheable:5');
Route::get('/featured-items', 'CommonController@view_featured_items')->middleware('cacheable:5');
Route::get('/sale', 'CommonController@view_sale_items')->middleware('cacheable:5');
Route::get('/popular-items', 'CommonController@view_popular_items')->middleware('cacheable:5');
Route::get('/new-releases', 'CommonController@view_new_items')->middleware('cacheable:5');
Route::get('/subscriber-downloads', 'CommonController@view_subscriber_items')->middleware('cacheable:5');
/* homepage pages */



/* verify */
Route::get('/verify', 'CommonController@view_verify');
Route::post('/verify', ['as' => 'verify','uses'=>'CommonController@update_verify']);
/* verify */


/* success */
/*Route::get('/success/{ord_token}', 'ProfileController@paypal_success');*/
/*Route::get('/cancel', 'ProfileController@payment_cancel');*/

/* success */


/* contact */

Route::get('/contact', 'CommonController@view_contact')->middleware('cacheable:5');
Route::post('/contact', ['as' => 'contact','uses'=>'CommonController@update_contact']);
/* contact */


/* item */
Route::get('/item/{slug}', 'CommonController@view_item')->middleware('cacheable:5');
Route::get('/item/{download}/{token}', 'CommonController@view_free_item');
Route::get('/item/{id}/{favorite}/{liked}', 'ProductController@view_favorite_item');
Route::post('/support', ['as' => 'support','uses'=>'ProductController@contact_support']);
Route::post('/post-comment', ['as' => 'post-comment','uses'=>'ProductController@add_post_comment']);
Route::post('/reply-post-comment', ['as' => 'reply-post-comment','uses'=>'ProductController@reply_post_comment']);
Route::get('/tag/{item}/{slug}', 'CommonController@view_tags');
Route::post('/report', ['as' => 'report','uses'=>'CommonController@issue_report']);
/* item */

/* download file */
Route::get('/download-file/{token}', 'ProductController@download_file');
/* download file */


/* purchases */
Route::get('/404', 'CommonController@not_found');
Route::get('/my-purchases', 'ProductController@view_purchases')->middleware('cacheable:5');
Route::get('/my-purchases/{token}', 'ProductController@purchases_download');
Route::get('/invoice/{product_token}/{order_id}', 'ProductController@invoice_download');
Route::post('/my-purchases', ['as' => 'my-purchases','uses'=>'ProductController@rating_purchases']);
Route::post('/refund', ['as' => 'refund','uses'=>'ProductController@refund_request']);
/* purchases */


/* shop */

Route::get('/shop', 'CommonController@view_all_items')->middleware('cacheable:5');
Route::post('/shop', ['as' => 'shop','uses'=>'CommonController@view_shop_items']);
Route::get('/shop/{type}/{slug}', 'CommonController@view_category_items');

/* shop */



/* cart */
Route::post('/cart', ['as' => 'cart','uses'=>'CommonController@view_cart']);
Route::get('/cart', 'CommonController@show_cart')->middleware('cacheable:5');
Route::get('/clear-cart', 'CommonController@clear_cart');
Route::get('/cart/{ord_id}', 'CommonController@remove_cart_item');
Route::get('/add-to-cart/{slug}', 'CommonController@add_to_cart');
Route::post('/coupon', ['as' => 'coupon','uses'=>'CommonController@view_coupon']);
Route::post('/subscription-coupon', ['as' => 'subscription-coupon','uses'=>'ProfileController@view_subscription_coupon']);
Route::get('/cart/{remove}/{coupon}', 'CommonController@remove_coupon');

/* cart */


/* checkout */
Route::get('/checkout', 'CommonController@show_checkout')->middleware('cacheable:5');
Route::post('/checkout', ['as' => 'checkout','uses'=>'CommonController@view_checkout']);
Route::post('/stripe', ['as' => 'stripe','uses'=>'StripeController@afterpayment']);
/* checkout */


/* success */
Route::get('/success/{ord_token}', 'ProductController@paypal_success');
Route::get('/cancel', 'CommonController@payment_cancel');
Route::get('/coinpayments-success/{ord_token}', 'ProductController@coinpayments_success');
Route::get('/coinbase/{ord_token}', 'ProductController@coinbase_success');
Route::get('/cashfree', 'ProductController@cashfree_success');
Route::get('/nowpayments/{ord_token}', 'ProductController@nowpayments_success');
Route::get('/checkout-stripe/{ord_token}', 'ProductController@stripe_success');
/* success */


/* coinbase webhook */
Route::post('/webhooks/coinbase-checkout', 'CommonController@coinbase_checkout');
Route::post('/webhooks/coinbase-subscription', 'ProfileController@coinbase_subscription');
/* coinbase webhook */


/* mercadopago */
Route::get('/mercadopago-success/{ord_token}', 'ProductController@mercadopago_success');
Route::get('/failure', 'CommonController@payment_failure');
Route::get('/pending', 'CommonController@payment_pending');
/* mercadopago */


/* payhere */
Route::get('/payhere-success/{ord_token}', 'ProductController@payhere_success');
/* payhere */

/* payfast */
Route::get('/payfast-success/{ord_token}', 'ProductController@payfast_success');
/* payfast */

/* flutterwave */
Route::get('/flutterwave', 'ProductController@flutterwaveCallback');
/* flutterwave */

/* coingate */


Route::get('/coingate', 'ProductController@coingate_success');
/* coingate */


/* favourites */
Route::get('/my-favourite', 'ProductController@favourites_item')->middleware('cacheable:5');
Route::get('/my-favourite/{fav_id}/{item_id}', 'ProductController@remove_favourites_item');
/* favourites */

/* paystack */
Route::post('/paystack', ['as' => 'paystack','uses'=>'ProductController@redirectToGateway']);
Route::get('/paystack', 'ProductController@handleGatewayCallback');
/* paystack */


/* razorpay */
Route::post('/razorpay', ['as' => 'razorpay','uses'=>'ProductController@razorpay_payment']);
/* razorpay */


/* newsletter */
	Route::post('/newsletter', ['as' => 'newsletter','uses'=>'CommonController@update_newsletter']);
	Route::get('/newsletter/{token}', 'CommonController@activate_newsletter');
	Route::get('/newsletter', 'CommonController@view_newsletter');
	/* newsletter */

/* subscription */

Route::get('/subscription', 'CommonController@view_subscription');
Route::get('/confirm-subscription/{id}', 'ProfileController@upgrade_subscription');
Route::post('/confirm-subscription', ['as' => 'confirm-subscription','uses'=>'ProfileController@update_subscription']);
Route::get('/subscription-success/{ord_token}', 'ProfileController@paypal_success');
Route::post('/subscription-paystack', ['as' => 'subscription-paystack','uses'=>'ProfileController@redirectToGateway']);
Route::get('/subscription-paystack', 'ProfileController@handleGatewayCallback');
Route::post('/subscription-razorpay', ['as' => 'subscription-razorpay','uses'=>'ProfileController@razorpay_payment']);
Route::get('/subscription-coingate', 'ProfileController@coingateCallback');
Route::get('/subscription-coinpayments/{ord_token}', 'ProfileController@coinpayments_success');
Route::get('/subscription-payhere/{ord_token}', 'ProfileController@payhere_success');
Route::get('/subscription-payfast/{ord_token}', 'ProfileController@payfast_success');
Route::get('/subscription-flutterwave', 'ProfileController@flutterwaveCallback');

Route::get('/remove-subscription/{id}', 'ProfileController@remove_coupon');
Route::get('/subscription-mercadopago/{ord_token}', 'ProfileController@mercadopago_success');
Route::get('/subscription-coinbase/{ord_token}', 'ProfileController@coinbase_success');
Route::get('/subscription-cashfree/', 'ProfileController@cashfree_success');
Route::get('/subscription-nowpayments/{ord_token}', 'ProfileController@nowpayments_success');
Route::get('/subscription-stripe/{ord_token}', 'ProfileController@stripe_success');
/* subscription */

/* recharge voucher */
Route::get('/redeem-voucher', 'ProductController@view_redeem_voucher')->middleware('cacheable:5');
Route::post('/add-money', ['as' => 'add-money','uses'=>'ProductController@add_money']);
/* recharge voucher */

/* updates */
Route::get('/updates', 'CommonController@view_updates')->middleware('cacheable:5');
/* updates */


/* sitemap */
Route::get('/sitemap.xml', 'SitemapController@index');
Route::get('/sitemap.xml/items', 'SitemapController@items');
Route::get('/sitemap.xml/category', 'SitemapController@category');
Route::get('/sitemap.xml/subcategory', 'SitemapController@subcategory');
Route::get('/sitemap.xml/pages', 'SitemapController@pages');
Route::get('/sitemap.xml/blog', 'SitemapController@blog');
/* sitemap */


/* pages */

Route::get('/{page_slug}', 'PageController@view_page')->middleware('cacheable:5');

/* pages */


});
