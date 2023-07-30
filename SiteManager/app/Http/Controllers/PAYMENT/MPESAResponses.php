<?php

namespace App\Http\Controllers\PAYMENT;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MPESAResponses extends Controller
{
    public function b2CResponse(){
        $content = file_get_contents('php://input');
        $mpesaResponse = json_decode($content, true); 
        date_default_timezone_set('Africa/Nairobi');
        $date = date('Y-m-d H:i:s');
        Log::info($date);
        Log::info($mpesaResponse);
    }
}
