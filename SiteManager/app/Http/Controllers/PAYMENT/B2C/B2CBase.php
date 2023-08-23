<?php

namespace App\Http\Controllers\PAYMENT\B2C;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class B2CBase extends Controller
{
    protected $siteManagerId;
    protected $workerId;
    protected $projectId;
    protected $date;

    public function __construct(Request $request)
    {
        $this->validate($request, [
            'siteManagerId' => 'required|numeric',
            'projectId'=> 'required|numeric',
            'workerId' => 'required|numeric',
            'date' => 'date'
        ]);

        $this->siteManagerId = $request->siteManagerId;
        $this->workerId = $request->workerId;
        $this->projectId = $request->projectId;
        $this->date = $request->date;
    }
    
}
