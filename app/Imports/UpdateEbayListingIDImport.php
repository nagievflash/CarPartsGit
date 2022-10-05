<?php

namespace App\Imports;

use App\Jobs\ReviseProductJob;
use App\Jobs\UpdateInventoryPricingJob;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class UpdateEbayListingIDImport implements ToModel
{
    use RemembersRowNumber, RegistersEventListeners;

    public $shop;

    public function __construct(string $shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param array $row
     *
     * @return void
     */
    public function model(array $row)
    {
        $listing = EbayListing::where('sku', $row[0])->where('type', $this->shop);
        if ($listing->exists()) {
            $listing = $listing->first();
            $listing->ebay_id = $row[1];
            $listing->save();
        }
    }
}
