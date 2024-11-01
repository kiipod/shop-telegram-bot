<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\Index;

$indexController = new Index();
$indexController->index();
