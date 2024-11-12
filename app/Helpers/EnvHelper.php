<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Helpers;

use Exception;

class EnvHelper
{
    /**
     * Метод отвечает за чтение файла .env
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public static function readEnv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("Файл .env не найден: $filePath");
        }

        $env = [];

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Пропускаем комментарии
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);

            // Убираем лишние пробелы и кавычки
            $key = trim($key);
            $value = trim($value);

            if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                $value = $matches[1];
            }

            $env[$key] = $value;
        }

        return $env;
    }
}
