THIS_FILE := $(lastword $(MAKEFILE_LIST))
.PHONY: docker-up docker-down docker-down-clear load-dump composer-update composer-du npm-install npm-update npm-build
app := app
app-npm := npm
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
	docker exec -i $(mysql) mysql -uroot -ppassword shop < tlg_dump.sql

#COMPOSER
composer-install:
	docker exec $(app) composer install
composer-update:
	docker exec $(app) composer update
composer-du:
	docker exec $(app) composer du

#NPM
npm-install:
	docker-compose run --rm --service-ports $(app-npm) install $(c)
npm-update:
	docker-compose run --rm --service-ports $(app-npm) update $(c)
npm-build:
	docker-compose run --rm --service-ports $(app-npm) run build $(c)

#TAILWIND
tailwind-build:
	npx tailwindcss -i ./public/css/app.css -o ./public/css/output.css --watch
