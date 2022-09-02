<?php

namespace App\Console\Commands;

use App\Imports\InventoryImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class UpdateInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update inventory pricing in Ebay';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $disk = Storage::disk('ftp');
        $files = $disk->files();
        $fileData = collect();
        foreach($files as $file) {
            $fileData->push([
                'file' => $file,
                'date' => $disk->lastModified($file)
            ]);
        }
        $newest = $fileData->sortByDesc('date')->first();

        $zip = new ZipArchive;
        Storage::disk('local')->put('files/inventory.zip', Storage::disk('ftp')->get($newest['file']));
        $zip_status = $zip->open(storage_path('app/files/inventory.zip'));
        if ($zip_status === true)
        {
            $zip->setPassword("LwrG9z0cC8");
            $zip->extractTo(storage_path('app/files/inventories'));
            $zip->close();
        }

        $disk = Storage::disk('local');
        $files = $disk->files('files/inventories');
        $fileData = collect();
        foreach($files as $file) {
            $fileData->push([
                'file' => $file,
                'date' => $disk->lastModified($file)
            ]);
        }
        $newest = $fileData->sortByDesc('date')->first();

        Excel::queueImport(new InventoryImport, storage_path().'/app/'.$newest['file']);
        return 'The Job started successfully!';
    }
}
