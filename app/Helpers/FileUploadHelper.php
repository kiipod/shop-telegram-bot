<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Helpers;

class FileUploadHelper
{
    private string $uploadDir;

    /**
     * Конструктор принимает директорию для загрузки файлов
     *
     * @param string $uploadDir
     */
    public function __construct(string $uploadDir = 'images/')
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
    }

    /**
     * Метод для загрузки файла
     *
     * @param string $fileInputName
     * @return string|null
     */
    public function upload(string $fileInputName): ?string
    {
        // Проверяем, загружен ли файл и нет ли ошибок
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
            $fileName = $_FILES[$fileInputName]['name'];

            // Проверка и создание директории, если её не существует
            if (!is_dir($this->uploadDir)) {
                mkdir($this->uploadDir, 0755, true);
            }

            // Составляем полный путь для загрузки
            $uploadPath = $this->uploadDir . basename($fileName);

            // Пытаемся переместить файл в директорию загрузок
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                return $uploadPath; // Возвращаем путь к файлу при успешной загрузке
            } else {
                return "Ошибка при перемещении файла!";
            }
        } else {
            return "Файл не был загружен или произошла ошибка.";
        }
    }
}

