<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

class CommandHandler
{
    private TelegramApi $telegramApi;

    /**
     * @param TelegramApi $telegramApi
     */
    public function __construct(TelegramApi $telegramApi)
    {
        $this->telegramApi = $telegramApi;
    }

    /**
     * Метод обрабатывает входящие команды и callback-запросы
     *
     * @param array $data
     */
    public function handleCommands(array $data): void
    {
        if (isset($data['message'])) {
            $this->handleMessage($data['message']);
        }

        if (isset($data['callback_query'])) {
            $this->handleCallbackQuery($data['callback_query']);
        }
    }

    /**
     * Метод обрабатывает текстовые сообщения
     *
     * @param array $message
     */
    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        if ($text === '/start') {
            $this->telegramApi->sendMessage($chatId, "Добро пожаловать в бот самого полезного магазина!");
        } else {
            $this->telegramApi->sendMessage($chatId, "Неизвестная команда.");
        }
    }

    /**
     * Метод обрабатывает callback-запросы
     *
     * @param array $callbackQuery
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $callbackData = $callbackQuery['data'];

        if ($callbackData === 'action_1') {
            $this->telegramApi->sendMessage($chatId, "Вы выбрали действие 1.");
        } else {
            $this->telegramApi->sendMessage($chatId, "Неизвестное действие.");
        }
    }
}
