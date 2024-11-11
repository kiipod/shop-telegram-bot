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
        } else {
            $this->telegramApi->sendMessage($chatId, "Неизвестная команда.");
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
            // Извлекаем ID заказа и отправляем детали заказа
            $orderId = $this->extractOrderId($callbackData);
            $this->sendOrderDetails($chatId, $orderId);
        } else {
            $this->telegramApi->sendMessage($chatId, "Неизвестное действие.");
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
                                'text' => "Подробнее о заказе",
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

        // Проверяем все поля заказа
        if (empty($order['product_name']) || empty($order['product_price']) || empty($order['product_count']) || empty($order['modified_at'])) {
            $this->telegramApi->sendMessage($chatId, "Ошибка: не удалось загрузить полные данные заказа.");
            return;
        }

        $total = ($order['product_price'] * $order['product_count']);
        $createdAt = (new DateTime($order['created_at']))->format('d F Y, H:i');
        $modifiedAt = $order['modified_at'] ? (new DateTime($order['modified_at']))->format('d F Y, H:i') : "Не изменялся";

        $message = "Информация о заказе № {$orderId}\n\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['product_price']} ₽\n";
        $message .= "Сумма: {$total} ₽\n";
        $message .= "Создан: {$createdAt} \n";
        $message .= "Изменен: {$modifiedAt}";

        $this->telegramApi->sendMessage($chatId, $message);
    }
}
