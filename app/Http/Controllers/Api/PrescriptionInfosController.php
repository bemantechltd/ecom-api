<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Resources\PrescriptionInfoCollection as PrescriptionInfoResource;

use App\Models\PrescriptionInfos;
use Illuminate\Http\Request;

use DB;
use Auth;

class PrescriptionInfosController extends Controller
{
    private $prescription_folder = 'images/prescriptions';
    
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
    public function store(PrescriptionInfos  $obj, Request $request)
    {
        // return $request->all();

        return ModificationController::save_content($obj, $request);
    }

    public function storeMyPrescription(PrescriptionInfos  $obj, Request $request)
    {
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        DB::beginTransaction();

        try {
            $request['created_at'] = date('Y-m-d H:i:s');

            if($request['file_content']<>''){
                /**
                 * IMAGE CONVERT
                 */
                $target_path = $this->prescription_folder.'/'.$user_id;
                $request['user_id'] = $user_id;
                $request['file_name'] = ModificationController::base64ToImage($request['file_content'],$target_path);

                unset($request['file_content'],$request['exist_content']);
            }

            $get_order_id = ModificationController::save_content($obj, $request, 1);

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
     * Display the specified resource.
     *
     * @param  \App\Models\PrescriptionInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(PrescriptionInfos $obj, Request $request)
    {
        //        
    }

    public function showMyPrescriptions(PrescriptionInfos $obj, Request $request)
    {
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        // return $request->all();
        $limit = $request['limit']>0?$request['limit']:10;

        $getData = $obj::select('*')                
        ->where('user_id',$user_id)        
        ->orderBy('id','DESC')
        ->paginate($limit);

        // return response()->json($getData, 200);
        return PrescriptionInfoResource::collection($getData);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PrescriptionInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(PrescriptionInfos $obj, $id)
    {
        $getData = $obj::select('*')
        ->where('id',$id)
        ->first();
        
        $getData->file_content = $getData->file_name?config('global.prescription_base_url').'/'.$getData->user_id.'/'.$getData->file_name:null;

        return response()->json($getData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PrescriptionInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PrescriptionInfos $obj, $req_id)
    {
        $user_id = Auth::id();

        if(!$user_id) return response()->json(['msg' => 'Unauthorized','status' => false], 200);

        /**
         * IMAGE CONVERT
         */
        $target_path = $this->prescription_folder.'/'.$user_id;
        $file_name = ModificationController::base64ToImage($request['file_content'],$target_path);

        if($file_name) $request['file_name'] = $file_name;
        if($request['file_content']){
            if($request['exist_content'] && ($request['exist_content']!==$request['file_name'])){            
                $content_path = config('global.prescription_base_path').'/'.$user_id.'/'.$request['exist_content'];                
                
                if(file_exists($content_path)) unlink($content_path);            
            }
        }

        unset($request['exist_content'],$request['file_content']);

        return ModificationController::update_content($obj, $request, $req_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PrescriptionInfos  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(PrescriptionInfos $obj, $id)
    {
        $geResult = $obj::find($id)->delete();

        return response()->json($geResult, 200);
    }
}
