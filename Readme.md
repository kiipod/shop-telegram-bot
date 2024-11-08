# PHP Telegram Shop Bot

<p align="left">
<img src="https://img.shields.io/badge/php-8.3-blue">
<img src="https://img.shields.io/badge/mysql-8.0-orange">
</p>

---

_Не удаляйте и не обращайте внимание на файлы:_<br>
_`.editorconfig`, `.gitattributes`, `.gitignore`._

---

## О проекте

Telegram бот работающий с заказами сайта интернет магазина.

## Основные сценарии использования:

- Работа с заказами сайта-магазина (отображение списка,
изменение статуса, удаление);
- Уведомление пользователя о новых заказах на сайте;
- Получение информации о заказе из базы данных;
- Удаление заказа из базы данных;
- Изменение статуса заказа (новый/выполнен);
- Фильтрация полученного списка заказов по заданным
параметрам:
  - статус (все заказы, только новые, только
     выполненные);
  - период (день, неделя, месяц);
  - товар (получение списка заказа содержащего
     определенный товар).

## Начало работы

1. Перед запуском проекта создайте .env файл:

```
cp .env.example .env
```

2. Для запуска проекта выполните команду:

```
make docker-up
```

3. Загрузите дамп базы в MySQL:

```
make mysql-dump
```

4. Установите зависимости composer:

```
make composer-install
```

5. Если вам нужно обновить загрузчик, т.к. появились новые классы, выполните команду:

```
make composer-du
```

6. В проекте используется Tailwind, для установки выполните команду:

```
make npm-install
```

7. Для выполнения билда Tailwind выполните команду:

```
make tailwind-build
```

## Адрес бота:

https://t.me/KanaiShopBot

## Техническое задание

[Посмотреть техническое задание](tz.pdf)
