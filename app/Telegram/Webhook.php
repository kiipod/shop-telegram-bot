<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

use Kiipod\ShopTelegramBot\Repositories\UserRepository;

class Webhook
{
    private string $apiUrl;
    private string $botToken;

    /**
     * @param string $botToken
     */
    public function __construct(string $botToken)
    {
        $this->botToken = $botToken;
        $this->apiUrl = "https://api.telegram.org/bot" . $this->botToken . "/";
    }

    /**
     * Устанавливает webhook для Telegram-бота
     *
     * @param string $webhookUrl
     * @return bool
     */
    public function setWebhook(string $webhookUrl): bool
    {
        $url = $this->apiUrl . "setWebhook";
        $response = $this->sendRequest($url, ['url' => $webhookUrl]);

        return isset($response['ok']) && $response['ok'];
    }

    /**
     * Удаляет webhook, если он больше не нужен
     *
     * @return bool
     */
    public function deleteWebhook(): bool
    {
        $url = $this->apiUrl . "deleteWebhook";
        $response = $this->sendRequest($url);

        return isset($response['ok']) && $response['ok'];
    }

    /**
     * Обрабатывает входящие обновления от Telegram
     *
     * @return void
     */
    public function getUpdate(): void
    {
        $update = file_get_contents("php://input");
        $data = json_decode($update, true);

        if ($data) {
            $this->handleCommands($data);
        }
    }

    /**
     * Отправляет запрос к Telegram API
     *
     * @param string $url
     * @param array $data
     * @return array|null
     */
    private function sendRequest(string $url, array $data = []): ?array
    {
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result ? json_decode($result, true) : null;
    }

    /**
     * Обрабатывает команды, приходящие от пользователя
     *
     * @param array $data
     */
    private function handleCommands(array $data): void
    {
        $userRepository = new UserRepository();
        $telegramApi = new TelegramApi($this->botToken);

        if (isset($data['message'])) {
            $chatId = $data['message']['chat']['id'];
            $text = $data['message']['text'] ?? '';

            // Добавление chatId в базу
            $userRepository->create($chatId);

            // Пример обработки текстовых команд
            if ($text === '/start') {
                $telegramApi->sendMessage($chatId, "Добро пожаловать в бот самого полезного магазина!");
            } else {
                $telegramApi->sendMessage($chatId, "Неизвестная команда.");
            }
        }

        // Обработка callback-query
        if (isset($data['callback_query'])) {
            $chatId = $data['callback_query']['message']['chat']['id'];
            $callbackData = $data['callback_query']['data'];

            // Пример обработки callback-query
            if ($callbackData === 'action_1') {
                $telegramApi->sendMessage($chatId, "Вы выбрали действие 1.");
            } else {
                $telegramApi->sendMessage($chatId, "Неизвестное действие.");
            }
        }
    }
}

