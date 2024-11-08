<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Database;

use PDO;
use PDOException;

class MysqlClient implements Database
{
    private ?PDO $pdo = null;

    /**
     * Метод подключается к базе данных и сохраняет соединение в $this->pdo
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @return void
     */
    public function connect(string $host, string $user, string $password, string $database): void
    {
        try {
            // Формируем DSN строку для подключения к MySQL
            $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

            // Создаем экземпляр PDO и сохраняем в свойстве класса
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo "Подключение к базе данных успешно выполнено.";
        } catch (PDOException $e) {
            // Обработка ошибки подключения
            echo "Ошибка подключения к базе данных: " . $e->getMessage();
        }
    }

    /**
     * Метод получает подключение к базе данных
     *
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }
}
