<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Helpers;

class TemplateHelper
{
    /**
     * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    public function includeTemplate(string $name, array $data = []): string
    {
        $name = '/app/public/templates/' . $name;
        $result = '';

        // Проверяем, доступен ли файл для чтения
        if (!is_readable($name)) {
            echo "Шаблон $name не найден или недоступен для чтения.";
            return $result;
        }

        ob_start();
        extract($data);
        require $name;

        return ob_get_clean();
    }
}
