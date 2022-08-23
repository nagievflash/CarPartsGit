<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class InventoryImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, WithUpserts, WithStartRow, ShouldQueue
{
    use RemembersRowNumber;

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
        $product = Product::where('sku', $row['sku']);
        if ($product->exists()) {
            $product = $product->first();
            $price = (float)$row['cost'] + (float)$row['shipping_cost'] + (float)$row['handling_cost'];
            $qty = $row['stock_total'];
            $product->old_price = $product->price;
            $product->price = $price;
            $product->old_qty = $product->qty;
            $product->qty = $qty;
            $product->save();
        }
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
}
