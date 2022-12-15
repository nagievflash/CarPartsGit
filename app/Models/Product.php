<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static paginate(int $int)
 * @method static where(string $string, mixed $input)
 * @method static when(bool $has, \Closure $param)
 * @method static findOrFail(string $string, string $sku)
 * @method static whereHas(string $string, \Closure $param)
 * @method static firstOrCreate(array $array, array $array1)
 * @method static firstOrNew(array $array)
 * @method static updateOrCreate(array $array, array $array1)
 * @method static hasFitments()
 * @method static join(string $string, string $string1, string $string2, string $string3)
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
        'images' => 'array',
        'shipping' => 'float',
        'handling' => 'float'
    ];

    protected $appends = ['shipping', 'handling'];

    /**
     * @return string
     */
    public function getTitleAttribute(): string
    {
        return $this->getTitle();
    }

    public function getHandlingAttribute(): string
    {
        if (Warehouse::where('sku', $this->sku)->where('supplier_id', 1)->exists()) {
            return (float) Warehouse::where('sku', $this->sku)->first()->handling + Warehouse::where('sku', $this->sku)->first()->handling / 4;
        }
        else return 0;
    }

    public function getShippingAttribute(): string
    {
        if (Warehouse::where('sku', $this->sku)->where('supplier_id', 1)->exists()) {
            return (float) Warehouse::where('sku', $this->sku)->first()->shipping + Warehouse::where('sku', $this->sku)->first()->shipping / 4;
        }
        else return 0;
    }

    public function setPriceAttribute($value)
    {
        if (Warehouse::where('sku', $this->sku)->where('supplier_id', 1)->exists()) {
            return Warehouse::where('sku', $this->sku)->first()->price + Warehouse::where('sku', $this->sku)->first()->price / 4;
        }
        else return 0;
    }

    public function setQtyAttribute($value)
    {
        if (Warehouse::where('sku', $this->sku)->where('supplier_id', 1)->exists()) {
            return Warehouse::where('sku', $this->sku)->first()->qty;
        }
        else return 0;
    }

    public function setImagesAttribute($value)
    {
        if (Warehouse::where('sku', $this->sku)->where('supplier_id', 1)->exists()) {
            return Images::where('sku', $this->sku)->get();
        }
        else return 0;
    }

    /**
     * Get the VEHICLE COMPATIBILITY FITMENT
     */
    public function fitments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Fitment::class, 'sku', 'sku');
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

    public function rate(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Rates::class, 'rate');
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Images::class, 'item');
    }

    public function scopeIsAvailable($query)
    {
        return $query->where('qty', '>', 0);
    }

    public function scopeHasFitments($query)
    {
        return $query->where('fitments', '=', 1);
    }

}
