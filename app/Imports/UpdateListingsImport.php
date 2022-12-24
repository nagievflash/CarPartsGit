<?php

namespace App\Imports;

use App\Jobs\ReviseProductJob;
use App\Jobs\UpdateInventoryPricingJob;
use App\Jobs\UpdateListingImagesJob;
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

class UpdateListingsImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, WithUpserts, WithStartRow, ShouldQueue, WithEvents
{
    use RemembersRowNumber, RegistersEventListeners;

    public function startRow(): int
    {
        return 2;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => "\t"
        ];
    }

    /**
     * @param array $row
     *
     * @return void
     */
    public function model(array $row)
    {
        $listing = EbayListing::where('sku', $row['sku'])->firstOrFail();
        //if (isset($row['listing_id'])) $listing = EbayListing::where('ebay_id', $row['listing_id']);

        dispatch(new UpdateListingImagesJob($listing));
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function uniqueBy()
    {
        return 'sku';
    }

    public static function afterImport(AfterImport $event)
    {
        Backlog::createBacklog('importInventory', 'Supplier 1 inventory csv file imported successful');
    }
}
