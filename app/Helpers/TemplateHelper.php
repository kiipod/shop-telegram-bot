<?php

declare(strict_types=1);

namespace Helpers;

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
        $name = 'templates/' . $name;
        $result = '';

        if (!is_readable($name)) {
            return $result;
        }

        ob_start();
        extract($data);
        require $name;

        return ob_get_clean();
    }
}
