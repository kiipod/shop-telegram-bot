<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

use Kiipod\ShopTelegramBot\Database\MysqlService;
use PDO;
use PDOException;

class ProductRepository implements ProductRepositories
{
    private MysqlService $db;

    public function __construct()
    {
        $this->db = new MysqlService();
        $this->db->connect('mysql', 'root', 'password', 'shop');
    }

    /**
     * Получить список продуктов из базы данных
     *
     * @return array|null Массив продуктов или null в случае ошибки
     */
    public function getProducts(): ?array
    {
        // Получаем соединение PDO
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->query('SELECT * FROM products');
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Обработка ошибки запроса
                echo "Ошибка выполнения запроса: " . $e->getMessage();
                return null;
            }
        }

        // Возвращаем null, если соединение не установлено
        return null;
    }
}
