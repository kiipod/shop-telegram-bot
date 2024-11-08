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
     * Метод получает список продуктов из базы данных
     *
     * @return array|null
     */
    public function getProducts(): ?array
    {
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->query('SELECT * FROM products');
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
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
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');

                $stmt->execute(['id' => $productId]);

                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                return $product ?: null;

            } catch (PDOException $e) {
                echo "Ошибка выполнения запроса: " . $e->getMessage();
                return null;
            }
        }

        // Возвращаем null, если соединение не установлено
        return null;
    }
}
