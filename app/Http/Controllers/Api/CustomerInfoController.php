<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Controllers\Common\EncryptionController as EncryptionController;
use App\Http\Resources\CustomerInfoListCollection as CustomerInfoListResource;

use App\User;
use App\Models\UserInfos;
use Illuminate\Http\Request;
use Session;
use Auth;
use DB;

class CustomerInfoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(User $obj, Request $request)
    {
        // $messages = [
        //     'required' => 'This field is required',
        //     'unique' => 'This field is unique'
        // ];

        $data = [];        
        $data['password'] = bcrypt($request['password']);
        $data['user_type'] = 3;
        $data['email'] = $request['email'];
        $data['auth_code'] = str_random(12);
        $data['verified'] = $request['verified'];
        $data['status'] = $request['status'];

        $getLastId = ModificationController::save_content($obj, $data, 1);

        $obj = new UserInfos;
        $userData = [];
        $userData = $request['user_info'];
        if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
        $userData['user_id'] = $getLastId;

        return ModificationController::save_content($obj, $userData);
        // return $request->all();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(User $obj, Request $request)
    {
        $user_id = Auth::id();
        
        $limit = $request->has('limit')?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';
        $verified = $request->has('verified')?$request['verified']:'';
        $status = $request->has('status')?$request['status']:'';
        $date_range = $request->has('date_range')?explode(',',$request['date_range']):'';

        $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%")
            ->orWhere('mobile','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->when($date_range, function($q) use($date_range){
            return $q->whereBetween(DB::raw('DATE(created_at)'),$date_range);
        })->when($verified, function($q) use($verified){
            if($verified==2) $verified = 0;
            return $q->where('status',$verified);
        })->when($status, function($q) use($status){
            if($status==2) $status = 0;
            return $q->where('status',$status);
        })->where('user_type',3)
        ->orderBy('id','DESC')
        ->with(['UserInfo'])
        ->paginate($limit);

        // return response()->json($getData, 200);
        return CustomerInfoListResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(User $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with(['UserInfo'])
        ->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(User $obj, Request $request, $req_id)
    {
        $data = [];        
        if($request->has('password')) $data['password'] = bcrypt($request['password']);
        if($request->has('email')) $data['email'] = $request['email'];
        if($request->has('mobile')) $data['mobile'] = $request['mobile'];
        $data['status'] = $request['status'];
        $data['verified'] = $request['verified'];

        ModificationController::update_content($obj, $data, $req_id);        

        $obj = new UserInfos;
        $userData = [];
        $userData = $request['user_info'];        
        if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
        $userData['user_id'] = $req_id;

        return ModificationController::update_content($obj, $userData, $req_id, 'user_id');
        // return $request->all();
    }    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}