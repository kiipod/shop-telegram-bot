<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

use Exception;
use Kiipod\ShopTelegramBot\Database\MysqlClient;
use Kiipod\ShopTelegramBot\Helpers\EnvHelper;
use PDO;
use PDOException;

class UserRepository implements UserRepositories
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
     * Метод добавляет нового пользователя в БД по chat_id
     *
     * @param int $chatId
     * @return int|null
     */
    public function create(int $chatId): ?int
    {
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('INSERT INTO users (chat_id) VALUES (:chat_id)');

                // Выполнение запроса с передачей параметров
                $stmt->execute([
                    'chat_id' => $chatId,
                ]);

                // Возвращаем ID последней вставленной записи
                return (int)$pdo->lastInsertId();

            } catch (PDOException $e) {
                // Обработка ошибки запроса
                echo "Ошибка добавления пользователя: " . $e->getMessage();
                return null;
            }
        }

        // Возвращаем null, если соединение не установлено
        return null;
    }

    /**
     * Метод осуществляет поиск последнего нового подписчика в БД
     *
     * @return int|null
     */
    public function getNewSubscriberChatId(): ?int
    {
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                // Запрос для получения chat_id последнего пользователя
                $stmt = $pdo->query('SELECT chat_id FROM users ORDER BY id DESC LIMIT 1');
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Проверка, что результат не пустой
                return $result ? (int)$result['chat_id'] : null;

            } catch (PDOException $e) {
                // Обработка ошибки запроса
                echo "Ошибка запроса: " . $e->getMessage();
                return null;
            }
        }

        // Возвращаем null, если соединение не установлено
        return null;
    }

    /**
     * Метод отвечает за поиск chat_id в БД
     *
     * @param int $chatId
     * @return array|null
     */
    public function findByChatId(int $chatId): ?array
    {
        $pdo = $this->db->getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('SELECT * FROM users WHERE chat_id = :chat_id');
                $stmt->execute(['chat_id' => $chatId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                return $user ?: null;
            } catch (PDOException $e) {
                echo "Ошибка поиска пользователя: " . $e->getMessage();
                return null;
            }
        }

        return null;
    }
}
