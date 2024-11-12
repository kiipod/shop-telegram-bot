<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

use Exception;
use Kiipod\ShopTelegramBot\Repositories\OrderRepository;

class CommandHandler
{
    private TelegramApi $telegramApi;
    private TelegramService $telegramService;

    /**
     * @param TelegramApi $telegramApi
     */
    public function __construct(TelegramApi $telegramApi)
    {
        $this->telegramApi = $telegramApi;
        $this->telegramService = new TelegramService($telegramApi);
    }

    /**
     * Метод обрабатывает входящие команды и callback-запросы
     *
     * @param array $data
     * @throws Exception
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
     * Метод обрабатывает команды бота
     *
     * @param array $message
     * @throws Exception
     */
    private function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Массив команд и соответствующих действий
        $commands = [
            '/start' => fn () =>
            $this->telegramApi->sendMessage($chatId, "Добро пожаловать в бот самого полезного магазина!"),

            '/orders' => fn () => $this->telegramService->sendOrderLists($chatId),

            '/help' => fn () => $this->telegramService->sendHelpMessage($chatId),
        ];

        // Проверка на команды из массива
        if (isset($commands[$text])) {
            $commands[$text]();
            return;
        }

        // Проверка, что команда начинается с /order без ID
        if (preg_match('/^\/order(?:\s*)$/', $text)) {
            $this->telegramApi->sendMessage($chatId, "Необходимо указать ID заказа");
            return;
        }

        // Паттерны для команд с параметрами
        $patterns = [
            // Проверка на наличие корректного ID после /order
            '/^\/order(?:[_=\s])(\d+)$/' => function ($matches) use ($chatId) {
                $orderId = (int)$matches[1];
                $this->telegramService->sendOrderDetails($chatId, $orderId);
            },

            // Фильтрация заказов по статусу или периоду
            '/^\/orders(?:[_=\s])(new|done|today|week|month)$/' => fn ($matches) =>
            $this->telegramService->sendOrderFilters($chatId, $matches[1]),
        ];

        // Проверка на паттерны
        foreach ($patterns as $pattern => $action) {
            if (preg_match($pattern, $text, $matches)) {
                $action($matches);
                return;
            }
        }

        $this->telegramApi->sendMessage($chatId, "Команда не распознана. Пожалуйста, проверьте правильность ввода");
    }

    /**
     * Метод обрабатывает callback-запросы
     *
     * @param array $callbackQuery
     * @throws Exception
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $callbackData = $callbackQuery['data'];
        $messageId = $callbackQuery['message']['message_id'];

        if (str_starts_with($callbackData, 'order_')) {
            $parsedData = $this->parseCallback($callbackData);
            $orderId = $parsedData['orderId'];
            $orderStatus = $parsedData['status'];

            // Обработка на основе статуса
            switch ($orderStatus) {
                case 'new':
                case 'done':
                    // Смена статуса заказа
                    $this->telegramService->sendOrderStatus($chatId, $messageId, $orderStatus, $orderId);
                    break;

                case 'confirm':
                    // Форма подтверждения удаления заказа
                    $this->telegramService->sendOrderDeleteConfirm($chatId, $orderId);
                    break;

                case 'delete':
                case 'cancel':
                    // Подтверждение и удаления заказа
                    $this->telegramService->sendOrderDeleteStatus($chatId, $messageId, $orderStatus, $orderId);
                    break;

                default:
                    // По умолчанию отображаем сообщение с информацией о заказе
                    $this->telegramService->sendOrderDetails($chatId, $orderId);
                    break;
            }
        }
    }

    /**
     * Извлекает аргументы заказа из callbackData
     *
     * @param string $callbackData
     * @return array
     */
    private function parseCallback(string $callbackData): array
    {
        $result = [
            'status' => '',
            'orderId' => 0
        ];

        if (preg_match('/^order_([a-zA-Z]+)_(\d+)$/', $callbackData, $matches)) {
            // Строка вида "order_{status}_{id}"
            $result['status'] = $matches[1];
            $result['orderId'] = (int)$matches[2];
        } elseif (preg_match('/^order_(\d+)$/', $callbackData, $matches)) {
            // Строка вида "order_{id}"
            $result['orderId'] = (int)$matches[1];
        }

        return $result;
    }
}
