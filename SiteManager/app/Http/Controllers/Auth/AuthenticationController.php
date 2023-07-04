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
            'email' => 'required|unique:siteManagers|email',
            'phoneNumber' => 'required|unique:siteManagers|numeric',
        ]);
        
        $siteManager = SiteManager::create([
            'name' => $request->name,
            'email' => $request->email,
            'phoneNumber' => Hash::make($request->phoneNumber),
        ]);   


        return response([
            'message' => 'Site Manager created successfully',
            'siteManager' => $siteManager,
        ], 201);
        
    }

    public function login(Request $request){
        $request->validate([
            'name' => 'required',
            'phoneNumber' => 'required|digits:10',
        ]);

        $siteManager = SiteManager::where('name', $request->name)->first(); 
        if(!$siteManager || !Hash::check($request->phoneNumber, $siteManager->phoneNumber)){ 
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }
        else{
            $token = $siteManager->createToken('siteManagerToken')->plainTextToken;
            return response([
                'message' => 'logged in successfully',
                'siteManager' => $siteManager,
                'token' => $token,
            ], 201);
        }
        
    }
}
