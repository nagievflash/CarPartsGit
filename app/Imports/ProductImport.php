<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ProductImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, WithUpserts, ShouldQueue, WithStartRow
{
    use RemembersRowNumber;

    public function startRow(): int
    {
        return 2;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }

    /**
     * @param array $row
     *
     * @return Model|Product|null
     */
    public function model(array $row): Model|Product|null
    {
        $images = [];
        for ($i = 1; $i < 8; $i++) {
            $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . $row["sku"] . '_' . $i;
            $ch = curl_init($file);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode == 200) {
                $images[] = $file;
            }
            else break;
        }
        return new Product([
            'title'     => $row["title"],
            'sku'       => $row["sku"],
            'price'     => '1',
            'qty'       => '0',
            'length'    => $row["length"],
            'width'     => $row["width"],
            'height'    => $row["height"],
            'weight'    => $row["weight"],
            'images'    => implode(',', $images),
        ]);
    }

    public function chunkSize(): int
    {
        return 15;
    }

    public function batchSize(): int
    {
        return 15;
    }

    public function uniqueBy()
    {
        return 'sku';
    }
}
