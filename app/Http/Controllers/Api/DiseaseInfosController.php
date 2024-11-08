<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\DiseaseInfosCollection as DiseaseInfosResource;

use App\Models\DiseaseInfos;
use Illuminate\Http\Request;

use Auth;

class DiseaseInfosController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DiseaseInfos $obj, Request $request)
    {
        // return $request->all();

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DiseaseInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(DiseaseInfos $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('disease_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->paginate($limit);

        // return response()->json($getData, 200);
        return DiseaseInfosResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DiseaseInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(DiseaseInfos $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DiseaseInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DiseaseInfos $obj, $req_id)
    {
        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DiseaseInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(DiseaseInfos $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }

    /**
     * Search disease infos
     */
     public function search(DiseaseInfos $obj)
     {
         // return request()->get('term');
         $getData = $obj::select('disease_title')->where('disease_title','LIKE','%'.request()->get('term').'%')
         ->take(request()->get('limit'))->get();
 
         return response()->json($getData, 200);
     }
}
