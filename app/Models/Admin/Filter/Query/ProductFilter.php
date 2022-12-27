<?php

namespace App\Models\Admin\Filter\Query;
use App\Models\Admin\Filter\Filter;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filter
{
    /**
     * @param string $title
     */
    public function title(string $title = '')
    {
        $this->builder->where('title', 'like', "%$title%");
    }

    /**
     * @param string $sku
     */
    public function sku(string $sku = '')
    {
        $this->builder->where('sku', 'like', "%$sku%");
    }

    /**
     * @param string $price
     */
    public function price(string $price = '')
    {
        $this->builder->where('price',(double)$price);
    }

    /**
     * @param string $total
     */
    public function qty(string $qty = '')
    {
        $this->builder->where('qty', (int)$qty);
    }

    /**
     * @param string $status
     */
    public function status(string $status = '')
    {
        $this->builder->where('status',(int)$status);
    }

    /**
     * @param string $sort
     */
    public function sort(string $sort = '')
    {
        $this->builder->orderBy('id', $sort);
    }
}
