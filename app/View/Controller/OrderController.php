<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Exception;
use Kiipod\ShopTelegramBot\Helpers\EnvHelper;
use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\OrderRepository;
use Kiipod\ShopTelegramBot\Repositories\ProductRepository;
use Kiipod\ShopTelegramBot\Telegram\TelegramApi;
use Kiipod\ShopTelegramBot\Telegram\TelegramService;

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
        $env = EnvHelper::readEnv('../.env');

        $orderRepository = new OrderRepository();
        $telegramApi = new TelegramApi(botToken: $env['BOT_TOKEN']);
        $telegramService = new TelegramService($telegramApi);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)$_POST['product_id'];
            $productCount = (int)$_POST['product_count'];

            // Добавляем заказ в БД
            $orderId = $orderRepository->createOrder($productId, $productCount);

            if ($orderId) {
                echo "Заказ успешно добавлен! ID заказа: " . $orderId . " \n";
                $telegramService->sendNewOrderMessage($orderId);
            } else {
                echo "Ошибка: chat_id не найден. Сообщение не отправлено.";
            }
        } else {
            echo "Ошибка при добавлении заказа.";
        }
    }
}
