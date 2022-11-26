<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static firstOrCreate(array $category)
 * @method static create(array $category)
 * @method static updateOrCreate(array $category)
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'mcat_name',
        'mscat_name',
        'part_name'
    ];
}
