<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\UserRoleInfoListCollection as UserRoleInfoListResource;

use App\Models\UserRoleInfos;
use App\Models\UserRoleAccess;
use Illuminate\Http\Request;

use Auth;
use DB;
class UserRoleInfosController extends Controller
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

    protected function more_featured_management($obj,$data,$req_id,$action){
        /**
         * DELETE user role access first
         */
        if($action=='update') DB::select('DELETE FROM `user_role_accesses` WHERE role_id='.$req_id);
        
        /**
         * USER ROLE ACCESS STORE
         */
        $qry = 'INSERT INTO `user_role_accesses` (`role_id`,`feature_id`,`create`,`view_others`,`edit`,`edit_others`,`delete`,`delete_others`) VALUES';

        $co=0; foreach($data['role_accesses'] as $key => $val){
            if($co++>0) $qry .= ',';
            $qry .= '('.$req_id.','.$key.','.(isset($val['create'])?$val['create']:0).','.(isset($val['view_others'])?$val['view_others']:0).','.(isset($val['edit'])?$val['edit']:0).','.(isset($val['edit_others'])?$val['edit_others']:0).','.(isset($val['delete'])?$val['delete']:0).','.(isset($val['delete_others'])?$val['delete_others']:0).')';
        }

        $obj = DB::select($qry);

        return $obj;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRoleInfos $obj, Request $request)
    {
        $data = [];        
        $data['role_title'] = $request['role_title'];
        $data['weight'] = $request['weight'];
        $data['status'] = $request['status'];

        $getLastId = ModificationController::save_content($obj, $data, 1);
        
        $obj = $this->more_featured_management($obj,$request,$getLastId,'');

        $data = [
            'data'      => $obj,
            'status'    => true,
            'code'      => '200',
            'message'   => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
        ];

        return response()->json($data, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserRoleInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(UserRoleInfos $obj, Request $request)
    {
        $user_id = Auth::id();

        $limit = $request->has('limit')?$request['limit']:'';
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';

        if($limit>0) $getData = $obj::select('*')        
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('role_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->with('RoleAccesses')
        ->orderBy('weight','DESC')
        ->paginate($limit);
        
        else $getData = $obj::select('*')        
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('role_title','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->with('RoleAccesses')
        ->orderBy('weight','DESC')
        ->get();

        // return response()->json($getData, 200);
        return UserRoleInfoListResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserRoleInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(UserRoleInfos $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with('RoleAccesses')
        ->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserRoleInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserRoleInfos $obj, $req_id)
    {
        $data = [];        
        $data['role_title'] = $request['role_title'];
        $data['weight'] = $request['weight'];
        $data['status'] = $request['status'];

        $getLastId = ModificationController::update_content($obj, $data, $req_id);
        
        $obj = $this->more_featured_management($obj,$request,$req_id,'update');

        $data = [
            'data'      => $obj,
            'status'    => true,
            'code'      => '200',
            'message'   => '<i class="fa fa-check-circle"></i> Data has been updated successfully.',
        ];

        return response()->json($data, 200);
        
        // return $request->all();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserRoleInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserRoleInfos $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
