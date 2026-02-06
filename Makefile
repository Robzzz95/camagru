all: run

run: build
	docker compose -f docker-compose.yml up -d

down:
	docker compose -f docker-compose.yml down

stop:
	docker compose -f docker-compose.yml stop

build:
	docker compose -f docker-compose.yml build

url:
	@echo "=== URL de l'application ==="
	@echo "https://$(shell hostname -I | awk '{print $$1}'):8080"	

clean:
	@docker stop $$(docker ps -qa) || true
	@docker rm $$(docker ps -qa) || true
	@docker rmi -f $$(docker images -qa) || true
	@docker volume rm $$(docker volume ls -q) || true
	@docker network rm $$(docker network ls -q) || true

fclean: clean
	@docker system prune -af || true

re: fclean run

.PHONY: run down stop build clean fclean all