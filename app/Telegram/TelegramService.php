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
     * Метод отправляет сообщение по команде /help
     *
     * @param int $chatId
     * @return void
     */
    public function sendHelpMessage(int $chatId): void
    {
        $message = "Список команд бота:\n\n";
        $message .= "Перед использованием команд не забудьте поставить обратную косую черту * / *\n\n";
        $message .= "* orders * - Выводит список последних 10 заказов, от новых к старым\n\n";
        $message .= "* order ID * - Выводит информацию о заказе с указанным ID\n\n";
        $message .= "* orders new * - Выводит заказы со статусом Новый\n\n";
        $message .= "* orders done * - Выводит заказы со статусом Выполнен\n\n";
        $message .= "* orders today * - Выводит заказы за текущий день\n\n";
        $message .= "* orders week * - Выводит заказы за последнюю неделю\n\n";
        $message .= "* orders month * - Выводит заказы за последний месяц\n\n";

        $this->telegramApi->sendMessage($chatId, $message);
    }

    /**
     * Метод отвечает за отправку списка заказов по команде /orders
     *
     * @param int $chatId
     * @return void
     * @throws Exception
     */
    public function sendOrderLists(int $chatId): void
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
        $this->orderDetails($chatId, $orderId);
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
        $this->orderDetails($chatId, $orderId, $messageId, $orderStatus);
    }

    /**
     * Метод отвечает за компоновку сообщения с информацией о заказе
     *
     * @param int $chatId
     * @param int $orderId
     * @param int|null $messageId
     * @param string|null $status
     * @return void
     * @throws Exception
     */
    private function orderDetails(int $chatId, int $orderId, ?int $messageId = null, ?string $status = null): void
    {
        $orderRepository = new OrderRepository();

        if ($status !== null) {
            // Преобразуем строку статуса в булево значение: 'new' -> false, 'done' -> true
            $newStatus = $status === 'new' ? true : false;

            // Обновляем статус в БД
            $orderRepository->updateOrderStatus($orderId, $newStatus);
        }

        // Загружаем обновленные данные заказа из БД
        $order = $orderRepository->getOrders(['id' => $orderId]);

        // Проверка: если заказ не найден
        if ($order === null) {
            $this->telegramApi->sendMessage($chatId, "Заказа с ID {$orderId} не существует");
            return;
        }

        $modifiedAt = $order['modified_at']
            ? (new DateTime($order['modified_at']))->format('d F Y, H:i')
            : "Статус не изменялся";

        $message = "Заказ № {$order['id']}\n\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['product_price']} ₽\n";
        $message .= "Сумма: " . ($order['product_price'] * $order['product_count']) . " ₽\n";
        $message .= "Создан: " . (new DateTime($order['created_at']))->format('d F Y, H:i') . "\n";
        $message .= "Изменен: {$modifiedAt}";

        // Определяем текст для кнопки статуса на основе актуального статуса заказа из БД
        $buttonText = $order['status'] ? 'Новый' : 'Выполнен';
        $callbackStatus = $order['status'] ? 'new' : 'done';

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $buttonText,
                        'callback_data' => "order_{$callbackStatus}_{$order['id']}"
                    ],
                    [
                        'text' => 'Удалить',
                        'callback_data' => "order_confirm_{$order['id']}"
                    ]
                ]
            ]
        ];

        if ($messageId) {
            // Редактирование сообщения, если оно уже существует
            $this->telegramApi->editMessageText($chatId, $messageId, $message, $keyboard);
        } else {
            $this->telegramApi->sendMessage($chatId, $message, $keyboard);
        }
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
    public function sendOrderFilters(int $chatId, string $orderStatus): void
    {
        $filters = [];

        // Фильтрация по статусу
        if ($orderStatus === 'new' || $orderStatus === 'done') {
            $filters['status'] = $orderStatus === 'new' ? 0 : 1;
        }

        // Фильтрация по периоду
        if (in_array($orderStatus, ['today', 'week', 'month'])) {
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
