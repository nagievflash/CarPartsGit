<?php

namespace App\Jobs;

use App\Models\Backlog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Images;
use App\Models\Product;


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
        $sku = Product::where('id',$this->product_id)->pluck()->fitst();

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
            foreach ($images as $url){
                if(!empty(config('sizes')['product'])) {
                    $id = null;
                    $sort_order = 0;
                    foreach (config('sizes')['product'] as $key => $size) {

                        $image = file_get_contents($url);

                        $resize = Image::make(public_path($image));
                        $resize->fit($size['w'], $size['h']);
                        $path = '/images/products/' . $sku . '/' . $sku . '_resize_w-' . $size['w'] . '_h-' . $size['h'];
                        $resize->save(Storage::path($path));
                        $status = Storage::exists($path);

                        if ($status) {
                            $sort_order = is_null($id) ? $sort_order : $sort_order + 1;
                            (new Images())->updateOrCreate(['id' => $id],
                                [
                                    'item_type'  => 'App\Models\Product',
                                    'item_id'    => $this->product_id,
                                     $key        => $path,
                                    'url'        => $url,
                                    'sort_order' => $sort_order
                                ]
                            );

                            $id = (new Images)->id;
                        }

                        Backlog::createBacklog('ImageResize', 'resize image  ' . $sku . ' width - ' . $size['w'] . ' height - ' . $size['h'] . ' status - ' . $status);
                    }
                }
            }
        }
    }
}
