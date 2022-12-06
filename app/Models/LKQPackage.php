<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(float[]|int[] $array)
 * @method static firstOrCreate(array $array)
 * @method static where(string $string, mixed $id)
 */
class LKQPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'method',
    ];

    protected $table = 'lkq_packages';
}
