<?php

namespace App\Imports;

use App\Jobs\ReviseProductJob;
use App\Jobs\UpdateInventoryPricingJob;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Product;
use App\Models\Setting;
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

class InventoryImportPF implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, WithStartRow, ShouldQueue, WithEvents
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
        $price = (float)$row['cost'];
        $qty = $row['stock_total'];

        Warehouse::updateOrCreate(
            [
                'sku' => $row['sku'],
                'supplier_id' => 1],
            [
                'price' => $price,
                'qty' => $qty,
                'shipping' => (float)$row['shipping_cost'],
                'handling' => (float)$row['handling_cost'],
                'partslink' => $row['partslink'],
                'category' => $row['part_name']
            ]
        );
        Product::updateOrCreate(
            [
                'sku'        => $row['sku']
            ],
            [
                'title'      => $row['part_name'],
                'price'      => $price + $price * .25,
                'qty'        => $qty,
                'partslink'  => $row['partslink'],
                'oem_number' => $row['oem_number'],
                'available'  => 1
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
        DB::table('products')->where('available', 0)->delete();
        Backlog::createBacklog('importInventory', 'Supplier PF inventory csv file imported successful');
    }
}
