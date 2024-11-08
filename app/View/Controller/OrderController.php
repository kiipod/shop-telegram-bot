<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Exception;
use Kiipod\ShopTelegramBot\Helpers\EnvHelper;
use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\OrderRepository;
use Kiipod\ShopTelegramBot\Repositories\ProductRepository;
use Kiipod\ShopTelegramBot\Repositories\UserRepository;
use Kiipod\ShopTelegramBot\Telegram\TelegramApi;

class OrderController
{
    /**
     * Метод отвечает за показ страницы с формой заказа
     *
     * @param int $productId
     * @return void
     */
    public function index(int $productId): void
    {
        $templateHelper = new TemplateHelper();
        $productRepository = new ProductRepository();

        $product = $productRepository->getProductById($productId);

        $content = $templateHelper->includeTemplate('order-form.php', ['product' => $product]);
        $layout = $templateHelper->includeTemplate('layout.php', ['content' => $content]);

        print($layout);
    }

    /**
     * Метод отвечает за создание нового заказа
     *
     * @return void
     * @throws Exception
     */
    public function create(): void
    {
        $envHelper = new EnvHelper();
        $env = $envHelper->readEnv('../.env');
        $botToken = $env['BOT_TOKEN'];

        $productRepository = new ProductRepository();
        $orderRepository = new OrderRepository();
        $userRepository = new UserRepository();
        $telegramApi = new TelegramApi($botToken);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)$_POST['product_id'];
            $productCount = (int)$_POST['product_count'];

            // Добавляем заказ
            $orderId = $orderRepository->createOrder($productId, $productCount);

            if ($orderId) {
                echo "Заказ успешно добавлен! ID заказа: " . $orderId . " \n";
                $product = $productRepository->getProductById($productId);

                // Получаем chat_id нового подписчика
                $chatId = $userRepository->getNewSubscriberChatId();

                // Проверяем, есть ли chat_id, и отправляем сообщение
                if ($chatId) {
                    // Формируем сообщение с деталями заказа
                    $message = "Ваш заказ успешно создан!\n";
                    $message .= "Новый заказ № $orderId\n";
                    $message .= "Товар: {$product['name']}\n";
                    $message .= "Количество: $productCount\n";
                    $message .= "Цена: " . $product['price'] . " ₽\n";
                    $message .= "Сумма: " . ($product['price'] * $productCount) . " ₽";

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
