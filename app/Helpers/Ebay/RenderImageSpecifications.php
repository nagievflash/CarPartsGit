<?php

namespace App\Helpers\Ebay;

use Intervention\Image\Facades\Image;
use XMLWriter;
use Exception;
use App\Jobs\RenderProductImagesJob;

trait RenderImageSpecifications
{

    /**
     * Method for rendering image specifications from first Listing's image
     * @param $imageUrl
     * @param $type
     * @return string
     */
    public function renderImageSpecifications($imageUrl, $type): string
    {
        $contents = file_get_contents($imageUrl);
        $url = 'images/ebay/'. substr($imageUrl, strrpos($imageUrl, '/') + 1) . '_' . $type . '.jpg';
        $imageName = substr($imageUrl, strrpos($imageUrl, '/') + 1) . '_' . $type;
        file_put_contents(public_path($url), $contents);

        shell_exec('mogrify -write ' . public_path($url) . '  -resize ' . '1200' . 'x' . '1200' . ' -gravity center -extent ' . '1200' . 'x' . '1200' . ' -background none -quality 100 -strip -colorspace sRGB ' . public_path($url));
        shell_exec('optipng -o2 -strip all ' . public_path($url));

//        $img = Image::make(public_path($url));
//        $watermark = Image::make(public_path('images/bg/watermark_'.$type.'.png'));
//        $canvas = Image::canvas(1200, 1200);
//
//        $img->resize(1200, 1200, function($constraint)
//        {
//            $constraint->aspectRatio();
//        });
//
//        if ($type == 'ebay4') $canvas->insert($img, 'center', 0, 100);
//        else $canvas->insert($img, 'center', 0, 0);
//
//        $canvas->insert($watermark, 'center');
//        $canvas->save(public_path($url));

        return env('APP_URL') . '/' . $url;
    }
}
