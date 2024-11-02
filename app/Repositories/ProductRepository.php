<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

use Kiipod\ShopTelegramBot\Database\MysqlClient;
use PDO;
use PDOException;

class ProductRepository implements ProductRepositories
{
    private MysqlClient $db;

    public function __construct()
    {
        $this->db = new MysqlClient();
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

    /**
     * Метод отвечает за поиск товара по ID
     *
     * @param int $productId
     * @return array|null
     */
    public function getProductById(int $productId): ?array
    {
        // Получаем соединение PDO
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                // Подготовленный запрос для поиска продукта по ID
                $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');

                // Выполнение запроса с передачей параметра ID
                $stmt->execute(['id' => $productId]);

                // Извлечение данных о продукте
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                return $product ?: null;

            } catch (PDOException $e) {
                // Обработка ошибки запроса
                echo "Ошибка выполнения запроса: " . $e->getMessage();
                return null;
            }
        }

        // Возвращаем null, если соединение не установлено
        return null;
    }

    /**
     * Метод отвечает за добавление нового товара в БД
     *
     * @param array $data
     * @return int|null
     */
    public function createProduct(array $data): ?int
    {
        // Получаем соединение PDO
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                // Подготовленный запрос для добавления нового продукта
                $stmt = $pdo->prepare('INSERT INTO products (name, price, image) VALUES (:name, :price, :image)');

                // Выполнение запроса с передачей параметров
                $stmt->execute([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'image' => $data['image']
                ]);

                // Возвращаем ID последней вставленной записи
                return (int) $pdo->lastInsertId();

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
