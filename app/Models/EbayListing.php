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
 * @property mixed $shop
 * @property mixed $fixed
 * @method static create(array $array)
 * @method static where(string $string, mixed $input)
 * @method static first()
 * @method static when(bool $has, \Closure $param)
 * @method static paginate(int $int)
 * @method static take(int $int)
 * @method static updateOrCreate(array $array, array $array1)
 * @method static findOrFail(string $string, string $ebay_id)
 * @method static firstOrFail($id)
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

    public $visited = array();


    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'listing_product', 'listing_id', 'product_id')->withPivot('quantity')->withTimestamps();
    }

    public function shops(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop', 'slug');
    }

    public function partslinks(): \Illuminate\Support\Collection
    {
        return DB::table('listing_partslink')
            ->where('listing_id', $this->id)
            ->get();
    }

    public function getPrice() {
        $price = DB::table('listing_price')
            ->where('listing_id', $this->id)
            ->first();
        return $price->price;
    }

    public function getPriceGraph($graph = false, $needQty = false) {
        $partslinks = array();
        $error = false;
        foreach ($this->partslinks() as $key => $item) {
            $p = (string)$item->partslink;
            if (strlen($p) > 6) {
                $parts = Warehouse::where("sku", $p)->orWhere('partslink', 'like', '%'. $p .'%');
            }
            else $parts = Warehouse::where("sku", $p)->orWhere('partslink', $p);
            $parts = $parts->where('supplier_id', '!=', 3);
            if ($parts->exists()) {
                foreach ($parts->get() as $k => $part) {
                    $packErr = false;
                    if ($part->supplier->title == 'PF') {
                        $jc = Warehouse::where('sku', $part->sku)->where('supplier_id', 3);
                        if ($jc->exists()) {
                            $jc = $jc->first();
                            if (($jc->qty - 2) >= $item->quantity) {
                                $partslinks[$key][] = array(
                                    'supplier'  => 'JC',
                                    'sku'       => $jc->sku,
                                    'partslink' => $part->partslink,
                                    'qty'       => $jc->qty,
                                    'nums'      => $item->quantity,
                                    'price'     => $jc->price,
                                    'shipping'  => $jc->shipping,
                                );
                            }
                        }

                    }
                    if (($part->qty - 2) >= $item->quantity) {
                        $arr = array(
                            'supplier'  => $part->supplier->title,
                            'sku'       => $part->sku,
                            'partslink' => $part->partslink,
                            'qty'       => $part->qty,
                            'nums'      => $item->quantity,
                            'price'     => $part->price + $part->handling,
                            'shipping'  => $part->shipping,
                        );
                        if ($part->supplier->title == 'LKQ') {
                            $str = $part->partslink;
                            preg_match('/.*[0-9]/', $str, $res);
                            $packages = DB::table('lkq_packages')
                                ->select('method')
                                ->where('sku', 'like', '%'.$res[0].'%');
                            if ($packages->exists()) $arr['method'] =
                                $packages->first()->method;
                            else {
                                $packErr = true;
                                Backlog::createBacklog('error 403', 'Package method not found for: ' . $part->partslink . ', listing id: ' . $this->ebay_id);
                            }
                        }


                        if (!$packErr) $partslinks[$key][] = $arr;
                    }
                }
               if (!isset($partslinks[$key])) $error = true;
            }
            else {
                Backlog::createBacklog('error 402', 'Partslink not found: ' . $p . ', listing id: ' . $this->ebay_id);
                $error = true;
                // dd('Error 402: some parts not found');
            }
        }
        if (!$error && sizeof($partslinks) > 0) {
            $clear = array();
            foreach ($partslinks as $key => $part) {
                $minLKQ = array();
                $minPF  = array();
                $minJC  = array();
                foreach ($part as $key => $item) {
                    if ($item['supplier']  == "PF") {
                        if (sizeof($minPF) == 0) $minPF = $item;
                        else {
                            if ($item['price'] < $minPF['price']) $minPF = $item;
                        }
                    }
                    if ($item['supplier']  == "LKQ") {
                        if (sizeof($minLKQ) == 0) $minLKQ = $item;
                        else {
                            if ($item['price'] < $minLKQ['price']) $minLKQ = $item;
                        }
                    }
                    if ($item['supplier']  == "JC") {
                        if (sizeof($minJC) == 0) $minJC = $item;
                        else {
                            if ($item['price'] < $minJC['price']) $minJC = $item;
                        }
                    }
                }
                $parts = array();
                if ((sizeof($minPF) > 0) && (sizeof($minLKQ) > 0)) {
                    $parts[] = $minPF;
                    if (($minPF['price'] + $minPF['shipping']) >= ($minLKQ['price'] + Setting::where('key', 'lkq_cost_' . strtolower($minLKQ['method']))->first()->value)) {
                        $parts[] = $minLKQ;
                    }
                }
                elseif ((sizeof($minPF) > 0) && (sizeof($minLKQ) == 0)) {
                    $parts[] = $minPF;
                }
                elseif ((sizeof($minPF) == 0) && (sizeof($minLKQ) > 0)) {
                    $parts[] = $minLKQ;
                }
                if (sizeof($minJC) > 0) $parts[] = $minJC;
                $clear[] = $parts;
            }
            $collection = $this->cartesian($clear);
            $price = 0;
            $currentItem = array();
            foreach ($collection as $key => $items) {
                $localPrice = 0;
                $shipping = 0;
                $shippingLKQ = 0;
                $shippingLKQPrice = 0;
                $sp = 0;
                $mp = 0;
                $lp = 0;
                $lt = 0;
                foreach ($items as $item) {
                    $localPrice += $item['price'] * $item['nums'];
                    $shipping = max($shipping, $item['shipping']);
                    if ($item['supplier'] == 'LKQ') {
                        $shippingLKQ += $item['nums'];
                        switch ($item['method']) {
                            case "SP":
                                $sp += $item['nums'];
                                break;
                            case "MP":
                                $mp += $item['nums'];
                                break;
                            case "LP":
                                $lp += $item['nums'];
                                break;
                            case "LTL":
                            case "LT":
                                $lt += $item['nums'];
                                break;
                        }
                    }
                }
                if ($shippingLKQ < 3) {
                    if ($shippingLKQ > 0) {
                        if ($sp > 0) $shippingLKQPrice += Setting::where('key','lkq_cost_sp')->first()->value + ($sp - 1) * Setting::where('key','lkq_cost_additional_sp')->first()->value;
                        if ($mp > 0) $shippingLKQPrice += Setting::where('key','lkq_cost_mp')->first()->value + ($mp - 1) * Setting::where('key','lkq_cost_additional_mp')->first()->value;
                        if ($lp > 0) $shippingLKQPrice += Setting::where('key','lkq_cost_lp')->first()->value * $lp;
                        if ($lt > 0) $shippingLKQPrice += Setting::where('key','lkq_cost_lt')->first()->value * $lt;
                    }
                    $localPrice = $localPrice + $shippingLKQPrice + $shipping;
                    if ($price != 0 && $price > $localPrice) {
                        $price = $localPrice;
                        $currentItem = $items;
                    }
                    elseif ($price == 0) {
                        $price = $localPrice;
                        $currentItem = $items;
                    }
                }
            }
            $price = $price + $price  * $this->shops->percent / 100;
            $currentItem['price'] = $price;
            if ($graph) return $currentItem;
            else return $price;
        }
        else {
            if ($graph) return array(0 => array('qty' => 0, 'nums' => 1), 'price' => 0 );
            else return 0;
        }
    }

    public function getQuantity() {
        $listing = DB::table('listing_price')
            ->where('listing_id', $this->id)
            ->first();
        return $listing->quantity;
    }


    /**
     * Function for (decart) cartesian product arrays of variations
     * @param $arr
     * @return mixed
     */
    public function cartesian($arr): mixed
    {
        $variant = array();
        $result  = array();
        $sizearr = sizeof($arr);

        return $this->recursiv($arr, $variant, -1, $result, $sizearr);
    }

    public function recursiv($arr, $variant, $level, $result, $sizearr) {
        $level++;
        if ($level < $sizearr){
            foreach ($arr[$level] as $val) {
                $variant[$level] = $val;
                $result = $this->recursiv($arr, $variant, $level, $result, $sizearr);
            }
        } else {
            $result[] = $variant;
        }
        return $result;
    }

    public function updatePrice() {
        if ($this->fixed == 0) {
            $listingInfo = $this->getPriceGraph(true);
            $listingPrice = DB::table('listing_price')->where('listing_id', $this->id);
            $nums = $listingInfo[0]['nums'];
            if ($nums == 0) $nums = 1;
            if ($listingPrice->exists()) {
                $listingPrice->update(['price' => $listingInfo['price'], 'price_old' => $listingPrice->first()->price, 'quantity' => $listingInfo[0]['qty'] / $nums]);
            }
            else {
                DB::table('listing_price')->insert(['listing_id' => $this->id, 'price' => $listingInfo['price'], 'quantity' => $listingInfo[0]['qty'] / $nums]);
            }
        }
    }
}
