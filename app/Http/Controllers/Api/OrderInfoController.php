<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Controllers\Common\EncryptionController as EncryptionController;
use App\Http\Controllers\Common\SmsController as SmsController;
use App\Http\Resources\OrderInfoCollection as OrderInfoResource;

use App\User;
use App\Models\UserInfos;
use App\Models\OrderInfo;
use App\Models\OrderDeliveryTimeline;
use Illuminate\Http\Request;

use DB;
use Auth;

class OrderInfoController extends Controller
{
    protected $mobile_pattern = "/^[\+]?[0-9]{1,3}?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,9}$/";
    
    protected function CreateNewCustomer($req_user_id){
        $obj = new User;
        $email = false; $mobile = false;
        if(filter_var($req_user_id, FILTER_VALIDATE_EMAIL)) $email = true;
        else if(preg_match($this->mobile_pattern, $req_user_id)) $mobile = true;
        
        // set default password
        $get_password = str_random(8); // mt_rand(100000, 999999);

        DB::beginTransaction();

        $data = [];        
        $data['password'] = bcrypt($get_password);
        $data['user_type'] = 3;
        if($email) $data['email'] = $req_user_id;
        else if($mobile) $data['mobile'] = $req_user_id;
        $data['auth_code'] = mt_rand(100000, 999999);
        $data['verified'] = 0;
        $data['status'] = 0;

        $getLastId = ModificationController::save_content($obj, $data, 1);

        if($getLastId>0){
            $obj = new UserInfos;
            $userData = [];
            $userData['pass_code'] = EncryptionController::encode_content($get_password);
            $userData['user_id'] = $getLastId;
            $getCode = '';

            if($mobile){
                $sms_data['number'] = $data['mobile'];
                $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour activation code is " . $data['auth_code'] . "\n" . config('global.domain_url');
            
                $getCode = SmsController::sendTo($sms_data);
            }else if($email){

                $getData['html'] = "Dear,<br>Your account has been created.<br>Please activate your account. Your activation code is ".$data['auth_code']."<br><br>Thank you for join with us";                

                Mail::send(['html'=>'email_template'], $getData, function($message) use($data) {
                    $message->to($data['email'])->subject('Account activation info | '.config('global.domain_title'));
                    $message->from('no-reply@'.config('global.domain_url'), config('global.domain_title'));
                });
            }

            $getData = ModificationController::save_content($obj, $userData);
            
            DB::commit();
            
            return $getLastId;
        }else return '';
    }
    protected function SaveTheReqOrder($obj,$request,$user_id){
        DB::beginTransaction();

        try {
            $orderInfoData = $request['order_infos'];

            $orderInfoData = (gettype($orderInfoData)=='string'?json_decode($orderInfoData, true):$orderInfoData);
            
            $orderInfoData['order_id'] = time(); 
            $orderInfoData['customer_id'] = $user_id;
            $orderInfoData['total_payable'] = $orderInfoData['total_amount'] + $orderInfoData['vat_amount'] + $orderInfoData['discount'] + $orderInfoData['delivery_fee'];
            $orderInfoData['created_at'] = date('Y-m-d H:i:s');
            // return $orderInfoData;
            $get_order_id = ModificationController::save_content($obj, $orderInfoData, 1);

            if($get_order_id){
                /**
                 * Order item saved to DB
                 */                
                $orderItemInfoData = $request['cart_item_infos'];

                $orderItemInfoData = (gettype($orderItemInfoData)=='string'?json_decode($orderItemInfoData, true):$orderItemInfoData);

                $cols = ['order_id','created_at'];
                $def_cols_values = [$get_order_id, '"'.date('Y-m-d H:i:s').'"'];
                
                $valuesArr = [];
                foreach($orderItemInfoData[0] as $key => $val) array_push($cols, $key);

                $qry = 'INSERT INTO `order_items_infos`('.implode(',',$cols).') VALUES';
                foreach($orderItemInfoData as $key => $val){
                    $valStr = '(';
                    $arr = [];
                    foreach($cols as $ck => $cv){
                        if(isset($val[$cv])) array_push($arr, (gettype($val[$cv])=='string'?'"'.addslashes(htmlentities($val[$cv])).'"':$val[$cv]));
                        elseif(isset($def_cols_values[$ck])) array_push($arr, $def_cols_values[$ck]);
                    }
                    $valStr .= implode(',',$arr);
                    $valStr .= ')';
                    array_push($valuesArr,$valStr);
                }
                $qry.= implode(',',$valuesArr);

                try{
                    DB::select($qry);
                } catch (\Exception $e) {
                    DB::rollback();

                    return response()->json(['msg' => 'Order items  didn\'t submit properly', 'status' => false], 200);
                }
                
                /**
                 * Order prescription saved to DB
                 */
                $orderPrescriptionInfoData = $request['prescription_info'];
                if(!empty($orderPrescriptionInfoData)){
                    $qry = 'INSERT INTO `order_prescription_infos`(`order_ID`,`prescription_id`) VALUES';
                    
                    foreach($orderPrescriptionInfoData as $key => $val){
                        if($key>0) $qry .= ',';
                        $qry .= '('.$get_order_id.','.$val['id'].')';
                    }
                    
                    try{
                        DB::select($qry);
                    } catch (\Exception $e) {
                        DB::rollback();
    
                        return response()->json(['msg' => 'Order prescription info  didn\'t submit properly', 'status' => false], 200);
                    }
                }

                /**
                 * Order shipping and billing info saved to DB
                 */
                $shippingBillingInfoData = $request['shipping_billing_info'];

                $shippingBillingInfoData = (gettype($shippingBillingInfoData)=='string'?json_decode($shippingBillingInfoData, true):$shippingBillingInfoData);                

                $cols = ['order_id','created_at'];
                $def_cols_values = [$get_order_id, '"'.date('Y-m-d H:i:s').'"'];
                $valuesArr = [];
                foreach($shippingBillingInfoData as $key => $val){
                    if(isset($shippingBillingInfoData[$key])) array_push($cols, $key);
                }

                $qry = 'INSERT INTO `order_ship_bill_infos`('.implode(',',$cols).') VALUES';
                
                foreach($cols as $ck => $cv){
                    if(isset($shippingBillingInfoData[$cv])) array_push($valuesArr, (gettype($shippingBillingInfoData[$cv])=='string'?'"'.addslashes(htmlentities($shippingBillingInfoData[$cv])).'"':$shippingBillingInfoData[$cv]));
                    elseif(isset($def_cols_values[$ck])) array_push($valuesArr, $def_cols_values[$ck]);
                }
                
                $qry.= '(' . implode(',',$valuesArr) . ')';

                try{
                    DB::select($qry);
                } catch (\Exception $e) {
                    DB::rollback();

                    return response()->json(['msg' => 'Order shipping and billing info didn\'t submit properly', 'error' => $e->getMessage(), 'status' => false], 200);
                }
                
                /**
                 * Order timeline setup
                 */
                $qry = 'INSERT INTO `order_delivery_timelines`(`order_id`,`timeline_id`,`status`,`created_at`) VALUES('.$get_order_id.',1,0,"'.date('Y-m-d H:i:s').'")';
                try{
                    DB::select($qry);
                } catch (\Exception $e) {
                    DB::rollback();

                    return response()->json(['msg' => 'Order timeline  didn\'t submit properly', 'status' => false], 200);
                }
            }else{
                DB::rollback();

                return response()->json(['msg' => 'Order  didn\'t submit properly', 'status' => false], 200);
            }
            
            DB::commit();
            // all good

            // for success submit
            return response()->json(['status' => true], 200);
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'status' => false], 200);
        }
    }
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
    public function store(OrderInfo $obj, Request $request)
    {
        // return $request->all();

        // return ModificationController::save_content($obj, $request);
        if($request->has('new_customer_id')) $user_id = $this->CreateNewCustomer($request['new_customer_id']);
        elseif($request->has('customer_id')) $user_id = $request['customer_id'];
        else $user_id = '';
        
        if($user_id) return $this->SaveTheReqOrder($obj,$request,$user_id);
        else return response()->json(['msg' => 'Unregistered customer id', 'status' => false], 200);
    }
    
    public function storeMyOrder(OrderInfo $obj, Request $request){
        // return $request->all();

        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        return $this->SaveTheReqOrder($obj,$request,$user_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderInfo  $orderInfo
     * @return \Illuminate\Http\Response
     */
    public function show(OrderInfo $obj, Request $request)
    {
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;
        $srch_keyword = $request->has('keyword')?$request['keyword']:'';
        $own_result = $request->has('own_result')?$request['own_result']:'';
        $status = $request->has('status')?$request['status']:'';
        $date_range = $request->has('date_range')?explode(',',$request['date_range']):'';

        $getData = $obj::select('order_infos.*')                
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('order_id','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->when($date_range, function($q) use($date_range){
            return $q->whereBetween(DB::raw('DATE(created_at)'),$date_range);
        })->when($status, function($q) use($status){
            if($status==4) $status = 0;
            return $q->where('status',$status);
        })
        ->with(['OrderItemsInfo','OrderShipBillInfo','DeliveryTimelineInfo','DeliveryPersonInfo','CustomerInfo','PrescriptionInfo'])
        ->orderBy('order_infos.id','DESC')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return OrderInfoResource::collection($getData);
    }

    public function showMyOrders(OrderInfo $obj, Request $request){
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $type = $request->has('type')?$request['type']:0;

        if($limit>0) $getData = $obj::select('*')                
        ->where(['customer_id' => $user_id, 'status' => $type])
        ->with(['OrderItemsInfo','OrderShipBillInfo','DeliveryTimelineInfo','DeliveryPersonInfo','CustomerInfo'])
        ->orderBy('id','DESC')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return OrderInfoResource::collection($getData);
    }

    public function getOrders(OrderInfo $obj, Request $request){
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;
        $type = $request->has('type')?$request['type']:3;

        if($limit>0) $getData = $obj::select('order_infos.*')
        ->leftJoin('order_delivery_person_infos as odpi','odpi.order_id','=','order_infos.id')
        ->where(['odpi.delivery_person_id' => $user_id, 'order_infos.status' => $type?$type:3])        
        ->with(['OrderItemsInfo','OrderShipBillInfo','DeliveryTimelineInfo','CustomerInfo'])
        ->orderBy('order_infos.id','DESC')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return OrderInfoResource::collection($getData);
    }

    public function getReviews(OrderInfo $obj, Request $request){
        $user_id = Auth::id();

        // return $request->all();
        $limit = $request->has('limit')?$request['limit']:10;        

        if($limit>0) $getData = $obj::select('order_infos.*')
        ->leftJoin('order_delivery_person_infos as odpi','odpi.order_id','=','order_infos.id')
        ->where('odpi.delivery_person_id', $user_id)
        ->where('odpi.rating_points', '>', '0')
        ->where('order_infos.status', 1)       
        ->with(['OrderItemsInfo','OrderShipBillInfo','DeliveryTimelineInfo','CustomerInfo'])
        ->orderBy('order_infos.id','DESC')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return OrderInfoResource::collection($getData);
    }
    
    public function cancelOrder(Request $request){
        DB::beginTransaction();
        
        
        $updateQryStr = 'UPDATE `order_infos`
            SET cancel_reason="'.$request['cancel_reason'].'", status=2, updated_at = "'.date('Y-m-d H:i:s').'"
            WHERE `id` = '.$request['order_id'].'
            AND `status` = 0';
        
        try {
            DB::update($updateQryStr);
            
            DB::commit();
            
            $obj = new OrderDeliveryTimeline();
            $getData = $obj::select('*')
                ->where('order_id', $request['order_id'])
                ->orderBy('timeline_id', 'DESC')
                ->get();

            $obj = new OrderInfo();
            $getFcmTokenData = $obj::select('fti.*')
                ->leftJoin('fcm_token_infos as fti','fti.user_id','=','order_infos.customer_id')
                ->where('order_infos.id', $request['order_id'])
                ->get();
            
            return response()->json(['data' => $getData, 'fcm_token_data' => $getFcmTokenData, 'status' => true], 200);
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'status' => false], 200);
        }
    }
    
    public function updateOrder(Request $request){        
        DB::beginTransaction();

        $updateQryStr = 'UPDATE `order_delivery_timelines`
            SET status=1, updated_at = "'.date('Y-m-d H:i:s').'"
            WHERE `order_id` = '.$request['order_id'].'            
            AND `status` = 0';
        
        try {
            DB::select($updateQryStr);
            
            $insertQryStr = 'INSERT INTO `order_delivery_timelines`(`order_id`,`timeline_id`,`status`,`created_at`)
            VALUES('.$request['order_id'].','.$request['timeline_id'].','. ($request['final_submit']?1:0) .',"'.date('Y-m-d H:i:s').'")';            
            
            try {
                DB::select($insertQryStr);
                                
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return response()->json(['error', $e, 'msg' => 'Delivery timeline not update. try again', 'status' => false], 200);
            }

            if($request['final_submit']){
                try{
                    $updateQryStr = 'UPDATE `order_delivery_person_infos` SET `status` = 1 WHERE `order_id` = ' . $request['order_id'];

                    DB::select($updateQryStr);
                }catch(\Exception $e) {
                    DB::rollback();
                    // something went wrong
                    return response()->json(['error', $e, 'msg' => 'Delivery man\'s order status not update. try again', 'status' => false], 200);
                }

                try{
                    $updateQryStr = 'UPDATE `order_infos` SET `paid` = 1, `status` = 1 WHERE `id` = ' . $request['order_id'];

                    DB::select($updateQryStr);
                }catch(\Exception $e) {
                    DB::rollback();
                    // something went wrong
                    return response()->json(['error', $e, 'msg' => 'Order submit not update. try again', 'status' => false], 200);
                }
            }
            
            DB::commit();
            
            $obj = new OrderDeliveryTimeline();
            $getData = $obj::select('*')
                ->where('order_id', $request['order_id'])
                ->orderBy('timeline_id', 'DESC')
                ->get();

            $obj = new OrderInfo();
            $getFcmTokenData = $obj::select('fti.*')
                ->leftJoin('fcm_token_infos as fti','fti.user_id','=','order_infos.customer_id')
                ->where('order_infos.id', $request['order_id'])
                ->get();
            
            return response()->json(['data' => $getData, 'fcm_token_data' => $getFcmTokenData, 'status' => true], 200);
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            return response()->json(['error' => $e, 'status' => false], 200);
        }        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderInfo  $orderInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderInfo $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->with(['OrderItemsInfo','OrderShipBillInfo','DeliveryTimelineInfo'])
        ->first();

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderInfo  $orderInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $req_id)
    {
        // return $request;
        
        DB::beginTransaction();
        
        $total_amount = $request['total_amount'];
        $discount = $request['discount'];
        $delivery_fee = $request['delivery_fee'];
        $total_payable = $request['total_payable'];
        $vat_amount = $request['vat_amount'];
        $order_items_list = $request['order_items_info'];
        
        $updateQryStr = 'UPDATE `order_infos`
            SET total_amount='.$total_amount.', discount='.$discount.', delivery_fee='.$delivery_fee.', total_payable='.$total_payable.', vat_amount='.$vat_amount.', updated_at = "'.date('Y-m-d H:i:s').'"
            WHERE `id` = '.$req_id.'
            AND `status` = 0';
        
        try {
            DB::update($updateQryStr);
            
            $updateQryStr = 'UPDATE `order_items_infos` SET `qty` = CASE';
            
            $getIds = [];
            foreach($order_items_list as $key => $val){
                $updateQryStr .= '
                WHEN id = '.$val['id'].' THEN '.$val['qty'];
                array_push($getIds, $val['id']);
            }
            
            $updateQryStr .= '
            ELSE `qty` END
            WHERE id IN ('.implode(',',$getIds).')';
            
            try{
                
                DB::update($updateQryStr);
                
                $delQryStr = 'DELETE FROM `order_items_infos` WHERE order_id='.$req_id.' AND id NOT IN('.implode(',',$getIds).')';
                
                try{
                    DB::update($delQryStr);
                } catch (\Exception $e) {
                    DB::rollback();
                    // something went wrong
                    return response()->json(['error' => $e, 'msg' => 'Order item infos remove problem!!!', 'status' => false], 200);
                }
                
            } catch (\Exception $e) {
                DB::rollback();
                // something went wrong
                return response()->json(['error' => $e, 'msg' => 'Order item infos problem!!!', 'status' => false], 200);
            }
            
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
     * @param  \App\Models\OrderInfo  $orderInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderInfo $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }

    /**
     * Search Order Info
     */
    public function search(OrderInfo $obj)
    {
        // return request()->get('term');
        $getData = $obj::select('*')->where('order_id','LIKE','%'.request()->get('term').'%')
        ->take(request()->get('limit'))->get();

        return response()->json($getData, 200);
    }

    public function CurrentOrderStatusInfo($obj, $user_id){
        $getData = $obj::select('*')                
            ->where('customer_id', $user_id)
            ->where('status', 0)
            ->orderBy('id', 'DESC')            
            ->with(['OrderItemsInfo','OrderShipBillInfo','DeliveryTimelineInfo','DeliveryPersonInfo'])
            ->get();
        
        $orderShipBillInfoIndex = 'order_ship_bill_info';
        
        foreach($getData as $index => $data){
            foreach($data->toArray() as $key => $val){                
                if($key==$orderShipBillInfoIndex){                    
                    $getData[$index]['shipping_address'] = html_entity_decode($val['shipping_address']);
                    $getData[$index]['billing_address'] = html_entity_decode($val['billing_address']);
                }
            }            
        }
        
        return $getData;
    }

    // public function liveOrderTimelineStatus(OrderInfo $obj, Request $request){
    //     header('Access-Control-Allow-Origin: *');
    //     header('Content-Type: text/event-stream');        
    //     header('Cache-Control: no-cache');
                
    //     if($request['user_id']>0){
    //         $getData = $this->CurrentOrderStatusInfo($obj,$request['user_id']);
    //         echo "data: {$getData}\n\n";
    //     }else echo "data: None\n\n";

    //     // $time = date('r');
    //     // echo "data: The server time is: {$time} user_id: {$request['user_id']}\n\n";        
    //     flush();
    // }

    public function liveOrderTimelineStatus(OrderInfo $obj){
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);        

        try {
            $getData = $this->CurrentOrderStatusInfo($obj,$user_id);
            
            return response()->json(['data' => $getData,'status' => true], 200);
        } catch (\Exception $e) {           
            // something went wrong
            return response()->json(['error' => $e, 'status' => false], 200);
        }
    }
}
