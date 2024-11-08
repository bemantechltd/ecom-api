<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\ProductTypesCollection as ProductTypesResource;

use App\Models\ProductTypes;
use Illuminate\Http\Request;

use Cache;
use Auth;
use DB;

class ProductTypesController extends Controller
{
    protected $target_path = 'images/product-type-iconz';
    protected $cache_tag_name = 'product_types';

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
    public function store(ProductTypes $obj, Request $request)
    {
        // return $request->all();

        /**
         * IMAGE CONVERT
         */
        $request['icon'] = ModificationController::base64ToImage($request['icon'],$this->target_path);

        unset($request['exist_icon']);
        
        // Cache flush with tag name
        Cache::tags($this->cache_tag_name)->flush();

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductTypes  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(ProductTypes $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:'';
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('type_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->with('CatInfo')->paginate($limit);
        
        else $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('type_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->with('CatInfo')->get();

        // return response()->json($getData, 200);
        return ProductTypesResource::collection($getData);
    }

    public function load(ProductTypes $obj, Request $request)
    {
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:'';
        $page = $request->has('page')?$request['page']:1;
        
        // Cache key init
        $cacheKey = $this->cache_tag_name.":load:{$page}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj,$limit){
            
            if($limit>0) $getData = $obj::select('*')
            ->where('status', 1)
            ->paginate($limit);
            else $getData = $obj::select('*')
            ->where('status', 1)
            ->get();
    
            // return response()->json($getData, 200);
            return ProductTypesResource::collection($getData);
        });
        
        return $getResponseData;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductTypes  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductTypes $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        $getData->exist_icon = $getData->icon;
        $getData->icon = $getData->icon?config('global.product_type_icon_base_url').'/'.$getData->icon:null;

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductTypes  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductTypes $obj, $req_id)
    {
        /**
         * IMAGE CONVERT
         */        
        $request['icon'] = ModificationController::base64ToImage($request['icon'],$this->target_path);

        if($request['icon']){
            if($request['exist_icon'] && ($request['exist_icon']!==$request['icon'])){
                $icon_path = config('global.product_type_icon_base_path').'/'.$request['exist_icon'];
            
                if(file_exists($icon_path)) unlink($icon_path);            
            }
        } else unset($request['icon']);

        unset($request['exist_icon']);
        
        // Cache flush with tag name
        Cache::tags($this->cache_tag_name)->flush();

        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductTypes  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductTypes $obj, $id)
    {
        $geResult = $obj::find($id)->delete();
        
        // Cache flush with tag name
        Cache::tags($this->cache_tag_name)->flush();

        return response()->json($geResult, 200);
    }
}
