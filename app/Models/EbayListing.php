<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * @property mixed $id
 * @property mixed $product
 * @property string $type
 * @property mixed $ebay_id
 * @property mixed $sku
 * @method static create(array $array)
 * @method static where(string $string, mixed $input)
 * @method static first()
 * @method static when(bool $has, \Closure $param)
 * @method static paginate(int $int)
 * @method static take(int $int)
 * @method static updateOrCreate(array $array, array $array1)
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


    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'listing_product', 'listing_id', 'product_id')->withPivot('quantity')->withTimestamps();
    }

    public function getPrice() {
        $prices = array();
        foreach ($this->products as $item) {
            $prices[$item->sku]['qty'] = $item->pivot->quantity;
            foreach (Warehouse::where('sku', $item->sku)->orWhere('partslink', $item->sku)->get() as $warehouse) {
                $prices[$item->sku][$warehouse->supplier->title] = [
                    'price'     => $warehouse->price + $warehouse->handling,
                    'qty'       => $warehouse->qty,
                    'shipping'  => $warehouse->shipping,
                ];
                if ($warehouse->supplier->title == 'LKQ') {
                    $packages = DB::table('lkq_packages')
                        ->select('method')
                        ->where('sku', $item->sku);
                    if ($packages->exists()) $prices[$item->sku][$warehouse->supplier->title]['method'] =
                        DB::table('lkq_packages')
                        ->select('method')
                        ->where('sku', $item->sku)
                        ->first()->method;
                    else $prices[$item->sku][$warehouse->supplier->title]['method'] = 'MP';
                }
            }
        }
        $price = 0;
        $shippingPF = 0;
        $shippingLKQ = 0;
        $priceLKQ = 0;
        $methodLKQ = 'SP';
        foreach ($prices as $item) {
            if (isset($item["PF"])) {
                $price += $item["PF"]["price"] * $item["qty"];
                $shippingPF = max($shippingPF, $item["PF"]["shipping"]);
            }
            elseif (isset($item["LKQ"])) {
                $price += $item["LKQ"]["price"] * $item["qty"];
                $shippingLKQ += $item["qty"];
                if ($item["LKQ"]["method"] == 'LP' || $item["LKQ"]["method"] == 'LTL') $methodLKQ = $item["LKQ"]["method"];
                elseif ($item["LKQ"]["method"] == 'MP' && $methodLKQ == 'SP') $methodLKQ = 'MP';
            }
            else {
                $price = 0;
            }
        }
        $shippingLKQPrice = 0;
        if ($shippingLKQ > 0) {
            if ($methodLKQ == 'SP') {
                $shippingLKQPrice = 16 + ($shippingLKQ - 1) * 5;
            }
            if ($methodLKQ == 'MP') {
                $shippingLKQPrice = 28 + ($shippingLKQ - 1) * 10;
            }
        }
        return $price + $shippingPF + $shippingLKQPrice;
    }

    public function getQuantity() {
        $quantity = 0;
        foreach ($this->products as $item) {
            $qty = $item->pivot->quantity;
            $quantities[$item->sku] = array();
            foreach ($item->warehouses as $warehouse) {
                $quantity = max($quantity, (int)($warehouse->qty / $qty));
            }
        }
        return $quantity;
    }
}
