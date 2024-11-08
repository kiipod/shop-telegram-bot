<?php

declare(strict_types=1);

namespace Kiipod\ShopTelegramBot\Repositories;

interface UserRepositories
{
    public function create(int $chatId);

    public function getNewSubscriberChatId();
}
