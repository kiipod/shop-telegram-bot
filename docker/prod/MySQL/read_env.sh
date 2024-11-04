#!/bin/sh

# Получение пути до директории, где находится скрипт
SCRIPT_DIR=$(dirname "$0")

# Чтение секретов из файлов
MYSQL_DATABASE="${MYSQL_DATABASE}"
MYSQL_USERNAME="${MYSQL_USERNAME}"
MYSQL_USER_PASSWORD="${MYSQL_USER_PASSWORD}"

# Замена переменных в SQL шаблоне и выполнение скрипта
sed -e "s/%MYSQL_DATABASE%/$MYSQL_DATABASE/g" \
    -e "s/%MYSQL_USERNAME%/$MYSQL_USERNAME/g" \
    -e "s/%MYSQL_USER_PASSWORD%/$MYSQL_USER_PASSWORD/g" \
    "$SCRIPT_DIR/init.sql"
