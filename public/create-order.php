<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\OrderController;

$orderController = new OrderController();
$orderController->create();
