<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\Helpers\EnvHelper;
use Kiipod\ShopTelegramBot\Telegram\Webhook;

$envHelper = new EnvHelper();

try {
    $env = $envHelper->readEnv('../.env');
} catch (Exception $e) {
    echo "Ошибка при чтении .env файла: " . $e->getMessage();
}

$botToken = $env['BOT_TOKEN'];
$webhookUrl = 'https://kiipod.ru/webhook.php';
$telegramWebhook = new Webhook($botToken);

if ($telegramWebhook->setWebhook($webhookUrl)) {
    echo "Webhook установлен успешно!";
} else {
    echo "Ошибка при установке webhook.";
}

$telegramWebhook->getUpdate();
