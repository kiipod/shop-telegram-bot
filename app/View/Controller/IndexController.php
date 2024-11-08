<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\View\Controller;

use Kiipod\ShopTelegramBot\Helpers\TemplateHelper;
use Kiipod\ShopTelegramBot\Repositories\ProductRepository;

class IndexController
{
    /**
     * Метод отвечает за рендер index.php
     *
     * @return void
     */
    public function __invoke(): void
    {
        $templateHelper = new TemplateHelper();
        $productService = new ProductRepository();

        $products = $productService->getProducts();

        $content = $templateHelper->includeTemplate('product.php', ['products' => $products]);
        $layout = $templateHelper->includeTemplate('layout.php', ['content' => $content]);

        print($layout);
    }
}
