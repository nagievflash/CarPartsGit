<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PendingReceipt;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
use App\Mail\PendingReceipt as mPendingReceipt;

class SendEmailsToSubscribersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subs = PendingReceipt::get()->toArray();

        if(!empty($subs)){
            foreach ($subs as $sub){
                $product = Product::where('id',$sub['product_id'])->get()->toArray()->first();
                if(!empty($product)){
                    Mail::to($sub['email'])->send(new mPendingReceipt($product));
                }
            }
        }
    }
}
