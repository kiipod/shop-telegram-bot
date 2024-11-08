<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\OrderController;

$orderController = new OrderController();

try {
    $orderController->create();
} catch (Exception $e) {
    echo "Ошибка создания заказа: " . $e->getMessage();
}
