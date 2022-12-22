<?php

namespace App\Models\Admin\Filter\Query;
use App\Models\Admin\Filter\Filter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends Filter
{
    /**
     * @param string $name
     */
    public function name(string $name = '')
    {
        $this->builder->where('name', 'like', "%$name%");
    }

    /**
     * @param string $lastname
     */
    public function lastname(string $lastname = '')
    {
        $this->builder->where('lastname', 'like', "%$lastname%");
    }

    /**
     * @param string $email
     */
    public function email(string $email = '')
    {
        $this->builder->where('email', 'like', "%$email%");
    }

    /**
     * @param string $phone
     */
    public function phone(string $phone = '')
    {
        $this->builder->where('phone', 'like', "%$phone%");
    }

    /**
     * @param string $sort
     */
    public function sort(string $sort = '')
    {
        $this->builder->orderBy('id', $sort);
    }
}
