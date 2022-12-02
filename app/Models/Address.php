<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(float[]|int[] $array)
 * @method static firstOrCreate(array $array)
 * @method static where(string $string, mixed $id)
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


    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_address','address_id', 'user_id');
    }
}
