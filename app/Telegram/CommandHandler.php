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

        if (str_starts_with($callbackData, 'order_')) {
            $orderId = $this->extractOrderId($callbackData);
            $this->sendOrderDetails($chatId, $orderId);
        } elseif ($callbackData === 'order_new') {
            $orderStatus = $this->extractOrderString($callbackData);
            $this->sendOrderStatus($chatId, $orderStatus);
        } elseif ($callbackData === 'order_done') {
            $orderStatus = $this->extractOrderString($callbackData);
            $this->sendOrderStatus($chatId, $orderStatus);
        } elseif ($callbackData === 'order_delete') {
            $orderStatus = $this->extractOrderString($callbackData);
            $this->sendOrderDeleteStatus($chatId, $orderStatus);
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
                $orderId = $order['id'];
                $total = ($order['product_price'] * $order['product_count']);
                $createdAt = (new DateTime($order['created_at']))->format('d F Y, H:i');

                // Формируем сообщение для каждого заказа
                $message = "Заказ № {$orderId}\n";
                $message .= "Сумма: {$total} ₽\n";
                $message .= "Создан: {$createdAt}";

                // Формируем кнопки в правильном формате
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Подробнее о заказе',
                                'callback_data' => "order_{$orderId}"
                            ]
                        ]
                    ]
                ];

                // Отправляем сообщение с кнопкой
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
     * @return int|null
     */
    private function extractOrderId(string $callbackData): ?int
    {
        $orderId = (int) str_replace('order_', '', $callbackData);
        return $orderId > 0 ? $orderId : null;
    }

    /**
     * Извлекает Статус заказа из callbackData
     *
     * @param string $callbackData
     * @return string|null
     */
    private function extractOrderString(string $callbackData): ?string
    {
        $orderStatus = (string) str_replace('order_', '', $callbackData);
        return $orderStatus != null ? $orderStatus : null;
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

        // Проверяем, является ли $order массивом массивов, и если да, извлекаем первый элемент
        if (is_array($order) && isset($order[0])) {
            $order = $order[0];
        }

        if (!$order) {
            $this->telegramApi->sendMessage($chatId, "Заказ с ID {$orderId} не существует.");
            return;
        }

        // Продолжаем формирование сообщения, зная, что $order — это ассоциативный массив
        $total = ($order['product_price'] * $order['product_count']);
        $createdAt = (new DateTime($order['created_at']))->format('d F Y, H:i');
        $modifiedAt = $order['modified_at'] ? (new DateTime($order['modified_at']))->format('d F Y, H:i') : "Статус не изменялся";

        $message = "Информация о заказе № {$order['id']}\n\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['product_price']} ₽\n";
        $message .= "Сумма: {$total} ₽\n";
        $message .= "Создан: {$createdAt} \n";
        $message .= "Изменен: {$modifiedAt}";

        // Формируем кнопки в правильном формате
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Выполнен',
                        'callback_data' => 'order_new'
                    ],
                    [
                        'text' => 'Удалить',
                        'callback_data' => 'order_delete'
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
     * @param string $orderStatus
     * @return void
     */
    private function sendOrderStatus(int $chatId, string $orderStatus): void
    {
        $orderRepository = new OrderRepository();
        $newStatus = $orderStatus === 'new' ? 1 : 0;

        $orderRepository->updateOrderStatus($orderId, $orderStatus);

        // Получение обновленных данных заказа
        $order = $orderRepository->getOrders(['id' => $orderId]);

        // Проверяем, является ли $order массивом массивов, и если да, извлекаем первый элемент
        if (is_array($order) && isset($order[0])) {
            $order = $order[0];
        }

        if (!$order) {
            $this->telegramApi->sendMessage($chatId, "Ошибка: не удалось загрузить обновленные данные заказа.");
            return;
        }

        $statusText = $newStatus === 1 ? "Выполнен" : "Новый";
        $modifiedAt = (new DateTime())->format('d F Y, H:i');

        $message = "Информация о заказе № {$order['id']}\n\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['product_price']} ₽\n";
        $message .= "Сумма: " . ($order['product_price'] * $order['product_count']) . " ₽\n";
        $message .= "Создан: {$order['created_at']} \n";
        $message .= "Изменен: {$modifiedAt}\n";
        $message .= "Статус: {$statusText}";

        $buttonText = $newStatus === 1 ? 'Новый' : 'Выполнен';

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $buttonText,
                        'callback_data' => "order_" . ($newStatus ? 'new' : 'done')
                    ]
                ]
            ]
        ];

        $this->telegramApi->editMessageText($chatId, $order['message_id'], $message, $keyboard);
    }

    private function sendOrdersPeriodFilter(mixed $chatId, string $orderPeriod)
    {
    }

    private function sendOrderDeleteStatus(mixed $chatId, ?string $orderStatus)
    {
    }
}
