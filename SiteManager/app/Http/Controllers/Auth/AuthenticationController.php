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
            'email' => 'required|email',
            'phoneNumber' => 'required|numeric',
        ]);

        //if email or phone number already exist and phoneVerified is true
        $siteManager = SiteManager::where('email', $request->email)
                 ->orWhere('phoneNumber', $request->phoneNumber)
                 ->first();
        if ($siteManager && $siteManager->phoneVerified) {
            return response([
                'message' => 'Email or Phone Number already exists',
            ], 409);
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
        
        // if(config('app.env') == 'local' || config('app.env') == 'testing' ){
            $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)->first();
            if(!$siteManager){
                return response([
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $siteManager->phoneVerified = true;
            $siteManager->otp = null;
            $siteManager->save();
            
            return response([
                'valid' => true,
                'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
            ], 201);
        // }
        // else{
        //     $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)->first();
        
        //     if ($siteManager->otp != $request->otp) {
        //         return response([
        //             'message' => 'Invalid OTP',
               
        //         ], 401);
        //     }

        //     $siteManager->phone_verified = true;
        //     $siteManager->otp = null;
        //     $siteManager->save();

        //     return response([
        //         'valid' => true,
        //         'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
        //     ], 201);
        // }

    }

    public function setPassword(Request $request){
        $request->validate([
            'phoneNumber' => 'required|numeric',
            'password' => 'required|string|min:8',
        ]);

        $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)->first();
        if(!$siteManager){
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }
        //check if phone number is verified
        if (!$siteManager->phoneVerified) {
            return response([
                'message' => 'Phone number not verified',
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
            'siteManager' => $siteManager->only(['siteManagerId','name', 'email', 'phoneNumber']),
        ], 201);

    }

    public function login(Request $request){
        $request->validate([
            'phoneNumber' => 'required|numeric',
            'password' => 'required|string|min:8'
        ]);

        $siteManager = SiteManager::where('phoneNumber',$request->phoneNumber)->first(); 
        if(!$siteManager || !Hash::check($request->password, $siteManager->password)){
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if phone number is verified
        if (!$siteManager->phoneVerified) {
            return response([
                'message' => 'Phone number not verified',
            ], 401);
        }
       
        //check if password is correct
        if (!Hash::check($request->password, $siteManager->password)) {
            return response([
                'message' => 'Invalid credentials',
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





    

