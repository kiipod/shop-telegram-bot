<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

class TelegramWebhook
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
     * @return array|null
     */
    public function getUpdate(): ?array
    {
        $update = file_get_contents("php://input");
        return json_decode($update, true);
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
}

