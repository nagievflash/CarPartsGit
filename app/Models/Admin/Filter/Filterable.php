<?php

namespace App\Models\Admin\Filter;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Admin\Filter\Filter;

trait Filterable
{
    /**
     * @param Builder $builder
     * @param Filter $filter
     */
    public function scopeFilter(Builder $builder, Filter $filter)
    {
        $filter->apply($builder);
    }
}
