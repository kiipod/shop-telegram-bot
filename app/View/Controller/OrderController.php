<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\OrderRepository;
use Kiipod\ShopTelegramBot\Repositories\ProductRepository;

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
        $orderRepository = new OrderRepository();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int)$_POST['product_id'];
            $productCount = (int)$_POST['product_count'];
            $phone = $_POST['phone'];

            // Добавляем заказ
            $orderId = $orderRepository->createOrder($productId, $productCount, $phone);

            if ($orderId) {
                echo "Заказ успешно добавлен! ID заказа: " . $orderId;
            } else {
                echo "Ошибка при добавлении заказа.";
            }
        }
    }
}