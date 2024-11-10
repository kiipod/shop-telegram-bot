<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

use Exception;
use Kiipod\ShopTelegramBot\Database\MysqlClient;
use Kiipod\ShopTelegramBot\Helpers\EnvHelper;
use PDO;
use PDOException;

class ProductRepository implements ProductRepositories
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
     * Метод получает список продуктов из базы данных с возможностью фильтрации
     *
     * @param array $filters Фильтры для выборки продуктов (например: 'id' => 123)
     * @return array|null
     */
    public function getProducts(array $filters = []): ?array
    {
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                $query = 'SELECT * FROM products';
                $conditions = [];
                $params = [];

                // Фильтр по ID продукта
                if (isset($filters['id'])) {
                    $conditions[] = 'id = :id';
                    $params[':id'] = $filters['id'];
                }

                // Если есть условия, добавляем их в запрос
                if ($conditions) {
                    $query .= ' WHERE ' . implode(' AND ', $conditions);
                }

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);

                // Если фильтр по ID, возвращаем один продукт, иначе - список
                if (isset($filters['id'])) {
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $product ?: null;
                }

                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                echo "Ошибка выполнения запроса: " . $e->getMessage();
                return null;
            }
        }

        // Возвращаем null, если соединение не установлено
        return null;
    }
}
