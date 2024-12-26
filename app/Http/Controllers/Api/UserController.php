<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\ModificationController as ModificationController;
use App\Http\Controllers\Common\EncryptionController as EncryptionController;
use App\Http\Controllers\Common\SmsController as SmsController;
use App\Http\Resources\AdminUserListCollection as AdminUserListResource;
use App\Http\Resources\ManageAddressCollection as ManageAddressResource;

use App\User;
use App\Models\UserRoles;
use App\Models\UserInfos;
use App\Models\ManageAddress;
use Illuminate\Http\Request;
use Session;
use Auth;
use DB;
use Hash;
use Mail;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{    
    protected $mobile_pattern = "/^[\+]?[0-9]{1,3}?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,9}$/";

    public function AuthCodeGenerator(User $obj, Request $request){
        $obj = $obj->find($request['user_id']);
        if(!empty($obj)){
            $obj->auth_code = mt_rand(100000, 999999);            
            if($obj->update()){

                $sms_data = [];
                $sms_data['number'] = $obj->mobile;
                $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour activation code is " . $obj->auth_code . "\n" . config('global.domain_url');
                $getCode = SmsController::sendTo($sms_data);

                return response()->json(['sms_status' => SmsController::smsStatus($getCode), 'status' => 1] , 200);
            }else  return response()->json(['status' => 5] , 200);
        }else return response()->json(['status' => 0] , 200);
    }

    public function ForgotPassword(User $obj, Request $request){
        $email = false; $mobile = false;
        if(filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)){
            $email = true;
            $getData = $obj::select('id')
            ->where('email',trim($request['login_id']))
            ->whereNotNull('email')
            ->with('UserInfo')
            ->first();            
        }else if(preg_match($this->mobile_pattern, $request['login_id'])){
            $mobile = true;
            $getData = $obj::select('id')
            ->where('mobile','LIKE','%'.str_replace('+880','',$request['login_id']))
            ->whereNotNull('mobile')
            ->with('UserInfo')
            ->first();
        }else{
            return response()->json(['msg' => 'Invalid email or mobile number','status' => 0] , 200);
        }

        // return $getData;

        if(!empty($getData)){
            $getPassword = EncryptionController::decode_content($getData->UserInfo->pass_code);
            if($email){                
                $data['html'] = "Dear ". ($getData->UserInfo?$getData->UserInfo->full_name:'') .",<br>Your forgot password request has been accepted. Your current password is ".$getPassword.".<br><br>Thank you for join with us";                

                Mail::send(['html'=>'email_template'], $data, function($message) use($request) {
                    $message->to($request['login_id'])->subject('Forgot password request | '.config('global.domain_title'));
                    $message->from('no-reply@'.config('global.domain_url'), config('global.domain_title'));
                });

                return response()->json(['login_type' => 'email','status' => 1], 200);
            }else if($mobile){

                $sms_data['number'] = $request['login_id'];
                $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour current password is ". $getPassword . "\n" . config('global.domain_url');
            
                $getCode = SmsController::sendTo($sms_data);

                return response()->json(['login_type' => 'mobile','sms_response' => $getCode,'status' => 1], 200);
            }
        }return response()->json(['msg' => 'Invalid email or mobile number','status' => 0] , 200);
    }

    public function UserActivation(User $obj, Request $request){
        // return $request->all();

        $obj = $obj->find($request['user_id']);

        if(!empty($obj)){
            if($obj->verified){                
                return response()->json(['status' => 3] , 200);
            }elseif(!$obj->verified && $obj->auth_code===$request['auth_code']){
                $obj->auth_code = '';
                $obj->mobile_verified_at = date('Y-m-d H:i:s');
                $obj->verified = 1;
                $obj->status = 1;
                if($obj->update()) return response()->json(['status' => 1] , 200);
                else  return response()->json(['status' => 5] , 200);
            }else return response()->json(['status' => 2] , 200);
        }else return response()->json(['status' => 0] , 200);
    }

    public function UserInfo(Request $request){
        // return Auth::user();
        return response()->json(['user' => Auth::user()]);
    }

    public function SocialUserInfo(Request $request){
        $data = $request['data'];
        $getUser = User::where('email',$data['email'])->first();
        // return $request->all();

        // set default password
        $get_password = '123456';

        if(empty($getUser)){
            $User = new User;
            $User->name = $data['name'];
            $User->email = isset($data['email'])?$data['email']:null;
            $User->password = bcrypt($get_password);
            $User->save();            

            return response()->json(['status' => true , 'user' => $User] , 200);
        } else return response()->json(['status' => true , 'user' => $getUser] , 200);
    }

    public function Login(Request $request){
        // return $request->all();
        // return Auth::user();
        // return $request->session()->all();

        if (Auth::attempt(['email' => $request['login_id'], 'password' => $request['password']])) {

            $getUser = User::where('email', $request['login_id'])->with('userInfo')->first();
            $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
            
        }

        if(isset($getUser)){
            if($getUser->verified==0){
                return response()->json(['status' => 2] , 200);
            }else{
                return response()->json(['status' => 1, 'user_info' => $getUser] , 200);
            }
            
       }else{
           return response()->json(['status' => 0] , 200);
       }
    }

    public function AdminLogin(User $obj, Request $request){
        // return $request->all();
        // return Auth::user();
        // return $request->session()->all();
        if (Auth::attempt(['email' => $request['login_id'], 'password' => $request['password']])) {
            $getUser = $obj::where('id', Auth::id())->with(['UserInfo','RoleInfo'])->first();            
        }

        if(isset($getUser)){
            if($getUser->verified==0){
                return response()->json(['status' => 2] , 200);
            }else{
                $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
                return response()->json(['status' => 1, 'user_info' => $getUser] , 200);
            }
            
       }else{
           return response()->json(['status' => 0] , 200);
       }
    }
    
    public function SocialUserLogin(User $obj, Request $data){
        $loginData = []; $email = ''; $mobile = '';

        if(filter_var($data['login_id'], FILTER_VALIDATE_EMAIL)){
            $loginData = ['email' => $data['login_id']];
            $email = $data['login_id'];
        }else if(preg_match($this->mobile_pattern, $data['login_id'])){
            $loginData = ['mobile' => $data['login_id']];
            $mobile = $data['login_id'];
        }else{
            return response()->json(['msg' => 'Invalid email or mobile number','status' => 0] , 200);
        }

        $User = $obj::select('ui.pass_code')
        ->join('user_infos AS ui','ui.user_id','=','users.id')
        ->where($loginData)
        ->first();

        // set default password
        $get_password = str_random(8); // mt_rand(100000, 999999);

        DB::beginTransaction();
        
        if(empty($User)){
            $User = new User;
            if($email) $User->email = isset($email)?$email:null;
            elseif($mobile) $User->mobile = isset($mobile)?$mobile:null;
            $User->password = bcrypt($get_password);
            $User->user_type = 3;
            $User->verified = 1;
            $User->status = 1;
            
            if($User->save()){
                $getUserId = $User->id;
                
                /**
                 * User Info data insert
                 */
                $UserInfo = new UserInfos;
                $UserInfo->user_id = $getUserId;
                $UserInfo->full_name = $data['full_name'];
                $UserInfo->social_id_info = json_encode($data['social_id_info']);
                $UserInfo->pass_code = EncryptionController::encode_content($get_password);
                $UserInfo->photo = $data['photo'];
                $UserInfo->created_by = $getUserId;
                $UserInfo->save();
            }
            
            $loginData['password'] = $get_password;
        }else{
            $loginData['password'] = EncryptionController::decode_content($User->pass_code);
        }
        
        $getUser = [];
        if (Auth::attempt($loginData)) {
            $getUser = $obj::where('id', Auth::id())->with(['UserInfo','RoleInfo'])->first();            
        }
        
        // return response()->json(['get_user' => $getUser], 200);

        if(isset($getUser)){
            DB::commit();
            $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
            return response()->json(['user_info' => $getUser, 'status' => 1] , 200);
       }else{
           DB::rollback();
           return response()->json(['status' => 0] , 200);
       }
    }

    public function UserLogin(User $obj, Request $request){
        // return $request->all();
        // return Auth::user();
        // return $request->session()->all();        

        $getUser = '';
        $loginData = [];

        if(filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)){
            $loginData = ['email' => $request['login_id'], 'password' => $request['password']];
        }else if(preg_match($this->mobile_pattern, $request['login_id'])){
            $loginData = ['mobile' => $request['login_id'], 'password' => $request['password']];
        }else{
            return response()->json(['msg' => 'Invalid email or mobile number','status' => 0] , 200);
        }
        
        
        if (Auth::attempt($loginData)) {                
            $getUser = $obj::where('id', Auth::id())->with(['UserInfo','RoleInfo'])->first();            
        }

        if($getUser != ''){
            // return $getUser;
            // if($getUser->mobile==NULL || $getUser->user_type!==3){
            //     return response()->json(['status' => 0] , 200);
            // }else
            if($getUser->verified==0){
                return response()->json(['user_info' => $getUser,'status' => 2] , 200);
            }else{
                $getUser['token'] =  $getUser->createToken('MyApp')-> accessToken;
                return response()->json(['user_info' => $getUser, 'status' => 1] , 200);
            }
            
       }else{
           return response()->json(['status' => 0] , 200);
       }
    }

    public function UserSignup(User $obj, Request $request) {        
        
        $email = false; $mobile = false;
        if(filter_var($request['login_id'], FILTER_VALIDATE_EMAIL)){
            $email = true;
            $getData = $obj::select('*')
            ->where('email',trim($request['login_id']))
            ->whereNotNull('email')
            ->first();
        }else if(preg_match($this->mobile_pattern, $request['login_id'])){
            $mobile = true;
            $getData = $obj::select('*')
            ->where('mobile','LIKE','%'.str_replace('+880','',$request['login_id']))
            ->whereNotNull('mobile')
            ->first();
        }else{
            return response()->json(['msg' => 'Invalid email or mobile number', 'status' => 0] , 200);
        }        

        if(!empty($getData)) {
            return response()->json(['registered' => true, 'req_id_type' => $email?'email':'mobile', 'data' => $getData] , 200);
        }

        $data = [];        
        $data['password'] = bcrypt($request['password']);
        $data['user_type'] = 3;
        if($email) $data['email'] = $request['login_id'];
        else if($mobile) $data['mobile'] = $request['login_id'];
        $data['auth_code'] = mt_rand(100000, 999999);
        $data['verified'] = 0;
        $data['status'] = 0;

        $getLastId = ModificationController::save_content($obj, $data, 1);

        if($getLastId>0){
            $obj = new UserInfos;
            $userData = [];
            $userData = $request['user_info'];
            if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
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

            $data = [
                'user_id'       => $getLastId,
                'sms_status'    => $getCode?SmsController::smsStatus($getCode):'',
                'status'        => true,
                'code'          => '200',
                'message'       => '<i class="fa fa-check-circle"></i> Data has been saved successfully.',
            ];
        } else {
            $data = [
                'data'          => $getLastId,                
                'status'    => false,
                'code'      => '200',
                'message'   => '<i class="fa fa-check-circle"></i> Data has not been saved.',
            ];
        }
        
        return response()->json($data, 200);
    }

    public function Logout(Request $request) {
        try{
            Auth::logout();
            return response()->json(['status' => true] , 200);
        }catch(Exception $e){
            return $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(User $obj, Request $request)
    {
        $messages = [
            'required' => 'This field is required',
            'unique' => 'This field is unique'
        ];

        $data = [];        
        $data['password'] = bcrypt($request['password']);
        if($request->has('user_type')) $data['user_type'] = $request['user_type'];
        $data['email'] = $request['email'];
        $data['auth_code'] = str_random(12);
        $data['verified'] = $request['verified'];
        $data['status'] = $request['status'];

        $getLastId = ModificationController::save_content($obj, $data, 1);            

        /**
         * User role save
         */
        DB::select('INSERT INTO `user_roles` (`user_id`,`role_id`) VALUES('.$getLastId.','.$request['role_info']['role_id'].')');

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

        if($limit>0) $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->where('user_type',1)
        ->with(['UserInfo','RoleInfo'])
        ->paginate($limit);
        
        else $getData = $obj::select('*')
        ->when($srch_keyword, function($q) use($srch_keyword){
            return $q->where('email','LIKE',"%$srch_keyword%");
        })->when($own_result, function($q) use($user_id){
            return $q->where('created_by',$user_id);
        })->where('user_type',1)
        ->with(['UserInfo','RoleInfo'])
        ->get();

        // return response()->json($getData, 200);
        return AdminUserListResource::collection($getData);
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
        ->with(['UserInfo','RoleInfo'])
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
        $data['status'] = $request['status'];
        $data['verified'] = $request['verified'];

        ModificationController::update_content($obj, $data, $req_id);

        /**
         * User Role delete        
         */
        DB::select('DELETE FROM `user_roles` WHERE `user_id`='.$req_id);

        /**
         * User role save
         */        
        DB::select('INSERT INTO `user_roles` (`user_id`,`role_id`) VALUES('.$req_id.','.$request['role_info']['role_id'].')');

        $obj = new UserInfos;
        $userData = [];
        $userData = $request['user_info'];
        if($request->has('password')) $userData['pass_code'] = EncryptionController::encode_content($request['password']);
        $userData['user_id'] = $req_id;

        return ModificationController::update_content($obj, $userData, $req_id, 'user_id');
        // return $request->all();
    }

    public function updateProfile(User $obj, Request $request){
        // return $request->all();
        $user_id = Auth::id();
        
        if(!$user_id) return response()->json(['msg' => 'Invalid credential', 'status' => false], 200);
        else $req_id = $user_id;

        $data = [];
        $data['auth_code'] = '';
        if($request['email']) $data['email'] = $request['email'];
        if($request['mobile']) $data['mobile'] = $request['mobile'];
        
        // return $request->all();

        if(!empty($data)) ModificationController::update_content($obj, $data, $req_id);
        else return response()->json(['status' => 2], 200);

        $obj = new UserInfos;
        $userData = [];
                
        $userData = $request['user_info'];
        if(gettype($userData)=='string') $userData = json_decode($userData, true);
        // return response()->json(['getDataType' => gettype($userData),'userInfo' => $userData], 200);

        if($request['full_name']!=='') $userData['full_name'] = $request['full_name'];
        if($request['dob']!=='') $userData['dob'] = date('Y-m-d', strtotime($request['dob']));
        if($request['gender_id']!=='') $userData['gender_id'] = $request['gender_id'];
        if($request['blood_group']!=='') $userData['blood_group'] = $request['blood_group'];

        // return $userData;

        return ModificationController::update_content($obj, $userData, $req_id, 'user_id');
    }
    
    public function changePassword(User $obj, Request $request){
        $user_id = Auth::id();
        
        if(!$user_id) return response()->json(['msg' => 'Invalid credential', 'status' => false], 200);
        else $req_id = $user_id;

        $getData = $obj->find($req_id);

        if (Hash::check($request['current_password'], $getData->password)) {
            $data = [];
            $data['password'] = bcrypt($request['new_password']);
            ModificationController::update_content($obj, $data, $req_id);

            $obj = new UserInfos;
            $userData = [];
            $userData['pass_code'] = EncryptionController::encode_content($request['new_password']);            

            return ModificationController::update_content($obj, $userData, $req_id, 'user_id');
        }else{
            return response()->json(['msg' => 'Current password didn\'t match', 'status' => false], 200);
        }
    }
    
    public function accountActivate(User $obj, Request $request){
        $user_id = Auth::id();
        
        if(!$user_id) return response()->json(['msg' => 'Invalid credential', 'status' => false], 200);
        else $req_id = $user_id;

        $getData = $obj::select('id')->where(['id' => $req_id, 'auth_code' => $request['value']])->first();
        if(isset($getData->id)){
            return response()->json($getData, 200);
        }else{
            return response()->json(['msg' => 'Invalid activation code', 'status' => 0] , 200);
        }
    }
    
    public function checkUser(User $obj, $req_id){
        $user_id = Auth::id();
        
        if(!$user_id) return response()->json(['msg' => 'Invalid authorization', 'status' => false], 200);
        
        $email = false; $mobile = false;
        if(filter_var($req_id, FILTER_VALIDATE_EMAIL)){
            $email = true;
            $getData = $obj::select('*')
            ->where('email',trim($req_id))
            ->whereNotNull('email')
            ->with('UserInfo')
            ->first();
        }else if(preg_match($this->mobile_pattern, $req_id)){
            $mobile = true;
            $getData = $obj::select('*')
            ->where('mobile','LIKE','%'.str_replace('+880','',$req_id))
            ->whereNotNull('mobile')
            ->with('UserInfo')
            ->first();
        }else{
            return response()->json(['msg' => 'Invalid email or mobile number', 'status' => 0] , 200);
        }
        
        if(!empty($getData)){
            $getAddressData = ManageAddress::select('*')                
            ->where('user_id',$getData->id)        
            ->where(function($q) {
                $q->where('default_shipping_address', 1)
                ->orWhere('default_billing_address', 1);
            })
            ->with(['RegionInfo','CityInfo','AreaInfo'])
            ->get();
        }
        
        return response()->json(['data' => $getData, 'default_addresses' => isset($getAddressData)?ManageAddressResource::collection($getAddressData):''], 200);
    }
    
    public function checkAvailability(User $obj, Request $request){
        $user_id = Auth::id();
        
        if(!$user_id) return response()->json(['msg' => 'Invalid credential', 'status' => false], 200);
        else $req_id = $user_id;

        $getData = $obj::select('id')->where($request['column'], $request['value'])->first();
        
        if(empty($getData)){
            $data = [];
            $data['auth_code'] = mt_rand(100000, 999999);
    
            ModificationController::update_content($obj, $data, $req_id);
            
            $email = false; $mobile = false;
            if(filter_var($request['value'], FILTER_VALIDATE_EMAIL)){
                $email = true; $data['email'] = $request['value'];
                
                $getHtmlData['html'] = "Dear,<br>Your activation code is ".$data['auth_code']."<br><br>Thank you for join with us";                

                Mail::send(['html'=>'email_template'], $getHtmlData, function($message) use($data) {
                    $message->to($data['email'])->subject('New E-mail activation info | '.config('global.domain_title'));
                    $message->from('no-reply@'.config('global.domain_url'), config('global.domain_title'));
                });
            }else if(preg_match($this->mobile_pattern, $request['value'])){
                $mobile = true; $data['mobile'] = $request['value'];
                
                $sms_data['number'] = $data['mobile'];
                $sms_data['msg'] = 'Welcome to ' . config('global.domain_title') . ",\nYour activation code is " . $data['auth_code'] . "\n" . config('global.domain_url');
            
                $getCode = SmsController::sendTo($sms_data);
            }else{
                return response()->json(['msg' => 'Invalid email or mobile number', 'status' => 0] , 200);
            } 
        }

        return response()->json($getData, 200);
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