<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'file',
        'messages',
        'info',
        'utm',
        'created_at',
        'updated_at',
    ];

    protected $table = 'tickets';
    public $timestamps = true;

}
