<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(float[]|int[] $array)
 * @method static firstOrCreate(array $array)
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'address2',
        'city',
        'zipcode',
        'country',
        'state'
    ];
}
