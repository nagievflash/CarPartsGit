<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @attribute string $type
 * @attribute string $sku
 * @attribute bigInteger $ebay_id
 * @method static create(array $array)
 */
class EbayListing extends Model
{
    use HasFactory;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ebay_listings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function product() {
        return $this->hasOne(Product::class, 'sku', 'sku');
    }
}
