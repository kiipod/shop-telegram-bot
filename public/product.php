<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\ProductController;

$indexController = new ProductController();
$indexController->create();
