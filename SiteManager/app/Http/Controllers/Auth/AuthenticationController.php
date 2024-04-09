<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\SiteManagerWallet;
use Illuminate\Http\Request;
use App\Models\SiteManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;



class AuthenticationController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'email|unique:siteManagers',
            'phoneNumber' => 'required|numeric|unique:siteManagers',
            'profilePicture' => 'string',
        ]);

        //TODO: add to env file
        $dummyPhoneNumber = config('settings.dummyPhoneNumber');
        $dummyEmail = config('settings.dummyEmail');

        //handle dummy info
        if($request->phoneNumber == $dummyPhoneNumber && $request->email == $dummyEmail){
    
            $siteManager = SiteManager::where('email', $request->email)
                 ->orWhere('phoneNumber', $request->phoneNumber)
                 ->first();

            if (!$siteManager){
                //create
                $siteManager = SiteManager::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phoneNumber' => $request->phoneNumber,
                    'phoneVerified'=> true,
                ]);
            }
            return response([
                'message' => 'Dummy OTP 123456',
            ], 201); 
          
        }
     
        $siteManager = SiteManager::where('phoneNumber', $request->phoneNumber)
                 ->where('phoneVerified', true)
                 ->first();
        if ($siteManager) {
            return response([
                'message' => 'Account with this phone number already exists',
            ], 401);
        }

        $name = $request->firstName.' '.$request->lastName;

        $siteManager = SiteManager::create([
            'name' => $name,
            'email' => $request->email,
            'phoneNumber' => $request->phoneNumber,
            'phoneVerified'=> false,
        ]);

        
        $this->sendSMS($request->phoneNumber,$siteManager);

        $phoneNumber = substr($siteManager->phoneNumber, 0, 4) . "*****" . substr($siteManager->phoneNumber, 8, 2);

        return response([
            'message' => 'An OTP has been sent to ' . $phoneNumber . '',
             
        ], 201);
        

    }
    
    public function verify(Request $request){
        $request->validate([
            'phoneNumber' => 'required|string',
            'otp' => 'required|digits:4',
        ]);

        //TODO: add to env
        // $dummyPhoneNumber = config('settings.dummyPhoneNumber');
        // $dummyOTP = config('settings.dummyOTP');

        //if dummy phone number and dummy otp
        // if($request->phoneNumber == $dummyPhoneNumber && $request->otp == $dummyOTP){
        //     $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)->first();
           
        //     return response([
        //         'valid' => true,
        //         'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
        //     ], 201);
        // }

        $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)
                       ->where('otp', $request->otp)
                       ->first();
       
        if(!$siteManager){
            return response([
                'message' => 'Invalid OTP',
            ], 401);
        }


        $siteManager->phoneVerified = true;
        $siteManager->otp = null;
        $siteManager->save();

        $wallet = SiteManagerWallet::create([ 
            'siteManagerId' => $siteManager->siteManagerId,
            'phoneNumber' => $request->phoneNumber,
        ]);

        //$token = $siteManager->createToken('siteManagerToken')->accessToken;

        return response([
            'valid' => true,
            'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
            //'token' => $token
        ], 201);

    }


    public function setPassword(Request $request){
        $request->validate([
            'phoneNumber' => 'required|numeric',
            'password' => 'required|string|min:8',
        ]);

        $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)
                        ->where('phoneVerified', true)
                        ->first();

        if(!$siteManager){
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }

        //east africa time zone
        //TODO: configure it in env
        date_default_timezone_set('Africa/Nairobi');
        $time = date('Y-m-d H:i:s');
        
        //set password
        $siteManager->password = Hash::make($request->password);
        $siteManager->save();

        //$token = $siteManager->createToken('siteManagerToken')->accessToken;

        return response([
            'message' => 'Password set successfully',
            'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber', 'dateRegistered']),
            //'token' => $token,
        ], 201);
    }

    public function login(Request $request){
        //use auth
        $request->validate([
            'phoneNumber' => 'required|numeric',
            'password' => 'required|string|min:8'
        ]);

        $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)
                        ->where('phoneVerified', true)
                        ->first();

        if(!$siteManager){
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if(!Hash::check($request->password, $siteManager->password)){
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $siteManager->createToken('siteManagerToken')->accessToken;

        return response([
            'valid' => true,
            'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
            'token' => $token
        ], 200);        
    }

    public function forgotPassword(Request $request){
        $request->validate([
            'phoneNumber' => 'required|numeric',
        ]);

        $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)
                        ->where('phoneVerified', true)
                        ->first();

        if(!$siteManager){
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $this->sendSMS($request->phoneNumber,$siteManager);
        $phoneNumber = $request->phoneNumber;
        $phoneNumber = substr($siteManager->phoneNumber, 0, 4) . "*****" . substr($siteManager->phoneNumber, 8, 2);
        return response([
            'message' => 'An OTP has been sent to ' . $phoneNumber . '',
             
        ], 201);
    }


    public function sendSMS($phoneNumber, $siteManager){

        $otp = rand(1000, 9999);
        $message = "Your OTP is: " . $otp;
        $siteManager->otp = $otp;
        $siteManager->save();

        $url = config('settings.smsUrl'); 
       
        $data = array(
            'notificationCode' =>config('settings.notificationCode'),
            'clientID' => 1,
            'message' => $message,
            'subject' => config('settings.subject'),
            'recepient' => $phoneNumber,
            'cCrecepients' => '',
            'bCCrecepients' => '',
            'type' => 'text',
        );


        $payload = json_encode($data); 

        $ch = curl_init($url); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);//set maximum time to wait for a connection
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);//set maximum time to wait for a response
        
        try {
             $result = curl_exec($ch); //executes the cURL session
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
        } catch (\Exception $e) {
            // handle exception
            Log::error($e->getMessage());
            return "Error: " . $e->getMessage();
        } finally {

            curl_close($ch);
        }

        Log::info($result);//log the response
        return $result; 
    }

    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response([
            'message' => 'Logged out successfully'
        ], 201);
    }
}





    

