<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\IndexController;

$indexController = new IndexController();
$indexController();
