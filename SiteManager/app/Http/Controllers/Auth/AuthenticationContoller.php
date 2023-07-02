<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;

class AuthenticationContoller extends Controller
{
    public function register(RegisterRequest $request){
        // return response([
        //     'message' => 'success'
        // ], 200);
        $validated = $request->validated();
        return response()->json($validated);
    }
}
