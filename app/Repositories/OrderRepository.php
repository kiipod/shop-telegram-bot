<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

use Kiipod\ShopTelegramBot\Database\MysqlClient;
use PDO;
use PDOException;

class OrderRepository implements OrderRepositories
{
    private MysqlClient $db;

    public function __construct()
    {
        $this->db = new MysqlClient();
        $this->db->connect('mysql', 'root', 'password', 'shop');
    }

    public function createOrder(int $productId, int $productCount, string $phone): ?int
    {
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                // Получаем информацию о продукте
                $stmt = $pdo->prepare("SELECT name, price FROM products WHERE id = :product_id");
                $stmt->execute(['product_id' => $productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    // Создаем новый заказ
                    $stmt = $pdo->prepare(
                        "INSERT INTO orders (product_id, product_count, product_name, product_price, created_at, status, phone)
                                VALUES (:product_id, :product_count, :product_name, :product_price, :created_at, :status, :phone)");

                    // Выполнение запроса с передачей параметров
                    $stmt->execute([
                        'product_id' => $productId,
                        'product_count' => $productCount,
                        'product_name' => $product['name'],
                        'product_price' => $product['price'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'status' => 0,
                        'phone' => $phone,
                    ]);

                    // Возвращаем ID последней вставленной записи
                    return (int)$pdo->lastInsertId();
                }

            } catch (PDOException $e) {
                echo "Ошибка создания заказа: " . $e->getMessage();
                return null;
            }
        }

        return null;
    }
}
