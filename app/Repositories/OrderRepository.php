<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(protected Order $model) {}

    public function create(array $order): Order
    {
        return $this->model->create($order);
    }
}
