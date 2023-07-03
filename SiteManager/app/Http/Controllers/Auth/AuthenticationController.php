<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function register(RegisterRequest $request){
        $request->validate();
        $user = User::create([
            'user_name' => $request->username,
            'email' => $request->email,
            'phone_number' => Hash::make($request->phone),
        ], 201);
    }

    public function login(Request $request){
        $request->validate();
        $user = User::where('user_name', $request->username)->first();
        if(!$user || !Hash::check($request->phone, $user->phone_number)){
            return response([
                'message' => 'Bad credentials'
            ], 401);
        }
    }
}
