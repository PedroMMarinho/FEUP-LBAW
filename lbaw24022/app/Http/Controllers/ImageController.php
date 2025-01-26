<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\GeneralUser;
use App\Models\Auction;
use App\Models\AdminChange;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ImageController extends Controller
{   
    static $default = 'default.jpg';
    static $diskName = 'okshon';


    private static function defaultAsset(String $type) 
    {
        return asset('images/'. $type . '/' . self::$default);
    }
    

    public static function getProfileImage(int $id)
    {

        $fileName = GeneralUser::find($id)->image?->path;

        if ($fileName) {
            return asset('images/profile/' . $fileName);
        }
    
        return self::defaultAsset('profile');
    }
   

    
    public static function auctionGet(int $id)
    {
        $imgs = [];

        foreach (Auction::find($id)->images as $img) {
            
            $imgs[] = asset('images/auction/' . $img->path);
        }
        
        return $imgs;
    }


   
    public function uploadProfileImage(Request $request, int $userId): RedirectResponse
    {   

        $userToChange = GeneralUser::findOrFail($userId);

        if (($userToChange->id !== $request->user()->id) && ($request->user()->role !== 'Admin')) {
            return redirect('/');
            // TODO: mandar erro
        }

        $image = $request->file('image');

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:15360',
        ]);

        $path = $image->hashName();

        $existingImage = $userToChange->image;

        if($existingImage){
            $existingImagePath = 'profile/' . $existingImage->path;
            
            Storage::disk(self::$diskName)->delete($existingImagePath);

            $existingImage->delete();
        }

        $image->storeAs('profile', $path ,'okshon');

  Image::create([
            'path' => $path,
            'general_user_id' => $userToChange->id,
        ]);

        return back()->with('status', 'image-updated')->with('success', 'Image Successfully Uploaded');
    }
    




    public static function auctionPhotos(Auction $auction, array $oldUrls, array $newPhotos)
    {

        // Change URLS
        $processedUrls = array_map(function ($url) {
            return basename(parse_url($url, PHP_URL_PATH));
        }, $oldUrls);


        // Delete removed photos
        $toRemove = [];

        foreach ($auction->images ?? [] as $image) {
            if (! in_array($image->path, $processedUrls)) {
                $toRemove[] = $image; 
            }
        }
        

        foreach ($toRemove as $img) {
            $img->delete();
            Storage::disk(self::$diskName)->delete('auction/' . $img->path);
        }



        // Add new photos
        foreach ($newPhotos as $img)
        {
            // Store filename?
            $originalName = $img->getClientOriginalName();
            $fileName = $img->hashName(); 

            $img->storeAs("auction/", $fileName, self::$diskName);


            Image::create([
                'path' => $fileName,
                'auction' => $auction->id,
            ]);
        }


        return redirect()->back();
    }


    public static function destroyImage(int $userId)
    {
        $user = GeneralUser::findOrFail($userId);

        $existingImage = $user->image;
        if($existingImage){
            $existingImagePath = 'profile/' . $existingImage->path;
        
            Storage::disk(self::$diskName)->delete($existingImagePath);

            $existingImage->delete();
        }

    }



    /**
     * Delete the user's image.
     */
    public static function destroy(Request $request, int $userId): RedirectResponse
    {

        $userToChange = GeneralUser::findOrFail($userId);

        if (($userToChange->id !== $request->user()->id) && ($request->user()->role !== 'Admin')) {
            return redirect('/');
            // TODO: mandar erro
        }


        $existingImage = $userToChange->image;
        if($existingImage){
            self::destroyImage($userId);

            if($request->user()->role === 'Admin' && $request->user()->id !== $userId){
                AdminChange::create([
                    'description' => 'Changed profile image of ' . $userToChange->role . ' ' . $userToChange->username,
                    'admin' => $request->user()->id
                ]);
            }

            return back()->with('status', 'image-deleted');

        }

        return back()->withErrors(['image' => 'The file does not exist'])->withInput();
    }
}
