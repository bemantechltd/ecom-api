<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\OrderInfo;
use App\Models\OrderDeliveryPersonInfo;
use Illuminate\Http\Request;

use Auth;
use DB;

class DataReportController extends Controller
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
    
    public function dataEntryOperatorReport(Request $request){
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        $getData = (Object) [];
        $req_date = $request->has('req_date')?explode(',',$request['req_date']):[date('Y-m-d').','.date('Y-m-d')];
        
        $getTotalInsertByOperator = Products::select('created_by',DB::raw('COUNT(id) AS total_insert'))
        ->whereBetween(DB::raw('DATE(created_at)'),$req_date)
        ->whereNotNull('created_by')
        ->with('OperatorInfo')
        ->groupBy('created_by')
        ->get();
        
        $getData->total_insert_by_opt = $getTotalInsertByOperator;
        
        $getTotalUpdateByOperator = Products::select('updated_by AS created_by',DB::raw('COUNT(id) AS total_update'))
        ->whereBetween(DB::raw('DATE(updated_at)'),$req_date)
        ->whereNotNull('updated_by')
        ->with('OperatorInfo')
        ->groupBy('updated_by')
        ->get();
        
        $getData->total_update_by_opt = $getTotalUpdateByOperator;
        
        return response()->json(['data' => $getData, 'status' => true], 200);
    }

    public function myOrderHistory(){
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        $getData = (Object) [];

        $getTotalOrders = OrderInfo::select(DB::raw('COUNT(id) AS total_orders'))        
        ->where('customer_id', $user_id)
        ->groupBy('customer_id')
        ->first();

        if(isset($getTotalOrders->total_orders)) $getData->total_orders = $getTotalOrders->total_orders;
        else $getData->total_orders = 0;

        $getTotalCancelOrders = OrderInfo::select(DB::raw('COUNT(id) AS total_cancel_orders'))        
        ->where('customer_id', $user_id)
        ->where('status', 2)
        ->groupBy('customer_id')
        ->first();

        if(isset($getTotalCancelOrders->total_cancel_orders)) $getData->total_cancel_orders = $getTotalCancelOrders->total_cancel_orders;
        else $getData->total_cancel_orders = 0;

        $getTotalExpenses = OrderInfo::select(DB::raw('SUM(total_payable) AS total_expenses'))        
        ->where('customer_id', $user_id)
        ->where('paid', 1)
        ->groupBy('customer_id')
        ->first();

        if(isset($getTotalExpenses->total_expenses)) $getData->total_expenses = $getTotalExpenses->total_expenses;
        else $getData->total_expenses = 0;

        return response()->json(['data' => $getData, 'status' => true], 200);
    }

    public function DeliveryManOrderHistory(){
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        $getData = (Object) [];

        $getTotalOrders = OrderDeliveryPersonInfo::select(DB::raw('COUNT(id) AS total_orders'))        
        ->where('delivery_person_id', $user_id)
        ->groupBy('delivery_person_id')
        ->first();

        if(isset($getTotalOrders->total_orders)) $getData->total_orders = $getTotalOrders->total_orders;
        else $getData->total_orders = 0;

        $getTotalDeliveredOrders = OrderDeliveryPersonInfo::select(DB::raw('COUNT(id) AS total_delivered_orders'))        
        ->where('delivery_person_id', $user_id)
        ->where('status', 1)
        ->groupBy('delivery_person_id')
        ->first();

        if(isset($getTotalDeliveredOrders->total_delivered_orders)) $getData->total_delivered_orders = $getTotalDeliveredOrders->total_delivered_orders;
        else $getData->total_delivered_orders = 0;

        $getTotalCashOnDelivery = OrderDeliveryPersonInfo::select(DB::raw('COUNT(order_delivery_person_infos.id) AS total_cash_on_delivery'))
        ->leftJoin('order_infos','order_infos.id','=','order_delivery_person_infos.order_id')
        ->where('order_delivery_person_infos.delivery_person_id', $user_id)        
        ->where('order_infos.choose_payment_type', 1)
        ->where('order_infos.paid', 1)
        ->groupBy('order_delivery_person_infos.delivery_person_id')
        ->first();

        if(isset($getTotalCashOnDelivery->total_cash_on_delivery)) $getData->total_cash_on_delivery = $getTotalCashOnDelivery->total_cash_on_delivery;
        else $getData->total_cash_on_delivery = 0;

        $getTotalDigitalPayment = OrderDeliveryPersonInfo::select(DB::raw('COUNT(order_delivery_person_infos.id) AS total_digital_payment_delivery'))
        ->leftJoin('order_infos','order_infos.id','=','order_delivery_person_infos.order_id')
        ->where('order_delivery_person_infos.delivery_person_id', $user_id)        
        ->where('order_infos.choose_payment_type', 2)
        ->where('order_infos.paid', 1)
        ->groupBy('order_delivery_person_infos.delivery_person_id')
        ->first();

        if(isset($getTotalDigitalPayment->total_digital_payment_delivery)) $getData->total_cash_on_delivery = $getTotalDigitalPayment->total_digital_payment_delivery;
        else $getData->total_digital_payment_delivery = 0;

        return response()->json(['data' => $getData, 'status' => true], 200);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderInfo  $OrderInfo
     * @return \Illuminate\Http\Response
     */
    public function show(OrderInfo $obj)
    {        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderInfo  $OrderInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderInfo $OrderInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderInfo  $OrderInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderInfo $OrderInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderInfo  $OrderInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderInfo $OrderInfo)
    {
        //
    }
}
