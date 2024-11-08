<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

class TelegramApi
{
    private string $apiUrl;
    private string $botToken;

    /**
     * @param $botToken
     */
    public function __construct($botToken)
    {
        $this->botToken = $botToken;
        $this->apiUrl = "https://api.telegram.org/bot" . $this->botToken . "/";
    }

    /**
     * Отправляет сообщение пользователю с указанным chat_id
     *
     * @param int $chatId
     * @param string $message
     * @return bool
     */
    public function sendMessage(int $chatId, string $message): bool
    {
        $url = $this->apiUrl . "sendMessage";
        $response = $this->sendRequest($url, [
            'chat_id' => $chatId,
            'text' => $message,
        ]);

        return isset($response['ok']) && $response['ok'];
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
