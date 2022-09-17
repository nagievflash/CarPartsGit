<?php

namespace App\Imports;

use App\Models\Compatibility;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterImport;

class FitmentImport implements ToModel, WithChunkReading, WithBatchInserts, ShouldQueue, WithHeadingRow
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
        $fitment = [
            'application_id'    => $row['application_id'],
            'part_name'         => $row['part_name'],
            'sku'               => $row['sku'],
            'sku_merchant'      => $row['sku_merchant'],
            'year'              => $row['year'],
            'make_name'         => $row['make_name'],
            'model_name'        => $row['model_name'],
            'submodel_name'     => $row['submodel_name'],
            'liter'             => $row['liter'],
            'cylinders'         => $row['cylinders'],
            'bodytypename'      => $row['bodytypename'],
            'mfrbodycodename'   => $row['mfrbodycodename'],
            'position'          => $row['position'],
            'fnotes_name'       => $row['fnotes_name'],
        ];

        $fitments = Compatibility::create($fitment);
        $fitments->save();
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
