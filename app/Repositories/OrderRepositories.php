<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

interface OrderRepositories
{
    public function createOrder(int $productId, int $productCount, string $phone);
}
