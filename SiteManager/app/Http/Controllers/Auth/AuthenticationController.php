<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\SiteManager;
use Illuminate\Support\Facades\Hash;


class AuthenticationController extends Controller
{

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'email',
            'phoneNumber' => 'required|numeric',
        ]);

        $dummyPhoneNumber = "0712345678";
        $dummyEmail = "testemail@gmail.com";

        if($request->phoneNumber == $dummyPhoneNumber && $request->email == $dummyEmail){
    
            $siteManager = SiteManager::where('email', $request->email)
                 ->orWhere('phoneNumber', $request->phoneNumber)
                 ->first();
            if ($siteManager) {
                return response([
                    'message' => 'Dummy OTP 123456',
                ], 201);
            }else{
                //create
                $siteManager = SiteManager::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phoneNumber' => $request->phoneNumber,
                    'phoneVerified'=> true,
                ]);
                return response([
                    'message' => 'Dummy OTP 123456',
                    //'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber', 'phoneVerified']),
                ], 201);
              
            }
          
        }
     
        $siteManager = SiteManager::where('phoneNumber', $request->phoneNumber)
                 ->where('phoneVerified', true)
                 ->first();
        if ($siteManager) {
            return response([
                'message' => 'Account with this phone number already exists',
            ], 401);
        }

        $otp = rand(100000, 999999);

        $siteManager = SiteManager::create([
            'name' => $request->name,
            'email' => $request->email,
            'phoneNumber' => $request->phoneNumber,
            'otp' => $otp,
            'phoneVerified'=> false,
        ]);

        $message = "Your OTP is: " . $otp;
        $this->sendSMS($request->phoneNumber, $message);

        $phoneNumber = substr($siteManager->phoneNumber, 0, 4) . "*****" . substr($siteManager->phoneNumber, 8, 2);

        return response([
            'message' => 'An OTP has been sent to ' . $phoneNumber . '',
            
        ], 201);
        

    }
    
    public function verify(Request $request){
        $request->validate([
            'phoneNumber' => 'required|numeric',
            'otp' => 'required|digits:6',
        ]);
        
        $dummyPhoneNumber = "0712345678";
        $dummyOTP = 123456;

        //if dummy phone number and dummy otp
        if($request->phoneNumber == $dummyPhoneNumber && $request->otp == $dummyOTP){
            $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)->first();
           
            return response([
                'valid' => true,
                'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
            ], 201);
        }

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

        return response([
            'valid' => true,
            'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
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
        date_default_timezone_set('Africa/Nairobi');
        $time = date('Y-m-d H:i:s');
        //set password
        $siteManager->password = Hash::make($request->password);
        $siteManager->save();

        return response([
            'message' => 'Password set successfully',
            'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber', 'dateRegistered']),
        ], 201);

    }

    public function login(Request $request){
        $request->validate([
            'phoneNumber' => 'required|numeric',
            'password' => 'required|string|min:8'
        ]);

        $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)
                        ->where('phoneVerified', true)
                        ->first();

        if(!$siteManager || !Hash::check($request->password, $siteManager->password)){
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response([
            'valid' => true,
            'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
        ], 201);
        

    }


    public function sendSMS($phoneNumber, $message){
        $url = "http://172.105.90.112:8080/notification-api/v1/notification/create";
        $data = array(
            'notificationCode' => 'PMANAGER-SMS',
            'clientID' => 1,
            'message' => $message,
            'subject' => 'SMS Test',
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
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }
}





    

