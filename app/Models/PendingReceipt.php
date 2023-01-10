<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PendingReceipt extends Model
{
    protected $table = 'pending_receipts';

    protected $fillable = [
        'product_id',
        'email',
    ];

    public function getProductStatus():HasOne
    {
        return $this->hasOne(Product::class,'id','product_id')->select('status');
    }
}
