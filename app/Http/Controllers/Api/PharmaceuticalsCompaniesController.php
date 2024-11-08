<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\PharmaCompaniesCollection as PharmaCompaniesResource;

use App\Models\PharmaceuticalsCompanies;
use Illuminate\Http\Request;

use Auth;

class PharmaceuticalsCompaniesController extends Controller
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
    public function store(PharmaceuticalsCompanies $obj, Request $request)
    {
        // return $request->all();

        /**
         * IMAGE CONVERT
         */
        $target_path = 'images/company-logos';
        $request['logo'] = ModificationController::base64ToImage($request['logo'],$target_path);

        unset($request['exist_logo']);

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PharmaceuticalsCompanies  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(PharmaceuticalsCompanies $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:'';
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('company_name','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->paginate($limit);
        else $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('company_name','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->get();

        // return response()->json($getData, 200);
        return PharmaCompaniesResource::collection($getData);
    }

    public function load(PharmaceuticalsCompanies $obj, Request $request)
    {
        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:'';
        if($limit>0) $getData = $obj::select('*')
        ->where('status', 1)
        ->paginate($limit);
        else $getData = $obj::select('*')
        ->where('status', 1)
        ->get();

        // return response()->json($getData, 200);
        return PharmaCompaniesResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PharmaceuticalsCompanies  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(PharmaceuticalsCompanies $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        $getData->exist_logo = $getData->logo;
        $getData->logo = $getData->logo?config('global.company_logo_base_url').'/'.$getData->logo:null;

        return response()->json($getData, 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PharmaceuticalsCompanies  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PharmaceuticalsCompanies $obj, $req_id)
    {
        /**
         * IMAGE CONVERT
         */
        $target_path = 'images/company-logos';
        $request['logo'] = ModificationController::base64ToImage($request['logo'],$target_path);

        if($request['logo']){
            if($request['exist_logo'] && ($request['exist_logo']!==$request['logo'])){
                $logo_path = config('global.company_logo_base_path').'/'.$request['exist_logo'];
                if(file_exists($logo_path)) unlink($logo_path);
            }
        }else unset($request['logo']);
        
        unset($request['exist_logo']);

        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PharmaceuticalsCompanies  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(PharmaceuticalsCompanies $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
