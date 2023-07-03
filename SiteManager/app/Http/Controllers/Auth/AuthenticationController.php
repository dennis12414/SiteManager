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
            'email' => 'required|unique:site_managers|email',
            'phone_number' => 'required|unique:site_managers|numeric',
        ]);
        
        $siteManager = SiteManager::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => Hash::make($request->phone_number),
        ]);   


        return response([
            'message' => 'Site Manager created successfully',
            'site_manager' => $siteManager,
        ], 201);
        
    }

    public function login(Request $request){
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required|digits:10',
        ]);

        $siteManager = SiteManager::where('name', $request->name)->first(); 
        if(!$siteManager || !Hash::check($request->phone_number, $siteManager->phone_number)){ 
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }
        else{
            $token = $siteManager->createToken('site_manager_token')->plainTextToken;
            return response([
                'message' => 'logged in successfully',
                'site_manager' => $siteManager,
                'token' => $token,
            ], 201);
        }
        
    }
}
