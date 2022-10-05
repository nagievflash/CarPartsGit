<?php

namespace App\Imports;

use App\Http\Controllers\Admin\EbayController;
use App\Jobs\AddFixedPriceItemJob;
use App\Jobs\ReviseProductJob;
use App\Models\Backlog;
use App\Models\Compatibility;
use App\Models\EbayListing;
use App\Models\Fitment;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Events\AfterImport;

class CustomProductsImport implements ToModel, WithChunkReading, WithBatchInserts, WithStartRow, ShouldQueue
{
    use RemembersRowNumber;

    public string $shop;

    public function  __construct(string $shop = 'ebay4')
    {
        $this->shop = $shop;
    }

    public function startRow(): int
    {
        return 1;
    }

    public function getCsvSettings()
    {
        return [
            'delimiter' => ";"
        ];
    }

    /**
     * @param array $row
     *
     */
    public function model(array $row)
    {
        if (!EbayListing::where('sku', $row[0])->where('type', $this->shop)->exists()) {
            if (Compatibility::where('sku', $row[0])->exists()) {
                $product = Product::where('sku', $row[0]);
                if ($product->exists()) dispatch(new AddFixedPriceItemJob($product->first(), $this->shop));
            }
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public static function afterImport(AfterImport $event)
    {
        Backlog::createBacklog('CustomProductsImport', 'Custom listings upload job started');
    }
}
