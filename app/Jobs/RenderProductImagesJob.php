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
        $sku = Product::where('id',$this->product_id)->pluck('sku')->first();

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
            foreach ($images as $index => $url){
                if(!empty(config('sizes')['product'])) {
                    $sort_order = 0;
                    foreach (config('sizes')['product'] as $key => $size) {

                        $image = file_get_contents($url);

                        $dir = 'images\products\\' . $sku;

                        if (!is_dir(storage_path('app/' . $dir))) {
                            mkdir(storage_path('app/' . $dir), 0777, true);
                        }

                        $filename = $sku . '_' . $index . '_' . $key . '_resize_w_' . $size['w'] . '_h_' . $size['h'] . '.jpg';
                        $path = $dir .  '\\' . $filename;

                        if (!file_exists(storage_path('app\\' . $path))) {
                            $resize = Image::make($image);
                            $resize->fit($size['w'], $size['h']);

                            $resize->save(Storage::path($path));
                            $status = Storage::exists($path);

                            if ($status) {
                                (new Images())->updateOrCreate([['item_id',1],['url',$url]],
                                    [
                                        'item_type'  => 'App\Models\Product',
                                        'item_id'    => $this->product_id,
                                         $key         => $path,
                                        'url'        => $url,
                                        'sort_order' => $sort_order
                                    ]
                                );
                                $sort_order++;
                            }

                            Backlog::createBacklog('ImageResize', 'resize image  ' . $sku . ' width - ' . $size['w'] . ' height - ' . $size['h'] . ' status - successfully');
                        }else{
                            Backlog::createBacklog('ImageResize', 'resize image  ' . $sku . ' width - ' . $size['w'] . ' height - ' . $size['h'] . ' status - already exists');
                        }
                    }
                }
            }
        }else{
            Backlog::createBacklog('ImageResize', 'failed to parse any images');
        }
    }
}
