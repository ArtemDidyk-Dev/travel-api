include .env
build:
	docker stop $$(docker ps -aq)
	docker compose build
	docker network create ${NETWORK_NAME}_proxynet
	docker compose up -d
	exit
	@echo "\033[0;32mBuild done\033[0m"
run:
	docker-compose up -d
	@echo "\033[0;32mDone\033[0m"
stop:
	docker-compose down
	@echo "\033[0;32mDone\033[0m"
import-db:
	@read -p "Enter the path to your SQL file: " SQL_FILE_PATH; \
	docker exec -i ${DB_HOST} mysql -u root -proot ${DB_DATABASE} < $$SQL_FILE_PATH; \
	echo "\033[0;32mDone\033[0m"

# ECS command
.PHONY: ecs
ecs:
	@docker exec ${PROJECT_NAME}_app vendor/bin/ecs check --fix

# TEST Start command
test:
	docker exec -it ${PROJECT_NAME}_app sh -c "php artisan test"

# TEST Start command filter
test-filter:
	@read -p "Enter the Test class: " TEST; \
	docker exec -it ${PROJECT_NAME}_app sh -c "php artisan test --filter $$TEST"

migrate:
	docker exec -it ${PROJECT_NAME}_app sh -c "php artisan migrate --env=testing && php artisan migrate"

generate-doc:
	docker exec -it ${PROJECT_NAME}_app sh -c "php artisan l5-swagger:generate"
