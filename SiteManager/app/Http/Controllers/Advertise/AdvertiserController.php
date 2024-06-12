<?php

namespace App\Http\Controllers\Advertise;

use App\Http\Controllers\Controller;
use App\Models\Advertiser;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdvertiserController extends Controller
{
    public function index() {
        $advertisers = AdvertiserController::all();

        return response([
            'message' => 'Advertisers retrieved successfully',
            'advertisers' => $advertisers
        ], 200);
    }


    public function store(Request $request){
        $validatedData = $request->validate([
            'advertiserId' => 'required|unique:advertisers,advertiserId',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:advertisers,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $uuid = Str::uuid();
            $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
            if (!$imageFolderPath) {
                return response([
                    'message' => 'Image folder path not found in settings',
                ], 500);
            }
            $domain = Setting::where('setting_key', 'domain')->value('value');

            $uploadedImage = $request->file('image');
            $imageName = $uuid . '_' . $uploadedImage->getClientOriginalName();
            $uploadedImage->move($imageFolderPath->value, $imageName);
            $imagePath = $domain . '/' . $imageFolderPath->value . '/' . $imageName;
        }

        $advertiser = AdvertiserController::create([
            'advertiserId' => $request->advertiserId,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'image' => $imagePath,
        ]);

        return response([
            'message' => 'Advertiser created successfully',
            'advertiser' => $advertiser,
        ], 201);
    }


    public function update(Request $request, $advertiserId){
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:advertisers,email,' . $advertiserId . ',advertiserId',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048'
        ]);

        $advertiser = AdvertiserController::where('advertiserId', $advertiserId)->first();

        if (!$advertiser) {
            return response([
                'message' => 'Advertiser not found',
            ], 404);
        }

        if ($request->hasFile('image')) {
            $uuid = Str::uuid();
            $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
            if (!$imageFolderPath) {
                return response([
                    'message' => 'Image folder path not found in settings',
                ], 500);
            }
            $domain = Setting::where('setting_key', 'domain')->value('value');

            $uploadedImage = $request->file('image');
            $imageName = $uuid . '_' . $uploadedImage->getClientOriginalName();
            $uploadedImage->move($imageFolderPath->value, $imageName);

            // Delete the old image if it exists
            if ($advertiser->image) {
                $oldImagePath = str_replace($domain . '/', '', $advertiser->image);
                if (file_exists(public_path($oldImagePath))) {
                    unlink(public_path($oldImagePath));
                }
            }

            $advertiser->image = $domain . '/' . $imageFolderPath->value . '/' . $imageName;
        }

        $advertiser->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response([
            'message' => 'Advertiser updated successfully',
            'advertiser' => $advertiser,
        ], 200);
    }


    public function show($advertiserId) {
        $advertiser = AdvertiserController::where('advertiserId', $advertiserId)->first();

        if (!$advertiser) {
            return response([
                'message' => 'Advertiser not found',
            ], 404);
        }

        return response([
            'message' => 'Advertiser retrieved successfully',
            'advertiser' => $advertiser,
        ], 200);
    }


    public function destroy($advertiserId){
        $advertiser = Advertiser::where('advertiserId', $advertiserId)->first();

        if (!$advertiser) {
            return response([
                'message' => 'Advertiser not found',
            ], 404);
        }

        // Deleting the advertiser and its related adverts
        $advertiser->delete();

        return response([
            'message' => 'Advertiser deleted successfully',
        ], 200);
    }


}
