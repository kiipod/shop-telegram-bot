<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Exception;
use Kiipod\ShopTelegramBot\Telegram\CommandHandler;
use Kiipod\ShopTelegramBot\Helpers\EnvHelper;
use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\UserRepository;
use Kiipod\ShopTelegramBot\Telegram\TelegramApi;
use Kiipod\ShopTelegramBot\Telegram\Webhook;

class WebhookController
{
    /**
     * Метод отвечает за логику работу webhook telegram
     *
     * @return void
     * @throws Exception
     */
    public function __invoke(): void
    {
        $userRepository = new UserRepository();
        $env = EnvHelper::readEnv('../.env');

        $telegramWebhook = new Webhook(botToken: $env['BOT_TOKEN']);

        // Проверяем установку webhook и передаем результат в шаблон
        $webhookStatus = $telegramWebhook->setWebhook(webhookUrl: $env['WEBHOOK_URL'])
            ? "Webhook установлен успешно!"
            : "Ошибка при установке webhook.";

        // Получаем обновления от Telegram
        $updates = $telegramWebhook->getUpdate();

        // Записываем лог $updates в файл
        $telegramWebhook->logUpdate($updates);

        // Проверка и добавление chat_id в базу данных, если его еще нет
        $userRepository->userIsExists($updates);

        // Инициализируем CommandHandler для обработки команд
        $telegramApi = new TelegramApi(botToken: $env['BOT_TOKEN']);
        $commandHandler = new CommandHandler($telegramApi);
        $commandHandler->handleCommands($updates);

        $content = TemplateHelper::includeTemplate('webhook.php', ['webhookStatus' => $webhookStatus]);
        $layout = TemplateHelper::includeTemplate('layout.php', ['content' => $content]);

        print($layout);
    }
}
