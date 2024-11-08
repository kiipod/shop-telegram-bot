THIS_FILE := $(lastword $(MAKEFILE_LIST))
.PHONY: docker-up docker-down docker-up-prod docker-down-prod mysql-dump composer-install composer-update composer-du
app := app
mysql := mysql

#DOCKER
docker-up:
	docker compose up -d
docker-down:
	docker compose down --remove-orphans

#PRODUCTION
docker-up-prod:
	docker compose -f docker-compose-production.yml up -d
docker-down-prod:
	docker compose -f docker-compose-production.yml down -v --remove-orphans

#MYSQL
mysql-dump:
	docker exec -i $(mysql) mysql -ukiipod -p12345 shop < tlg_dump.sql

#COMPOSER
composer-install:
	docker exec $(app) composer install
composer-update:
	docker exec $(app) composer update
composer-du:
	docker exec $(app) composer du
