<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\ManageAddressCollection as ManageAddressResource;

use App\Models\ManageAddress;
use Illuminate\Http\Request;

use Auth;
use DB;

class ManageAddressController extends Controller
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
    public function store(ManageAddress $obj, Request $request)
    {
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Invalid credential', 'status' => false], 200);
        else $request['user_id'] = $user_id;

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ManageAddress  $manageAddress
     * @return \Illuminate\Http\Response
     */
    public function show(ManageAddress $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;

        if($limit>0) $getData = $obj::select('*')                
        ->where('user_id',$user_id)
        ->with(['RegionInfo','CityInfo','AreaInfo'])
        ->paginate($limit);

        // return response()->json($getData, 200);
        return ManageAddressResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ManageAddress  $manageAddress
     * @return \Illuminate\Http\Response
     */
    public function edit(ManageAddress $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ManageAddress  $manageAddress
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ManageAddress $obj, $req_id)
    {
        return ModificationController::update_content($obj, $request, $req_id);
    }

    public function defaultAddressShow(ManageAddress $obj){
        $user_id = Auth::id();

        $getData = $obj::select('*')                
        ->where('user_id',$user_id)        
        ->where(function($q) {
            $q->where('default_shipping_address', 1)
            ->orWhere('default_billing_address', 1);
        })
        ->with(['RegionInfo','CityInfo','AreaInfo'])
        ->get();

        // return response()->json($getData, 200);
        return ManageAddressResource::collection($getData);
    }

    public function defaultAddressUpdate(Request $request, ManageAddress $obj, $req_id){
        $type = $request->has('type')?$request['type']:'';
        if($type!==''){            
            $qry = 'UPDATE `manage_addresses` SET '.($type==1?'`default_shipping_address`':'`default_billing_address`').' = 0';
            DB::select($qry);

            $getRequest = $request->all();
            unset($getRequest['type']);
            // return $getRequest;

            $getUpdateData = ModificationController::update_content($obj, $getRequest, $req_id);
            return response()->json(['data' => $getUpdateData, 'status' => true], 200);
        }else{
            return response()->json(['msg' => 'Invalid default address setup', 'status' => false], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ManageAddress  $manageAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(ManageAddress $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
