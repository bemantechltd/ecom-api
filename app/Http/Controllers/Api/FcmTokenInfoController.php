<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;

use App\Models\FcmTokenInfo;
use Illuminate\Http\Request;

use Auth;
use DB;

class FcmTokenInfoController extends Controller
{
    protected function getUserIP(){
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
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
    public function store(FcmTokenInfo $obj, Request $request)
    {
        // return $request->all();        

        // $ip_addr = $this->getUserIP();
        // $user_agent_info = $_SERVER['HTTP_USER_AGENT'];
        $cur_token = $request['cur_token'];
        $app_platform = $request['app_platform'];
        $token = $request['token'];
        $user_id = $request['user_id'];

        if($cur_token){
            // $getData = $obj::select('id')
            // ->where('ip_addr',$ip_addr)
            // ->where('user_agent_info', $user_agent_info)
            // ->first();

            // return response()->json(['data' => $getData], 200);

            if($user_id) $qryStr = 'DELETE FROM `fcm_token_infos` WHERE `user_id` ='.$user_id.' AND `app_platform` = "'.$app_platform.'"';
            else $qryStr = 'DELETE FROM `fcm_token_infos` WHERE `token` = "'.$cur_token.'"';

            try{
                $result = DB::select($qryStr);                
            }catch(\Exception $e){
                return response()->json(['msg' => $e,'status' => false], 200);
            }
        }

        // if($getData){
        //     $qryStr = 'UPDATE `fcm_token_infos` SET `user_id` = '.($user_id?$user_id:'NULL').', `user_agent_info` = "'.$user_agent_info.'", `ip_addr` = "'.$ip_addr.'", `token` = "'.$token.'", `updated_at` = "'.date('Y-m-d H:i:s').'" WHERE `id` = '.$getData['id'];            
        // }else{
            // $qryStr = 'INSERT INTO `fcm_token_infos`(`user_id`,`user_agent_info`,`ip_addr`,`token`,`created_at`) VALUES('.($user_id?$user_id:'NULL').',"'.$user_agent_info.'","'.$ip_addr.'","'.$token.'","'.date('Y-m-d H:i:s').'")';                        
        // }

        $qryStr = 'INSERT INTO `fcm_token_infos`(`user_id`,`app_platform`,`token`,`created_at`) VALUES('.($user_id?$user_id:'NULL').',"'.$app_platform.'","'.$token.'","'.date('Y-m-d H:i:s').'")';
        
        try{
            $result = DB::select($qryStr);
            return response()->json(['status' => true], 200);
        }catch(\Exception $e){
            return response()->json(['msg' => $e,'status' => false], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FcmTokenInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function show(FcmTokenInfo $obj)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FcmTokenInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function edit(FcmTokenInfo $obj)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FcmTokenInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FcmTokenInfo $obj)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FcmTokenInfo  $obj
     * @return \Illuminate\Http\Response
     */
    public function destroy(FcmTokenInfo $obj)
    {
        //
    }
}
