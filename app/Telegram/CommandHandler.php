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

        if ($text === '/start') {
            $this->telegramApi->sendMessage($chatId, "Добро пожаловать в бот самого полезного магазина!");
        } elseif ($text === '/orders') {
            $this->telegramService->sendOrdersList($chatId);
        } elseif (preg_match('/^\/orders(?:[_=\s])(\d+)$/', $text, $matches)) {
            $orderId = (int)$matches[1];

            // Проверка: если ID пустое
            if ($orderId === null) {
                $this->telegramApi->sendMessage($chatId, "Необходимо указать ID заказа.");
                return;
            }

            // Получаем заказ из репозитория для проверки существования
            $orderRepository = new OrderRepository();
            $order = $orderRepository->getOrders(['id' => $orderId]);

            // Проверка: если заказ не найден
            if (empty($order)) {
                $this->telegramApi->sendMessage($chatId, "Заказ с ID {$orderId} не существует.");
                return;
            }

            // Отправка подробностей заказа
            $this->telegramService->sendOrderDetails($chatId, $orderId);
        } elseif (preg_match('/^\/orders(?:[_=\s])(new|done|day|week|month)$/', $text, $matches)) {
            $orderFilter = $matches[1];
            $this->telegramService->sendOrdersFilter($chatId, $orderFilter);
        }
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
