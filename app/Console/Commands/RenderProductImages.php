<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\RenderProductImagesJob;
use App\Models\Warehouse;

class RenderProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'render:images {product_id=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resize images';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->argument('product_id') == 'all') {
            dispatch(new RenderProductImagesJob($this->argument('product_id')));
        }else {
            foreach (Warehouse::where('supplier_id', 1)->get() as $product) {
                dispatch((new RenderProductImagesJob($product->id)));
            }
        }

        return 'The Job started successfully!';
    }
}
