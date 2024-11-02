<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\ProductController;

$productController = new ProductController();
$productController->create();
