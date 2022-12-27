<?php

namespace App\Helpers\Ebay;

use Imagick;
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
        $url = 'images/ebay/'. substr($imageUrl, strrpos($imageUrl, '/') + 1) . '_' . $type . '1.jpg';
        file_put_contents(public_path($url), $contents);

        $im = new Imagick(public_path($url));

        $im->trimImage(20000);

        $im->resizeImage(1200, 1200,Imagick::FILTER_LANCZOS,1, TRUE);
        $im->setImageBackgroundColor("white");

        $w = $im->getImageWidth();
        $h = $im->getImageHeight();

        $off_top=0;
        $off_left=0;

        if($w > $h){
            $off_top = ((1200-$h)/2) * -1;
        }else{
            $off_left = ((1200-$w)/2) * -1;
        }

        $im->extentImage(1200,1200, $off_left, $off_top);

        $watermark = new Imagick();
        $watermark->readImage(public_path('images/bg/watermark_'.$type.'.png'));

        $x = 0;
        $y = 0;

        $im->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);

        $im->writeImage(public_path($url));

        /*$img = Image::make(public_path($url));
        $watermark = Image::make(public_path('images/bg/watermark_'.$type.'.png'));
        $canvas = Image::canvas(1200, 1200);

        if ($type == 'ebay4') $canvas->insert($img, 'center', 0, 100);
        else $canvas->insert($img, 'center', 0, 0);

        $canvas->insert($watermark, 'center');
        $canvas->save(public_path($url));*/

        return env('APP_URL') . '/' . $url;
    }
}
