<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    protected $fillable = [
        'item',
        'item_type',
        'item_id',
        'url',
        'small',
        'medium',
        'large',
        'sort_order',
    ];

    public $timestamps = false;

    public function itemable()
    {
        return $this->morphTo();
    }
}
