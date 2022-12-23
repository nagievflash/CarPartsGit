<?php

namespace App\Models;

use App\Models\Admin\Filter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method create(array $array)
 * @method static firstOrFail($id)
 * @method static where(string $string, $id)
 * @method static findOrFail($id)
 * @property mixed $id
 */
class Order extends Model
{
    use HasFactory;
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product',  'order_id', 'product_sku', 'id', 'sku')->withPivot('qty', 'price', 'total');
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Address::class, 'order_address',  'order_id', 'address_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentSystem():HasOne
    {
        return $this->hasOne(PaymentSystem::class,'id','payment_system_id');
    }
}
