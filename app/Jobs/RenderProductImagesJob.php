<?php

namespace App\Jobs;

use App\Models\Backlog;
use App\Models\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Images;
use Imagick;

class RenderProductImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public int $product_id;

    public function __construct($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sku = Warehouse::where('id',$this->product_id)->pluck('sku')->first();
        $images = [];
        for ($i = 1; $i < 8; $i++) {
            $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . $sku . '_' . $i;
            $ch = curl_init($file);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode == 200) {
                $images[] = $file;
            } else {
                $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . explode('-', $sku)[0] . '_' . $i;
                $ch = curl_init($file);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($httpCode == 200) {
                    $images[] = $file;
                }
                else break;
            }
        }

        if(!empty($images)){
            $sort_order = 0;
            foreach ($images as $index => $url){
                if(!empty(config('sizes')['product'])) {
                    foreach (config('sizes')['product'] as $key => $size) {

                        $image = file_get_contents($url);

                        $dir = 'images\products\\' . $sku;

                        if (!is_dir(storage_path('app\\' . $dir))) {
                            mkdir(storage_path('app\\' . $dir), 0777, true);
                        }

                        $filename = $sku . '_' . $index . '_' . $key . '_resize_w_' . $size['w'] . '_h_' . $size['h'] . '.jpg';
                        $path = $dir .  '\\' . $filename;

                        if (!file_exists(storage_path('app\\' . $path))) {

                            $contents = file_get_contents($url);
                            file_put_contents(public_path($path), $contents);

                            $im = new Imagick(public_path($path));

                            $im->trimImage(20000);

                            $im->resizeImage($size['w'], $size['h'],Imagick::FILTER_LANCZOS,1, TRUE);
                            $im->setImageBackgroundColor("white");

                            $w = $im->getImageWidth();
                            $h = $im->getImageHeight();

                            $off_top  = 0;
                            $off_left = 0;

                            if ($w > $h) {
                                $off_top  = ((1200 - $h) / 2) * -1;
                            } else{
                                $off_left = ((1200 - $w) / 2) * -1;
                            }

                            $im->extentImage(1200,1200, $off_left, $off_top);

                            $status = Storage::exists($path);

                            if ($status) {
                                (new Images())->updateOrCreate([['item_id',$this->product_id],['url',$url]],
                                    [
                                        'item_type'  => 'App\Models\Warehouse',
                                        'item_id'    => $this->product_id,
                                        'sku'        => $sku,
                                         $key        => $path,
                                        'url'        => $url,
                                        'sort_order' => $sort_order
                                    ]
                                );
                            }

                            Backlog::createBacklog('ImageResize', 'resize image  ' . $sku . ' width - ' . $size['w'] . ' height - ' . $size['h'] . ' status - successfully');
                        }else{
                            Backlog::createBacklog('ImageResize', 'resize image  ' . $sku . ' width - ' . $size['w'] . ' height - ' . $size['h'] . ' status - already exists');
                        }
                    }
                }
                $sort_order++;
            }
        }else{
            Backlog::createBacklog('ImageResize', 'failed to parse any images');
        }
    }
}
