<?php

namespace App\Http\Controllers\SiteManager;
use App\Models\SiteManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


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


        return response([
            'message' => 'Retrieved successfully',
            'env' => $env,
            'siteManagers' => $siteManagers->map(function($siteManager){
                return $siteManager->only(['siteManagerId', 'name', 'phoneNumber', 'dateRegistered']);
            }),
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
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
