<?php

namespace App\Models;

use App\Models\Admin\Filter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class Backlog extends Model
{
    use HasFactory;
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @param string $type
     * @param string $value
     * @return static
     */
    public static function createBacklog(string $type, string $value): self
    {
        return self::create([
            'type'  => $type,
            'value' => $value,
        ]);
    }
}
