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


class RenderProductImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public string $url;
    public string $name;
    public string $type;
    public int $product_id;

    public function __construct($url,$name,$type,$product_id)
    {
        $this->url  = $url;
        $this->name = $name;
        $this->type = $type;
        $this->product_id = $product_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty(config('sizes')['product'])) {
            $id = null;
            $sort_order = 0;
            foreach (config('sizes')['product'] as $key => $size) {
                $resize = Image::make(public_path($this->url));
                $resize->fit($size['w'], $size['h']);
                $path = '/images/products/' . $this->type . '/' . $this->name . '_resize_w-' . $size['w'] . '_h-' . $size['h'];
                $resize->save(Storage::path($path));
                $status = Storage::exists($path);

                if ($status) {
                    if(is_null($id)){
                        (new Images)->create(
                            [
                                'item_type' => 'App\Models\Product',
                                'item_id'   => $this->product_id,
                                 $key => $path,
                                'sort_order' => $sort_order
                            ]
                        );
                        $id = (new Images)->id;
                    }else{
                        $sort_order++;
                        (new Images())->updateOrCreate(['id' => $id],
                            [
                              $key => $path,
                              'sort_order' => $sort_order
                            ]
                        );
                    }
                }

                Backlog::createBacklog('ImageResize', 'resize image  ' . $this->url . ' width - ' . $size['w'] . ' height - ' . $size['h'] . ' status - ' . $status);
            }
        }
    }
}
