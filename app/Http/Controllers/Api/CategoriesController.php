<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\CategoriesCollection as CategoriesResource;
use App\Models\Categories;
use Illuminate\Http\Request;

use Cache;
use Auth;
use DB;

class CategoriesController extends Controller
{
    protected $target_path = 'images/category-iconz';
    protected $cache_tag_name = 'categories';

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
    public function store(Categories $obj, Request $request)
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
     * @param  \App\Models\Categories  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(Categories $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:'';
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';
        
        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('category_name','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })
        // ->whereNull('parent_id')
        ->with('SubCategories')
        ->paginate($limit);
        else $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('category_name','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })
        // ->whereNull('parent_id')
        ->with('SubCategories')
        ->get();

        // return response()->json($getData, 200);
        return CategoriesResource::collection($getData);
    }

    public function load(Categories $obj, Request $request)
    {
        // return $request->all();
        $type = $request->has('type')?$request['type']:'';
        $limit = $request->has('limit')?$request['limit']:'';
        $page = $request->has('page')?$request['page']:'';
        
        // Cache key init
        $cacheKey = $this->cache_tag_name.":load:{$type}:{$limit}:{$page}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj,$type,$limit){
            
            if($limit>0) $getData = $obj::select('*')
            ->when($type, function($q) use($type){
                return $q->where($type,1);
            })
            ->where('status', 1)
            // ->whereNull('parent_id')
            ->with('SubCategories')
            ->paginate($limit);
            else $getData = $obj::select('*')
            ->when($type, function($q) use($type){
                return $q->where($type,1);
            })
            ->where('status', 1)
            // ->whereNull('parent_id')
            ->with('SubCategories')
            ->get();
    
            // return response()->json($getData, 200);
            return CategoriesResource::collection($getData);
        });
        
        return $getResponseData;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Categories  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(Categories $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        $getData->exist_icon = $getData->icon;
        $getData->icon = $getData->icon?config('global.category_icon_base_url').'/'.$getData->icon:null;

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Categories  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Categories $obj, $req_id)
    {
        if($request['parent_id']=='null') $request['parent_id'] = null;        

        /**
         * IMAGE CONVERT
         */        
        $request['icon'] = ModificationController::base64ToImage($request['icon'],$this->target_path);

        if($request['icon']){
            if($request['exist_icon'] && ($request['exist_icon']!==$request['icon'])){
                $icon_path = config('global.category_icon_base_path').'/'.$request['exist_icon'];
            
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
     * @param  \App\Models\Categories  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(Categories $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        // Cache flush with tag name
        Cache::tags($this->cache_tag_name)->flush();
        
        return response()->json($geResult, 200);
    }
}
