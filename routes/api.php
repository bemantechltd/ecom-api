<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/health', 'HealthCheckController@check')->name('health.check');

// Public routes should be at the top, before any middleware groups
Route::get('/promotional-banners/load', 'Api\PromotionalBannerInfoController@load')
    ->name('promotional.banners.load');

Route::group([
    // 'middleware' => 'auth:api'
    ] , function() {
    Route::prefix('site-basic-config')->group(function(){
        Route::get('/' , 'Api\BasicConfigController@get');
        Route::post('/' , 'Api\BasicConfigController@store');
    });
    
    Route::prefix('logo-info')->group(function(){
        Route::get('/' , 'Api\LogoInfoController@get');
        Route::post('/' , 'Api\LogoInfoController@store');
    });

    Route::prefix('auth')->group(function(){
        Route::post('login', 'Api\UserController@UserLogin');
        Route::post('social-login', 'Api\UserController@SocialUserLogin');
        Route::post('register', 'Api\UserController@UserSignup');
        Route::post('forgot-password', 'Api\UserController@ForgotPassword');
        Route::post('activate-account', 'Api\UserController@UserActivation');
        Route::post('code-generate', 'Api\UserController@AuthCodeGenerator');
    });

    Route::prefix('report-data')->group(function(){
        Route::get('/products-by-operator' , 'Api\TotalProductsByOperatorController@show');
        Route::get('/products-by-category' , 'Api\TotalProductsByCategoryController@show');
        Route::get('/products-by-company' , 'Api\TotalProductsByCompanyController@show');
        Route::get('/products-by-product-type' , 'Api\TotalProductsByProductTypeController@show');
        Route::get('/data-entry-opt-rpt','Api\DataReportController@dataEntryOperatorReport');
    });

    Route::prefix('customer-infos')->group(function(){
        Route::get('/' , 'Api\CustomerInfoController@show');
        Route::get('/edit/{id}' , 'Api\CustomerInfoController@edit');
        Route::get('/delete/{id}' , 'Api\CustomerInfoController@destroy');
        Route::post('/store' , 'Api\CustomerInfoController@store');
        Route::post('/update/{id}' , 'Api\CustomerInfoController@update');        
    });

    Route::prefix('delivery-persons')->group(function(){
        Route::get('/' , 'Api\DeliveryPersonController@show');
        Route::get('/available' , 'Api\DeliveryPersonController@getAvailableList'); 
        Route::get('/edit/{id}' , 'Api\DeliveryPersonController@edit');
        Route::get('/delete/{id}' , 'Api\DeliveryPersonController@destroy');
        Route::post('/store' , 'Api\DeliveryPersonController@store');
        Route::post('/update/{id}' , 'Api\DeliveryPersonController@update');        
    });

    Route::prefix('users')->group(function(){
        Route::get('/' , 'Api\UserController@show');
        Route::get('/edit/{id}' , 'Api\UserController@edit');
        Route::get('/delete/{id}' , 'Api\UserController@destroy');
        Route::post('/store' , 'Api\UserController@store');
        Route::get('/check/{user_id}', 'Api\UserController@checkUser');
        Route::post('/update/{id}' , 'Api\UserController@update');
        Route::post('/update-profile' , 'Api\UserController@updateProfile');
        Route::post('/change-password' , 'Api\UserController@changePassword');
        Route::post('/check-availability' , 'Api\UserController@checkAvailability');
        Route::post('/account-activate' , 'Api\UserController@accountActivate');
    });

    Route::prefix('user-role-infos')->group(function(){
        Route::get('/' , 'Api\UserRoleInfosController@show');
        Route::get('/edit/{id}' , 'Api\UserRoleInfosController@edit');
        Route::get('/delete/{id}' , 'Api\UserRoleInfosController@destroy');
        Route::post('/store' , 'Api\UserRoleInfosController@store');
        Route::post('/update/{id}' , 'Api\UserRoleInfosController@update');        
    });

    Route::prefix('categories')->group(function(){
        Route::get('/' , 'Api\CategoriesController@show');
        Route::get('/edit/{id}' , 'Api\CategoriesController@edit');
        Route::get('/delete/{id}' , 'Api\CategoriesController@destroy');
        Route::post('/store' , 'Api\CategoriesController@store');
        Route::post('/update/{id}' , 'Api\CategoriesController@update');        
    });

    Route::prefix('products')->group(function(){
        Route::get('/' , 'Api\ProductsController@show');
        Route::get('/edit/{id}' , 'Api\ProductsController@edit');
        Route::get('/delete/{id}' , 'Api\ProductsController@destroy');
        Route::post('/exist-check', 'Api\ProductsController@existCheck');
        Route::post('/store' , 'Api\ProductsController@store');
        Route::post('/update/{id}' , 'Api\ProductsController@update');
    });
    
    Route::prefix('generics')->group(function(){
        Route::get('/' , 'Api\GenericsController@show');
        Route::get('search', 'Api\GenericsController@search');
        Route::get('/edit/{id}' , 'Api\GenericsController@edit');
        Route::get('/delete/{id}' , 'Api\GenericsController@destroy');
        Route::post('/store' , 'Api\GenericsController@store');
        Route::post('/update/{id}' , 'Api\GenericsController@update');        
    });

    Route::prefix('disease')->group(function(){
        Route::get('/' , 'Api\DiseaseInfosController@show');
        Route::get('search', 'Api\DiseaseInfosController@search');
        Route::get('/edit/{id}' , 'Api\DiseaseInfosController@edit');
        Route::get('/delete/{id}' , 'Api\DiseaseInfosController@destroy');
        Route::post('/store' , 'Api\DiseaseInfosController@store');
        Route::post('/update/{id}' , 'Api\DiseaseInfosController@update');        
    });

    Route::prefix('pharma-companies')->group(function(){
        Route::get('/' , 'Api\PharmaceuticalsCompaniesController@show');
        Route::get('/edit/{id}' , 'Api\PharmaceuticalsCompaniesController@edit');
        Route::get('/delete/{id}' , 'Api\PharmaceuticalsCompaniesController@destroy');
        Route::post('/store' , 'Api\PharmaceuticalsCompaniesController@store');
        Route::post('/update/{id}' , 'Api\PharmaceuticalsCompaniesController@update');
    });
    
    Route::prefix('product-info-types')->group(function(){
        Route::get('/' , 'Api\ProductInfoTypesController@show');
        Route::get('/edit/{id}' , 'Api\ProductInfoTypesController@edit');
        Route::get('/delete/{id}' , 'Api\ProductInfoTypesController@destroy');
        Route::post('/store' , 'Api\ProductInfoTypesController@store');
        Route::post('/update/{id}' , 'Api\ProductInfoTypesController@update');        
    });

    Route::prefix('product-types')->group(function(){
        Route::get('/' , 'Api\ProductTypesController@show');
        Route::get('/edit/{id}' , 'Api\ProductTypesController@edit');
        Route::get('/delete/{id}' , 'Api\ProductTypesController@destroy');
        Route::post('/store' , 'Api\ProductTypesController@store');
        Route::post('/update/{id}' , 'Api\ProductTypesController@update');
    });

    Route::prefix('product-price-types')->group(function(){
        Route::get('/' , 'Api\ProductPriceTypesController@show');
        Route::get('/edit/{id}' , 'Api\ProductPriceTypesController@edit');
        Route::get('/delete/{id}' , 'Api\ProductPriceTypesController@destroy');
        Route::post('/store' , 'Api\ProductPriceTypesController@store');
        Route::post('/update/{id}' , 'Api\ProductPriceTypesController@update');
    });

    Route::prefix('media-galleries')->group(function(){
        Route::get('/' , 'Api\MediaGalleryController@show');
        Route::get('search', 'Api\MediaGalleryController@search');
        Route::get('/edit/{id}' , 'Api\MediaGalleryController@edit');
        Route::get('/delete/{id}' , 'Api\MediaGalleryController@destroy');
        Route::post('/store' , 'Api\MediaGalleryController@store');
        Route::post('/update/{id}' , 'Api\MediaGalleryController@update');
    });

    Route::prefix('manage-address')->group(function(){
        Route::get('/' , 'Api\ManageAddressController@show');        
        Route::get('/edit/{id}' , 'Api\ManageAddressController@edit');
        Route::get('/delete/{id}' , 'Api\ManageAddressController@destroy');
        Route::post('/store' , 'Api\ManageAddressController@store');
        Route::post('/update/{id}' , 'Api\ManageAddressController@update');
        Route::get('/default', 'Api\ManageAddressController@defaultAddressShow');
        Route::post('/default-address-update/{id}', 'Api\ManageAddressController@defaultAddressUpdate');        
    });
    
    Route::prefix('product-return-reason')->group(function(){
        Route::get('/' , 'Api\ProductReturnReasonController@show');
        Route::get('search', 'Api\ProductReturnReasonController@search');
        Route::get('/edit/{id}' , 'Api\ProductReturnReasonController@edit');
        Route::get('/delete/{id}' , 'Api\ProductReturnReasonController@destroy');
        Route::post('/store' , 'Api\ProductReturnReasonController@store');
        Route::post('/update/{id}' , 'Api\ProductReturnReasonController@update');        
    });
    
    Route::prefix('product-return-request')->group(function(){
        Route::get('/' , 'Api\ProductReturnRequestInfosController@show');
        Route::get('/my-status' , 'Api\ProductReturnRequestInfosController@myStatus');
        Route::get('search', 'Api\ProductReturnRequestInfosController@search');
        Route::get('/edit/{id}' , 'Api\ProductReturnRequestInfosController@edit');
        Route::get('/delete/{id}' , 'Api\ProductReturnRequestInfosController@destroy');
        Route::post('/store' , 'Api\ProductReturnRequestInfosController@store');
        Route::post('/update/{id}' , 'Api\ProductReturnRequestInfosController@update');
    });
    
    Route::prefix('my-orders')->group(function(){
        Route::get('/' , 'Api\OrderInfoController@showMyOrders');
        Route::get('/live/timeline-status' , 'Api\OrderInfoController@liveOrderTimelineStatus');
        Route::post('/store' , 'Api\OrderInfoController@storeMyOrder');
    });

    Route::prefix('my-reviews')->group(function(){
        Route::get('/' , 'Api\OrderInfoController@getReviews');
    });

    Route::prefix('manage-orders')->group(function(){
        Route::get('/' , 'Api\OrderInfoController@getOrders');
        Route::post('/update' , 'Api\OrderInfoController@updateOrder');
    });

    Route::prefix('my-prescriptions')->group(function(){
        Route::get('/' , 'Api\PrescriptionInfosController@showMyPrescriptions');
        Route::get('/edit/{id}' , 'Api\PrescriptionInfosController@edit');
        Route::get('/delete/{id}' , 'Api\PrescriptionInfosController@destroy');
        Route::post('/store' , 'Api\PrescriptionInfosController@storeMyPrescription');
        Route::post('/update/{id}' , 'Api\PrescriptionInfosController@update');
    });

    Route::prefix('order-infos')->group(function(){
        Route::get('/' , 'Api\OrderInfoController@show');        
        Route::get('/edit/{id}' , 'Api\OrderInfoController@edit');
        Route::get('/delete/{id}' , 'Api\OrderInfoController@destroy');
        Route::post('/store' , 'Api\OrderInfoController@store');
        Route::post('/cancel' , 'Api\OrderInfoController@cancelOrder');
        Route::post('/update/{id}' , 'Api\OrderInfoController@update');
    });

    Route::prefix('order-delivery-person-infos')->group(function(){
        Route::get('/' , 'Api\OrderDeliveryPersonInfoController@show');        
        Route::get('/edit/{id}' , 'Api\OrderDeliveryPersonInfoController@edit');
        Route::get('/delete/{id}' , 'Api\OrderDeliveryPersonInfoController@destroy');
        Route::post('/store' , 'Api\OrderDeliveryPersonInfoController@store');        
        Route::post('/update/{id}' , 'Api\OrderDeliveryPersonInfoController@update');
        Route::get('/review/pending' , 'Api\OrderDeliveryPersonInfoController@reviewPending');
        Route::post('/review/store', 'Api\OrderDeliveryPersonInfoController@reviewStore');
    });

    Route::prefix('tags')->group(function(){
        Route::get('/' , 'Api\TagsController@show');
        Route::get('search', 'Api\TagsController@search');
        Route::get('/edit/{id}' , 'Api\TagsController@edit');
        Route::get('/delete/{id}' , 'Api\TagsController@destroy');
        Route::post('/store' , 'Api\TagsController@store');
        Route::post('/update/{id}' , 'Api\TagsController@update');
    });

    Route::prefix('regions')->group(function(){
        Route::get('/' , 'Api\RegionController@show');
        Route::get('search', 'Api\RegionController@search');
        Route::get('/edit/{id}' , 'Api\RegionController@edit');
        Route::get('/delete/{id}' , 'Api\RegionController@destroy');
        Route::post('/store' , 'Api\RegionController@store');
        Route::post('/update/{id}' , 'Api\RegionController@update');
    });

    Route::prefix('cities')->group(function(){
        Route::get('/' , 'Api\CityController@show');
        Route::get('search', 'Api\CityController@search');
        Route::get('/edit/{id}' , 'Api\CityController@edit');
        Route::get('/delete/{id}' , 'Api\CityController@destroy');
        Route::post('/store' , 'Api\CityController@store');
        Route::post('/update/{id}' , 'Api\CityController@update');
    });

    Route::prefix('areas')->group(function(){
        Route::get('/' , 'Api\AreaController@show');
        Route::get('search', 'Api\AreaController@search');
        Route::get('/edit/{id}' , 'Api\AreaController@edit');
        Route::get('/delete/{id}' , 'Api\AreaController@destroy');
        Route::post('/store' , 'Api\AreaController@store');
        Route::post('/update/{id}' , 'Api\AreaController@update');
    });

    Route::prefix('static-page-infos')->group(function(){
        Route::get('/' , 'Api\StaticPageInfoController@show');
        Route::get('search', 'Api\StaticPageInfoController@search');
        Route::get('/edit/{id}' , 'Api\StaticPageInfoController@edit');
        Route::get('/delete/{id}' , 'Api\StaticPageInfoController@destroy');
        Route::post('/store' , 'Api\StaticPageInfoController@store');
        Route::post('/update/{id}' , 'Api\StaticPageInfoController@update');
    });

    Route::prefix('promotional-banners')->group(function(){
        Route::get('/' , 'Api\PromotionalBannerInfoController@show');
        Route::get('search', 'Api\PromotionalBannerInfoController@search');
        Route::get('/edit/{id}' , 'Api\PromotionalBannerInfoController@edit');
        Route::get('/delete/{id}' , 'Api\PromotionalBannerInfoController@destroy');
        Route::post('/store' , 'Api\PromotionalBannerInfoController@store');
        Route::post('/update/{id}' , 'Api\PromotionalBannerInfoController@update');
    });

    Route::prefix('data-report')->group(function(){
        Route::get('/my-order-history' , 'Api\DataReportController@myOrderHistory');
        Route::get('/delivery-man-order-history', 'Api\DataReportController@DeliveryManOrderHistory');
    });
});
