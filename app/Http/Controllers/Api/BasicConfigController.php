<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\CategoriesCollection as CategoriesResource;
use App\Http\Resources\PromotionalBannersCollection as PromotionalBannersResource;
use App\Models\Categories;
use App\Models\PromotionalBannerInfo;

use Cache;
use DB;
use Auth;

class BasicConfigController extends Controller
{
    protected $cache_category_tag_name = 'categories';
    protected $cache_promotional_banner_tag_name = 'promotional_banners';
    
    protected function get_basic_data(){
        /**
         * Get site basic configuration
         */
        $path = storage_path('app/public') . "/json/site-basic-config.json";
        $getContents = file_get_contents($path);
        
        preg_match("/\{(.*)\}/s", $getContents, $matches);

        $siteBasicConfigData = json_decode($matches[0]);
        
        /**
         * Get Default Setting Objects
         */
        $getArrObj = (object)[
            // Site basic config data
            'site_basic_config_data' => $siteBasicConfigData,
            
            // gender list
            'gender_list' =>  (object)[
                '1' =>  'Male',
                '2' =>  'Female',
                '3' =>  'Common',
                '4' =>  'Not Mentioned'
            ],
            
            // blood group list
            'blood_group_list' => (object)[
                '1' => 'O-',
                '2' => 'O+',
                '3' => 'A-',
                '4' => 'A+',
                '5' => 'B-',
                '6' => 'B+',
                '7' => 'AB-',
                '8' => 'AB+'
            ],

            // display on list
            'display_on_list' =>  (object)[
                '1' =>  'Top Header Section',
                '2' =>  'Header Navigation Section',
                '3' =>  'Footer Section'
            ],

            // order timeline list
            'order_timeline_list' =>  (object)[
                '1' => 'We are getting your order',
                '2' => 'Delivery person has been given the responsibility',
                '3' => 'The order has been placed',
                '4' => 'The order is ready',
                '5' => 'Your order is on the way to delivery',
                '6' => 'Order delivering to the customer\'s hand',                
                '7' => 'Payment has been paid',
                '8' => 'Order has been completed'
            ],

            // order status list
            'order_status_list' =>  (object)[
                '1' => 'Looking for a nearby delivery person',
                '2' => 'The delivery person is going to place the order',
                '3' => 'Order is being prepared',
                '4' => 'Order is being packed',
                '5' => 'Order delivery time (15min)',
                '6' => 'Order has been delivered',
                '7' => 'Payment is being paid'
            ],

            // order status images
            'order_status_images' =>  (object)[
                '1' => config('global.timeline_images_base_url').'/1.png',
                '2' => config('global.timeline_images_base_url').'/2.png',
                '3' => config('global.timeline_images_base_url').'/3.png',
                '4' => config('global.timeline_images_base_url').'/4.png',
                '5' => config('global.timeline_images_base_url').'/5.png',
                '6' => config('global.timeline_images_base_url').'/6.png',
                '7' => config('global.timeline_images_base_url').'/7.png'
            ],
            
            // init order label setup            
            'order_init_status_label' => 'Order Submitted',

            // User type setup
            'admin_user_type_id' => 1,
            'delivery_user_type_id' => 2,
            'customer_user_type_id' => 3,            

            // Global variables
            'currency_info' => (object) [
                'title' => 'Tk',
                'symbol' => '৳',
                'symbol_pos' => 'left'
            ],

            // Banner display types
            'banner_display_types' => (object)[
                '1' => 'On body',
                '2' => 'Popup'
            ],
            'banner_schedule_types' => (object)[
                '0' => 'Always Show',
                '1' => 'On Schedule'
            ],
            
            // Media gallery image path
            'media_gallery_img_path' => config('global.media_gallery_base_url') . '/images',

            // Checkout page variables
            'vat' => 0, // percentage
            'delivery_fee_default' => 60
        ];
        
        return $getArrObj;
    }
    
    protected function get_category_info($type='',$limit='',$page=''){
        
        $obj = new Categories;
        
        // Cache key init
        $cacheKey = $this->cache_category_tag_name.":load:{$type}:{$limit}:{$page}";
        
        // Cache flush with tag name
        Cache::tags($this->cache_category_tag_name)->flush();
        
        // $getResponseData = Cache::tags([$this->cache_category_tag_name])->rememberForever($cacheKey, function() use($obj,$type,$limit){
            
            $getData = $obj::select('*')
            ->when($type, function($q) use($type){
                return $q->where($type,1);
            })
            ->where('status', 1)
            // ->whereNull('parent_id')
            // ->with('SubCategories')
            ->get();
    
            // return response()->json($getData, 200);
            return CategoriesResource::collection($getData);
        // });
        
        // return $getResponseData;
    }
    
    protected function get_promotional_banner_info($limit='',$page='')
    {
        // return $request->all();
        $obj = new PromotionalBannerInfo;
        
        // Cache key init
        $cacheKey = $this->cache_promotional_banner_tag_name.":load:{$limit}:{$page}";
        
        // Cache flush with tag name
        Cache::tags($this->cache_promotional_banner_tag_name)->flush();
        
        // $getResponseData = Cache::tags([$this->$cache_promotional_banner_tag_name])->rememberForever($cacheKey, function() use($obj, $limit){
            
            if($limit>0) $getData = $obj::select('*')
            ->where('status', 1)
            ->take($limit)->get();
            else $getData = $obj::select('*')
            ->where('status', 1)
            ->get();
    
            // return response()->json($getData, 200);
            return PromotionalBannersResource::collection($getData);
        // });
        
        // return $getResponseData;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){        
        Storage::disk('public')->put('json/site-basic-config.json', response()->json($request));
        
        return response()->json(['status' => true], 200);
    }
    
    public function get(){
        $path = storage_path('app/public') . "/json/site-basic-config.json";
        $getContents = file_get_contents($path);
        
        preg_match("/\{(.*)\}/s", $getContents, $matches);

        $data = json_decode($matches[0]);
        
        return response()->json(['data' => $data, 'status' => true], 200);
    }
    
    public function loadData(){
        $getData = [];
        $getData['basic_config_info'] = $this->get_basic_data();
        
        /**
         * Get site logo info
         */
        $path = storage_path('app/public') . "/json/logo-info.json";
        $getContents = file_get_contents($path);
        
        preg_match("/\{(.*)\}/s", $getContents, $matches);

        $getData['logo_info'] = json_decode($matches[0]);
        
        /**
         *  Get category info
         */
        $getData['category_info']['display_on_nav'] = $this->get_category_info('display_on_nav');
        $getData['category_info']['display_on_body'] = $this->get_category_info('display_on_body');
        
        /**
         * Get promotional banner info
         */
        $getData['promotional_banner_info'] = $this->get_promotional_banner_info(8);
        
        return response()->json(['data' => $getData, 'status' => true], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(){
        $getData = $this->get_basic_data();
        
        return response()->json(['data' => $getData], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderDeliveryPersonInfo $obj)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderDeliveryPersonInfo $obj)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderDeliveryPersonInfo $obj)
    {
        //
    }
}
