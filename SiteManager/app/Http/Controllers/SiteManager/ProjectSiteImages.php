<?php

namespace App\Http\Controllers\SiteManager;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectSiteImages extends Controller
{
    public function uploadSiteImage(Request $request,String $id){


        $projectId = $id;

        $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
        if (!$imageFolderPath) {
            return response([
                'message' => 'Image folder path not found in settings',
            ], 500);
        }
    

        $savedImages = [];     
        if ($request->hasFile('images')) {
            
            $image = $request->file('images');               
            foreach($request->file('images') as $image ){
             
                $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                
                
                try {
                    $image->move($imageFolderPath->value, $fileName);
                    

                    $savedImages[] = [
                        'projectId' => $projectId,
                        'name' => $fileName,
                    ];
                } catch (\Exception $e) {
                    
                    return response([
                        'message' => 'Error uploading image: ' . $e->getMessage(),
                    ], 500);
                }


            }
            
            
        }else {
            return response([
                'message' => 'No images uploaded',
            ], 422); 
        }

        $project = Project::find($projectId);
        foreach ($savedImages as $image) { 
            $project->images()->create($image);
        }

 
        $projectImages = $project->images()->get(['imageId', 'name']);

        $domain = Setting::where('setting_key', 'domain')->value('value');

        $imagesData = $projectImages->map(function ($projectImage) use ($domain, $imageFolderPath) {
        $imageUrl = $domain . '/' . 'api/images' . '/' . $projectImage->name;
        return [
            'imageId' => $projectImage->imageId,
            'url' => $imageUrl,
        ];
      });



        return response([
            'message'=>'images uploaded successfully',
            'images'=> $imagesData,
        ],200);
    }
    

    public function show($filename)
    {
        $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
        if (!$imageFolderPath) {
            return response([
                'message' => 'Image folder path not found in settings',
            ], 500);
        }
    
        
            
        $path =  $imageFolderPath->value .'/' . $filename;

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }


    public function projectImages($projectId){
        $projectImages = Project::find($projectId)->images()->get(['imageId', 'name']);

        $imageFolderPath = Setting::where('setting_key', 'image_folder_path')->first();
        if (!$imageFolderPath) {
            return response([
                'message' => 'Image folder path not found in settings',
            ], 500);
        }

        $domain = Setting::where('setting_key', 'domain')->value('value');

        $imageUrls = $projectImages->map(function ($projectImage) use ($domain, $imageFolderPath) {
            $imageUrl = $domain . '/' . 'api/images' . '/' . $projectImage->name;
            return [
                'imageId' => $projectImage->imageId,
                'url' => $imageUrl,
            ];
        });



        return response([
            'message'=>'images retreived successfully',
            'images'=>$imageUrls,
        ],200);

    }

}
