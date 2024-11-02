<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\OrderController;

// Получаем productId из запроса
$productId = isset($_GET['productId']) ? (int)$_GET['productId'] : null;

// Проверяем, что productId передан и корректен
if ($productId) {
    $orderController = new OrderController();
    $orderController->index($productId);
} else {
    echo "Product ID не указан или некорректен.";
}
