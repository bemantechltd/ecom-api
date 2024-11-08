<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\StaticPageCollection as StaticPageResource;

use App\Models\StaticPageInfo;
use Illuminate\Http\Request;

use Cache;
use Auth;
use DB;

class StaticPageInfoController extends Controller
{
    protected $cache_tag_name = 'static_pages';
    
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
    public function store(StaticPageInfo $obj, Request $request)
    {
        return $this->save_content($obj, $request);
    }

    protected function more_featured_management($obj,$data,$req_id,$action){        
        /**
         * Remove page photo infos first
         */
        if($action=='update') DB::select('DELETE FROM `static_page_photo_infos` WHERE static_page_id='.$req_id);

        /**
         * page photo Information
         */
        if(!empty($data['photo_infos'])){
            /**
             * Save to page photo infos table
             */
            $staticPagePhotoInfoQry = 'INSERT INTO `static_page_photo_infos`(static_page_id,photo_id) VALUES';
            $co=0; foreach($data['photo_infos'] as $key => $val){
                if($co++>0) $staticPagePhotoInfoQry .= ',';
                $staticPagePhotoInfoQry .= '('.$req_id.','.$val['id'].')';
            }
            $obj->photo_infos =  DB::select($staticPagePhotoInfoQry);
        }
        
        // Cache flush
        Cache::tags($this->cache_tag_name)->flush();

        return $obj;
    }

    /**
     * SAVE CONTENT
     */
    protected function save_content($obj, $data){
        try{                
            $user_id = Auth::id();

            $obj->page_title        = $data['page_title'];
            $obj->slug              = $data['slug'];
            $obj->details           = $data['details'];
            $obj->display_on        = $data['display_on'];
            $obj->status            = $data['status'];
            $obj->created_by        = $user_id;

            if($obj->save()){
                /**
                 * MORE FEATURED MANAGEMENT FUNCTION
                 */
                $obj = $this->more_featured_management($obj,$data,$obj->id,'update');

                $data = [
                    'data'      => $obj,
                    'status'    => 'success',
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => '<i class="fa fa-info-circle"></i> Error occurred. Data doesn\'t save.'
                ];

                return response()->json($data, 404);
            }
        }catch(\Exception $e){
            $data = [
                'status'  => 'error',
                'code'    => '404',
                'message' => $e->getMessage(),
            ];

            return response()->json($data, 404);
        }
    }

    /**
     * UPDATE CONTENT
     */
    protected function update_content($obj, $data, $req_id){        
        try{                
            $user_id = Auth::id();            

            /**
             * QUERY SETUP
             */
            $obj = $obj->find($req_id);

            $obj->page_title        = $data['page_title'];
            $obj->slug              = $data['slug'];
            $obj->details           = $data['details'];
            $obj->display_on        = $data['display_on'];
            $obj->status            = $data['status'];
            $obj->updated_by        = $user_id;            

            if($obj->update()){
                /**
                 * MORE FEATURED MANAGEMENT FUNCTION
                 */
                $obj = $this->more_featured_management($obj,$data,$obj->id,'update');

                $data = [
                    'data'        => $obj,
                    'status'    => 'success',
                    'code'      => '200',
                    'message'   => '<i class="fa fa-check-circle"></i> Data has been updated successfully.',
                ];

                return response()->json($data, 200);
            }else{
                $data = [
                    'status'  => 'error',
                    'code'    => '404',
                    'message' => '<i class="fa fa-info-circle"></i> Error occurred. Data doesn\'t update.'
                ];

                return response()->json($data, 404);
            }
        }catch(\Exception $e){
            $data = [
                'status'  => 'error',
                'code'    => '404',
                'message' => $e->getMessage(),
            ];

            return response()->json($data, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StaticPageInfo  $staticPageInfo
     * @return \Illuminate\Http\Response
     */
    public function show(StaticPageInfo $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        $getData = $obj::select('*')                
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('page_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })
        ->with('PhotoInfos')
        ->orderBy('page_title','ASC')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return StaticPageResource::collection($getData);
    }

    public function load(StaticPageInfo $obj, Request $request)
    {
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:'';
        // $page = $request->has('page')?$request['page']:1;
        
        // Cache key init
        $cacheKey = $this->cache_tag_name.":load";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj, $limit){
            
            $getData = $obj::select('page_title','slug','display_on')
            ->where('status', 1)
            // ->paginate($limit);
            ->take($limit)->get();
    
            return response()->json($getData, 200);
            // return StaticPageResource::collection($getData);
        });
        
        return $getResponseData;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StaticPageInfo  $staticPageInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(StaticPageInfo $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with('PhotoInfos')
        ->first();

        // return response()->json($getData, 200);
        return new StaticPageResource($getData);
    }

    public function detailsStaticPage(StaticPageInfo $obj, $slug){
        // Cache key init
        $cacheKey = $this->cache_tag_name.":slug:{$slug}";
        
        $getResponseData = Cache::tags([$this->cache_tag_name])->rememberForever($cacheKey, function() use($obj, $slug){
            
            $getData = $obj::select('page_title','details')
            ->where('slug',$slug)
            ->where('status', 1)
            ->with('PhotoInfos')
            ->first();
    
            // return response()->json($getData, 200);
            return new StaticPageResource($getData);
        });
        
        return $getResponseData;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StaticPageInfo  $staticPageInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StaticPageInfo $obj, $req_id)
    {
        // return ModificationController::update_content($obj, $request, $req_id);
        return $this->update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StaticPageInfo  $staticPageInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(StaticPageInfo $obj, $id)
    {
        $geResult = $obj::find($id)->delete();
        
        // Cache flush
        Cache::tags($this->cache_tag_name)->flush();

        return response()->json($geResult, 200);
    }
}
