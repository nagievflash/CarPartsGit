<?php

namespace App\Imports;

use App\Http\Controllers\Admin\EbayController;
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

class CustomProductsImport implements ToModel, WithChunkReading, WithBatchInserts, WithStartRow, ShouldQueue
{
    use RemembersRowNumber;

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
     * @return Model|Product|null
     */
    public function model(array $row)
    {
        $product = Product::where('sku', $row[0]);
        if ($product->fitment->exists()) {
            $request = new Request();
            $request->request->set('sku' , $row[0]);
            $ebay = new EbayController($request);
            $ebay->addFixedPriceItem($request);
        }
    }

    public function chunkSize(): int
    {
        return 5;
    }

    public function batchSize(): int
    {
        return 5;
    }
}
