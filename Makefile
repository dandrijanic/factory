all: ## Will build and spin-up the project from scratch
	docker compose down -v
	docker compose build
	docker compose up -d
	chmod +x init.sh
	make init

restart:
	docker compose down -v
	docker compose up -d

start: ## Starts all containers in the background
	docker compose up -d

fg: ## Starts all containers in the foreground
	docker compose up

stop: ## Stops all containers
	docker compose stop

sh: ## Opens a bash shell for the node container
	docker compose exec php /bin/sh

init: ## Runs init script
	docker compose exec php ./init.sh

migrate: ## Runs migrations
	docker compose exec php php artisan migrate
