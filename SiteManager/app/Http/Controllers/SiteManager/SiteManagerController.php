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
        //
    }
}
