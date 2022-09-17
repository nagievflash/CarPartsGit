<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static select(string $string, string $string1, string $string2, string $string3, string $string4)
 * @method static paginate()
 * @method static where(string $string, mixed $input)
 * @method static firstOrNew(array $update)
 * @method static create(array $fitment)
 */
class Compatibility extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'compatibilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return list of attributes usable in fitments
     * @return array
     */
    public function getUsableAttributes(): array
    {
        $attributes = $this->attributesToArray();
        $except = ['id', 'application_id', 'brand_name', 'part_name', 'pcdb_part_name', 'sku', 'team', 'sku_merchant'];
        return array_diff_key($attributes, array_flip((array) $except));
    }
}
