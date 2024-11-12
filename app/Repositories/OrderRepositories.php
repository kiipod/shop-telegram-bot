<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

interface OrderRepositories
{
    public function getOrders(array $filters);

    public function createOrder(int $productId, int $productCount);

    public function updateOrderStatus(int $orderId, bool $status);

    public function deleteOrder(int $orderId);
}
