<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Telegram;

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
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $callbackData = $callbackQuery['data'];

        if (strpos($callbackData, 'order_') === 0) {
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
     */
    private function sendOrdersList(int $chatId): void
    {
        $orderRepository = new OrderRepository();
        $orders = $orderRepository->getOrders();

        if ($orders) {
            $message = "Список заказов:\n\n";

            foreach ($orders as $order) {
                $orderId = $order['id'];
                $total = $order['price'] * $order['product_count'];
                $createdAt = date('Y-m-d H:i', strtotime($order['created_at']));

                $message .= "Заказ № {$orderId}\n";
                $message .= "Сумма: {$total} ₽\n";
                $message .= "Создан: {$createdAt}\n";

                // Добавляем кнопку для получения подробной информации о заказе
                $keyboard[] = [
                    'text' => "Подробнее о заказе №{$orderId}",
                    'callback_data' => "order_{$orderId}"
                ];
            }

            $this->telegramApi->sendMessage($chatId, $message, [
                'reply_markup' => json_encode([
                    'inline_keyboard' => [$keyboard]
                ])
            ]);
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
     */
    private function sendOrderDetails(int $chatId, ?int $orderId): void
    {
        // Проверка на отсутствие номера заказа
        if ($orderId === null) {
            $this->telegramApi->sendMessage($chatId, "Необходимо указать ID заказа.");
            return;
        }

        $orderRepository = new OrderRepository();
        $order = $orderRepository->getOrders(['id' => $orderId]);

        // Проверка на существование заказа
        if (!$order) {
            $this->telegramApi->sendMessage($chatId, "Заказ с ID {$orderId} не существует.");
            return;
        }

        $total = $order['price'] * $order['product_count'];
        $createdAt = date('Y-m-d H:i', strtotime($order['created_at']));
        $modifiedAt = date('Y-m-d H:i', strtotime($order['modified_at']));

        $message = "Информация о заказе № {$orderId}\n";
        $message .= "Товар: {$order['product_name']}\n";
        $message .= "Количество: {$order['product_count']}\n";
        $message .= "Цена: {$order['price']} ₽\n";
        $message .= "Сумма: {$total} ₽\n";
        $message .= "Создан: {$createdAt} \n";
        $message .= "Изменене: {$modifiedAt}";

        $this->telegramApi->sendMessage($chatId, $message);
    }
}
