<?php

namespace App\Models\Admin\Filter\Query;
use App\Models\Admin\Filter\Filter;
use Illuminate\Database\Eloquent\Builder;

class ListingFilter extends Filter
{
    /**
     * @param string $ebay_id
     */
    public function ebay_id(string $ebay_id = '')
    {
        $this->builder->where('ebay_id', $ebay_id);
    }

    /**
     * @param string $shop
     */
    public function shop(string $shop = '')
    {
        $this->builder->where('shop', 'like', "%$shop%");
    }
}
