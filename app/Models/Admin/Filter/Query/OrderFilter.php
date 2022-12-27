<?php

namespace App\Models\Admin\Filter\Query;
use App\Models\Admin\Filter\Filter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends Filter
{
    /**
     * @param string $status
     */
    public function status(string $status = '')
    {
        $this->builder->where('status', 'like', "%$status%");
    }

    /**
     * @param string $shipping
     */
    public function shipping(string $shipping = '')
    {
        $this->builder->where('shipping', 'like', "%$shipping%");
    }

    /**
     * @param string $discount
     */
    public function discount(string $discount = '')
    {
        $this->builder->where('discount',(double)$discount);
    }

    /**
     * @param string $total
     */
    public function total(string $total = '')
    {
        $this->builder->where('total', (double)$total);
    }

    /**
     * @param string $total_quantity
     */
    public function total_quantity(string $total_quantity = '')
    {
        $this->builder->where('total_quantity',(int)$total_quantity);
    }

    /**
     * @param string $tax
     */
    public function tax(string $tax = '')
    {
        $this->builder->where('total',(double)$tax);
    }

    /**
     * @param string $sort
     */
    public function sort(string $sort = '')
    {
        $this->builder->orderBy('id', $sort);
    }
}
