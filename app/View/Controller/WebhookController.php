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
        $templateHelper = new TemplateHelper();
        $envHelper = new EnvHelper();
        $userRepository = new UserRepository();

        try {
            $env = $envHelper->readEnv('../.env');
        } catch (Exception $e) {
            // Если произошла ошибка при чтении .env файла, передаем ошибку в шаблон
            $content = $templateHelper->includeTemplate('error.php', ['error' => "Ошибка при чтении .env файла: " . $e->getMessage()]);
            $layout = $templateHelper->includeTemplate('layout.php', ['content' => $content]);
            print($layout);
            return;
        }

        // Установка webhook
        $botToken = $env['BOT_TOKEN'];
        $webhookUrl = 'https://kiipod.ru/webhook.php';
        $telegramWebhook = new Webhook($botToken);

        // Проверяем установку webhook и передаем результат в шаблон
        $webhookStatus = $telegramWebhook->setWebhook($webhookUrl)
            ? "Webhook установлен успешно!"
            : "Ошибка при установке webhook.";

        // Получаем обновления от Telegram
        $updates = $telegramWebhook->getUpdate();

        // Записываем лог $updates в файл
        $telegramWebhook->logUpdate($updates);

        // Проверка и добавление chat_id в базу данных, если его еще нет
        if (!empty($updates['message']['chat']['id'])) {
            $chatId = $updates['message']['chat']['id'];

            // Проверка существования chat_id
            if (!$userRepository->findByChatId($chatId)) {
                $userRepository->create($chatId);
            }
        }

        // Инициализируем CommandHandler для обработки команд
        $telegramApi = new TelegramApi($botToken);
        $commandHandler = new CommandHandler($telegramApi);
        $commandHandler->handleCommands($updates);

        $content = $templateHelper->includeTemplate('webhook.php', [
            'webhookStatus' => $webhookStatus
        ]);

        $layout = $templateHelper->includeTemplate('layout.php', ['content' => $content]);

        print($layout);
    }
}
