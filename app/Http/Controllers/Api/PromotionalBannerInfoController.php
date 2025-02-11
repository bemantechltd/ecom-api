<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\PromotionalBannersCollection as PromotionalBannersResource;

use App\Models\PromotionalBannerInfo;
use Illuminate\Http\Request;

use Cache;
use Auth;
use DB;

class PromotionalBannerInfoController extends Controller
{
    protected $desktop_banner_target_path = 'images/promotional-banner-images/desktop';
    protected $mobile_banner_target_path = 'images/promotional-banner-images/mobile';
    protected $cache_tag_name = 'promotional_banners';

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
    public function store(PromotionalBannerInfo $obj, Request $request)
    {
        // return $request->all();
        
        $request['start_time']    = date('Y-m-d H:i:s', strtotime($request['start_time']));
        $request['end_time']      = date('Y-m-d H:i:s', strtotime($request['end_time']));

        /**
         * IMAGE CONVERT
         */
        $request['desktop_banner_image'] = ModificationController::base64ToImage($request['desktop_banner_image'],$this->desktop_banner_target_path);

        unset($request['exist_desktop_banner_image']);

        $request['mobile_banner_image'] = ModificationController::base64ToImage($request['mobile_banner_image'],$this->mobile_banner_target_path);

        unset($request['exist_mobile_banner_image']);
        
        // Cache flush
        Cache::tags($this->cache_tag_name)->flush();

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PromotionalBannerInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(PromotionalBannerInfo $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:'';
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('banner_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->paginate($limit);
        
        else $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('banner_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->get();

        // return response()->json($getData, 200);
        return PromotionalBannersResource::collection($getData);
    }

    public function load(Request $request)
    {
        $limit = $request->query('limit', 8);
        $banners = PromotionalBannerInfo::take($limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PromotionalBannerInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(PromotionalBannerInfo $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();
        // return response()->json($getData, 200);
        return new PromotionalBannersResource($getData);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromotionalBannerInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromotionalBannerInfo $obj, $req_id)
    {
        $request['start_time']    = date('Y-m-d H:i:s', strtotime($request['start_time']));
        $request['end_time']      = date('Y-m-d H:i:s', strtotime($request['end_time']));

        /**
         * Desktop Banner IMAGE CONVERT
         */        
        $request['desktop_banner_image'] = ModificationController::base64ToImage($request['desktop_banner_image'],$this->desktop_banner_target_path);

        if($request['desktop_banner_image']){
            if($request['exist_desktop_banner_image'] && ($request['exist_desktop_banner_image']!==$request['desktop_banner_image'])){
                $desktop_banner_image_path = config('global.desktop_banner_image_base_path').'/'.$request['exist_desktop_banner_image'];
            
                if(file_exists($desktop_banner_image_path)) unlink($desktop_banner_image_path);            
            }
        } else unset($request['desktop_banner_image']);

        unset($request['exist_desktop_banner_image']);

        /**
         * Mobile Banner IMAGE CONVERT
         */        
        $request['mobile_banner_image'] = ModificationController::base64ToImage($request['mobile_banner_image'],$this->mobile_banner_target_path);

        if($request['mobile_banner_image']){
            if($request['exist_mobile_banner_image'] && ($request['exist_mobile_banner_image']!==$request['mobile_banner_image'])){
                $mobile_banner_image_path = config('global.mobile_banner_image_base_path').'/'.$request['exist_mobile_banner_image'];
            
                if(file_exists($mobile_banner_image_path)) unlink($mobile_banner_image_path);            
            }
        } else unset($request['mobile_banner_image']);

        unset($request['exist_mobile_banner_image']);
        
        // Cache flush
        Cache::tags($this->cache_tag_name)->flush();

        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PromotionalBannerInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(PromotionalBannerInfo $obj, $id)
    {
        $geResult = $obj::find($id)->delete();
        
        // Cache flush
        Cache::tags($this->cache_tag_name)->flush();

        return response()->json($geResult, 200);
    }
}
