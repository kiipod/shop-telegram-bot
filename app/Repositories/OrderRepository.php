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

    /**
     * Метод получает список заказов из базы данных с возможностью динамической фильтрации
     *
     * @param array $filters
     * @return array|null
     */
    public function getOrders(array $filters = []): ?array
    {
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                $query = 'SELECT * FROM orders';
                $conditions = [];
                $params = [];

                // Фильтрация по ID заказа
                if (isset($filters['id'])) {
                    $conditions[] = 'id = :id';
                    $params[':id'] = $filters['id'];
                }

                // Фильтрация по статусу
                if (isset($filters['status'])) {
                    $conditions[] = 'status = :status';
                    $params[':status'] = $filters['status'];
                }

                // Фильтрация по периоду
                if (isset($filters['period'])) {
                    switch ($filters['period']) {
                        case 'day':
                            $conditions[] = 'created_at >= :day_start';
                            $params[':day_start'] = date('Y-m-d 00:00:00');
                            break;
                        case 'week':
                            $conditions[] = 'created_at >= :week_start';
                            $params[':week_start'] = date('Y-m-d 00:00:00', strtotime('-7 days'));
                            break;
                        case 'month':
                            $conditions[] = 'created_at >= :month_start';
                            $params[':month_start'] = date('Y-m-d 00:00:00', strtotime('-1 month'));
                            break;
                    }
                }

                // Добавляем условия в запрос
                if ($conditions) {
                    $query .= ' WHERE ' . implode(' AND ', $conditions);
                }

                // Применяем сортировку и ограничение на последние 10 записей только при отсутствии фильтров
                if (empty($filters)) {
                    $query .= ' ORDER BY created_at DESC LIMIT 10';
                }

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);

                // Возвращаем одну запись, если поиск по ID
                return isset($filters['id']) ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                echo "Ошибка выполнения запроса: " . $e->getMessage();
                return null;
            }
        }

        // Возвращаем null, если соединение не установлено
        return null;
    }

    /**
     * Метод отвечает за создание заказа
     *
     * @param int $productId
     * @param int $productCount
     * @return int|null
     */
    public function createOrder(int $productId, int $productCount): ?int
    {
        $pdo = $this->db->getConnection();
        $productRepository = new ProductRepository();

        if ($pdo) {
            try {
                // Получаем информацию о продукте
                $product = $productRepository->getProducts(['id' => $productId]);

                if ($product) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO orders (product_id, product_count, product_name, product_price, created_at, status)
                                VALUES (:product_id, :product_count, :product_name, :product_price, :created_at, :status)");

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
