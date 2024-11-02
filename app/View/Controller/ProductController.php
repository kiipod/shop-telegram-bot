<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Kiipod\ShopTelegramBot\Helpers\FileUploadHelper;
use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\ProductRepository;

class ProductController
{
    /**
     * @return void
     */
    public function create(): void
    {
        $templateHelper = new TemplateHelper();
        $productService = new ProductRepository();
        $fileUploadHelper = new FileUploadHelper('images/');

        // Проверка, был ли отправлен POST-запрос
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Получаем данные из формы
            $productName = $_POST['name'] ?? '';
            $productPrice = $_POST['price'] ?? 0;
            $uploadedImagePath = null;

            // Проверяем и загружаем изображение
            if (isset($_FILES['image'])) {
                $uploadedImagePath = $fileUploadHelper->upload('image');

                // Если загрузка изображения прошла успешно
                if ($uploadedImagePath && strpos($uploadedImagePath, 'images/') === 0) {
                    // Данные нового продукта
                    $productData = [
                        'name' => $productName,
                        'price' => $productPrice,
                        'image' => $uploadedImagePath
                    ];

                    // Добавляем продукт в базу данных
                    $productId = $productService->createProduct($productData);

                    if ($productId) {
                        // Перенаправление на список продуктов после успешного добавления
                        header("Location: /index");
                        exit;
                    } else {
                        $error = "Ошибка при добавлении продукта.";
                    }
                } else {
                    $error = "Ошибка загрузки изображения: " . $uploadedImagePath;
                }
            } else {
                $error = "Файл изображения обязателен для загрузки.";
            }
        }

        // Отображаем форму добавления с возможным сообщением об ошибке
        $content = $templateHelper->includeTemplate('create-product.php', ['error' => $error ?? null]);
        $layout = $templateHelper->includeTemplate('layout.php', ['content' => $content]);
        print($layout);
    }
}
