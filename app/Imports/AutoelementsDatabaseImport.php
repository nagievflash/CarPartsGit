<?php

namespace App\Imports;

use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class AutoelementsDatabaseImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts,  WithEvents, WithProgressBar
{
    use RemembersRowNumber, RegistersEventListeners, Importable;

    private $rows = 0;

    public function startRow(): int
    {
        return 2;
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
        ++$this->rows;

        $type = $row['type'];
        if ($type == 'Single Part') $type = 'single';
        if ($type == 'Kit') $type = 'kit';
        if (!$row['type']) $type = 'single';
        $shop = '';
        $ebay_id = '';
        if ($row['ebay3']) {
            $shop = 'ebay3';
            $ebay_id = $row['ebay3'];
        }
        if ($row['ebay4']) {
            $shop = 'ebay4';
            $ebay_id = $row['ebay4'];
        }
        if ($ebay_id != '') {
            $listing = EbayListing::updateOrCreate(
                ['ebay_id' => $ebay_id],
                ['type' => $type, 'shop' => $shop]
            );
            DB::table('listing_partslink')
                ->where('listing_id', $listing->id)
                ->delete();

            $components = array();
            for ($i = 0; $i < 18; $i++) {
                $components[] = array(
                    'sku' => $row['component_item_'. $i + 1 .'_sku'],
                    'qty' => $row['component_item_'. $i + 1 .'_quantity']
                );
            }
            foreach ($components as $item) {
                if ($item['sku']) {
                    if (!$item['qty']) {
                        Backlog::createBacklog('error', 'Column quantity cannot be null '. $this->rows);
                    }
                    else {
                        DB::table('listing_partslink')->insert([
                            'listing_id'    => $listing->id,
                            'partslink'     => $item['sku'],
                            'quantity'      => $item['qty'],
                            "created_at" =>  \Carbon\Carbon::now(),
                            "updated_at" => \Carbon\Carbon::now(),
                        ]);
                        if (!Warehouse::where('sku',     $item['sku'])->orWhere('partslink', $item['sku'])->exists()) {
                            Backlog::createBacklog('error 401', 'SKU or Partslink not founded: '. $item['sku']. ' listing_id: ' . $listing->id);
                        }
                    }

                }
            }
        }

    }

    public function chunkSize(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 1;
    }

    public static function afterImport(AfterImport $event)
    {
        Backlog::createBacklog('importInventory', 'Autoelements database csv file imported successful');
    }
}
