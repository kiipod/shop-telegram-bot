<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\ProductRepository;

class Index
{
    /**
     * Метод отвечает за рендер index.php
     *
     * @return void
     */
    public function index(): void
    {
        $templateHelper = new TemplateHelper();
        $productService = new ProductRepository();

        // Получаем список продуктов из базы данных
        $products = $productService->getProducts();

        // Передаем список продуктов в шаблон product.php
        $content = $templateHelper->includeTemplate('product.php', ['products' => $products]);

        // Передаем сгенерированный контент в основной layout
        $layout = $templateHelper->includeTemplate('layout.php', ['content' => $content]);

        print($layout);
    }
}
