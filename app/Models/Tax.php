<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static findOrFail(string $string, string $ebay_id)
 * @method static firstOrFail(string $string, string $ebay_id)
 * @method static updateOrCreate(array $array, array $array1)
 */
class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'state',
        'rate'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'stare' => 'string',
        'rate'  => 'float'
    ];
}
