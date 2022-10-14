<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @method static paginate(int $int)
 * @method static where(string $string, mixed $input)
 * @method static when(bool $has, \Closure $param)
 * @method static findOrFail(string $string, string $sku)
 * @method static whereHas(string $string, \Closure $param)
 * @method static firstOrCreate(array $array, array $array1)
 * @method static firstOrNew(array $array)
 * @property string $sku
 * @property string $title
 * @property string $partslink
 * @property mixed $oem_number
 * @property integer $price
 * @property integer $qty
 * @property integer $old_price
 * @property integer $old_qty
 * @property mixed|array|string $images
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'images' => 'array'
    ];

    /**
     * @return string
     */
    public function getTitleAttribute(): string
    {
        return $this->getTitle();
    }

    /**
     * Get the VEHICLE COMPATIBILITY FITMENT
     */
    public function fitments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Compatibility::class, 'sku', 'sku');
    }


    public function listings(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EbayListing::class, 'listing_product', 'product_id', 'listing_id');
    }

    public function warehouses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Warehouse::class, 'sku', 'sku');
    }

    /**
     * Get the product's attributes
     */
    public function attributes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attribute::class, 'sku', 'sku');
    }


    public function getTitle(): string
    {
        $fitment = Compatibility::select('make_name', 'part_name', 'model_name', 'year', 'position')->where('sku', $this->sku)->get();
        $positions = $fitment->where('position', '!=', '')->unique('position')->count() > 0 ? ' '.strtolower($fitment->where('position', '!=', '')->unique('position')->implode('position', ', ')) : '';
        $title = '';
        if ($fitment->count() > 0) {
            $items = $fitment->where('model_name', $fitment->first()->model_name)->sortBy(['year', 'asc']);
            if ($items->first()->year != $items->last()->year) $title = 'New ' . $items->first()->part_name . $positions . ' for ' . $items->first()->year . '-' . $items->last()->year . ' ' . $items->first()->make_name . ' ' . $items->first()->model_name;
            else $title = 'New ' . $items->first()->part_name . $positions . ' for ' . $items->first()->year . ' ' . $items->first()->make_name . ' ' . $items->first()->model_name;
        }
        return $title;
    }

    public function getTitleShort(): string
    {
        $fitment = Fitment::select('make_name', 'part_name', 'model_name', 'year')->where('sku', $this->sku)->get();
        $title = '';
        if ($fitment->count() > 0) {
            $items = $fitment->where('model_name', $fitment->first()->model_name)->sortBy(['year', 'asc']);
            if ($items->first()->year != $items->last()->year) $title = 'New ' . $items->first()->part_name . ' for ' . $items->first()->year . '-' . $items->last()->year . ' ' . $items->first()->make_name . ' ' . $items->first()->model_name;
            else $title = 'New ' . $items->first()->part_name . ' for ' . $items->first()->year . ' ' . $items->first()->make_name . ' ' . $items->first()->model_name;
        }

        if (strlen($title) > 80)  {
            $items = $fitment->where('model_name', $fitment->first()->model_name)->sortBy(['year', 'asc']);
            $title = 'New ' . $this->title . 'For ' .$items->first()->make_name . ' ' . $items->first()->model_name;
        }

        return $title;
    }

    public function scopeHasFitments($query)
    {
        return $query->has('fitments');
    }


    public function scopeIsAvailable($query)
    {
        return $query->where('qty', '>', 0);
    }

}
