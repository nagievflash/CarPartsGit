<?php

namespace App\Imports;

use App\Jobs\ReviseProductJob;
use App\Jobs\UpdateInventoryPricingJob;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
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

class InventoryImportLKQ implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts,  WithEvents, ShouldQueue
{
    use RemembersRowNumber, RegistersEventListeners;

    public function startRow(): int
    {
        return 1;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ","
        ];
    }

    /**
     * @param array $row
     *
     * @return void
     */
    public function model(array $row)
    {

        Warehouse::updateOrCreate(
            ['sku' => $row['partnumber'], 'supplier_id' => 2],
            ['price' => $row["customerprice"], 'qty' => $row["quantityavailable"], 'partslink' => $row['basepartnumber']]
        );
        Product::firstOrCreate(
            [
                'sku' => $row['partnumber']
            ],
            [
                'images' => $row['imagepath'],
                'title'  => $row['webdescription1']
            ]
        );
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public static function afterImport(AfterImport $event)
    {
        Backlog::createBacklog('importInventory', 'Supplier LKQ inventory csv file imported successful');
    }
}
