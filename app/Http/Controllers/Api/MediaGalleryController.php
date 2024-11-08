<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\MediaGalleriesCollection as MediaGalleriesResource;

use App\Models\MediaGallery;
use Illuminate\Http\Request;

use Auth;

class MediaGalleryController extends Controller
{
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(MediaGallery $obj, Request $request)
    {
        // return $request->all();

        /**
         * IMAGE CONVERT
         */
        $watermarkPosition = '';
        if($request['watermark']) $watermarkPosition = $request['watermark_pos'];
        $target_path = 'media-gallery/'.($request['content_type']==1?'images':'videos');
        $request['content'] = ModificationController::base64ToImage($request['content'],$target_path,$watermarkPosition);

        unset($request['exist_content']);

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MediaGallery $obj
     * @return \Illuminate\Http\Response
     */
    public function show(MediaGallery $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        $limit = $request->has('limit')?$request['limit']:10;
        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('content_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->orderBy('id','DESC')->paginate($limit);        

        // return response()->json($getData, 200);
        return MediaGalleriesResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MediaGallery $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(MediaGallery $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        $getData->exist_content = $getData->content;
        $getData->content = $getData->content?config('global.media_gallery_base_url').'/'.($getData->content_type==1?'images':'videos').'/'.$getData->content:null;        

        return response()->json($getData, 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MediaGallery  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MediaGallery $obj, $req_id)
    {        
        /**
         * IMAGE CONVERT
         */
        $watermarkPosition = '';
        if($request['watermark']) $watermarkPosition = $request['watermark_pos'];
        
        $target_path = 'media-gallery/'.($request['content_type']==1?'images':'videos');
        $request['content'] = ModificationController::base64ToImage($request['content'],$target_path,$watermarkPosition);

        if($request['content']){
            if($request['exist_content'] && ($request['exist_content']!==$request['content'])){            
                $content_path = config('global.media_gallery_base_path').'/'.($request['content_type']==1?'images':'videos').'/'.$request['exist_content'];
                
                if(file_exists($content_path)) unlink($content_path);            
            }
        }else unset($request['content']);

        unset($request['exist_content']);

        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MediaGallery  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(MediaGallery $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }

    /**
     * Search tags
     */
    public function search(MediaGallery $obj)
    {
        // return request()->get('term');
        $getData = $obj::select('content_title')->where('content_title','LIKE','%'.request()->get('term').'%')
        ->take(request()->get('limit'))->get();

        return response()->json($getData, 200);
    }
}
