<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

use DateTime;
use Exception;
use Kiipod\ShopTelegramBot\Repositories\OrderRepository;

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
            $this->sendOrdersList($chatId);
        } elseif ($text === preg_match('/^\/order (\d+)$/', $text, $matches)) {
            $orderId = (int)$matches[1];
            $this->sendOrderDetails($chatId, $orderId);
        } elseif ($text === preg_match('/^\/order (\d+)$/', $text, $matches)) {
            $orderStatus = (string)$matches[1];
            $this->sendOrdersStatusFilter($chatId, $orderStatus);
        } elseif ($text === preg_match('/^\/order (\d+)$/', $text, $matches)) {
            $orderPeriod = (string)$matches[1];
            $this->sendOrdersPeriodFilter($chatId, $orderPeriod);
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
            $parsedData = $this->parseOrderCallback($callbackData);
            $orderId = $parsedData['orderId'];
            $orderStatus = $parsedData['status'];

            // Обработка на основе статуса
            switch ($orderStatus) {
                case 'new':
                case 'done':
                    // Смена статуса заказа
                    $this->sendOrderStatus($chatId, $messageId, $orderStatus, $orderId);
                    break;

                case 'confirm':
                    // Форма подтверждения удаления заказа
                    $this->sendOrderDeleteConfirm($chatId, $orderId);
                    break;

                case 'delete':
                case 'cancel':
                    // Подтверждение и удаления заказа
                    $this->sendOrderDeleteStatus($chatId, $messageId, $orderStatus, $orderId);
                    break;

                default:
                    // Если статус не указан, просто отображаем детали заказа
                    $this->sendOrderDetails($chatId, $orderId);
                    break;
            }
        }
    }


    /**
     * Метод отвечает за отправку списка заказов по команде /orders
     *
     * @param int $chatId
     * @return void
     * @throws Exception
     */
    private function sendOrdersList(int $chatId): void
    {
        $orderRepository = new OrderRepository();
        $orders = $orderRepository->getOrders();

        if ($orders) {
            foreach ($orders as $order) {
                // Формируем сообщение для каждого заказа
                $message = "Заказ № {$order['id']}\n";
                $message .= "Сумма: " . ($order['product_price'] * $order['product_count']) . " ₽\n";
                $message .= "Создан: " . (new DateTime($order['created_at']))->format('d F Y, H:i');

                $keyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Подробнее о заказе',
                                'callback_data' => "order_{$order['id']}"
                            ]
                        ]
                    ]
                ];

                $this->telegramApi->sendMessage($chatId, $message, $keyboard);
            }
        } else {
            $this->telegramApi->sendMessage($chatId, "У вас нет заказов.");
        }
    }

    /**
     * Извлекает ID заказа из callbackData
     *
     * @param string $callbackData
     * @return array
     */
    private function parseOrderCallback(string $callbackData): array
    {
        // Устанавливаем значения по умолчанию
        $result = [
            'status' => '',
            'orderId' => 0
        ];

        // Пытаемся найти статус и ID
        if (preg_match('/^order_([a-zA-Z]+)_(\d+)$/', $callbackData, $matches)) {
            // Строка вида "order_status_id"
            $result['status'] = $matches[1];
            $result['orderId'] = (int)$matches[2];
        } elseif (preg_match('/^order_(\d+)$/', $callbackData, $matches)) {
            // Строка вида "order_id"
            $result['orderId'] = (int)$matches[1];
        }

        return $result;
    }

    /**
     * Отправляет детали заказа пользователю
     *
     * @param int $chatId
     * @param int|null $orderId
     * @return void
     * @throws Exception
     */
    private function sendOrderDetails(int $chatId, ?int $orderId): void
    {
        if ($orderId === null) {
            $this->telegramApi->sendMessage($chatId, "Необходимо указать ID заказа.");
            return;
        }

        $orderRepository = new OrderRepository();
        $order = $orderRepository->getOrders(['id' => $orderId]);

        if (!$order) {
            $this->telegramApi->sendMessage($chatId, "Заказ с ID {$orderId} не существует.");
            return;
        }

        $modifiedAt = $order['modified_at'] ? (new DateTime($order['modified_at']))->format('d F Y, H:i') : "Статус не изменялся";

        $message = "Заказ № {$order['id']}\n\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['product_price']} ₽\n";
        $message .= "Сумма: " . ($order['product_price'] * $order['product_count']) . " ₽\n";
        $message .= "Создан: " . (new DateTime($order['created_at']))->format('d F Y, H:i') . "\n";
        $message .= "Изменен: {$modifiedAt}";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Новый',
                        'callback_data' => "order_done_{$order['id']}"
                    ],
                    [
                        'text' => 'Удалить',
                        'callback_data' => "order_confirm_{$order['id']}"
                    ]
                ]
            ]
        ];

        $this->telegramApi->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Метод отвечает за изменение статуса заказа
     *
     * @param int $chatId
     * @param int $messageId
     * @param string $orderStatus
     * @param int $orderId
     * @return void
     * @throws Exception
     */
    private function sendOrderStatus(int $chatId, int $messageId, string $orderStatus, int $orderId): void
    {
        $orderRepository = new OrderRepository();

        // Преобразуем строку в булево значение (new => false, done => true)
        $newStatus = $orderStatus === 'new' ? false : true;

        $orderRepository->updateOrderStatus($orderId, $newStatus);

        $order = $orderRepository->getOrders(['id' => $orderId]);

        if (!$order) {
            $this->telegramApi->sendMessage($chatId, "Ошибка: не удалось загрузить обновленные данные заказа.");
            return;
        }

        $modifiedAt = $order['modified_at'] ? (new DateTime($order['modified_at']))->format('d F Y, H:i') : "Статус не изменялся";

        $message = "Заказ № {$order['id']}\n\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['product_price']} ₽\n";
        $message .= "Сумма: " . ($order['product_price'] * $order['product_count']) . " ₽\n";
        $message .= "Создан: " . (new DateTime($order['created_at']))->format('d F Y, H:i') . "\n";
        $message .= "Изменен: {$modifiedAt}";

        $buttonText = $newStatus ? 'Новый' : 'Выполнен';

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $buttonText,
                        'callback_data' => "order_" . ($newStatus ? 'new' : 'done') . "_{$order['id']}"
                    ],
                    [
                        'text' => 'Удалить',
                        'callback_data' => "order_confirm_{$order['id']}"
                    ]
                ]
            ]
        ];

        $this->telegramApi->editMessageText($chatId, $messageId, $message, $keyboard);
    }

    /**
     * Метод отвечает за отправку сообщения о подтверждении удаления заказа
     *
     * @param int $chatId
     * @param int $orderId
     * @return void
     */
    private function sendOrderDeleteConfirm(int $chatId, int $orderId): void
    {
        $message = "Удалить заказ № {$orderId}?";

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Да',
                        'callback_data' => "order_delete_{$orderId}"
                    ],
                    [
                        'text' => 'Отмена',
                        'callback_data' => "order_cancel_{$orderId}"
                    ]
                ]
            ]
        ];

        $this->telegramApi->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Метод отвечает за удаление заказа из базы
     *
     * @param int $chatId
     * @param int $messageId
     * @param string $deleteStatus
     * @param int $orderId
     * @return void
     */
    private function sendOrderDeleteStatus(int $chatId, int $messageId, string $deleteStatus, int $orderId): void
    {
        $orderRepository = new OrderRepository();

        if ($deleteStatus === 'delete') {
            $orderRepository->deleteOrder($orderId);
            $message = "Заказ № {$orderId} удален.";
            $this->telegramApi->editMessageText($chatId, $messageId, $message);
        } else {
            $this->telegramApi->deleteMessage($chatId, $messageId);
        }
    }

    private function sendOrdersStatusFilter(int $chatId, string $orderStatus)
    {
    }
}
