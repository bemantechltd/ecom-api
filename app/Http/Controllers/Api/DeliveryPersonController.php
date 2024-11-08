<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Controllers\Common\EncryptionController as EncryptionController;
use App\Http\Resources\DeliveryPersonListCollection as DeliveryPersonListResource;

use App\User;
use App\Models\UserInfos;
use Illuminate\Http\Request;
use Session;
use Auth;
use DB;

class DeliveryPersonController extends Controller
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
        $email = $request['email'];
        $mobile = $request['mobile'];
        
        $checkExist = $obj::when($email, function($q) use($email){
            return $q->orWhere('email',$email);
        })->when($mobile, function($q) use($mobile){
            return $q->orWhere('mobile',$mobile);
        })->first();
        
        if($checkExist) return response()->json(['msg' => 'E-mail or mobile number already exist', 'status' => false], 200);

        $data = [];        
        $data['password'] = bcrypt($request['password']);
        $data['user_type'] = 2;
        $data['email'] = $request['email'];
        if(isset($request['mobile'])) $data['mobile'] = $request['mobile'];
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

        $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%")
            ->orWhere('mobile','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->where('user_type',2)
        ->with(['UserInfo'])
        ->paginate($limit);

        // return response()->json($getData, 200);
        return DeliveryPersonListResource::collection($getData);
    }

    public function getAvailableList(User $obj, Request $request)
    {
        $user_id = Auth::id();
        
        $limit = $request->has('limit')?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';        

        $getData = $obj::select('users.*')
        // ->leftJoin('order_delivery_person_infos as odpi','odpi.delivery_person_id','=','users.id')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('users.email','LIKE',"%$srch_keyword%")
            ->orWhere('users.mobile','LIKE',"%$srch_keyword%");
        })->where('users.user_type',2)
        // ->where(function($q){
        //     return $q->whereNotIn('users.id', function($qry){
        //       return $qry->select('odpi.delivery_person_id')
        //       ->from('order_delivery_person_infos AS odpi')
        //       ->where('odpi.status',0);
        //     });
        //     // return $q->where('odpi.status',0);
        //     // ->orWhereNull('odpi.status');
        // })
        // ->groupBy('odpi.order_id')
        ->with(['UserInfo','OrderDeliveryInfo'])
        ->paginate($limit);

        // return response()->json($getData, 200);
        return DeliveryPersonListResource::collection($getData);
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
        $data['email'] = $request['email'];
        if(isset($request['mobile'])) $data['mobile'] = $request['mobile'];
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