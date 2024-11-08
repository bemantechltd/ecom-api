<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\GenericsCollection as GenericsResource;

use App\Models\Generics;
use Illuminate\Http\Request;

use Auth;

class GenericsController extends Controller
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
    public function store(Generics $obj, Request $request)
    {
        // return $request->all();

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Generics  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(Generics $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')                
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('generic_name','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->paginate($limit);

        // return response()->json($getData, 200);
        return GenericsResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Generics  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(Generics $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Generics  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Generics $obj, $req_id)
    {
        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Generics  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(Generics $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }

    /**
     * Search Generics
     */
    public function search(Generics $obj)
    {
        // return request()->get('term');
        $getData = $obj::select('generic_name')->where('generic_name','LIKE','%'.request()->get('term').'%')
        ->take(request()->get('limit'))->get();

        return response()->json($getData, 200);
    }
}
