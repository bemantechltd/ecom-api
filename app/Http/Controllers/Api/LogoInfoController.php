<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use DB;
use Auth;

class LogoInfoController extends Controller
{    
    protected $target_path = 'images/logoz';
    
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
    public function store(Request $request){
        
        /**
         * LOGO CONVERT
         */
        $logo = null;
        if($request['logo']){
            $logo = ModificationController::base64ToImage($request['logo'],$this->target_path);
            
            if($logo){
                if($request['exist_logo'] && ($request['exist_logo']!==$logo)){            
                    $content_path = config('global.logoz_base_path').'/'.$request['exist_logo'];
                    
                    if(file_exists($content_path)) unlink($content_path);            
                }
            }else $logo = $request['exist_logo'];
            
            unset($request['exist_logo']);
        }
        
        /**
         * DARK LOGO CONVERT
         */
        $dark_logo = null;
        if($request['dark_logo']){
            $dark_logo = ModificationController::base64ToImage($request['dark_logo'],$this->target_path);
            
            if($dark_logo){
                if($request['exist_dark_logo'] && ($request['exist_dark_logo']!==$dark_logo)){            
                    $content_path = config('global.logoz_base_path').'/'.$request['exist_dark_logo'];
                    
                    if(file_exists($content_path)) unlink($content_path);            
                }
            }else $dark_logo = $request['exist_dark_logo'];
            
            unset($request['exist_dark_logo']);
        }
        
        
        /**
         * LOGO CONVERT
         */
        $watermark_logo = null;
        if($request['watermark_logo']){
            $watermark_logo = ModificationController::base64ToImage($request['watermark_logo'],$this->target_path);
            
            if($watermark_logo){
                if($request['exist_watermark_logo'] && ($request['exist_watermark_logo']!==$watermark_logo)){            
                    $content_path = config('global.logoz_base_path').'/'.$request['exist_watermark_logo'];
                    
                    if(file_exists($content_path)) unlink($content_path);            
                }
            }else $watermark_logo = $request['exist_watermark_logo'];
            
            unset($request['exist_watermark_logo']);
        }
        
        /**
         * LOGO CONVERT
         */
        $favicon = null;
        if($request['favicon']){
            $favicon = ModificationController::base64ToImage($request['favicon'],$this->target_path);
            
            if($favicon){
                if($request['exist_favicon'] && ($request['exist_favicon']!==$favicon)){            
                    $content_path = config('global.logoz_base_path').'/'.$request['exist_favicon'];
                    
                    if(file_exists($content_path)) unlink($content_path);            
                }
            }else $favicon = $request['exist_favicon'];
            
            unset($request['exist_favicon']);
        }
        
        $getArrObj = (object)[
            // Logo info data
            'logo' => config('global.logoz_base_url').'/'.$logo,
            'exist_logo' => $logo,
            'dark_logo' => config('global.logoz_base_url').'/'.$dark_logo,
            'exist_dark_logo' => $dark_logo,
            'watermark_logo' => config('global.logoz_base_url').'/'.$watermark_logo,
            'exist_watermark_logo' => $watermark_logo,
            'favicon' => config('global.logoz_base_url').'/'.$favicon,
            'exist_favicon' => $favicon
        ];
        
        // return response()->json($getArrObj);
        Storage::disk('public')->put('json/logo-info.json', response()->json($getArrObj));
        
        return response()->json(['status' => true], 200);
    }
    
    public function get(){
        $path = storage_path('app/public') . "/json/logo-info.json";
        if(!file_exists($path)){
            $getArrObj = (object)[
                // Logo info data
                'logo' => null,
                'exist_logo' => null,
                'dark_logo' => null,
                'exist_dark_logo' => null,
                'watermark_logo' => null,
                'exist_watermark_logo' => null,
                'favicon' => null,
                'exist_favicon' => null
            ];
            
            Storage::disk('public')->put('json/logo-info.json', response()->json($getArrObj));
        }
        
        $getContents = file_get_contents($path);
        
        preg_match("/\{(.*)\}/s", $getContents, $matches);

        $data = json_decode($matches[0]);
        
        return response()->json(['data' => $data, 'status' => true], 200);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(){
        /**
         * Get site logo info
         */
        $path = storage_path('app/public') . "/json/logo-info.json";
        $getContents = file_get_contents($path);
        
        preg_match("/\{(.*)\}/s", $getContents, $matches);

        $data = json_decode($matches[0]);
        
        return response()->json(['data' => $data], 200);
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