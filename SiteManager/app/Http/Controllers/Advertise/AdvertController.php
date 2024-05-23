<?php

namespace App\Http\Controllers\Advertise;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvertController extends Controller
{
    public function index(){
        $advertisers = Advert::all();

        return response([
            'message' => 'Advert retrieved successfully',
            'adverts' => $advertisers
        ], 200);
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'advertiserId' => 'required|unique:advertiser,advertiserId',
            'title' => 'required|string',
            'image' => 'required|image|max:2048',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric',
            'date' => 'required|date',
            'location' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $advertiser  = AdvertiserController::where('advertiserId',$request->advertiserId)->first();

        if(!$advertiser)
        {
            return response([
                'message' => 'advertiser  not found',
            ], 404);
        }

        if($request->file('image') != null) {
            $uuid = Str::uuid();

            $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
            if (!$imageFolderPath) {
                return response([
                    'ty' => $imageFolderPath,
                    'message' => 'Image folder path not found in settings',
                ], 500);
            }
            $domain = Setting::where('setting_key', 'domain')->value('value');


            $uploadedImage = $request->file('image');


            $imageName = $uuid . '_' . $uploadedImage->getClientOriginalName();


            $uploadedImage->move($imageFolderPath->value, $imageName);

            $advert = Advert::create([
                'advertiserId' => $request->advertiserId,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'price' => $request->price,
                'date' => $request->date,
                'location' => $request->location,
                'address' => $request->address,
                'phone' => $request->phone,
                'image' => $imageName,
            ]);

            return response([
                'message' => 'Advert created successfully',
                'advert' => $advert
            ], 201);


        }else{
            $advert = Advert::create([
                'advertiserId' => $request->advertiserId,
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'price' => $request->price,
                'date' => $request->date,
                'location' => $request->location,
                'address' => $request->address,
                'phone' => $request->phone,
                'image' => null,
            ]);

            return response([
                'message' => 'Advert created successfully',
                'advert' => $advert
            ], 201);

        }


    }

    public function update(Request $request, $id){
        $validatedData = $request->validate([
            'advertiserId' => 'required|exists:advertisers,advertiserId',
            'title' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
            'price' => 'required|numeric',
            'date' => 'required|date',
            'location' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $advert = Advert::find($id);

        if (!$advert) {
            return response([
                'message' => 'Advert not found',
            ], 404);
        }

        $advertiser = AdvertiserController::where('advertiserId', $request->advertiserId)->first();

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
            if ($advert->image) {
                $oldImagePath = str_replace($domain . '/', '', $advert->image);
                if (file_exists(public_path($oldImagePath))) {
                    unlink(public_path($oldImagePath));
                }
            }

            $advert->image = $domain . '/' . $imageFolderPath->value . '/' . $imageName;
        }

        $advert->update([
            'advertiserId' => $request->advertiserId,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'price' => $request->price,
            'date' => $request->date,
            'location' => $request->location,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        return response([
            'message' => 'Advert updated successfully',
            'advert' => $advert
        ], 200);
    }


    public function destroy(Request $request){

    }

    public function show($id){
        $advert = Advert::find($id);

        if (!$advert) {
            return response([
                'message' => 'Advert not found',
            ], 404);
        }

        return response([
            'message' => 'Advert retrieved successfully',
            'advert' => $advert,
        ], 200);

    }
}
