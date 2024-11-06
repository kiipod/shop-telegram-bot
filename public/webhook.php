<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiipod\ShopTelegramBot\Telegram\Webhook;

$botToken = '8160278396:AAEhWW3AMxHvilo6XAouWSUee9GA0dnGm9o';
$webhookUrl = 'https://kiipod.ru/webhook.php';
$telegramWebhook = new Webhook($botToken);

if ($telegramWebhook->setWebhook($webhookUrl)) {
    echo "Webhook установлен успешно!";
} else {
    echo "Ошибка при установке webhook.";
}

$telegramWebhook->getUpdate();
