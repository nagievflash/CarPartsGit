<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property mixed $product
 * @property string $type
 * @property mixed $ebay_id
 * @property mixed $sku
 * @method static create(array $array)
 * @method static where(string $string, mixed $input)
 * @method static first()
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

    /**
     * Get product associated with this Listing
     * @return HasOne
     */
    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'sku', 'sku');
    }
}
