<?php

namespace App\Helpers\Ebay;

use Intervention\Image\Facades\Image;

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
        $url = 'images/ebay/'. substr($imageUrl, strrpos($imageUrl, '/') + 1) . '_n_' . $type . '.jpg';
        file_put_contents(public_path($url), $contents);

        $img = Image::make(public_path($url));
        $watermark = Image::make(public_path('images/bg/watermark_'.$type.'.png'));
        $canvas = Image::canvas(1500, 1500);

        $img->resize(1500, 1500, function($constraint)
        {
            $constraint->aspectRatio();
        });

        if ($type == 'ebay4') $canvas->insert($img, 'center', 0, 100);
        else $canvas->insert($img, 'center');

        $canvas->insert($watermark, 'center');
        $canvas->save(public_path($url));

        return env('APP_URL') . '/' . $url;
    }
}
