<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rates extends Model
{
    protected $fillable = [
        'value',
        'rate_type',
        'rate_id'
    ];

    public $timestamps = false;

    public function rateable()
    {
        return $this->morphTo();
    }
}
