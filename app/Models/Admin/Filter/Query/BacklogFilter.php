<?php

namespace App\Models\Admin\Filter\Query;
use App\Models\Admin\Filter\Filter;
use Illuminate\Database\Eloquent\Builder;

class BacklogFilter extends Filter
{
    /**
     * @param string $id
     */
    public function id(string $id = '')
    {
        $this->builder->where('id', $id);
    }

    /**
     * @param string $type
     */
    public function type(string $type = '')
    {
        $this->builder->where('type', 'like', "%$type%");
    }

    /**
     * @param string $value
     */
    public function email(string $value = '')
    {
        $this->builder->where('value', 'like', "%$value%");
    }

    /**
     * @param string $sort
     */
    public function sort(string $sort = '')
    {
        $this->builder->orderBy('id', $sort);
    }
}
