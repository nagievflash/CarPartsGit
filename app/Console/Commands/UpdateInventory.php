<?php

namespace App\Console\Commands;

use App\Imports\InventoryImport;
use App\Imports\InventoryImportLKQ;
use App\Imports\InventoryImportPF;
use App\Jobs\UpdateListingsPricesJob;
use App\Models\EbayListing;
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
    protected $signature = 'update:inventory {supplier=none}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update inventory pricing in Ebay';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        if ($this->argument('supplier') == 'pf') {
            $disk = Storage::disk('pf');
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
            Storage::disk('local')->put('files/inventory.zip', $disk->get($newest['file']));
            $zip_status = $zip->open(storage_path('app/files/inventory.zip'));
            if ($zip_status === true)
            {
                $zip->setPassword(env('PF_ZIP_PASSWORD'));
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

            Excel::queueImport(new InventoryImportPF, storage_path().'/app/'.$newest['file']);
            return 'The Job started successfully!';
        }

        if ($this->argument('supplier') == 'lkq') {
            $disk = Storage::disk('lkq');
            $inventoryFile = '';
            foreach ($disk->files() as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'csv') {
                    $inventoryFile = $file;
                }
            }
            if ($inventoryFile != '') Storage::disk('local')->put('files/lkq_inventory.csv', $disk->get($inventoryFile));
            else dd('file not found');

            Excel::queueImport(new InventoryImportLKQ, storage_path().'/app/files/lkq_inventory.csv');
            return 'The Job started successfully!';
        }

        foreach (EbayListing::all() as $listing) {
            dispatch(new UpdateListingsPricesJob($listing));
        }

    }
}
