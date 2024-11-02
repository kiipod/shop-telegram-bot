<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

interface ProductRepositories
{
    public function getProducts();

    public function createProduct(array $data);
}
