<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;

use App\Models\OrderDeliveryPersonInfo;
use App\Models\OrderDeliveryTimeline;
use App\Models\FcmTokenInfo;
use Illuminate\Http\Request;

use DB;
use Auth;

class OrderDeliveryPersonInfoController extends Controller
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

    public function reviewPending(OrderDeliveryPersonInfo $obj, Request $request){
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);        

        try {
            $getData = $obj::whereNull('order_delivery_person_infos.rating_points')
            ->leftJoin('order_infos AS oi','oi.id','order_delivery_person_infos.order_id')
            ->where(['order_delivery_person_infos.status' => 1, 'oi.customer_id' => $user_id])
            ->with(['OrderInfo','User'])
            ->get();
            return response()->json(['data' => $getData,'status' => true], 200);
        } catch (\Exception $e) {           
            // something went wrong
            return response()->json(['error' => $e, 'status' => false], 200);
        }
    }

    public function reviewStore(OrderDeliveryPersonInfo $obj, Request $request){
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);        

        try {
            DB::beginTransaction();

            $updateQryStr = 'UPDATE `order_delivery_person_infos` SET rating_points = '. $request['rating_points'] .', review_comments = "'. $request['review_comments'] .'" WHERE `order_id` = '.$request['order_id'].' AND status=1';
            
            try {
                DB::select($updateQryStr);
                DB::commit();

                return response()->json(['qry' => $updateQryStr, 'msg' => 'Rating has been submitted','status' => true], 200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['error' => $e, 'status' => false], 200);
            }
        } catch (\Exception $e) {           
            // something went wrong
            return response()->json(['error' => $e, 'status' => false], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderDeliveryPersonInfo $obj, Request $request)
    {
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        DB::beginTransaction();

        try {            
            $obj->delivery_person_id = $request['delivery_person_id'];
            $obj->order_id = $request['order_id'];
            $obj->status = 0;

            if($obj->save()){
                /**
                 * Order timeline setup
                 */
                try{
                    $getObj = new OrderDeliveryTimeline();
                    $getObj->order_id = $request['order_id'];
                    $getObj->timeline_id = $request['timeline_id'];
                    $getObj->status = 0;

                    $getObj->save();
                    
                    $updateQryStr = 'UPDATE `order_infos`
                        SET status = 3
                        WHERE `id` = '.$request['order_id'];
                    
                    try {
                        DB::update($updateQryStr);
                    } catch (\Exception $e) {
                        DB::rollback();
    
                        return response()->json(['msg' => 'Order info status not submit properly', 'status' => false], 200);
                    }
                    
                } catch (\Exception $e) {
                    DB::rollback();

                    return response()->json(['msg' => 'Order timeline not submit properly', 'status' => false], 200);
                }
            }

            try{
                $obj = new FcmTokenInfo();
                $getFcmTokenData = $obj::where('user_id', $request['delivery_person_id'])->get();
            }catch(\Exception $e){
                DB::rollback();

                return response()->json(['msg' => 'Fcm token info problem', 'status' => false], 200);
            }

            DB::commit();
            // all good            

            // for success submit
            return response()->json(['fcm_token_data' => $getFcmTokenData, 'status' => true], 200);
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'status' => false], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(OrderDeliveryPersonInfo $obj)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderDeliveryPersonInfo $obj)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderDeliveryPersonInfo $obj)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderDeliveryPersonInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderDeliveryPersonInfo $obj)
    {
        //
    }
}
