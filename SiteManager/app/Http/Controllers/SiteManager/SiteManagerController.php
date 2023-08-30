<?php

namespace App\Http\Controllers\SiteManager;
use App\Models\SiteManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SiteManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $siteManagers = SiteManager::all();

        //read env file to know if it is local or production
        $env = env('APP_ENV');
        $url = env('APP_URL');
        
        return response([
            'message' => 'Retrieved successfully',
            'env' => $env,
            'url' => $url,
            'siteManagers' => $siteManagers->map(function($siteManager){
                return $siteManager->only(['siteManagerId', 'name', 'phoneNumber', 'dateRegistered','phoneVerified','otp']);
            }),
        ], 200);
    }

    public function destroy(string $id)
    {
        $siteManager = SiteManager::where('siteManagerId', $id)->first();
        if (!$siteManager) {
            return response([
                'message' => 'Site Manager does not exist',
            ], 404);
        }

        $siteManager->delete();
        return response([
            'message' => 'Site Manager deleted successfully',
        ], 200);
        
    }

}
