<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Database;

interface Database
{
    public function connect(string $host, string $user, string $password, string $database);

    public function getConnection();
}
