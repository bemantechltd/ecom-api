<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\ProductReturnRequestInfosCollection as ProductReturnRequestInfosResource;

use App\Models\OrderInfo;
use App\Models\ProductReturnRequestInfos;
use Illuminate\Http\Request;

use DB;
use Auth;

class ProductReturnRequestInfosController extends Controller
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
    public function store(ProductReturnRequestInfos $obj, Request $request)
    {
        // return $request->all();
        
        /**
         * IMAGE CONVERT
         */
        if(!empty($request['photos'])){
            $watermarkPosition = '';
            $target_path = 'product-return-images/'.$request['order_id'].'/'.$request['order_item_pk'];
            $photo_content_list = [];
            foreach($request['photos'] as $key => $val){
                $getFileName = ModificationController::base64ToImage($val,$target_path,$watermarkPosition);
                array_push($photo_content_list, $getFileName);
            }
            
            $request['photos'] = $photo_content_list;
        }
        
        $request['photos'] = json_encode($request['photos']);
        
        // return $request;

        return ModificationController::save_content($obj, $request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductReturnRequestInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(ProductReturnRequestInfos $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $status = $request->has('status')?$request['status']:'';
        $date_range = $request->has('date_range')?explode(',',$request['date_range']):'';

        $getData = $obj::select('*')                
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('description','LIKE',"%$srch_keyword%");
        })->when($date_range, function($q) use($date_range){
            return $q->whereBetween(DB::raw('DATE(created_at)'),$date_range);
        })->when($status, function($q) use($status){
            if($status==4) $status = 0;
            return $q->where('status',$status);
        })->with(['OrderItemInfo','OrderInfo','ReturnReasonInfo'])->paginate($limit);

        // return response()->json($getData, 200);
        return ProductReturnRequestInfosResource::collection($getData);
    }
    
    public function myStatus(ProductReturnRequestInfos $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $status = $request->has('status')?$request['status']:'';
        $date_range = $request->has('date_range')?explode(',',$request['date_range']):'';

        $getData = $obj::select('*')                
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('description','LIKE',"%$srch_keyword%");
        })->when($date_range, function($q) use($date_range){
            return $q->whereBetween(DB::raw('DATE(created_at)'),$date_range);
        })->when($status, function($q) use($status){
            if($status==4) $status = 0;
            return $q->where('status',$status);
        })->where('created_by',$user_id)
        ->with(['OrderItemInfo','OrderInfo','ReturnReasonInfo'])->paginate($limit);

        // return response()->json($getData, 200);
        return ProductReturnRequestInfosResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductReturnRequestInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductReturnRequestInfos $obj, $id)
    {
        $getData = $obj::select('*')->where('id',$id)->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductReturnRequestInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $req_id)
    {
        // return $request;
        
        DB::beginTransaction();
        
        $accept_status = $request['accept_status'];
        $reject_reason = $request->has('reject_reason')?trim($request['reject_reason']):'';
        $status = $request['status'];
        
        $updateQryStr = 'UPDATE `product_return_request_infos`
            SET status='.$status.', accept_status='.$accept_status.', reject_reason="'.($reject_reason!==''?$reject_reason:'').'", updated_at = "'.date('Y-m-d H:i:s').'"
            WHERE `id` = '.$req_id.'';
        
        try {
            DB::update($updateQryStr);
            
            DB::commit();

            $obj = new OrderInfo();
            $getFcmTokenData = $obj::select('fti.*')
                ->leftJoin('fcm_token_infos as fti','fti.user_id','=','order_infos.customer_id')
                ->where('order_infos.id', $request['order_id'])
                ->get();
            
            return response()->json(['fcm_token_data' => $getFcmTokenData, 'status' => true], 200);
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'msg' => 'Order info update problem!!!', 'status' => false], 200);
        }
        // return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductReturnRequestInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductReturnRequestInfos $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
