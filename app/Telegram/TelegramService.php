<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

use DateTime;
use Exception;
use Kiipod\ShopTelegramBot\Repositories\OrderRepository;

class TelegramService
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
     * Метод отвечает за отправку списка заказов по команде /orders
     *
     * @param int $chatId
     * @return void
     * @throws Exception
     */
    public function sendOrdersList(int $chatId): void
    {
        $orderRepository = new OrderRepository();
        $orders = $orderRepository->getOrders();

        if ($orders) {
            foreach ($orders as $order) {
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
     * Отправляет детали заказа пользователю
     *
     * @param int $chatId
     * @param int|null $orderId
     * @return void
     * @throws Exception
     */
    public function sendOrderDetails(int $chatId, ?int $orderId): void
    {
        $orderRepository = new OrderRepository();
        $order = $orderRepository->getOrders(['id' => $orderId]);

        $modifiedAt = $order['modified_at'] ? (new DateTime($order['modified_at']))->format('d F Y, H:i') : "Статус не изменялся";

        $message = "Заказ № {$order['id']}\n\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['product_price']} ₽\n";
        $message .= "Сумма: " . ($order['product_price'] * $order['product_count']) . " ₽\n";
        $message .= "Создан: " . (new DateTime($order['created_at']))->format('d F Y, H:i') . "\n";
        $message .= "Изменен: {$modifiedAt}";

        // Проверка статуса заказа
        $statusText = 'Новый';
        $callbackData = "order_new_{$order['id']}";

        if ($order['status'] == 1) {
            $statusText = 'Выполнен';
            $callbackData = "order_done_{$order['id']}";
        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $statusText,
                        'callback_data' => $callbackData
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
    public function sendOrderStatus(int $chatId, int $messageId, string $orderStatus, int $orderId): void
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
    public function sendOrderDeleteConfirm(int $chatId, int $orderId): void
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
    public function sendOrderDeleteStatus(int $chatId, int $messageId, string $deleteStatus, int $orderId): void
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

    /**
     * Метод отвечает за фильтрацию заказов
     *
     * @param int $chatId
     * @param string $orderStatus
     * @return void
     * @throws Exception
     */
    public function sendOrdersFilter(int $chatId, string $orderStatus): void
    {
        $filters = [];

        // Фильтрация по статусу
        if ($orderStatus === 'new' || $orderStatus === 'done') {
            $filters['status'] = $orderStatus === 'new' ? 0 : 1;
        }

        // Фильтрация по периоду
        if (in_array($orderStatus, ['day', 'week', 'month'])) {
            $filters['period'] = $orderStatus;
        }

        // Получаем список заказов, применяя фильтры
        $orderRepository = new OrderRepository();
        $orders = $orderRepository->getOrders($filters);

        if ($orders) {
            foreach ($orders as $order) {
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
            $this->telegramApi->sendMessage($chatId, "Нет заказов по выбранному фильтру.");
        }
    }
}
