<?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
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
    Route::get('site-init-info', 'Api\BasicConfigController@loadData');
    Route::get('basic-config', 'Api\BasicConfigController@show');
    Route::get('logo-info', 'Api\LogoInfoController@show');

    /**
     * Admin login requested routes
     */
    Route::get('logout' , 'Api\UserController@Logout');
    Route::prefix('admin')->group(function(){
        Route::post('login' , 'Api\UserController@AdminLogin');
    });

    // Route::prefix('auth')->group(function(){
    //     Route::post('login', 'Api\UserController@UserLogin');
    //     Route::post('social-login', 'Api\UserController@SocialUserLogin');
    //     Route::post('register', 'Api\UserController@UserSignup');
    //     Route::post('forgot-password', 'Api\UserController@ForgotPassword');
    //     Route::post('activate-account', 'Api\UserController@UserActivation');
    //     Route::post('code-generate', 'Api\UserController@AuthCodeGenerator');
    // });

    Route::prefix('static-page-infos')->group(function(){
        Route::get('/load' , 'Api\StaticPageInfoController@load');
        Route::get('/page/{slug}' , 'Api\StaticPageInfoController@detailsStaticPage');
    });

    Route::prefix('categories')->group(function(){
        Route::get('/load' , 'Api\CategoriesController@load');
    });

    // Route::prefix('promotional-banners')->group(function(){
    //     Route::get('/load' , 'Api\PromotionalBannerInfoController@load');
    // });

    Route::prefix('pharma-companies')->group(function(){
        Route::get('/load' , 'Api\PharmaceuticalsCompaniesController@load');
    });

    Route::prefix('product-types')->group(function(){
        Route::get('/load' , 'Api\ProductTypesController@load');        
    });
    
    Route::prefix('products')->group(function(){
        Route::get('/all' , 'Api\ProductsController@getAllData');
        Route::get('/search' , 'Api\ProductsController@getDataBySearch');
        Route::get('/search/list' , 'Api\ProductsController@search');
        Route::post('/recommended' , 'Api\ProductsController@recommended');
        Route::get('/selected', 'Api\ProductsController@selected');
        Route::get('/hot' , 'Api\ProductsController@hot');
        Route::get('/related', 'Api\ProductsController@relatedData');
        Route::get('/by-category/{id}' , 'Api\ProductsController@getDataByCategory');
        Route::get('/{slug}' , 'Api\ProductsController@getDataBySlug');
        Route::get('/category/{slug}' , 'Api\ProductsController@getDataByCatSlug');
        Route::get('/by-company/{company_id}', 'Api\ProductsController@getDataByCompany');
    });

    Route::prefix('fcm-token-info')->group(function(){
        Route::get('/', 'Api\FcmTokenInfoController@show');
        Route::post('/store', 'Api\FcmTokenInfoController@store');
    });
?>