FROM mysql/mysql-server:8.0

COPY ./docker/prod/MySQL/dump/init.sql /docker-entrypoint-initdb.d/init.sql
COPY ./docker/prod/MySQL/read_env.sh /docker-entrypoint-initdb.d/read_env.sql
