<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterImport;

class CategoriesImport implements ToModel, WithChunkReading, WithBatchInserts, ShouldQueue, WithHeadingRow
{
    use RemembersRowNumber;

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
        $category = [
            'mcat_name'    => $row['mcat_name'],
            'mscat_name'   => $row['mscat_name'],
            'part_name'    => $row['part_name'],
        ];

        Category::updateOrCreate($category);
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

    }
}
