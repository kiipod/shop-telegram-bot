<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\View\Controller\WebhookController;

$webhookController = new WebhookController();

try {
    $webhookController();
} catch (Exception $e) {
    echo "Webhook не установлен" . $e->getMessage() . "\n";
}
