# PHP Telegram Shop Bot

<p align="left">
<img src="https://img.shields.io/badge/php-8.3-blue">
<img src="https://img.shields.io/badge/mysql-8.0-orange">
</p>

---

_Не удаляйте и не обращайте внимание на файлы:_<br>
`.editorconfig`, `.gitattributes`, `.gitignore`.

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

## Список команд бота:

```/orders``` - Выводит список последних 10 заказов, от новых к старым

```/order ID``` - Выводит информацию о заказе с указанным ID

```/orders new``` - Выводит заказы со статусом Новый

```/orders done``` - Выводит заказы со статусом Выполнен

```/orders today``` - Выводит заказы за текущий день

```/orders week``` - Выводит заказы за последнюю неделю

```/orders month``` - Выводит заказы за последний месяц

Допустимо вводить команды в следующих вариантах: ```/order_5```, ```/orders=new```, ```/orders week``` с использованием нижнего подчеркивания, знака ровно или пробела.

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

## Техническое задание

[Посмотреть техническое задание](tz.pdf)
