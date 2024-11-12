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
     * @return void
     */
    public function index(): void
    {
        $templateHelper = new TemplateHelper();
        $productRepository = new ProductRepository();

        $products = $productRepository->getProducts();

        $content = $templateHelper->includeTemplate('order-form.php', ['products' => $products]);
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
                $order = $orderRepository->getOrders(['id' => $orderId]);

                // Получаем chat_id нового подписчика
                $chatId = $userRepository->getNewSubscriberChatId();

                if ($chatId) {
                    $message = "Ваш заказ успешно создан!\n\n";
                    $message .= "Новый заказ № {$order['id']}\n";
                    $message .= "Товар: {$order['product_name']}\n";
                    $message .= "Количество: {$order['product_count']}\n";
                    $message .= "Цена: " . $order['product_price'] . " ₽\n";
                    $message .= "Сумма: " . ($order['product_price'] * $order['product_count']) . " ₽";

                    $keyboard = [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => 'Новый',
                                    'callback_data' => "order_new_{$order['id']}"
                                ],
                                [
                                    'text' => 'Удалить',
                                    'callback_data' => "order_confirm_{$order['id']}"
                                ]
                            ]
                        ]
                    ];

                    $telegramApi->sendMessage($chatId, $message, $keyboard);
                } else {
                    echo "Ошибка: chat_id не найден. Сообщение не отправлено.";
                }
            } else {
                echo "Ошибка при добавлении заказа.";
            }
        }
    }
}
