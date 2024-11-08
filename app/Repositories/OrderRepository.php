<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

use Exception;
use Kiipod\ShopTelegramBot\Database\MysqlClient;
use Kiipod\ShopTelegramBot\Helpers\EnvHelper;
use PDO;
use PDOException;

class OrderRepository implements OrderRepositories
{
    private MysqlClient $db;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $envHelper = new EnvHelper();
        $env = $envHelper->readEnv('../.env');

        $host = $env['MYSQL_HOST'];
        $username = $env['MYSQL_USERNAME'];
        $password = $env['MYSQL_USER_PASSWORD'];
        $database = $env['MYSQL_DATABASE'];

        $this->db = new MysqlClient();
        $this->db->connect($host, $username, $password, $database);
    }

    public function createOrder(int $productId, int $productCount): ?int
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
                        "INSERT INTO orders (product_id, product_count, product_name, product_price, created_at, status)
                                VALUES (:product_id, :product_count, :product_name, :product_price, :created_at, :status)");

                    // Выполнение запроса с передачей параметров
                    $stmt->execute([
                        'product_id' => $productId,
                        'product_count' => $productCount,
                        'product_name' => $product['name'],
                        'product_price' => $product['price'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'status' => 0
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
