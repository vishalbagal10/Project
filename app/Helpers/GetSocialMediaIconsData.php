<?php

/**
 *
 */
namespace App\Helpers;
use Illuminate\Support\Facades\File;
class GetSocialMediaIconsData
{
    public static function getSMData()
    {
        $social_media_icons_array = [];
        $filesInFolder = File::files(public_path('images/social_media_icons'));
        foreach($filesInFolder as $file)
        {
            $social_media_icons_array[] = pathinfo($file);
        }
        
        return $social_media_icons_array;
        
    }
}
