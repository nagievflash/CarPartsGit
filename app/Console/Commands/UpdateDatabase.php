<?php

namespace App\Console\Commands;

use App\Imports\AutoelementsDatabaseImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class UpdateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Autoelements database';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->output->title('Starting import');
        //Excel::import(new AutoelementsDatabaseImport(), storage_path().'/app/files/db.csv');
        (new AutoelementsDatabaseImport())->withOutput($this->output)->import(storage_path().'/app/files/db.csv');
        $this->output->success('Import successful');
    }
}
