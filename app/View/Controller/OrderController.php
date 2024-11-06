<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\OrderRepository;
use Kiipod\ShopTelegramBot\Repositories\ProductRepository;
use Kiipod\ShopTelegramBot\Telegram\TelegramApi;

class OrderController
{
    /**
     * @param int $productId
     * @return void
     */
    public function index(int $productId): void
    {
        $templateHelper = new TemplateHelper();
        $productRepository = new ProductRepository();

        // Получаем список доступных продуктов
        $product = $productRepository->getProductById($productId);

        // Передаем продукты в шаблон для отображения формы
        $content = $templateHelper->includeTemplate('order-form.php', ['product' => $product]);

        // Основной лейаут
        $layout = $templateHelper->includeTemplate('layout.php', ['content' => $content]);

        print($layout);
    }

    /**
     * @return void
     */
    public function create(): void
    {
        $productRepository = new ProductRepository();
        $orderRepository = new OrderRepository();
        $telegramApi = new TelegramApi('8160278396:AAEhWW3AMxHvilo6XAouWSUee9GA0dnGm9o');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)$_POST['product_id'];
            $productCount = (int)$_POST['product_count'];
            $phone = $_POST['phone'];

            // Добавляем заказ
            $orderId = $orderRepository->createOrder($productId, $productCount, $phone);

            if ($orderId) {
                echo "Заказ успешно добавлен! ID заказа: " . $orderId;
                $product = $productRepository->getProductById($orderId);

                // Получаем chat_id нового подписчика
                $chatId = $telegramApi->getNewSubscriberChatId();

                // Проверяем, есть ли chat_id, и отправляем сообщение
                if ($chatId) {
                    // Формируем сообщение с деталями заказа
                    $message = "Ваш заказ успешно создан!\n";
                    $message .= "Номер заказа: $orderId\n";
                    $message .= "Товар: {$product['name']}\n";
                    $message .= "Количество: $productCount\n";
                    $message .= "Цена за единицу: " . number_format($product['price'], 2) . " ₽\n";
                    $message .= "Итого: " . number_format($product['price'] * $productCount, 2) . " ₽";

                    $telegramApi->sendMessage($chatId, $message);
                } else {
                    echo "Ошибка: chat_id не найден. Сообщение не отправлено.";
                }
            } else {
                echo "Ошибка при добавлении заказа.";
            }
        }
    }
}
