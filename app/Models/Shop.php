<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string title
 * @property string slug
 * @property string email
 * @property string store_url
 * @property string username
 * @property string token
 * @property float percent
 * @property integer max_qty
 * @property integer qty_reserve
 * @property string shipping_profile_id
 * @property string shipping_profile_name
 * @property string return_profile_id
 * @property string return_profile_name
 * @property string payment_profile_id
 * @property string payment_profile_name
 * @method static create(array $array)
 * @method static where(string $string, mixed $input)
 * @method static findOrFail(string $string, string $slug)
 * @method static updateOrCreate(array $array)
 * @method static firstOrCreate(array $array, array $array1)
 */
class Shop extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
