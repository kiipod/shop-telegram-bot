<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\WebhookController;

$webhookController = new WebhookController();
$webhookController();
