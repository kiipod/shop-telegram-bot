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
     * Метод отправляет запрос к Telegram API
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
     * Метод отправляет сообщение пользователю с указанным chat_id
     *
     * @param int $chatId
     * @param string $message
     * @param array|null $keyboard
     * @return bool
     */
    public function sendMessage(int $chatId, string $message, ?array $keyboard = null): bool
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        $url = $this->apiUrl . "sendMessage";
        $response = $this->sendRequest($url, $data);

        return isset($response['ok']) && $response['ok'];
    }

    /**
     * Метод отвечает за обновление сообщения
     *
     * @param int $chatId
     * @param int $messageId
     * @param string $text
     * @param array|null $keyboard
     * @return bool
     */
    public function editMessageText(int $chatId, int $messageId, string $text, ?array $keyboard = null): bool
    {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        $url = $this->apiUrl . "editMessageText";
        $response = $this->sendRequest($url, $data);

        return isset($response['ok']) && $response['ok'];
    }

    /**
     * Метод отвечает за удаление сообщения из чата
     *
     * @param int $chatId
     * @param int $messageId
     * @return bool
     */
    public function deleteMessage(int $chatId, int $messageId): bool
    {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        $url = $this->apiUrl . "deleteMessage";
        $response = $this->sendRequest($url, $data);

        return isset($response['ok']) && $response['ok'];
    }
}
